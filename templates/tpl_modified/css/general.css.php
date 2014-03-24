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

   // Put CSS-Definitions here, these CSS-files will be loaded at the TOP of every page
?>
<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Oswald|Open+Sans:400,700" type="text/css">
<link rel="stylesheet" href="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>/stylesheet.css" type="text/css" />
<link rel="stylesheet" href="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>/css/easy-responsive-tabs.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>/css/toggle.css" type="text/css" media="screen" />

<link rel="stylesheet" href="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>/css/thickbox.css" type="text/css" media="screen" />

<?php // BOF - web28 - 2010-07-09 - TABS/ACCORDION in product_info
if (strpos($PHP_SELF, FILENAME_PRODUCT_INFO) !== false) {

?>
<link rel="stylesheet" href="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>/css/jquery-ui.css" type="text/css" media="screen" />

<?php
} // EOF - web28 - 2010-07-09 - TABS/ACCORDION in product_info
?>
