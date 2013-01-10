<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_get_all_get_params.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_get_all_get_params.inc.php 1237 2005-09-23)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

/**
 * Return all HTTP GET variables, except those passed as a parameter
 *
 * The return is a urlencoded string
 *
 * @param mixed either a single or array of parameter names to be excluded from output
*/
  function xtc_get_all_get_params($exclude_array = '') {
    global $InputFilter;

    if (!is_array($exclude_array)) $exclude_array = array();
    $exclude_array = array_merge($exclude_array, array(xtc_session_name(), 'error', 'x', 'y'));
    $get_url = '';
    if (is_array($_GET) && (sizeof($_GET) > 0)) {
      reset($_GET);
      while (list($key, $value) = each($_GET)) {
        if (is_array($value) || in_array($key, $exclude_array)) continue;
        if (strlen($value) > 0) {
          $get_url .= rawurlencode(stripslashes($key)) . '=' . rawurlencode(stripslashes($value)) . '&';
        }
      }
    }
    while (strstr($get_url, '&&')) $get_url = str_replace('&&', '&', $get_url);
    while (strstr($get_url, '&amp;&amp;')) $get_url = str_replace('&amp;&amp;', '&amp;', $get_url);

    return $get_url;
  }
?>