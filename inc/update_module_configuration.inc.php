<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified eCommerce Shopsoftware
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function update_module_configuration($module_type) {
    $installed_modules = array();
    
    $language_dir = defined('DIR_FS_LANGUAGES') ? DIR_FS_LANGUAGES : DIR_WS_LANGUAGES;
    
    switch ($module_type) {
      case 'system':
      case 'export':
      case 'categories':
        $module_dir = DIR_FS_CATALOG.DIR_ADMIN.'includes/modules/';
        break;
        
      default:
        $module_dir = DIR_FS_CATALOG.'includes/modules/';
        break;
    }
    
    foreach(auto_include($module_dir.$module_type.'/','php') as $file) {
      $filename = basename($file);
      
      if (is_file($language_dir . $_SESSION['language'] . '/modules/' . $module_type . '/' . $filename)) {
        include_once($language_dir . $_SESSION['language'] . '/modules/' . $module_type . '/' . $filename);
      }
      
      require_once($file);

      $class = substr($filename, 0, strpos($filename, '.'));
      if (class_exists($class)) {
        $module = new $class();
        
        if (method_exists($module,'check')) {
          if ($module instanceof $class && $module->check() > 0) {
            if (!isset($module->sort_order) || !is_numeric($module->sort_order)) {
              $module->sort_order = 0;
            }
            if ($module->check() > 0) {
              $installed_modules[get_module_configuration_sorting($installed_modules, $module->sort_order)] = $filename;
            }
          }
        }              
      }
    }
    
    ksort($installed_modules);
    $installed_modules = array_values($installed_modules);
        
    xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " 
                     SET configuration_value = '" . implode(';', $installed_modules) . "', 
                         last_modified = now() 
                   WHERE configuration_key = 'MODULE_" . strtoupper($module_type) . "_INSTALLED'");

    return $installed_modules;
  }
  
  
  function get_module_configuration_sorting($installed_modules, $sort_order) {
    if (isset($installed_modules[(string)$sort_order])) {
      $sort_order += 0.0001;
      $sort_order = get_module_configuration_sorting($installed_modules, $sort_order);
    }
    
    return (string)$sort_order;
  }