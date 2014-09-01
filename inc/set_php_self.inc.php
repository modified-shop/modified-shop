<?php
/* -----------------------------------------------------------------------------------------
   $Id: set_php_self.inc.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
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