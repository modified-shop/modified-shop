<?php
  /* -----------------------------------------------------------------------------------------
   $Id: module_export.php 2985 2012-06-07 13:38:44Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(modules.php,v 1.45 2003/05/28); www.oscommerce.com
   (c) 2003 nextcommerce (modules.php,v 1.23 2003/08/19); www.nextcommerce.org
   (c) 2006 xt:Commerce (module_export.php)
   --------------------------------------------------------------
   Contribution
   image_processing_step (step-by-step Variante B) by INSEH 2008-03-26
   image_processing_new_step (mit leeren Verzeichnissen step-by-step Variante C) by INSEH 2008-03-26
   image_processing_new_step2 (mit leeren Verzeichnissen step-by-step Variante D) by INSEH 2008-03-26

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application_top.php');

  // include needed functions (for modules)
  require_once(DIR_WS_FUNCTIONS . 'export_functions.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $set = (isset($_GET['set']) ? $_GET['set'] : '');

  if (!is_writeable(DIR_FS_CATALOG . 'export/')) {
    $messageStack->add(ERROR_EXPORT_FOLDER_NOT_WRITEABLE, 'error');
  }
  $module_type = 'export';
  $module_directory = DIR_WS_MODULES . 'export/';
  $module_key = 'MODULE_EXPORT_INSTALLED';
  $file_extension = '.php';
  define('HEADING_TITLE', HEADING_TITLE_MODULES_EXPORT);

  if (isset($_GET['error'])) {
    $map='error';
    if ($_GET['kind']=='success') $map='success';
    $messageStack->add($_GET['error'], $map);
  }
  if (xtc_not_null($action)) {
    switch ($action) {
      //BOF NEW MODULE PROCESSING
      case 'module_processing_do':
        $class = basename($_GET['module']);
        include($module_directory . $class . $file_extension);
        $module = new $class;
        $module->process($_GET['file'], $_GET['start']);
        $add_params = '';
        $link = xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set .
                                                      '&file='. $_GET['file'].
                                                      '&module='. $class.
                                                      '&start=' . $limit.
                                                      '&count='.$count.
                                                      '&action=module_processing_do'.
                                                      '&max='. $_GET['max'].
                                                      '&count_records='. $count_records.
                                                      '&miss='. $_GET['miss'].
                                                      $add_params
                     );
        break;
      //EOF NEW MODULE PROCESSING
      case 'save':
        if (is_array($_POST['configuration'])) {
          if (count($_POST['configuration'])) {
            while (list($key, $value) = each($_POST['configuration'])) {
              xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '" . $value . "' WHERE configuration_key = '" . $key . "'");
              if (@strpos($key,'FILE') !== false) $file=$value; //GTB - 2010-08-06 - start Download Problem PHP > 5.3
            }
          }
        }
        $class = basename($_GET['module']);
        include($module_directory . $class . $file_extension);
        //BOF NEW MODULE PROCESSING
        if (isset($_POST['process']) && $_POST['process'] == 'module_processing_do') {
          $add_params = '';
          xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set .
                                                             '&file='. $file.
                                                             '&module='. $class.
                                                             '&start=0'.
                                                             '&count='.$count.
                                                             '&action=module_processing_do'.
                                                             '&max='. $_POST['max_datasets'].
                                                             '&miss='. $_POST['only_missing_images'].
                                                             $add_params
                                    ));
        //EOF NEW MODULE PROCESSING
        } else {
          $module = new $class;
          $module->process($file);
          xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $class));
        }
        break;

      case 'install':
      case 'remove':
        $file_extension = substr(basename($_SERVER['SCRIPT_NAME']), strrpos(basename($_SERVER['SCRIPT_NAME']), '.'));
        $class = basename($_GET['module']);
        if (file_exists($module_directory . $class . $file_extension)) {
          include($module_directory . $class . $file_extension);
          $module = new $class;
          if ($action == 'install') {
            $module->install();
          } elseif ($action == 'remove') {
            $module->remove();
          }
        }
        xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $class));
        break;
    }
  }


  require (DIR_WS_INCLUDES.'head.php');
?>
</head>
<body>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <!-- body //-->
    <?php
    //BOF NEW MODULE PROCESSING
    echo isset($link) ? '<form name="modul_continue" action="'.$link.'" method="POST"></form>' :'';
    echo isset($selbstaufruf) ? $selbstaufruf :'';
    //EOF NEW MODULE PROCESSING
    ?>
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
              <td width="100%">
                <div style="float:left; width:80px;"><?php echo xtc_image(DIR_WS_ICONS.'heading_modules.gif'); ?></div>
                <div class="pageHeading"><?php echo HEADING_TITLE; ?><br /></div>
                <div class="main">Modules</div>
                <div style="clear:both;margin:10px 0 5px 0;"><span class="main" style="border: 1px red solid; padding:5px; background: #FFD6D6;"><?php echo TEXT_MODULE_INFO; ?></span></div>
              </td>
            </tr>
            <tr>
              <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td valign="top">
                      <table border="0" width="100%" cellspacing="0" cellpadding="2">
                        <tr class="dataTableHeadingRow">
                          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODULES; ?></td>
                          <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?> </td>
                        </tr>
                        <?php
                        $file_extension = substr(basename($_SERVER['SCRIPT_NAME']), strrpos(basename($_SERVER['SCRIPT_NAME']), '.'));
                        $directory_array = array();
                        if ($dir = @dir($module_directory)) {
                          while ($file = $dir->read()) {
                            if (!is_dir($module_directory . $file)) {
                              if (substr($file, strrpos($file, '.')) == $file_extension) {
                                $directory_array[] = $file;
                              }
                            }
                          }
                          sort($directory_array);
                          $dir->close();
                        }
                        $installed_modules = array();
                        for ($i = 0, $n = sizeof($directory_array); $i < $n; $i++) {
                          $file = $directory_array[$i];
                          include($module_directory . $file);
                          $class = substr($file, 0, strrpos($file, '.'));
                          if (xtc_class_exists($class)) {
                            $module = new $class;
                            if ($module->check() > 0) {
                              if ($module->sort_order > 0) {
                                $installed_modules[$module->sort_order] = $file;
                              } else {
                                $installed_modules[] = $file;
                              }
                            }
                            if ((!isset($_GET['module']) || (isset($_GET['module']) && ($_GET['module'] == $class))) && !isset($mInfo)) {
                              $module_info = array('code' => $module->code,
                                                   'title' => $module->title,
                                                   'description' => $module->description,
                                                   'status' => $module->check());
                              $module_keys = $module->keys();
                              $keys_extra = array();
                              for ($j = 0, $k = sizeof($module_keys); $j < $k; $j++) {
                                $key_value_query = xtc_db_query("select configuration_key,configuration_value, use_function, set_function from " . TABLE_CONFIGURATION . " where configuration_key = '" . $module_keys[$j] . "'");
                                $key_value = xtc_db_fetch_array($key_value_query);
                                if ($key_value['configuration_key'] !='') {
                                  $keys_extra[$module_keys[$j]]['title'] = constant(strtoupper($key_value['configuration_key'] .'_TITLE'));
                                }
                                $keys_extra[$module_keys[$j]]['value'] = $key_value['configuration_value'];
                                if ($key_value['configuration_key'] !='') {
                                  $keys_extra[$module_keys[$j]]['description'] = constant(strtoupper($key_value['configuration_key'] .'_DESC'));
                                }
                                $keys_extra[$module_keys[$j]]['use_function'] = $key_value['use_function'];
                                $keys_extra[$module_keys[$j]]['set_function'] = $key_value['set_function'];
                              }
                              $module_info['keys'] = $keys_extra;
                              $mInfo = new objectInfo($module_info);
                            }
                            if (isset($mInfo) && is_object($mInfo) && ($class == $mInfo->code)) {
                              if ($module->check() > 0) {
                                echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $class . '&action=edit') . '\'">' . "\n";
                              } else {
                                echo '              <tr class="dataTableRowSelected">' . "\n";
                              }
                            } else {
                              echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $class) . '\'">' . "\n";
                            }
                              ?>
                              <td class="dataTableContent"><?php echo $module->title; ?></td>
                              <td class="dataTableContent" align="right"><?php if (isset($mInfo) && is_object($mInfo) && ($class == $mInfo->code) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $class) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?> </td>
                            </tr>
                            <?php
                          }
                        }
                        ksort($installed_modules);
                        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = '" . $module_key . "'");
                        if (xtc_db_num_rows($check_query)) {
                          $check = xtc_db_fetch_array($check_query);
                          if ($check['configuration_value'] != implode(';', $installed_modules)) {
                            xtc_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . implode(';', $installed_modules) . "', last_modified = now() where configuration_key = '" . $module_key . "'");
                          }
                        } else {
                          xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ( '" . $module_key . "', '" . implode(';', $installed_modules) . "','6', '0', now())");
                        }
                        ?>
                        <tr>
                          <td colspan="3" class="smallText"><?php echo TEXT_MODULE_DIRECTORY . ' admin/' . $module_directory; ?></td>
                        </tr>
                      </table>
                    </td>
                    <?php
                    $heading = array();
                    $contents = array();
                    switch ($action) {
                      case 'edit':
                        $keys = '';
                        reset($mInfo->keys);
                        while (list($key, $value) = each($mInfo->keys)) {
                          // if($value['description']!='_DESC' && $value['title']!='_TITLE'){
                          $keys .= '<b>' . $value['title'] . '</b><br />' .  $value['description'].'<br />';
                          // }
                          if ($value['set_function']) {
                            eval('$keys .= ' . $value['set_function'] . "'" . $value['value'] . "', '" . $key . "');");
                          } else {
                            $keys .= xtc_draw_input_field('configuration[' . $key . ']', $value['value']);
                          }
                          $keys .= '<br /><br />';
                        }
                        $keys = substr($keys, 0, strrpos($keys, '<br /><br />'));
                        $heading[] = array('text' => '<b>' . $mInfo->title . '</b>');
                        $class = substr($file, 0, strrpos($file, '.'));
                        $module = new $_GET['module'];
                        $contents = array('form' => xtc_draw_form('modules', FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $_GET['module'] . '&action=save','post'));
                        $contents[] = array('text' => $keys);
                        // display module fields
                        $contents[] = $module->display();
                        break;

                      default:
                        $heading[] = array('text' => '<b>' . $mInfo->title . '</b>');
                        if ($mInfo->status == '1') {
                          $keys = '';
                          reset($mInfo->keys);
                          while (list(, $value) = each($mInfo->keys)) {
                            $keys .= '<b>' . $value['title'] . '</b><br />';
                            if ($value['use_function']) {
                              $use_function = $value['use_function'];
                              if (strpos($use_function, '->') !== false) {
                                $class_method = explode('->', $use_function);
                                if (!is_object(${$class_method[0]})) {
                                  include(DIR_WS_CLASSES . $class_method[0] . '.php');
                                  ${$class_method[0]} = new $class_method[0]();
                                }
                                $keys .= xtc_call_function($class_method[1], $value['value'], ${$class_method[0]});
                              } else {
                                $keys .= xtc_call_function($use_function, $value['value']);
                              }
                            } else {
                              $keys .=  (strlen($value['value']) > 30) ? substr($value['value'],0,30) . ' ...' : $value['value'];
                            }
                            $keys .= '<br /><br />';
                          }
                          $keys = substr($keys, 0, strrpos($keys, '<br /><br />'));
                          $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $mInfo->code . '&action=remove') . '">' . BUTTON_MODULE_REMOVE . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $mInfo->code . '&action=edit') . '">' . BUTTON_START . '</a>');
                          $contents[] = array('text' => '<br />' . $mInfo->description);
                          $contents[] = array('text' => '<br />' . $keys);
                        } else {
                          $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set. '&module=' . $mInfo->code . '&action=install') . '">' . BUTTON_MODULE_INSTALL . '</a>');
                          $contents[] = array('text' => '<br />' . $mInfo->description);
                        }
                        break;
                    }

                    if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
                      echo '            <td width="25%" valign="top">' . "\n";
                      echo box::infoBoxSt($heading, $contents); // cYbercOsmOnauT - 2011-02-07 - Changed methods of the classes box and tableBox to static
                      //BOF NEW MODULE PROCESSING
                      if ($_GET['action']=='module_processing_do') {
                        echo $infotext;
                      }
                      if (isset($_GET['infotext'])) {
                        echo '<div style="margin:10px; font-family:Verdana; font-size:15px; text-align:center;">'. nl2br(strip_tags(urldecode($_GET['infotext']))) .'</div>';
                      }
                      //EOF NEW MODULE PROCESSING
                      echo '            </td>' . "\n";
                    }
                    ?>
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