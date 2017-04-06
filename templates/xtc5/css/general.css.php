<?php
/* -----------------------------------------------------------------------------------------
   $Id: general.css.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

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

  $css_array = array(
    DIR_TMPL.'stylesheet.css',
  );
  $css_min = DIR_TMPL.'stylesheet.min.css';
  
  $this_f_time = filemtime(DIR_FS_CATALOG.DIR_TMPL_CSS.'general.css.php');

  if (COMPRESS_STYLESHEET == 'true') {
    require_once(DIR_FS_BOXES_INC.'combine_files.inc.php');
    $css_array = combine_files($css_array,$css_min,true,$this_f_time);
  }

  // Put CSS-Inline-Definitions here, these CSS-files will be loaded at the TOP of every page
  
  foreach ($css_array as $css) {
    $css .= strpos($css,$css_min) === false ? '?v=' . filemtime(DIR_FS_CATALOG.$css) : '';
    echo '<link rel="stylesheet" href="'.DIR_WS_BASE.$css.'" type="text/css" media="screen" />'.PHP_EOL;
  }
?>