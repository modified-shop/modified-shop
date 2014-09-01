<?php
/* -----------------------------------------------------------------------------------------
   $Id: general.js.php 1262 2005-09-30 10:00:32Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

   // Put CSS-Definitions here, these CSS-files will be loaded at the TOP of every page
?>
<link rel="stylesheet" href="<?php echo 'templates/'.CURRENT_TEMPLATE; ?>/stylesheet.css" type="text/css" />
<link rel="stylesheet" href="<?php echo 'templates/'.CURRENT_TEMPLATE; ?>/css/thickbox.css" type="text/css" media="screen" />

<?php // BOF - web28 - 2010-07-09 - TABS/ACCORDION in product_info ?>
<?php
if (strstr($PHP_SELF, FILENAME_PRODUCT_INFO )) {
?>
<link rel="stylesheet" href="<?php echo 'templates/'.CURRENT_TEMPLATE; ?>/css/jquery-ui.css" type="text/css" media="screen" />
<?php
}
?>
<?php // EOF - web28 - 2010-07-09 - TABS/ACCORDION in product_info ?>