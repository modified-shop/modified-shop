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
  .dataTableContent a { float:right; }
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
          <table class="tableCenter">          
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
          <br/>          
          <?php 
          ob_start();
          phpinfo();
          $phpinfo = array('PHP Info' => array());
          if (preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER)){
            foreach($matches as $match){
              if (strlen($match[1])) {
                $phpinfo[$match[1]] = array();
              } elseif (isset($match[3])) {
                $keys1 = array_keys($phpinfo);
                $phpinfo[end($keys1)][$match[2]] = isset($match[4]) ? array($match[3], $match[4]) : $match[3];
              } else {
                $keys1 = array_keys($phpinfo);
                $phpinfo[end($keys1)][] = $match[2];      
              }
            }
          }
          echo '<table class="tableCenter">';
          if (count($phpinfo) > 1) {
            $first = true;
            foreach($phpinfo as $name => $section) {
              if ($first === false) {
                echo '<tr class="dataTableRow"><td colspan="3" class="dataTableContent" style="height:30px;"></td></tr>';
              }
              echo '<tr class="dataTableHeadingRow">
                      <td colspan="3" class="dataTableHeadingContent">'.$name.'</td>
                    </tr>';

              foreach($section as $key => $val){
                if(is_array($val)){
                  echo '<tr class="dataTableRow'.((strtolower($key) == 'directive') ? 'Over' : '').'">
                          <td class="dataTableContent" style="border-right: 1px solid #aaa;">'.$key.'</td>
                          <td class="dataTableContent" style="border-right: 1px solid #aaa;">'.$val[0].'</td>
                          <td class="dataTableContent">'.$val[1].'</td>
                        </tr>';                  
                } elseif (is_string($key)) {
                  echo '<tr class="dataTableRow">
                          <td class="dataTableContent" style="border-right: 1px solid #aaa;">'.$key.'</td>
                          <td colspan="2" class="dataTableContent">'.$val.'</td>
                        </tr>';
                } else {
                  echo '<tr class="dataTableRow">
                          <td colspan="3" class="dataTableContent">'.$val.'</td>
                        </tr>';
                }
              }
              $first = false;
            }
          } else {
            echo '<tr class="dataTableHeadingRow">
                    <td colspan="3" class="dataTableHeadingContent">PHP Info</td>
                  </tr>';
            echo '<tr class="dataTableRow">
                    <td colspan="3" class="dataTableContent">Sorry, the phpinfo() function is not accessable. Perhaps, it is disabled <a href="http://php.net/manual/en/function.phpinfo.php">See the documentation.</a></td>
                  </tr>';
          }
          echo '</table>';  
          ?>
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