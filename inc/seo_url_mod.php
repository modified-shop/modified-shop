<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined('SEO_URL_MOD_CLASS') OR define('SEO_URL_MOD_CLASS', 'seo_url_shopstat');

// include needed class
require_once(DIR_FS_CATALOG.'includes/classes/modified_seo_url.php');

function seo_url_mod($link, $page, $parameters, $connection, $separator) {

  foreach(auto_include(DIR_FS_CATALOG.'includes/extra/seo_url_mod/','php') as $file) require_once ($file);

  $seo_url_mod = SEO_URL_MOD_CLASS;
  $modified_seo = $seo_url_mod::getInstance();
  
  if ($seolink = $modified_seo->create_link($page, $parameters, $connection)) {
    $link = $seolink;
    $elements  = parse_url($link);
    $separator = (isset($elements['query']) ? '&' : '?');  
  }

  return array($link, $separator);
}

?>