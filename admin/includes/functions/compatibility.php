<?php
/* --------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(compatibility.php,v 1.8 2003/04/09); www.oscommerce.com 
   (c) 2003	 nextcommerce (compatibility.php,v 1.6 2003/08/18); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/
  
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  // Recursively handle magic_quotes_gpc turned off.
  // This is due to the possibility of have an array in
  // $HTTP_xxx_VARS
  // Ie, products attributes
  function do_magic_quotes_gpc(&$ar) {
    if (!is_array($ar)) return;

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

  /**
   * xtc_array_merge()
   *
   * @param mixed $array1
   * @param mixed $array2
   * @param string $array3
   * @return
   */
  function xtc_array_merge($array1, $array2, $array3 = '') {
      if (!is_array($array1)) {
        $array1 = array ();
      }
      if (!is_array($array2)) {
        $array2 = array ();
      }
      if (!is_array($array3)) {
        $array3 = array ();
      }
    if (function_exists('array_merge')) {
      $array_merged = array_merge($array1, $array2, $array3);
    } else {
      foreach ($array1 as $key => $val) {
        $array_merged[$key] = $val;
      }
      foreach ($array2 as $key => $val) {
        $array_merged[$key] = $val;
      }
      if (sizeof($array3) > 0) {
        foreach ($array3 as $key => $val) {
          $array_merged[$key] = $val;
        }
      }
    }
    return (array) $array_merged;
  }
?>