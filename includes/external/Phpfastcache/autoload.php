<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  spl_autoload_register(function ($class_path) {
      $parts = explode('\\', $class_path);
      $class_name = array_pop($parts) . '.php';
      $path = implode(DIRECTORY_SEPARATOR, $parts);
      $path .= DIRECTORY_SEPARATOR . $class_name;
      
      if (file_exists(DIR_FS_EXTERNAL . $path)) {
        require_once DIR_FS_EXTERNAL . $path;
      }
  });
