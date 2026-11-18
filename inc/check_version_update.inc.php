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
  require_once(DIR_FS_INC.'filter_external_html.inc.php');
  
  // include needed classes
  require_once (DIR_FS_CATALOG.'includes/classes/modified_api.php');

  /**
   * check_version_update_module()
   *
   * @param mixed $data module entry of the version endpoint
   * @return array|false the entry, false if it does not have the expected form
   */
  function check_version_update_module($data) {
    if (!is_array($data)) {
      return false;
    }

    foreach (array('title', 'version', 'class', 'path', 'variable', 'module', 'lang', 'regex', 'key', 'link') as $key) {
      if (!isset($data[$key]) || !is_string($data[$key])) {
        return false;
      }
    }

    foreach (array('path2', 'class2') as $key) {
      if (isset($data[$key]) && !is_string($data[$key])) {
        return false;
      }
    }

    // the second pair replaces the first one
    if (isset($data['path2'])) {
      $data['path'] = $data['path2'];
      if (isset($data['class2'])) {
        $data['class'] = $data['class2'];
      }
    }
    unset($data['path2'], $data['class2']);

    // module directories hold one class per file, named after the class
    $path_pattern = '/^(?:DIR_ADMIN\/includes\/(?:modules\/(?:system|export|categories)|haendlerbund)|includes\/modules\/(?:payment|shipping|order_total)|api\/[A-Za-z0-9_-]+\/classes)\/(?:class\.)?([A-Za-z_][A-Za-z0-9_]*)\.php$/D';
    $name_pattern = '/^[A-Za-z_][A-Za-z0-9_]*$/D';

    // a file of an external package may do more than define its class, so only these
    $external_files = array(
      'includes/external/klarna/classes/KlarnaPayment.php' => 'KlarnaPayment',
      'includes/external/micropayment/class.micropayment_method.php' => 'micropayment_method',
      'includes/external/paypal/classes/PayPalPaymentV2.php' => 'PayPalPaymentV2',
      'includes/external/paypal/classes/PayPalZettle.php' => 'PayPalZettle',
      'includes/external/payone/classes/PayoneModified.php' => 'PayoneModified',
    );

    if (isset($external_files[$data['path']])) {
      if ($external_files[$data['path']] !== $data['class']) {
        return false;
      }
    } elseif (!preg_match($path_pattern, $data['path'], $match) || strcasecmp($match[1], $data['class']) != 0) {
      return false;
    }

    foreach (array('class', 'variable') as $key) {
      if (!preg_match($name_pattern, $data[$key])) {
        return false;
      }
    }

    if (!preg_match('/^[A-Za-z0-9_]*$/D', $data['module'])
        || !preg_match('/^[A-Za-z0-9_%]+$/D', $data['key'])
        || !preg_match('/^[0-9A-Za-z._ -]*$/D', $data['version'])
        || ($data['lang'] != ''
            && (!preg_match('/^lang\/DIR_LANG\/modules\/[a-z_]+\/[A-Za-z0-9_.-]+\.php$/D', $data['lang']) || strpos($data['lang'], '..') !== false)
            )
        || ($data['regex'] != ''
            && !preg_match('/^\/[^\/\x00-\x1F]{1,100}\/[imsux]*$/D', $data['regex'])
            )
        )
    {
      return false;
    }

    $data['title'] = strip_tags($data['title']);
    $data['link'] = filter_external_url($data['link'], array('https'), true);

    // set by the shop below
    $data['shop'] = 'undefined';
    $data['update'] = false;
    $data['installed'] = '0';

    return $data;
  }


  function check_version_update($cache = true) {
    global $PHP_SELF;
    static $version;
    
    $filename = SQL_CACHEDIR.'version.cache';
  
    if (!isset($version)) {
      $version = PROJECT_VERSION;
      if (!defined('RUN_MODE_ADMIN')) {
        require_once(DIR_FS_CATALOG.DIR_ADMIN.'includes/version.php');
      }
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
      
      $contents['version'] = (is_array($response) && isset($response['stable']) && is_string($response['stable'])) ? strip_tags($response['stable']) : '';
      $contents['update'] = version_compare($contents['version'], $contents['version_installed'], '>');

      $installer = modified_api::request('modified/version/install/installer');

      $dbversion = get_database_version();
      $response = modified_api::request('modified/version/install/');
      if (!is_array($response)
          || !isset($response['version'])
          || !is_string($response['version'])
          || !preg_match('/^[0-9A-Za-z._ -]*$/D', $response['version'])
          )
      {
        // api not reachable, request() returns an empty array
        $response = array('version' => '');
      }

      $details = array(
        'Shop' => array(
          'shop' => array(
            'title' => 'Shopversion',
            'version' => $response['version'],
            'shop' => PROJECT_VERSION_NO,
            'link' => (version_compare($response['version'], PROJECT_VERSION_NO, '>') && is_array($installer) && isset($installer['download'])) ? xtc_href_link(basename($PHP_SELF), 'action=autoupdate') : '',
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
      defined('FILENAME_MODULE_EXPORT') OR define('FILENAME_MODULE_EXPORT', 'module_export.php');
      
      modified_api::reset();
      $modules_data = modified_api::request('modified/version/modules/');
      
      if (!is_array($modules_data)) {
        $modules_data = array();
      }

      foreach ($modules_data as $heading => $modules) {
        $heading = strip_tags((string)$heading);
        if (!is_array($modules) || $heading == '' || $heading == 'Shop') {
          continue;
        }

        foreach ($modules as $module => $data) {
          $data = check_version_update_module($data);
          if ($data === false
              || !preg_match('/^[A-Za-z0-9_]+$/D', (string)$module)
              || in_array((string)$module, array('shop', 'db'), true)
              )
          {
            continue;
          }

          $data['path'] = str_replace('DIR_ADMIN/', DIR_ADMIN, $data['path']);
          $data['lang'] = str_replace('DIR_LANG', $_SESSION['language'], $data['lang']);
          
          $details[$heading][$module] = $data;

          if (is_file(DIR_FS_CATALOG.$data['path'])) {
            if ($data['lang'] != '' && is_file(DIR_FS_CATALOG.$data['lang'])) {
              require_once(DIR_FS_CATALOG.$data['lang']);
            }
  
            require_once(DIR_FS_CATALOG.$data['path']);
            if (class_exists($data['class'])) {
              try {
                $module_object = new $data['class']($data['module']);
              } catch (Throwable $e) {
                // not a module class, the entry stays undefined
                continue;
              }
  
              if (array_key_exists($data['variable'], get_object_vars($module_object))
                  && is_scalar($module_object->{$data['variable']})
                  )
              {
                $details[$heading][$module]['shop'] = (string)$module_object->{$data['variable']};
              }
  
              if ($data['regex'] != '') {
                $details[$heading][$module]['shop'] = (string)@preg_replace($data['regex'], '', $details[$heading][$module]['shop']);
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
