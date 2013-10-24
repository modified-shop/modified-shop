<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  foreach (new DirectoryIterator(DIR_FS_CATALOG) as $shoproot) {
    if (strpos($shoproot->getFilename(), '..') === false && $shoproot->isDir() && is_file(DIR_FS_CATALOG . $shoproot->getFilename() . '/check_update.php')) {
      define('DIR_ADMIN', $shoproot->getFilename() . '/');
      break;
    }
  }
?>