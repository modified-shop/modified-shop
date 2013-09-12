<?php
/* -----------------------------------------------------------------------------------------
   $Id: set_admin_directory.inc.php 5728 2013-09-12 08:21:03Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  foreach (new DirectoryIterator(DIR_FS_CATALOG) as $shoproot) {
    if ($shoproot->isDir() && is_file(DIR_FS_CATALOG . $shoproot->getFilename() . '/check_update.php')) {
      define('DIR_ADMIN', $shoproot->getFilename() . '/');
      break;
    }
  }
?>