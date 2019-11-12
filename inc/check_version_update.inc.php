<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  require_once (DIR_FS_CATALOG.'includes/classes/modified_api.php');

  function check_version_update($cache = true) {
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
      modified_api::reset();
      $response = modified_api::request('modified/version');
      
      if (is_array($response) && isset($response['stable'])) {
        $check_version = $response['stable'];
        file_put_contents($filename, $check_version);
      }
    }
  
    $check_version = file_get_contents($filename);
  
    $update_recomended = false;
    if (version_compare($check_version, $version, '>')) {
      $update_recomended = true;
    }
  
    return array(
      'update' => $update_recomended,
      'version' => $check_version,
      'version_installed' => $version,
    );
  }
?>