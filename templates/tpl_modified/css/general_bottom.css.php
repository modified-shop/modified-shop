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

  $css_array = array(
    DIR_TMPL_CSS.'jquery.colorbox.css',
    DIR_TMPL_CSS.'jquery.alerts.css',
    DIR_TMPL_CSS.'jquery.bxslider.css',    
  );
  $css_min = DIR_TMPL_CSS.'tpl_plugins.min.css';

  if (COMPRESS_STYLESHEET == 'true') {
    require_once(DIR_FS_BOXES_INC.'combine_files.inc.php');
    $css_array = combine_files($css_array,$css_min,true);
  }
  
  foreach ($css_array as $css) {
    echo '<link rel="stylesheet" property="stylesheet" href="'.DIR_WS_BASE.$css.'" type="text/css" media="screen" />'.PHP_EOL;
  }
?>
<!--[if lte IE 8]>
<link rel="stylesheet" property="stylesheet" href="<?php echo DIR_WS_BASE.DIR_TMPL_CSS; ?>ie8fix.css" type="text/css" media="screen" />
<![endif]-->