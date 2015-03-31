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

$system = xtc_get_system_information();
require (DIR_WS_INCLUDES.'head.php');
?>
<style type="text/css">
  #phpinfo table {
    border-collapse: collapse;
    width: 980px !important;
    font-family: sans-serif !important;
  }
  #phpinfo td {
    max-width: 400px !important;
    word-wrap: break-word !important;
  }
  #phpinfo td, #phpinfo th {
    font-size: 75% !important; 
    vertical-align: middle !important;
    padding: 5px 2px !important; 
  }
  #phpinfo hr {
    width: 100% !important;
  }
  #phpinfo .h {
    background-color: #ccc !important;
  }
  #phpinfo .e {
    width: 300px;
    background-color: #cdd7b3 !important; 
  }
  #phpinfo .v {
    background-color: #f2f2f2 !important; 
  }
  #phpinfo1 h1{
    background-color: #ccc !important;
  }
  #phpinfo h1 a{
    font-size: 100% !important; 
    font-weight:bold !important;
  }
  #phpinfo h2{
    background-color: #ccc !important;
    padding: 10px;
    margin-bottom:0!important;
    border: 1px solid #666;
    border-bottom: none;
    margin-top: 20px !important;
    width: 980px !important;
  }
  #phpinfo h2 a:hover{
    font-size: 100% !important;
    font-weight:bold !important;
    font-family: sans-serif !important;
  }
</style>
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
          <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_configuration.png'); ?></div>
          <div class="pageHeading pdg2 flt-l">
            <?php echo HEADING_TITLE; ?>       
            <div class="main pdg2"><?php echo HTTP_CATALOG_SERVER; ?></div>
          </div>
          <div class="clear pdg2"></div>
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
        </td>
      </tr>
    </table>
      
    <table style="margin:10px auto">
      <tr>
        <td id="phpinfo" >
          <?php
          if (function_exists('ob_start')) {
            ob_start();
            phpinfo();
            $phpinfo = ob_get_contents();
            ob_end_clean();

            //$phpinfo = str_replace('border: 1px', '', $phpinfo);
            preg_match("!<style type=\"text/css\">(.+?)</style>!s", $phpinfo, $regs);
            $regs[1] = str_replace("\n", "\n#phpinfo ", $regs[1]);
            $regs[1] = str_replace("#phpinfo body", "body #phpinfo", $regs[1]);
            $regs[1] .= '{}';
            echo '<style type="text/css">' . $regs[1] . '</style>';
            preg_match("!<body>(.+)</body>!s", $phpinfo, $regs);
            echo $regs[1];
          } else {
            phpinfo();
          }
          ?>
        </td>
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