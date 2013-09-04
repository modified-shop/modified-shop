<?php
  /* --------------------------------------------------------------
   $Id: auto_require.inc.php 5336 2013-08-06 11:40:35Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

function auto_require($dir, $ext='php') {
    if ($extra_files = @scandir($dir)) {            
        foreach ($extra_files as $filename) {
            $filename_array = explode('.', $filename);
            if (end($filename_array) == $ext) { 
                require($dir . $filename);           
            }
        }
    }
}