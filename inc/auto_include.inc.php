<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2014 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

function auto_include($dir, $ext = 'php', $expr = '*') 
{
  $dir = rtrim($dir,'/');
  $files = glob("{$dir}/$expr.".$ext);
  $files = ((is_array($files)) ? $files : array());
  
  // sort files
  natcasesort($files);
  
  if (function_exists('debugMessage')) {
    debugMessage('auto_include',$files);
  }
  
  return $files;
}