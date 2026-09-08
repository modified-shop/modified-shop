<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('DIR_TMPL', Template::url(''));
  define('DIR_TMPL_CSS', DIR_TMPL.'css/');

  if ($_SESSION['customers_status']['customers_status'] == '0') {
    echo '<link rel="stylesheet" property="stylesheet" href="'.Template::url('css/adminbar.css').'" type="text/css" media="screen" />';
  }

  $css_array = array(
    'templates/' . Template::resolve('stylesheet.css'),
  );
  
  if (defined('THEME_COLOR')) {
    $theme_css = 'css/themes/'.THEME_COLOR.'.css';
    if (Template::findPath($theme_css) !== null) {
      array_unshift($css_array, 'templates/' . Template::resolve($theme_css));
    }
  }
  
  if (Template::findPath('css/tpl_custom.css') !== null) {
     array_push($css_array, 'templates/' . Template::resolve('css/tpl_custom.css'));
  }
  
  $css_min = 'templates/'.CURRENT_TEMPLATE.'/stylesheet.min.css';

  $this_f_time = filemtime(Template::path('css/general.css.php'));

  if (COMPRESS_STYLESHEET == 'true') {
    require_once(Template::path('source/inc/combine_files.inc.php'));
    $css_array = combine_files($css_array, $css_min, true, $this_f_time);
  }

  // Put CSS-Inline-Definitions here, these CSS-files will be loaded at the TOP of every page
  
  foreach ($css_array as $css) {
    $css .= strpos($css,$css_min) === false ? '?v=' . filemtime(DIR_FS_CATALOG.$css) : '';
    echo '<link rel="stylesheet" href="'.DIR_WS_BASE.$css.'" type="text/css" media="screen" />'.PHP_EOL;
  }
