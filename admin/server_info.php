<?php
  /* --------------------------------------------------------------
   $Id: server_info.php 4981 2013-06-26 02:39:47Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(server_info.php,v 1.4 2003/03/17); www.oscommerce.com
   (c) 2003 nextcommerce (server_info.php,v 1.7 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (server_info.php 899 2005-04-29)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

require('includes/application_top.php');

if (isset($_REQUEST['phpInfo'])) {
  phpinfo();
  exit;
}

$system = xtc_get_system_information();

require (DIR_WS_INCLUDES.'head.php');
?>

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
        
          <div class="pageHeading pgd2 mrg5"><?php echo HEADING_TITLE; ?></div>       
        
          <table class="tableCenter mrg5" style="width:900px">          
            <tr>
              <td class="smallText"><strong><?php echo TITLE_SERVER_HOST; ?></strong></td>
              <td class="smallText"><?php echo $system['host'] . ' (' . $system['ip'] . ')'; ?></td>
              <td class="smallText">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo TITLE_DATABASE_HOST; ?></strong></td>
              <td class="smallText"><?php echo $system['db_server'] . ' (' . $system['db_ip'] . ')'; ?></td>
            </tr>
            <tr>
              <td class="smallText"><strong><?php echo TITLE_SERVER_OS; ?></strong></td>
              <td class="smallText"><?php echo $system['system'] . ' ' . $system['kernel']; ?></td>
              <td class="smallText">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo TITLE_DATABASE; ?></strong></td>
              <td class="smallText"><?php echo $system['db_version']; ?></td>
            </tr>
            <tr>
              <td class="smallText"><strong><?php echo TITLE_SERVER_DATE; ?></strong></td>
              <td class="smallText"><?php echo $system['date']; ?></td>
              <td class="smallText">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo TITLE_DATABASE_DATE; ?></strong></td>
              <td class="smallText"><?php echo $system['db_date']; ?></td>
            </tr>
            <tr>
              <td class="smallText"><strong><?php echo TITLE_SERVER_UP_TIME; ?></strong></td>
              <td colspan="3" class="smallText"><?php echo $system['uptime']; ?></td>
            </tr>
            <tr>
              <td colspan="4"><?php echo xtc_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
            </tr>
            <tr>
              <td class="smallText"><strong><?php echo TITLE_HTTP_SERVER; ?></strong></td>
              <td colspan="3" class="smallText"><?php echo $system['http_server']; ?></td>
            </tr>
            <tr>
              <td class="smallText"><strong><?php echo TITLE_PHP_VERSION; ?></strong></td>
              <td colspan="3" class="smallText"><?php echo $system['php'] . ' (' . TITLE_ZEND_VERSION . ' ' . $system['zend'] . ')'; ?></td>
            </tr>
          </table>           
       
          <iframe src="<?php echo xtc_href_link(basename($PHP_SELF), 'phpInfo', 'NONSSL'); ?>" style="width:100%;height:700px;border:solid 1px #a3a3a3;">
          <p>Der verwendete Browser kann leider nicht mit inline Frames (iframe)
             umgehen:
             <a href="<?php echo xtc_href_link(basename($PHP_SELF), 'phpInfo', 'NONSSL'); ?>" target="_blank">Hier geht es zur phpinfo()
             Seite vom System</a>
          </p>
          </iframe>
        
        </td>
        <!-- body_text_eof //-->
      </tr>
    </table>
    <!-- body_eof //-->
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
    <br />
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
