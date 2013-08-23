<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

function auto_require($dir, $ext='php')
{
    //echo $dir.'<br>';
    if ($extra_files = @scandir($dir)) {            
        foreach ($extra_files as $filename) {
            // BOF - DokuMan - 2013-08-23 fix: "Strict Standards: Only variables should be passed by reference"
            //$file_ext = end(explode('.',$filename));
            //The problem is, that end() requires a reference, because it modifies the internal representation of the array
            $filename_array = explode('.',$filename);
            $file_ext = end($filename_array);
            // EOF - DokuMan - 2013-08-23 fix: "Strict Standards: Only variables should be passed by reference"
            if ($file_ext == $ext) { 
                require($dir . $filename);           
            }
        }
    }
}