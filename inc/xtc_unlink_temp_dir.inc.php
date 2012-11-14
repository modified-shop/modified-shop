<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   by Mario Zanier for neXTCommerce
   
   based on:
   (c) 2003 nextcommerce (xtc_unlink_temp_dir.inc.php,v 1.1 2003/08/18); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  // Unlinks all subdirectories and files in $dir
  // Works only on one subdir level, will not recurse
  function xtc_unlink_temp_dir($dir) {
    $h1 = opendir($dir);
    while ($subdir = readdir($h1)) {
      // Ignore non directories
      if (!is_dir($dir . $subdir)) continue;
      // Ignore . and .. and CVS
      if ($subdir == '.' || $subdir == '..' || $subdir == 'CVS') continue;
      // Loop and unlink files in subdirectory
      $h2 = opendir($dir . $subdir);
      while ($file = readdir($h2)) {
        if ($file == '.' || $file == '..') continue;
        @unlink($dir . $subdir . '/' . $file);
      }
      closedir($h2); 
      @rmdir($dir . $subdir);
    }
    closedir($h1);
  }
 ?>