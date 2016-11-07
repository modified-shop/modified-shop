<?php
/* --------------------------------------------------------------
   $Id: specials.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(specials.php,v 1.10 2002/01/31); www.oscommerce.com 
   (c) 2003	 nextcommerce (specials.php,v 1.4 2003/08/14); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

define('SPECIALS_TITLE', 'Special ');

define('TEXT_SPECIALS_PRODUCT', 'Product:');
define('TEXT_SPECIALS_SPECIAL_PRICE', 'Special Price:');
define('TEXT_SPECIALS_SPECIAL_QUANTITY', 'Quantity:');  
define('TEXT_SPECIALS_START_DATE', 'Start Date: <small>(YYYY-MM-DD)</small>');
define('TEXT_SPECIALS_EXPIRES_DATE', 'Expiry Date: <small>(YYYY-MM-DD)</small>');

define('TEXT_INFO_DATE_ADDED', 'Date Added:');
define('TEXT_INFO_LAST_MODIFIED', 'Last Modified:');
define('TEXT_INFO_NEW_PRICE', 'New Price:');
define('TEXT_INFO_ORIGINAL_PRICE', 'Original Price:');
define('TEXT_INFO_PERCENTAGE', 'Percentage:');
define('TEXT_INFO_START_DATE', 'Start at:');
define('TEXT_INFO_EXPIRES_DATE', 'Expires at:');

define('TEXT_INFO_HEADING_DELETE_SPECIALS', 'Delete Special');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete the special products price?');

define ('TEXT_SPECIALS_NO_PID', 'The item must first be stored, otherwise the discount can not be applied correctly');

define('TEXT_CATSPECIALS_START_DATE_TT', 'Enter the date from when the offer price will apply.<br>');
define('TEXT_CATSPECIALS_EXPIRES_DATE_TT', 'Leave the <strong>expiry date</strong> empty for no expiration.<br>');
define('TEXT_CATSPECIALS_SPECIAL_QUANTITY_TT', 'You can enter the item quantity in the field <strong>Quantity</strong> for products the special price apply to.<br>You can decide whether to check stock of specials or not under "Configuration" -> "Stock Options" -> "Check Specials Stock".');
define('TEXT_CATSPECIALS_SPECIAL_PRICE_TT', 'You can enter a percentage to deduct in the Specials Price field, for example: <strong>20%</strong><br>If you enter a new price, the decimal separator must be a \'.\' (decimal-point), example: <strong>49.99</strong>');

?>