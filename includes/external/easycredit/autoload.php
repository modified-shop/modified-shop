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

      // project-specific namespace prefix
      $prefix = 'EasyCredit\\';

      // base directory for the namespace prefix
      $baseDir = __DIR__.'/EasyCredit/';

      // does the class use the namespace prefix?
      $len = strlen($prefix);
      if (strncmp($prefix, $class, $len) !== 0) {
          // no, move to the next registered autoloader
          return;
      }

      // get the relative class name
      $relativeClass = substr($class, $len);

      // replace the namespace prefix with the base directory, replace namespace
      // separators with directory separators in the relative class name, append
      // with .php
      $file = $baseDir.str_replace('\\', '/', $relativeClass).'.php';

      // if the file exists, require it
      if (file_exists($file)) {
          require $file;
      }
  }, true, true);
?>