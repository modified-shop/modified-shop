<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2011 (c) by  web28 - www.rpa-com.de

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  define ('VERSION', 'Database Backup Ver. 2.20 UTF-8');
  define ('_IS_FILEMANAGER', true);
  
  require('includes/application_top.php');
  
  // check permission
  if (is_file(DIR_FS_ADMIN.$current_page) == false || $_SESSION['customers_status']['customers_status_id'] !== '0') {
    xtc_redirect(xtc_catalog_href_link(FILENAME_LOGIN));
  }

  // verfiy CSRF Token
  if (defined('CSRF_TOKEN_SYSTEM') && CSRF_TOKEN_SYSTEM == 'true') {
    require_once(DIR_FS_INC . 'csrf_token.inc.php');
  }

  if (!isset($_SESSION['customer_id'])) {
    xtc_redirect(xtc_catalog_href_link(FILENAME_LOGIN));
  }

  $pagename = strtok($current_page, '.');
  xtc_check_permission($pagename);

  // config
  defined ('ANZAHL_ZEILEN_BKUP') or define ('ANZAHL_ZEILEN_BKUP', 5000);
  defined ('MAX_RELOADS') or define ('MAX_RELOADS', 100000000);
  defined ('RESTORE_TEST') or define ('RESTORE_TEST', false); 
  
  include ('includes/functions/db_functions.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  //Animierte Gif-Datei und Hinweistext
  $info_wait = '<img src="images/loading.gif"> '. TEXT_INFO_WAIT ;
  $button_back = '';

  //aktiviert die Ausgabepufferung
  if (!@ob_start("ob_gzhandler")) @ob_start();

  include ('includes/db_actions.php');

  require (DIR_WS_INCLUDES.'head.php');
?>
<link rel="stylesheet" type="text/css" href="includes/css/backup_db.css">
<script type="text/javascript">
  //Check if jQuery is loaded
  !window.jQuery && document.write('<script src="includes/javascript/jquery-1.8.3.min.js" type="text/javascript"><\/script>');
</script>
</head>
  <body>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <!-- body //-->
    <table class="tableBody">
      <tr>
      <?php //left_navigation
      if (USE_ADMIN_TOP_MENU == 'false') {
        echo '<td class="columnLeft2">'.PHP_EOL;
        echo '<!-- left_navigation //-->'.PHP_EOL;       
        require_once(DIR_WS_INCLUDES . 'column_left.php');
        echo '<!-- left_navigation eof //-->'.PHP_EOL; 
        echo '</td>'.PHP_EOL;      
      }
      ?>
      <!-- body_text //--> 
        <td class="boxCenter"> 
          <div class="pageHeading pdg2"><?php echo HEADING_TITLE; ?><span class="smallText"> [<?php echo VERSION; ?>]</span></div>
          <div class="main txta-c">
            <div id="info_text" class="pageHeading txta-c mrg10"><?php echo $info_text; ?></div>
            <div id="info_wait" class="pageHeading txta-c mrg10" style="margin-top:20px;"><?php echo $info_wait; ?></div>
            <div style="clear:both;"></div>
            <div class="process_wrapper">
                <div class="process_inner_wrapper">
                  <div id="backup_process"></div>
                </div>
                <div id="backup_precents">0%</div>
              </div>
            <div id="data_ok" class="main txta-c" style="margin-top:30px;"></div>
            <div id="button_back" class="main txta-c" style="margin-top:20px;"></div>
            <div id="button_log" class="main txta-c" style="margin-top:10px;"></div>
            <div style="clear:both"></div>
          </div>                 
        </td>
        <!-- body_text_eof //-->
      </tr>
    </table>
    <!-- body_eof //-->
    <?php
    require (DIR_WS_INCLUDES.'javascript/jquery.backup_db.js.php');
    ?>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
    <br />
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>