<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // include needed functions
  require_once(DIR_FS_INC.'get_database_version.inc.php');
  
  // include needed classes
  require_once (DIR_FS_CATALOG.'includes/classes/modified_api.php');

  function check_version_update($cache = true) {
    global $PHP_SELF;
    
    $filename = SQL_CACHEDIR.'version.cache';
  
    $version = PROJECT_VERSION;
    if (!defined('RUN_MODE_ADMIN')) {
      require_once(DIR_FS_CATALOG.DIR_ADMIN.'includes/version.php');
    }

    if (!is_file($filename)
        || (filemtime($filename) + 86400) < time()
        || $cache === false
        )
    {
      $contents = array();
      $contents['total'] = 0;
      $contents['version_installed'] = $version;
      
      modified_api::reset();
      $response = modified_api::request('modified/version');
      
      $contents['version'] = $response['stable'];
      $contents['update'] = version_compare($contents['version'], $contents['version'], '>');

      $dbversion = get_database_version();
      $response = modified_api::request('modified/version/install/');

      $details = array(
        'Shop' => array(
          'shop' => array(
            'title' => 'Shopversion',
            'version' => $response['version'],
            'shop' => PROJECT_VERSION_NO,
            'link' => version_compare($response['version'], PROJECT_VERSION_NO, '>') ? xtc_href_link(basename($PHP_SELF), 'action=autoupdate') : '',
            'installed' => 1,
            'update' => version_compare($response['version'], PROJECT_VERSION_NO, '>')
          ),
          'db' => array(
            'title' => 'Database',
            'version' => $response['version'],
            'shop' => $dbversion['plain'],
            'link' => '',
            'installed' => 1,
            'update' => version_compare($response['version'], $dbversion['plain'], '>')
          ),
        ),
      );  
      
      defined('_VALID_XTC') OR define('_VALID_XTC', true);
      
      modified_api::reset();
      $modules_data = modified_api::request('modified/version/modules/');
      
      foreach ($modules_data as $heading => $modules) {
        foreach ($modules as $module => $data) {
          $details[$heading][$module] = $data;
          $data['path'] = str_replace('DIR_ADMIN/', DIR_ADMIN, $data['path']);
          $data['lang'] = str_replace('DIR_LANG', $_SESSION['language'], $data['lang']);
          
          if (is_file(DIR_FS_CATALOG.$data['path'])) {
            if ($data['lang'] != '' && is_file(DIR_FS_CATALOG.$data['lang'])) {
              require_once(DIR_FS_CATALOG.$data['lang']);
            }
  
            require_once(DIR_FS_CATALOG.$data['path']);
            ${$module} = new $data['class']($data['module']);
  
            if (array_key_exists($data['variable'], get_object_vars(${$module}))) {
              $details[$heading][$module]['shop'] = ${$module}->{$data['variable']};
            }
  
            if ($data['regex'] != '') {
              $details[$heading][$module]['shop'] = preg_replace($data['regex'], '', $details[$heading][$module]['shop']);
            }
            
            $check_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_CONFIGURATION."
                                          WHERE configuration_key LIKE '".xtc_db_input($data['key'])."'");
            if (xtc_db_num_rows($check_query) > 0) {
              $details[$heading][$module]['installed'] = 2;
              while ($check = xtc_db_fetch_array($check_query)) {
                if (strtolower($check['configuration_value']) == 'true') {
                  $details[$heading][$module]['installed'] = 1;
                  break;
                }
              }
            }
          }
    
          $details[$heading][$module]['update'] = version_compare($data['version'], $details[$heading][$module]['shop'], '>');
        }
      }

      foreach ($details as $heading => $modules) {
        foreach ($modules as $module => $data) {
          if ($data['update'] == true && (int)$data['installed'] > 0) {
            $contents['total'] ++;
          }
        }
      }
  
      $contents['details'] = $details;
      
      file_put_contents($filename, json_encode($contents));
    } else {
      $contents = json_decode(file_get_contents($filename), true);
    }
    
    return $contents;
  }
?>