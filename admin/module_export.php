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

  if (!is_writeable(DIR_FS_CATALOG . 'export/')) {
    $messageStack->add(ERROR_EXPORT_FOLDER_NOT_WRITEABLE, 'error');
  }

  // set default file extension
  $file_extension = '.php';

  if (isset($_GET['error'])) {
    $map='error';
    if ($_GET['kind']=='success') $map='success';
    $messageStack->add($_GET['error'], $map);
  }

  $set = (isset($_GET['set']) ? strip_tags($_GET['set']) : '');
  $module_class = (isset($_GET['module']) ? strip_tags($_GET['module']) : '');
  $box = (isset($_GET['box']) ? true : false);

  if (xtc_not_null($set)) {
    switch ($set) {
      case 'system':
        $module_type = 'system';
        $module_directory = DIR_FS_ADMIN.DIR_WS_MODULES . 'system/';
        $module_directory_include = DIR_WS_ADMIN.DIR_WS_MODULES . 'system/';
        $module_key = 'MODULE_SYSTEM_INSTALLED';
        define('HEADING_TITLE', HEADING_TITLE_MODULES_SYSTEM);
        break;
      case 'export':
      default:
        $module_type = 'export';
        $module_directory = DIR_FS_ADMIN.DIR_WS_MODULES . 'export/';
        $module_directory_include = DIR_WS_ADMIN.DIR_WS_MODULES . 'export/';
        $module_key = 'MODULE_EXPORT_INSTALLED';
        define('HEADING_TITLE', HEADING_TITLE_MODULES_EXPORT);
        break;
    }
  }
  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (xtc_not_null($action)) {
    //load language file for action
    if (file_exists(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/' . $module_type . '/' . basename($module_class) . '.php')) {
      include_once(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/' . $module_type . '/' . basename($module_class) . '.php');
    }
    switch ($action) {
      //BOF NEW MODULE PROCESSING
      case 'module_processing_do':
        $class = basename($module_class);
        include($module_directory . $class . $file_extension);
        $module = new $class();
        $module->process($_GET['file']);
        $get_params = isset($module->get_params) ? $module->get_params : '';
        //convert params array to params string
        $params = convert_params_array_to_string($get_params);
        $modulelink = xtc_href_link(FILENAME_MODULE_EXPORT, $params);
        $recursive_call = isset($module->recursive_call) ? $module->recursive_call : '';
        $infotext = isset($module->infotext) ? $module->infotext : '';
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
        $class = basename($module_class);
        include($module_directory . $class . $file_extension);
        $module = new $class();
        //BOF NEW MODULE PROCESSING
        if (isset($_POST['process']) && $_POST['process'] == 'module_processing_do') {
          $get_params = isset($module->get_params) ? $module->get_params : array();
          //add post params to get params
          $post_params = isset($module->post_params) ? $module->post_params : array();
          reset($post_params);
          while(list($key, $pparam) = each($post_params)) {
            $get_params[$pparam] = $_POST[$pparam];
          }
          //convert params array to params string
          $params = convert_params_array_to_string($get_params);          
          if (trim($params) != '') {
            xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT,$params));
          } else {
            $messageStack->add(ERROR_PARAMETERS_NOT_SET, 'error');//PARAMETER ERROR
          }
        //EOF NEW MODULE PROCESSING
        } else {
          $module->process($file);
          xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $module_class));
        }
        break;

      case 'install':
      case 'update':
      case 'backupconfirm':
      case 'removeconfirm':
      case 'restoreconfirm':
      case 'custom':
        $class = basename($module_class);
        if (file_exists($module_directory . $class . $file_extension)) {
          include($module_directory . $class . $file_extension);
          $module = new $class();
          if ($action == 'install') {
            $module->install();
          } elseif ($action == 'removeconfirm') {
            $module->remove();
          } elseif ($action == 'update') {
            // update keys             
            $module->update();
            $messageStack->add_session(MODULE_UPDATE_CONFIRM, 'success');
          } elseif ($action == 'backupconfirm') {            
            // save values
            xtc_backup_configuration($module->keys());
            $messageStack->add_session(MODULE_BACKUP_CONFIRM, 'success');            
          } elseif ($action == 'restoreconfirm') {
            // reset backup values 
            xtc_restore_configuration($module->keys());
            $messageStack->add_session(MODULE_RESTORE_CONFIRM, 'success');
          } elseif ($action == 'custom') {
            // call custom method
            if (method_exists($module,'custom')) {
              $module->custom(); 
            }
          }

        }
        xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $module_class));
        break;
    }
  }

  //########## FUNCTIONS ##########//

  function get_module_info($module)
  {
      $module_info = array('code' => $module->code,
                           'title' => $module->title,
                           'description' => $module->description,
                           'extended_description' => isset($module->extended_description) ? $module->extended_description : '',
                           'status' => $module->check());
      $module_info['properties'] = isset($module->properties) ? $module->properties : array();
      $module_info['keys_dispnone'] = isset($module->keys_dispnone) ? $module->keys_dispnone : array();
      $module_keys = method_exists($module,'keys') ? $module->keys() : array();

      $keys_extra = array();
      for ($j = 0, $k = sizeof($module_keys); $j < $k; $j++) {
        $key_value_query = xtc_db_query("SELECT configuration_key,
                                                configuration_value,
                                                use_function,
                                                set_function
                                           FROM " . TABLE_CONFIGURATION . "
                                          WHERE configuration_key = '" . $module_keys[$j] . "'");
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
      return $module_info;
  }

  function create_directory_array($module_directory,$file_extension)
  {
      global $module;
      $directory_array = array(array());
      if ($dir = @dir($module_directory)) {
        while ($file = $dir->read()) {
          if (!is_dir($module_directory . $file)) {
            if (substr($file, strrpos($file, '.')) == $file_extension) {
              include_once($module_directory . $file);
              $class = substr($file, 0, strrpos($file, '.'));
              if (xtc_class_exists($class)) {
                $module = new $class();
              }
              if (method_exists($module,'check') && $module->check() > 0) {
                $directory_array[0][] = $file;
              } else {
                $directory_array[1][] = $file;
              }
              unset($module);
            }
          }
        }
        if (is_array($directory_array[0])) {
          ksort($directory_array[0]);
          foreach ($directory_array[0] as $key => $val){
            $directory_array[0][$key] = $val;
          }
          $directory_array[0] = array_values($directory_array[0]);
        }
        if (is_array($directory_array[1])) {
          sort($directory_array[1]);
        }
        ksort($directory_array);
        $dir->close();
      }
      return $directory_array;
  }
  
  function convert_params_array_to_string($params_array)
  {
    reset($params_array);
    $params = array();
    while(list($key, $value) = each($params_array)) {
      if (is_array($value)) {
        reset($value);
        while(list($key2, $value2) = each($value)) {
          $params[] = $key.'_'.strtolower($key2) .'='. $value2;
        }
      } else {
        $params[] = $key .'='. $value;
      }
    }
    $params_string = implode('&', $params);
    return $params_string;
  }


//########## OUTPUT ##########//
require (DIR_WS_INCLUDES.'head.php');
if (xtc_not_null($action) && !$box) {
  echo '<link href="includes/css/module_box_full.css" rel="stylesheet" type="text/css" />';
  if (file_exists('includes/css/'.basename($module_class).'.css')) {
    echo '<link href="includes/css/'.basename($module_class).'.css" rel="stylesheet" type="text/css" />';
  }
}
?>
</head>
<body>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <!-- body //-->
    <?php
    //BOF NEW MODULE PROCESSING
    echo isset($modulelink) ? xtc_draw_form('modul_continue',FILENAME_MODULE_EXPORT, $params, 'post') : '';
    
    echo isset($recursive_call) ? $recursive_call : '';
    //EOF NEW MODULE PROCESSING
    ?>
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
          <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_modules.png'); ?></div>
          <div class="pageHeading pdg2"><?php echo HEADING_TITLE; ?><br /></div>
          <div class="main">Modules</div>
          <?php if ($set == 'export' && !xtc_not_null($action)) { ?>
          <div style="clear:both;margin:10px 0;"><span class="main important_info"><?php echo TEXT_MODULE_INFO; ?></span></div>
          <?php } ?>
          <table class="tableCenter">
            <tr>
                <?php if(!xtc_not_null($action) || $box) { ?>
                    <td class="boxCenterLeft">
                      <table class="tableBoxCenter collapse">
                        <?php
                        $directory_array = create_directory_array($module_directory,$file_extension);
                        $installed_modules = array();
                        foreach ($directory_array as $directory_array) {
                          for ($i = 0, $n = sizeof($directory_array); $i < $n; $i++) {
                            $file = $directory_array[$i];
                            if (file_exists(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/' . $module_type . '/' . $file)) {
                              include_once(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/' . $module_type . '/' . $file);
                            }
                            include_once($module_directory . $file);
                            $class = substr($file, 0, strrpos($file, '.'));
                            if (xtc_class_exists($class)) {
                              $module = new $class();
                              if ($module->check() > 0) {
                                if (($module->sort_order > 0) && !isset($installed_modules[$module->sort_order])) {
                                  $installed_modules[$module->sort_order] = $file;
                                } else {
                                  $installed_modules[] = $file;
                                }
                              }
                              if ((!$module_class || (isset($module_class) && ($module_class == $class))) && !isset($mInfo)) {
                                $module_info = get_module_info($module);
                                $mInfo = new objectInfo($module_info);
                              }
                              if ($module->check() > 0 && !$installed) {
                                $installed = true;
                                ?>
                                <tr class="dataTableHeadingRow sub">
                                  <td colspan="3" class="dataTableHeadingContent txta-c" ><?php echo TABLE_HEADING_MODULES_INSTALLED; ?></td>
                                </tr>
                                <tr class="dataTableHeadingRow">
                                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODULES; ?></td>
                                  <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_STATUS; ?>&nbsp;</td>
                                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?> </td>
                                </tr>
                                <?php
                              } elseif ($module->check() < 1 && !$deinstalled && $installed) {
                                $deinstalled = true;
                                ?>
                                <tr><td colspan="3" style="height:35px;">&nbsp;</td></tr>
                                <tr class="dataTableHeadingRow sub">
                                  <td colspan="3" class="dataTableHeadingContent txta-c" ><?php echo TABLE_HEADING_MODULES_NOT_INSTALLED; ?></td>
                                </tr>
                                <tr class="dataTableHeadingRow">
                                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODULES; ?></td>
                                  <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_STATUS; ?>&nbsp;</td>
                                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?> </td>
                                </tr>
                                <?php
                              }

                              if (isset($mInfo) && is_object($mInfo) && ($class == $mInfo->code)) {
                                if ($module->check() > 0) {
                                  $tr_attribute = 'class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $class . '&action=edit') . '\'"';
                                } else {
                                  $tr_attribute = 'class="dataTableRowSelected"';
                                }
                              } else {
                                $tr_attribute = 'class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $class) . '\'"';
                              }
                                ?>
                                <tr <?php echo $tr_attribute;?>>
                                <td class="dataTableContent"><?php echo $module->title; ?></td>
                                <td class="dataTableContent  txta-c">
                                  <?php
                                    if ($module->check() > 0) {
                                      if (isset($module->enabled) && $module->enabled) {
                                        echo xtc_image(DIR_WS_IMAGES . 'icon_lager_green.gif', BUTTON_STATUS_ON);
                                      } else {
                                        echo xtc_image(DIR_WS_IMAGES . 'icon_lager_red.gif', BUTTON_STATUS_OFF);
                                      }
                                    }
                                  ?>
                                  &nbsp;
                                </td>
                                <td class="dataTableContent txta-r"><?php if (isset($mInfo) && is_object($mInfo) && ($class == $mInfo->code) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $class) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?> </td>
                              </tr>
                              <?php
                            }
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
                      </table>
                      <div class="smallText pdg2"><?php echo TEXT_MODULE_DIRECTORY . $module_directory_include; ?></div>
                    </td>
                    <?php
                }
                //BOC BOX RIGHT
                $heading = array();
                $contents = array();
                switch ($action) {
                  case 'module_processing_do':
                  case 'ready':
                  case 'edit':
                    if (isset($module_class) && !isset($mInfo)) {
                      $heading = array();
                      $contents = array();
                      $class = basename($module_class);
                      if (file_exists(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/' . $module_type . '/' . $class . '.php')) {
                        include_once(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/' . $module_type . '/' . $class . '.php');
                      }
                      include($module_directory . $class . '.php');
                      if (xtc_class_exists($class)) {
                        $module = new $class();
                        $module_info = get_module_info($module);
                        $mInfo = new objectInfo($module_info);
                      }
                    }
                    $keys = '';
                    
                    reset($mInfo->keys_dispnone);
                    while (list($key, $value) = each($mInfo->keys_dispnone)) {
                      unset($mInfo->keys[$value]);
                    }
                    
                    reset($mInfo->keys);
                    while (list($key, $value) = each($mInfo->keys)) {
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
                    $contents = array('form' => (isset($mInfo->properties['form_edit']) ? $mInfo->properties['form_edit'] : xtc_draw_form('modules', FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $mInfo->code . '&action=save','post')));
                    $contents[] = array('text' => $keys);
                    // display module fields
                    $contents[] = $module->display();                          
                    break;

                case 'restore':
                    $heading[] = array('text' => '<b>' . $mInfo->title . '</b>');
                    $contents = array ('form' => (isset($mInfo->properties['form_restore']) ? $mInfo->properties['form_restore'] : xtc_draw_form('modules', FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $module_class . '&action=restoreconfirm')));
                    $contents[] = array ('text' => '<br />'.TEXT_INFO_MODULE_RESTORE);
                    if (isset($mInfo->properties['restore']) && count($mInfo->properties['restore']) > 0) {
                      foreach($mInfo->properties['restore'] as $key) {
                        $contents[] = array ('text' => '<br />'.$key);
                      }
                    }
                    $contents[] = array ('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="'. BUTTON_RESTORE .'"><a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $module_class).'">' . BUTTON_CANCEL . '</a>');
                    break;
                case 'backup':
                    $heading[] = array('text' => '<b>' . $mInfo->title . '</b>');
                    $contents = array ('form' => (isset($mInfo->properties['form_backup']) ? $mInfo->properties['form_backup'] : xtc_draw_form('modules', FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $module_class . '&action=backupconfirm')));
                    $contents[] = array ('text' => '<br />'.TEXT_INFO_MODULE_BACKUP);
                    if (isset($mInfo->properties['backup']) && count($mInfo->properties['backup']) > 0) {
                      foreach($mInfo->properties['backup'] as $key) {
                        $contents[] = array ('text' => '<br />'.$key);
                      }
                    }
                    $contents[] = array ('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="'. BUTTON_BACKUP .'"><a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $module_class).'">' . BUTTON_CANCEL . '</a><br/><br/>');
                    break;
                case 'remove':
                    $heading[] = array('text' => '<b>' . $mInfo->title . '</b>');
                    $contents = array ('form' => (isset($mInfo->properties['form_remove']) ? $mInfo->properties['form_remove'] : xtc_draw_form('modules', FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $module_class . '&action=removeconfirm')));
                    $contents[] = array ('text' => '<br />'.TEXT_INFO_MODULE_REMOVE);
                    if (isset($mInfo->properties['remove']) && count($mInfo->properties['remove']) > 0) {
                      foreach($mInfo->properties['remove'] as $key) {
                        $contents[] = array ('text' => '<br />'.$key);
                      }
                    }
                    $contents[] = array ('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="'. BUTTON_MODULE_REMOVE .'"><a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $module_class).'">' . BUTTON_CANCEL . '</a><br/><br/>');
                    break;

                  default:
                    if (isset($mInfo) && is_object($mInfo)) {
                      $heading[] = array('text' => '<b>' . $mInfo->title . ($mInfo->status > 1 ? ' '.sprintf(MULTIPLE_INSTALLATION,$mInfo->status) : '') . '</b>');
                      if ($mInfo->status != '0') {
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
                        $btn_edit = isset($mInfo->properties['btn_edit']) && trim($mInfo->properties['btn_edit']) != '' ? $mInfo->properties['btn_edit'] : (($set == 'system') ? BUTTON_EDIT : BUTTON_START);
                        $keys = substr($keys, 0, strrpos($keys, '<br /><br />'));
                        $contents[] = array('align' => 'center', 'text' => (!isset($mInfo->properties['process_key']) || (isset($mInfo->properties['process_key']) && $mInfo->properties['process_key'] == 1)
                                                                             ? '<a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $mInfo->code . '&action=edit') . '">' . $btn_edit . '</a>'
                                                                             : '').
                                                                           (!isset($mInfo->properties['process_key']) || (isset($mInfo->properties['process_key']) && $mInfo->properties['process_key'] == 1)
                                                                             ?
                                                                           '<a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $mInfo->code . '&action=backup&box=1') . '">' . BUTTON_BACKUP . '</a>'.
                                                                           '<a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $mInfo->code . '&action=restore&box=1') . '">' . BUTTON_RESTORE . '</a>'
                                                                            : '').
                                                                           '<a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set . '&module=' . $mInfo->code . '&action=remove&box=1') . '">' . BUTTON_MODULE_REMOVE . '</a>'
                                            );
                        $contents[] = array('text' => '<br />' . $mInfo->description);
                        $contents[] = array('text' => '<br />' . $keys);
                      } else {
                        $contents[] = array('align' => 'center', 'text' => '<a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set. '&module=' . $mInfo->code . '&action=install') . '">' . BUTTON_MODULE_INSTALL . '</a>');
                        $contents[] = array('text' => '<br />' . $mInfo->description);
                      }
                    }                        
                    break;
                }

                if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
                  echo '            <td class="boxRight">' . PHP_EOL;
                  echo '<div class="modulbox">' . PHP_EOL;
                  $box = new box;
                   echo $box->infoBox($heading, $contents);
                  //BOF NEW MODULE PROCESSING
                  if ($action=='module_processing_do') {
                    echo $infotext;
                  }

                  if ($action=='ready') {
                    echo sprintf(MODULE_STEP_READY_STYLE_TEXT,(isset($module->ready_text) ? $module->ready_text : ''));
                    echo sprintf(MODULE_STEP_READY_STYLE_BACK,xtc_button_link(BUTTON_BACK, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $set . '&module='.$mInfo->code))) ;
                  }
                  //EOF NEW MODULE PROCESSING
                  echo '</div>' . PHP_EOL;      
                  echo '            </td>'  . PHP_EOL;
                }
                //EOC BOX RIGHT
                ?>
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