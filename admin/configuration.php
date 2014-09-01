<?php
/* --------------------------------------------------------------
   $Id: configuration.php 3569 2012-08-30 15:39:18Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(configuration.php,v 1.40 2002/12/29); www.oscommerce.com
   (c) 2003 nextcommerce (configuration.php,v 1.16 2003/08/19); www.nextcommerce.org
   (c) 2006 XT-Commerce (configuration.php 229 2007-03-06)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application_top.php');

  //install new configurations
  if (file_exists(DIR_WS_INCLUDES.'configuration_installer.php')) {
    include(DIR_WS_INCLUDES.'configuration_installer.php');
  }
  //set value_limits
  $value_limits = array(); 
  if (file_exists(DIR_WS_INCLUDES.'configuration_limits.php')) {
    include(DIR_WS_INCLUDES.'configuration_limits.php');
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (xtc_not_null($action)) {
    switch ($action) {
      case 'save':
        // moneybookers payment module version 2.4
        if ($_GET['gID']=='31') {
          if (isset($_POST['_PAYMENT_MONEYBOOKERS_EMAILID'])) {
          $url = 'https://www.moneybookers.com/app/email_check.pl?email=' . urlencode($_POST['_PAYMENT_MONEYBOOKERS_EMAILID']) . '&cust_id=8644877&password=1a28e429ac2fcd036aa7d789ebbfb3b0';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $result = curl_exec($ch);
            if ($result=='NOK') {
              $messageStack->add_session(MB_ERROR_NO_MERCHANT, 'error');
            }
            if (strstr($result,'OK,')) {
              $data = explode(',',$result);
              $_POST['_PAYMENT_MONEYBOOKERS_MERCHANTID'] = $data[1];
              $messageStack->add_session(sprintf(MB_MERCHANT_OK,$data[1]), 'success');
            }
          }
        }

        // update changed configurations
        $configuration_query = xtc_db_query("select configuration_key,configuration_id, configuration_value, use_function,set_function from " . TABLE_CONFIGURATION . " where configuration_group_id = '" . (int)$_GET['gID'] . "' order by sort_order");
        while ($configuration = xtc_db_fetch_array($configuration_query)) {
          $configuration['configuration_value'] = stripslashes($configuration['configuration_value']);
          if ($_POST[$configuration['configuration_key']] != $configuration['configuration_value']) {
            //value_limits min
            if (isset($value_limits[$configuration['configuration_key']]['min']) && preg_match ("/^([0-9]+)$/", $_POST[$configuration['configuration_key']]) &&  (int)$_POST[$configuration['configuration_key']] < $value_limits[$configuration['configuration_key']]['min']) {
              $configuration_key_title = constant(strtoupper($configuration['configuration_key'].'_TITLE'));
              $messageStack->add_session(sprintf(CONFIG_MIN_VALUE_WARNING,$configuration_key_title,$_POST[$configuration['configuration_key']],$value_limits[$configuration['configuration_key']]['min'] ), 'warning');
              $_POST[$configuration['configuration_key']] = (int)$configuration['configuration_value'];
            }
            //value_limits max
            if (isset($value_limits[$configuration['configuration_key']]['max']) && preg_match ("/^([0-9]+)$/", $_POST[$configuration['configuration_key']]) &&  (int)$_POST[$configuration['configuration_key']] > $value_limits[$configuration['configuration_key']]['max']) {
              $configuration_key_title = constant(strtoupper($configuration['configuration_key'].'_TITLE'));
              $messageStack->add_session(sprintf(CONFIG_MAX_VALUE_WARNING,$configuration_key_title,$_POST[$configuration['configuration_key']],$value_limits[$configuration['configuration_key']]['max'] ), 'warning');
              $_POST[$configuration['configuration_key']] = (int)$configuration['configuration_value'];
            }
            //check numeric input
            if (!preg_match ("/^([0-9]+)$/", $_POST[$configuration['configuration_key']]) && (isset($value_limits[$configuration['configuration_key']]['min']) || isset($value_limits[$configuration['configuration_key']]['max']))) {
              $_POST[$configuration['configuration_key']] = (int)$configuration['configuration_value'];
              $configuration_key_title = constant(strtoupper($configuration['configuration_key'].'_TITLE'));
              $messageStack->add_session(sprintf(CONFIG_INT_VALUE_ERROR,$configuration_key_title,$_POST[$configuration['configuration_key']],''), 'error');
            }
            xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value='" . xtc_db_input($_POST[$configuration['configuration_key']]) . "', last_modified = NOW() where configuration_key='" . $configuration['configuration_key'] . "'");
            // load template config install/uninstall if exist
            if ($configuration['configuration_key'] == 'CURRENT_TEMPLATE') {
              $template_dir = DIR_FS_CATALOG.'templates/';
              if (file_exists($template_dir.$_POST[$configuration['configuration_key']].'/source/tmpl_config_install.php')) {
                include($template_dir.$_POST[$configuration['configuration_key']].'/source/tmpl_config_install.php');
              }
              if (file_exists($template_dir.$configuration['configuration_value'].'/source/tmpl_config_uninstall.php')) {
                include($template_dir.$configuration['configuration_value'].'/source/tmpl_config_uninstall.php');
              }
            }
          }
        }

        // DB Cache System [If Cache deactivated.. clean all cachefiles]
        if (isset($_POST['DB_CACHE']) && $_POST['DB_CACHE'] == 'false') {
          $handle = opendir(SQL_CACHEDIR);
          while (($file = readdir($handle)) !== false) {
            // Jump over files that are no sql-cache
            if (strpos($file, 'sql_') !== 0) continue;
            @unlink(SQL_CACHEDIR.$file);
          }
        }

        xtc_redirect(xtc_href_link(FILENAME_CONFIGURATION, 'gID=' . (int)$_GET['gID']));
        break;

      case 'delcache':
        $path = DIR_FS_CATALOG.'cache/';
        if ($dir = opendir($path)) {
          while (($file = readdir($dir)) !== false) {
            if (is_file($path.$file) && $file != "index.html" && $file != ".htaccess") {
              unlink($path.$file);
            }
          }
          closedir($dir);
        }
        $messageStack->add_session(DELETE_CACHE_SUCCESSFUL, 'success');
        xtc_redirect(xtc_href_link(FILENAME_CONFIGURATION, 'gID=' . (int)$_GET['gID']));
        break;

      case 'deltempcache':
        $path = DIR_FS_CATALOG.'templates_c/';
        if ($dir = opendir($path)) {
          while (($file = readdir($dir)) !== false) {
            if (is_file($path.$file) && $file != "index.html" && $file != ".htaccess") {
              unlink($path.$file);
            }
          }
          closedir($dir);
        }
        $messageStack->add_session(DELETE_TEMP_CACHE_SUCCESSFUL, 'success');
        xtc_redirect(xtc_href_link(FILENAME_CONFIGURATION, 'gID=' . (int)$_GET['gID']));
        break;
    }
  }

  $cfg_group_query = xtc_db_query("select configuration_group_title, configuration_group_id from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_id = '" . (int)$_GET['gID'] . "'"); // Hetfield - 2010-01-15 - multilanguage title in configuration
  $cfg_group = xtc_db_fetch_array($cfg_group_query);
  
  require (DIR_WS_INCLUDES.'head.php');
?>
    <script type="text/javascript" src="includes/general.js"></script>
  </head>
  <body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onLoad="SetFocus();">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <!-- body //-->
    <table border="0" width="100%" cellspacing="2" cellpadding="2">
      <tr>
        <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
          <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
            <!-- left_navigation //-->
            <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
            <!-- left_navigation_eof //-->
          </table>
        </td>
        <!-- body_text //-->
        <td class="boxCenter" width="100%" valign="top">
          <table border="0" width="100%" cellspacing="0" cellpadding="2">
            <tr>
              <td>
                <table border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td width="80" rowspan="2"><?php echo xtc_image(DIR_WS_ICONS.'heading_configuration.gif'); ?></td>
                    <td width="300" class="pageHeading">
                      <?php
                        $box_conf_gid = 'BOX_CONFIGURATION_'.$cfg_group['configuration_group_id'];
                        echo (defined($box_conf_gid) && constant($box_conf_gid) != '' ? constant($box_conf_gid) : $cfg_group['configuration_group_title']);
                      ?>
                    </td>
                    <td rowspan="2" class="pageHeading">
                      <?php
                        if ($_GET['gID']==11) { // delete cache files in admin section
                          echo xtc_draw_form('configuration', FILENAME_CONFIGURATION, 'gID=' . (int)$_GET['gID'] . '&action=delcache');
                          echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE_CACHE . '"/></form> ';
                          echo xtc_draw_form('configuration', FILENAME_CONFIGURATION, 'gID=' . (int)$_GET['gID'] . '&action=deltempcache');
                          echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE_TEMP_CACHE . '"/></form>';
                        }
                      ?>
                    </td>
                  </tr>
                  <tr>
                    <td class="main" valign="top">Configuration</td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td style="border-top: 3px solid; border-color: #cccccc;" class="main">
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <?php
                    switch ($_GET['gID']) {
                      case 21:
                        echo AFTERBUY_URL;
                      case 19: // Google Conversion-Tracking
                      case 111125: // Paypal Express Modul
                      case 31: // moneybookers payment module version 2.4 & paypal payment module                        
                        echo '<table class="infoBoxHeading" width="100%">
                                <tr>
                                  <td width="150" align="center">
                                    <a class="button" href="'.xtc_href_link(FILENAME_CONFIGURATION, 'gID=21', 'NONSSL').'">Afterbuy</a>
                                  </td>
                                  <td width="1">|</td>
                                  <td width="150" align="center">
                                    <a class="button" href="'.xtc_href_link(FILENAME_CONFIGURATION, 'gID=19', 'NONSSL').'">Google Conversion</a>
                                  </td>
                                  <td width="1">|</td>
                                  <td width="150" align="center">
                                    <a class="button" class="button" href="'.xtc_href_link(FILENAME_CONFIGURATION, 'gID=111125', 'NONSSL').'">PayPal</a>
                                  </td>
                                  <td width="1">|</td>
                                  <td width="150" align="center">
                                    <a class="button" href="'.xtc_href_link(FILENAME_CONFIGURATION, 'gID=31', 'NONSSL').'">Moneybookers.com</a>
                                  </td>
                                  <td width="1">|</td>
                                  <td></td>
                                </tr>
                              </table>';
                        if ($_GET['gID']=='31')
                          echo MB_INFO;
                        break;
                    }
                  ?>
                  <tr>
                    <td valign="top" align="right">
                      <?php echo xtc_draw_form('configuration', FILENAME_CONFIGURATION, 'gID=' . (int)$_GET['gID'] . '&action=save'); ?>
                        <table width="100%"  border="0" cellspacing="0" cellpadding="8">
                          <?php
                            $configuration_query = xtc_db_query("select configuration_key,configuration_id, configuration_value, use_function,set_function from " . TABLE_CONFIGURATION . " where configuration_group_id = '" . (int)$_GET['gID'] . "' order by sort_order");
                            while ($configuration = xtc_db_fetch_array($configuration_query)) {
                              $configuration['configuration_value'] = stripslashes($configuration['configuration_value']); //Web28 - 2012-08-09 - fix slashes
                              if ($_GET['gID'] == 6) {
                                switch ($configuration['configuration_key']) {
                                  case 'MODULE_PAYMENT_INSTALLED':
                                    if ($configuration['configuration_value'] != '') {
                                      $payment_installed = explode(';', $configuration['configuration_value']);
                                      for ($i = 0, $n = sizeof($payment_installed); $i < $n; $i++) {
                                        include(DIR_WS_LANGUAGES . $language . '/modules/payment/' . $payment_installed[$i]); //DokuMan - 2012-06-30 - replace DIR_FS_CATALOG_LANGUAGES with DIR_WS_LANGUAGES
                                      }
                                    }
                                    break;
                                  case 'MODULE_SHIPPING_INSTALLED':
                                    if ($configuration['configuration_value'] != '') {
                                      $shipping_installed = explode(';', $configuration['configuration_value']);
                                      for ($i = 0, $n = sizeof($shipping_installed); $i < $n; $i++) {
                                        include(DIR_WS_LANGUAGES . $language . '/modules/shipping/' . $shipping_installed[$i]); //DokuMan - 2012-06-30 - replace DIR_FS_CATALOG_LANGUAGES with DIR_WS_LANGUAGES
                                      }
                                    }
                                    break;
                                  case 'MODULE_ORDER_TOTAL_INSTALLED':
                                    if ($configuration['configuration_value'] != '') {
                                      $ot_installed = explode(';', $configuration['configuration_value']);
                                      for ($i = 0, $n = sizeof($ot_installed); $i < $n; $i++) {
                                        include(DIR_WS_LANGUAGES . $language . '/modules/order_total/' . $ot_installed[$i]); //DokuMan - 2012-06-30 - replace DIR_FS_CATALOG_LANGUAGES with DIR_WS_LANGUAGES
                                      }
                                    }
                                    break;
                                }
                              }
                              if (xtc_not_null($configuration['use_function'])) {
                                $use_function = $configuration['use_function'];
                                if (preg_match('/->/', $use_function)) { // Hetfield - 2009-08-19 - replaced deprecated function ereg with preg_match to be ready for PHP >= 5.3
                                  $class_method = explode('->', $use_function);
                                  if (!is_object(${$class_method[0]})) {
                                    include(DIR_WS_CLASSES . $class_method[0] . '.php');
                                    ${$class_method[0]} = new $class_method[0]();
                                  }
                                  $cfgValue = xtc_call_function($class_method[1], $configuration['configuration_value'], ${$class_method[0]});
                                } else {
                                  $cfgValue = xtc_call_function($use_function, $configuration['configuration_value']);
                                }
                              } else {
                                $cfgValue = $configuration['configuration_value'];
                              }
                              if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $configuration['configuration_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
                                $cfg_extra_query = xtc_db_query("select configuration_key,configuration_value, date_added, last_modified, use_function, set_function from " . TABLE_CONFIGURATION . " where configuration_id = '" . $configuration['configuration_id'] . "'");
                                $cfg_extra = xtc_db_fetch_array($cfg_extra_query);
                                $cInfo_array = xtc_array_merge($configuration, $cfg_extra);
                                $cInfo = new objectInfo($cInfo_array);
                              }
                              if ($configuration['set_function']) {
                                eval('$value_field = ' . $configuration['set_function'] . '"' . encode_htmlspecialchars($configuration['configuration_value']) . '");');
                              } else {
                                if ( $configuration['configuration_key'] == 'SMTP_PASSWORD') {
                                  $value_field = xtc_draw_password_field($configuration['configuration_key'], $configuration['configuration_value']);
                                } else {
                                  $value_field = xtc_draw_input_field($configuration['configuration_key'], $configuration['configuration_value'],'style="width:380px;"');
                                }
                              }
                              if (strstr($value_field,'configuration_value')) {
                                $value_field=str_replace('configuration_value',$configuration['configuration_key'],$value_field);
                              }

                              // catch up warnings if no language-text defined for configuration-key
                              $configuration_key_title = strtoupper($configuration['configuration_key'].'_TITLE');
                              $configuration_key_desc  = strtoupper($configuration['configuration_key'].'_DESC');
                              if( defined($configuration_key_title) ) {                                          // if language definition
                                $configuration_key_title = constant($configuration_key_title);
                                $configuration_key_desc  = constant($configuration_key_desc);
                              } else {                                                                          // if no language
                                $configuration_key_title = $configuration['configuration_key'];                 // name = key
                                $configuration_key_desc  = '&nbsp;';                                            // description = empty
                              }
                              if ($configuration_key_desc!=str_replace("<meta ","",$configuration_key_desc)) {
                                $configuration_key_desc = encode_htmlentities($configuration_key_desc);
                              }
                              echo '
                                    <tr>
                                      <td style="min-width:20%; border-bottom: 1px solid #aaaaaa;" class="dataTableContent"><b>'.$configuration_key_title.'</b></td>
                                      <td style="min-width:20%; border-bottom: 1px solid #aaaaaa; background-color:#e8e8e8;" class="dataTableContent">'.$value_field.'</td>
                                      <td style="min-width:60%; border-bottom: 1px solid #aaaaaa;empty-cells: show;" class="dataTableContent">'.$configuration_key_desc.'</td>
                                    </tr>
                                   ';

                            }
                          ?>
                        </table>
                        <?php echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>'; ?>
                      </form>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
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