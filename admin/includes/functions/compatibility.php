<?php
/* --------------------------------------------------------------
   $Id: compatibility.php 950 2005-05-14 16:45:21Z mz $   

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

    while (list($key, $value) = each($ar)) {
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
  if (!get_magic_quotes_gpc()) {
    do_magic_quotes_gpc($_GET);
    do_magic_quotes_gpc($_POST);
    do_magic_quotes_gpc($_COOKIE);
  }

  if (!function_exists('is_numeric')) {
    function is_numeric($param) {
      return preg_match("/^[0-9]{1,50}.?[0-9]{0,50}$/", $param); // Hetfield - 2009-08-19 - replaced deprecated function ereg with preg_match to be ready for PHP >= 5.3
    }
  }

  if (!function_exists('is_uploaded_file')) {
    function is_uploaded_file($filename) {
      if (!$tmp_file = get_cfg_var('upload_tmp_dir')) {
        $tmp_file = dirname(tempnam('', ''));
      }

      if (strchr($tmp_file, '/')) {
        if (substr($tmp_file, -1) != '/') $tmp_file .= '/';
      } elseif (strchr($tmp_file, '\\')) {
        if (substr($tmp_file, -1) != '\\') $tmp_file .= '\\';
      }

      return file_exists($tmp_file . basename($filename));
    }
  }

  if (!function_exists('move_uploaded_file')) {
    function move_uploaded_file($file, $target) {
      return copy($file, $target);
    }
  }

  if (!function_exists('checkdnsrr')) {
    function checkdnsrr($host, $type) {
      if(xtc_not_null($host) && xtc_not_null($type)) {
        @exec("nslookup -type=$type $host", $output);
        while(list($k, $line) = each($output)) {
          if(preg_match("/^$host/i", $line)) { // Hetfield - 2009-08-19 - replaced deprecated function eregi with preg_match to be ready for PHP >= 5.3
            return true;
          }
        }
      }
      return false;
    }
  }

  // Wrapper for class_exists() function
  // This function is not available in all PHP versions so we test it before using it.
  /**
   * xtc_class_exists()
  *
   * @param mixed $class_name
   * @return
   */
  function xtc_class_exists($class_name) {
    if (function_exists('class_exists')) {
      return class_exists($class_name);
    } else {
      return true;
    }
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
      while (list ($key, $val) = each($array1))
        $array_merged[$key] = $val;
      while (list ($key, $val) = each($array2))
        $array_merged[$key] = $val;
      if (sizeof($array3) > 0)
        while (list ($key, $val) = each($array3))
          $array_merged[$key] = $val;
    }
    return (array) $array_merged;
  }

  function xtc_array_shift(& $array) {
    if (function_exists('array_shift')) {
      return array_shift($array);
    } else {
      $i = 0;
      $shifted_array = array ();
      reset($array);
      while (list ($key, $value) = each($array)) {
        if ($i > 0) {
          $shifted_array[$key] = $value;
        } else {
          $return = $array[$key];
        }
        $i ++;
      }
      $array = $shifted_array;
      return $return;
    }
  }

  function xtc_array_reverse($array) {
    if (function_exists('array_reverse')) {
      return array_reverse($array);
    } else {
      $reversed_array = array ();
      for ($i = sizeof($array) - 1; $i >= 0; $i --) {
        $reversed_array[] = $array[$i];
      }
      return $reversed_array;
    }
  }

  /**
   * xtc_array_slice()
   *
   * @param mixed $array
   * @param mixed $offset
   * @param string $length
   * @return
   */
  function xtc_array_slice($array, $offset, $length = '0') {
    if (function_exists('array_slice')) {
      return array_slice($array, $offset, $length);
    } else {
      $length = abs($length);
      if ($length == 0) {
        $high = sizeof($array);
      } else {
        $high = $offset + $length;
      }
      for ($i = $offset; $i < $high; $i ++) {
        $new_array[$i - $offset] = $array[$i];
      }
      return $new_array;
    }
  }

  /**
   * xtc_constant()
   *
   * @param mixed $constant
   * @return
   */
  function xtc_constant($constant) {
    if (function_exists('constant')) {
      $temp = constant($constant);
    } else {
      eval ("\$temp=$constant;");
    }
    return $temp;
  }
?>