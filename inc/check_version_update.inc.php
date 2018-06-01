<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  require_once (DIR_FS_INC.'get_external_content.inc.php');

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
      $check_version = get_external_content('https://www.modified-shop.org/VERSION', 3, false);
      file_put_contents($filename, $check_version);
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