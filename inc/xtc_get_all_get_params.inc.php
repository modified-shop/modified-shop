<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_get_all_get_params.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_get_all_get_params.inc.php 1237 2005-09-23)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_get_all_get_params($exclude_array = '') {
    global $InputFilter;

    if (!is_array($exclude_array)) $exclude_array = array();
    $get_url = '';
    if (is_array($_GET) && (sizeof($_GET) > 0)) {
      reset($_GET);
      foreach($_GET as $key => $value) { //Dokuman - 2011-07-26 - Change while with foreach for performance
//-- SHOPSTAT --//
//        if ( (strlen($value) > 0) && ($key != xtc_session_name()) && ($key != 'error') && ($key != 'cPath') && (!in_array($key, $exclude_array)) && ($key != 'x') && ($key != 'y') ) {
//-- SHOPSTAT --//
        if ( (strlen($value) > 0) && ($key != xtc_session_name()) && ($key != 'error') && (!in_array($key, $exclude_array)) && ($key != 'x') && ($key != 'y') ) {

          $get_url .= rawurlencode(stripslashes($key)) . '=' . rawurlencode(stripslashes($value)) . '&';

        }
      }
    }

    return $get_url;
  }
?>