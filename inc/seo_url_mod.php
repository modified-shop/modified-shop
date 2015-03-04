<?php
/* -----------------------------------------------------------------------------------------
   $Id: stylesheet.css 4246 2013-01-11 14:36:07Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


function seo_url_mod($link, $page, $parameters, $connection, $separator) {
  require_once(DIR_FS_INC . 'shopstat_functions.inc.php');
  $mode = (!defined('RUN_MODE_ADMIN') ? 'user' : 'admin');
  if ($link = shopstat_getSEO($page, $parameters, $connection, true, true, $mode)) {
    $elements  = parse_url($link);
    $separator = (isset($elements['query']) ? '&' : '?');
  }  
  return array($link, $separator);
}

?>