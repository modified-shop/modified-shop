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
    'templates/'.CURRENT_TEMPLATE.'/css/jquery.colorbox.css',
    'templates/'.CURRENT_TEMPLATE.'/css/jquery.alerts.css',
    'templates/'.CURRENT_TEMPLATE.'/css/jquery.bxslider.css',    
  );
  $css_min = 'templates/'.CURRENT_TEMPLATE.'/css/tpl_plugins.min.css';

  if (COMPRESS_STYLESHEET == 'true') {
    $css_plain_ts = filemtime(DIR_FS_CATALOG.$css_plain);
    $css_min_ts = is_writeable(DIR_FS_CATALOG.$css_min) ? filemtime(DIR_FS_CATALOG.$css_min) : false;
    if ($css_min_ts && ($css_plain_ts > $css_min_ts || filesize(DIR_FS_CATALOG.$css_min) == 0)) {
      require_once(DIR_FS_EXTERNAL.'compactor/compactor.php');
      $compactor = new Compactor(array('strip_php_comments' => true));
      foreach ($css_array as $css_plain) {
        $compactor->add(DIR_FS_CATALOG.$css_plain);
      }
      if ($compactor->save($css_min) === true) {
        $css_array = array($css_min.'?v='.$css_min_ts);
      }
    } elseif ($css_min_ts) {
      $css_array = array($css_min.'?v='.$css_min_ts);
    }
  }
  
  foreach ($css_array as $css) {
    echo '<link rel="stylesheet" property="stylesheet" href="'.DIR_WS_BASE.$css.'" type="text/css" media="screen" />'.PHP_EOL;
  }
?>
<!--[if lte IE 8]>
<link rel="stylesheet" property="stylesheet" href="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>/css/ie8fix.css" type="text/css" media="screen" />
<![endif]-->