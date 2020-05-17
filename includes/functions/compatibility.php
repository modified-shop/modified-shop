<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(compatibility.php,v 1.19 2003/04/09); www.oscommerce.com 
   (c) 2003	 nextcommerce (compatibility.php,v 1.5 2003/08/13); www.nextcommerce.org 

   Released under the GNU General Public License
   Modified by Marco Canini, <m.canini@libero.it>
   Fixed a bug with arrays in $HTTP_xxx_VARS
   ---------------------------------------------------------------------------------------*/
   
  //PHP 5.4 compatibility - functions REMOVED as of PHP 5.4.0.
  if (!function_exists('session_register')) {
    function session_register() {
      $args = func_get_args();
      foreach($args as $key) {
        $_SESSION[$key] = $GLOBALS[$key];
      }
    }
    function session_is_registered($key){
      return isset($_SESSION[$key]);
    }
    function session_unregister($key){
      unset($_SESSION[$key]);
    }
  }

  // Recursively handle magic_quotes_gpc turned off.
  // This is due to the possibility of have an array in
  // $HTTP_xxx_VARS
  // Ie, products attributes
  function do_magic_quotes_gpc(&$ar) {
    if (!is_array($ar)) return false;

    foreach ($ar as $key => $value) {
      if (is_array($value)) {
        do_magic_quotes_gpc($value);
      } else {
        $ar[$key] = addslashes($value);
      }
    }
  }

  // $HTTP_xxx_VARS are always set on php4
  if (!is_array($_GET)) $_GET = array();
  if (!is_array($_POST)) $_POST = array();
  if (!is_array($_COOKIE)) $_COOKIE = array();

  // handle magic_quotes_gpc turned off.
  if (!function_exists('get_magic_quotes_gpc') || !get_magic_quotes_gpc()) {
    do_magic_quotes_gpc($_GET);
    do_magic_quotes_gpc($_POST);
    do_magic_quotes_gpc($_COOKIE);
  }
?>