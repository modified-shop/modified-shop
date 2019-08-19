<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  spl_autoload_register(function ($class) {  
    $class = ltrim($class, '\\');
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $class = __DIR__.DIRECTORY_SEPARATOR.$class.'.php';
    
    if (is_file($class)) {
      require_once($class);
    }
  });
?>