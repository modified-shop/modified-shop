<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified eCommerce Shopsoftware
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function update_module_configuration($module_type, $module_key) {
    $installed_modules = array();
    
    foreach(auto_include(DIR_FS_CATALOG.'includes/modules/'.$module_type.'/','php') as $file) {
      $filename = basename($file);
      $language_dir = defined('DIR_FS_LANGUAGES') ? DIR_FS_LANGUAGES : DIR_WS_LANGUAGES;
      
      if (is_file($language_dir . $_SESSION['language'] . '/modules/' . $module_type . '/' . $filename)) {
        include_once($language_dir . $_SESSION['language'] . '/modules/' . $module_type . '/' . $filename);
      }
      
      require_once($file);

      $class = substr(basename($file), 0, strpos(basename($file), '.'));
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
                   WHERE configuration_key = '" . $module_key . "'");

    return $installed_modules;
  }
  
  
  function get_module_configuration_sorting($installed_modules, $sort_order) {
    if (isset($installed_modules[(string)$sort_order])) {
      $sort_order += 0.0001;
      $sort_order = get_module_configuration_sorting($installed_modules, $sort_order);
    }
    
    return (string)$sort_order;
  }