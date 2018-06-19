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
    
    $get_url = '';
    if (is_array($_GET) && (count($_GET) > 0)) {
      foreach ($_GET as $key => $value) {
        if ((is_array($value) || (!is_array($value) && strlen($value) > 0))
            && (!in_array($key, $exclude_array)) 
            ) 
        {
          if (!is_array($value)) {
            $get_url .= rawurlencode(stripslashes($key)) . '=' . rawurlencode(stripslashes($value)) . '&';
          } else {
            foreach ($value as $k => $v) {
              if (strlen($v) > 0) {
                $get_url .= rawurlencode(stripslashes($key.'['.$k.']')) . '=' . rawurlencode(stripslashes($v)) . '&';
              }
            }
          }
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
          if (!is_array($value)) {
            $get_url .= rawurlencode(stripslashes($key)) . '=' . rawurlencode(stripslashes($value)) . '&';
          } else {
            foreach ($value as $k => $v) {
              if (strlen($v) > 0) {
                $get_url .= rawurlencode(stripslashes($key.'['.$k.']')) . '=' . rawurlencode(stripslashes($v)) . '&';
              }
            }
          }
        }
      }
    }

    return $get_url;
  }
?>