<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2014 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

function auto_include($dir, $ext='php') 
{
    $auto_include_arr = array();
		if ( count( glob("{$dir}/*.".$ext ) > 0 ) {
			foreach (glob("{$dir}/*.".$ext) as $filename) {
					$auto_include_arr[] = $filename;
			}
		}
    if (function_exists('debugMessage')) {
        debugMessage('auto_include',$auto_include_arr);
    }
    return $auto_include_arr;
}