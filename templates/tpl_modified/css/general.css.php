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

  $css_plain = DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/stylesheet.css';
  $css_min = DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/stylesheet.min.css';

  $css_file = '/stylesheet.css';
  if (COMPRESS_STYLESHEET == 'true' && (!is_file($css_min) || filemtime($css_plain) != COMPRESS_STYLESHEET_TIME)) {
    require_once(DIR_FS_EXTERNAL.'compactor/compactor.php');
  
    if (($css_content = file_get_contents($css_plain)) !== false) {
    $compactor = new Compactor(array('strip_php_comments' => true));
    $css_content = $compactor->squeeze($css_content);
      if (file_put_contents($css_min, $css_content, LOCK_EX) !== false) {
        $css_file = '/stylesheet.min.css';
        xtc_db_query("UPDATE ".TABLE_CONFIGURATION." 
                         SET configuration_value = '".filemtime($css_plain)."' 
                       WHERE configuration_key = 'COMPRESS_STYLESHEET_TIME'");
      }
    }
  } elseif (COMPRESS_STYLESHEET == 'true' && is_file($css_min)) {
    $css_file = '/stylesheet.min.css';
  }

  // Put CSS-Inline-Definitions here, these CSS-files will be loaded at the TOP of every page
?>
<link rel="stylesheet" href="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.$css_file; ?>" type="text/css" media="screen" />
