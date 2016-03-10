<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2014 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

function auto_include($dir, $ext = 'php', $expr = '*', $flags = 0)
{
  $dir = rtrim($dir,'/');

  $files = glob("{$dir}/$expr.".$ext, $flags);

  $files = ((false !== $files) ? $files : array());

  natcasesort($files);
  
  if (function_exists('debugMessage')) {
    debugMessage('auto_include',$files);
  }
  
  return $files;
}