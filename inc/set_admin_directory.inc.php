<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

   function set_admin_directory() {
    foreach (new DirectoryIterator(DIR_FS_CATALOG) as $shoproot) {
      if ($shoproot->isDir() && is_file($shoproot->getFilename() . '/check_update.php')) {
        define('DIR_ADMIN', $shoproot->getFilename() . '/');
        break;
      }
    }
  }
?>