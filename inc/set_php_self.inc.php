<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


function set_php_self() 
{
  if(!empty($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], '.php') !== false) {
    return $_SERVER['SCRIPT_NAME'];
  } elseif(!empty($_SERVER['PHP_SELF']) && strpos($_SERVER['PHP_SELF'], '.php') !== false) {
    $tmp = explode('.php',$_SERVER['PHP_SELF']);
    return $tmp[0] .'.php';
  }
  die('ERROR: PHP_SELF');
}
?>