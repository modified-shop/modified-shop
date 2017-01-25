<?php
/* -----------------------------------------------------------------------------------------
   $Id: general.css.php 9842 2016-05-20 15:35:55Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('DIR_TMPL', 'templates/'.CURRENT_TEMPLATE.'/');
  define('DIR_TMPL_CSS', DIR_TMPL.'css/');

  if ($_SESSION['customers_status']['customers_status'] == '0') {
    echo '<link rel="stylesheet" property="stylesheet" href="'.DIR_WS_BASE.DIR_TMPL_CSS.'adminbar.css" type="text/css" media="screen" />';
  }

  $css_array = array(
    DIR_TMPL.'stylesheet.css',
  );
  $css_min = DIR_TMPL.'stylesheet.min.css';

  if (COMPRESS_STYLESHEET == 'true') {
    require_once(DIR_FS_BOXES_INC.'combine_files.inc.php');
    $css_array = combine_files($css_array,$css_min,true);
  }

  // Put CSS-Inline-Definitions here, these CSS-files will be loaded at the TOP of every page

  echo '<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" type="text/css" />';
  
  foreach ($css_array as $css) {
    echo '<link rel="stylesheet" href="'.DIR_WS_BASE.$css.'" type="text/css" media="screen" />'.PHP_EOL;
  }
?>