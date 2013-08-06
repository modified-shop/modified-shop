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
            $file_ext = end(explode('.',$filename));
            if ($file_ext == $ext) { 
                require($dir . $filename);           
            }
        }
    }
}