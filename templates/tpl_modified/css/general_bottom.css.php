<?php
/* -----------------------------------------------------------------------------------------
   $Id: general_bottom.css.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // This CSS file get includes at the BOTTOM of every template page in shop
  // you can add your template specific css scripts here
  defined('DIR_TMPL') OR define('DIR_TMPL', Template::url(''));
  defined('DIR_TMPL_CSS') OR define('DIR_TMPL_CSS', DIR_TMPL.'css/');

  $css_array = array(
    'templates/' . Template::resolve('css/jquery.colorbox.css'),
    'templates/' . Template::resolve('css/jquery.alertable.css'),
    'templates/' . Template::resolve('css/jquery.bxslider.css'),
    'templates/' . Template::resolve('css/cookieconsent.css'),
  );
  $css_min = 'templates/'.CURRENT_TEMPLATE.'/css/tpl_plugins.min.css';

  $this_f_time = filemtime(Template::path('css/general_bottom.css.php'));

  if (COMPRESS_STYLESHEET == 'true') {
    require_once(Template::path('source/inc/combine_files.inc.php'));
    $css_array = combine_files($css_array,$css_min,true,$this_f_time);
  }
  
  foreach ($css_array as $css) {
    $css .= strpos($css,$css_min) === false ? '?v=' . filemtime(DIR_FS_CATALOG.$css) : '';
    echo '<link rel="stylesheet" property="stylesheet" href="'.DIR_WS_BASE.$css.'" type="text/css" media="screen" />'.PHP_EOL;
  }
?>
<!--[if lte IE 8]>
<link rel="stylesheet" property="stylesheet" href="<?php echo Template::url('css/ie8fix.css'); ?>" type="text/css" media="screen" />
<![endif]-->
