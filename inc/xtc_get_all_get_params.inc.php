<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com
   (c) 2003	 nextcommerce (xtc_get_all_get_params.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_get_all_get_params($exclude_array = '') {

    if (!is_array($exclude_array)) $exclude_array = array();
    
    $exclude_array[] = xtc_session_name();
    $exclude_array[] = 'XTCsid';
    $exclude_array[] = 'error';
    $exclude_array[] = 'x';
    $exclude_array[] = 'y';
    $exclude_array[] = '_';
    
    $get_url = '';
    if (is_array($_GET) && (count($_GET) > 0)) {
      foreach ($_GET as $key => $value) {
        if ((is_array($value) || (!is_array($value) && strlen($value) > 0))
            && (!in_array($key, $exclude_array)) 
            ) 
        {
          $get_url .= build_get_query(array($key => $value));          
        }
      }
    }

    return $get_url;
  }
  
  
  function xtc_get_all_get_params_include($include_array = '') {

    if (!is_array($include_array)) $include_array = array();
    
    $get_url = '';
    if (is_array($_GET) && (sizeof($_GET) > 0)) {
      foreach ($_GET as $key => $value) {
        if ((is_array($value) || (!is_array($value) && strlen($value) > 0))
            && (in_array($key, $include_array)) 
            ) 
        {
          $get_url .= build_get_query(array($key => $value));          
        }
      }
    }

    return $get_url;
  }
  
  
  function build_get_query($array) {
    $get_url = '';
    
    $array = clean_get_param($array);
    if (is_array($array)) {
      $array = sanitize_get_param($array);
      $get_url = http_build_query($array, '', '&', PHP_QUERY_RFC3986).'&';
    }
    
    return $get_url;
  }
  
  
  function clean_get_param($array) {
    foreach($array as $k => &$v){
      if (is_array($v)) {
        $v = clean_get_param($v);
        if (count($v) < 1) {
          unset($array[$k]);
        }
      } elseif (strlen($v) < 1) {
        unset($array[$k]);
      }
    }
    
    return $array;
  }
  
  
  function sanitize_get_param($string) {
    if (is_array($string)) {
      $data = array();
      foreach ($string as $key => $value) {
        $data[stripslashes($key)] = sanitize_get_param($value);
      }
    } else {
      $data = stripslashes($string);
    }
    
    return $data;
  }
?>