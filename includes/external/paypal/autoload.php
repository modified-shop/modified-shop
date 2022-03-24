<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


spl_autoload_register(function ($class_path) {
  $baseDir = __DIR__;

  $parts = explode('\\', $class_path);
  $class_name = array_pop($parts) . '.php';
  $path = implode(DIRECTORY_SEPARATOR, $parts);
  $path .= DIRECTORY_SEPARATOR . $class_name;
  if (file_exists($baseDir . DIRECTORY_SEPARATOR . $path)) {
    require_once $baseDir . DIRECTORY_SEPARATOR . $path;
  }
});
