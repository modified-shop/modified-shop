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
  
    if (!is_file($filename)
        || (filemtime($filename) + 86400) < time()
        || $cache === false
        )
    {
      $check_version = get_external_content('http://www.modified-shop.org/VERSION', 3, false);
      file_put_contents($filename, $check_version);
    }
  
    $check_version = file_get_contents($filename);
  
    $update_recomended = false;
    if (version_compare($check_version, PROJECT_VERSION, '>')) {
      $update_recomended = true;
    }
  
    return array(
      'update' => $update_recomended,
      'version' => $check_version,
    );
  }
?>