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

$css_plain = DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/stylesheet.css';
$css_min = DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/stylesheet.min.css';

$css_file = '/stylesheet.css';
if (COMPRESS_STYLESHEET == 'true' && filemtime($css_min) != COMPRESS_STYLESHEET_TIME) {
  require_once(DIR_FS_EXTERNAL.'compactor/compactor.php');
  
  if ($css_content = file_get_contents($css_plain) !== false) {
    $compactor = new Compactor();
    $css_content = $compactor->_simpleCodeCompress($css_content);
    if (file_put_contents($css_min, $css_content, LOCK_EX) !== false) {
      $css_file = '/stylesheet.min.css';
      xtc_db_query("UPDATE ".TABLE_CONFIGURATION." 
                       SET configuration_value = '".filemtime($css_min)."' 
                     WHERE configuration_key = 'COMPRESS_STYLESHEET_TIME'");
    }
  }
} elseif (is_file($css_min)) {
  $css_file = '/stylesheet.min.css';
}
?>

<link rel="stylesheet" href="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.$css_file; ?>" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>/css/jquery.toggle.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>/css/jquery.colorbox.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>/css/jquery.alerts.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>/css/jquery.bxslider.css" type="text/css" media="screen" />
     