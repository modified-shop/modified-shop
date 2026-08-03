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
  static $cache_array;

  if (!isset($cache_array)) $cache_array = array();

  $dir = rtrim($dir,'/');
  $key = $dir . '|' . $ext . '|' . $expr . '|' . $flags;

  if (!isset($cache_array[$key])) {
    $files = glob("{$dir}/$expr.".$ext, $flags);

    $files = ((false !== $files) ? $files : array());

    natcasesort($files);

    $cache_array[$key] = $files;
  }

  return $cache_array[$key];
}
