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

define('HEADING_TITLE', 'Specials');

define('TABLE_HEADING_PRODUCTS', 'Products');
define('TABLE_HEADING_PRODUCTS_PRICE', 'Products Price');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_SPECIALS_PRODUCT', 'Product:');
define('TEXT_SPECIALS_SPECIAL_PRICE', 'Special Price:');
define('TEXT_SPECIALS_SPECIAL_QUANTITY', 'Quantity:');  
// BOF - Tomcraft - 2009-11-06 - Use "iso 8601" for the date format
//define('TEXT_SPECIALS_EXPIRES_DATE', 'G&uuml;ltig bis:<br /><small>(dd.mm.yyyy)</small>'
define('TEXT_SPECIALS_EXPIRES_DATE', 'Expiry Date: <small>(YYYY-MM-DD)</small>');
// EOF - Tomcraft - 2009-11-06 - Use "iso 8601" for the date format
// BOF - Tomcraft - 2009-11-06 - extended description
//define('TEXT_SPECIALS_PRICE_TIP', '<b>Specials Notes:</b><ul><li>You can enter a percentage to deduct in the Specials Price field, for example: <b>20%</b></li><li>If you enter a new price, the decimal separator must be a \'.\' (decimal-point), example: <b>49.99</b></li><li>Leave the expiry date empty for no expiration</li></ul>');
define('TEXT_SPECIALS_PRICE_TIP', '<b>Specials Notes:</b><ul><li>You can enter a percentage to deduct in the Specials Price field, for example: <b>20%</b></li><li>If you enter a new price, the decimal separator must be a \'.\' (decimal-point), example: <b>49.99</b></li><li>Leave the expiry date empty for no expiration</li></ul><br>You can enter the item quantity in the field <strong>quantity</strong> for products the special price apply to. Remain the field empty, if you don\'t want to limit the quantity.');
// EOF - Tomcraft - 2009-11-06 - extended description

define('TEXT_INFO_DATE_ADDED', 'Date Added:');
define('TEXT_INFO_LAST_MODIFIED', 'Last Modified:');
define('TEXT_INFO_NEW_PRICE', 'New Price:');
define('TEXT_INFO_ORIGINAL_PRICE', 'Original Price:');
define('TEXT_INFO_PERCENTAGE', 'Percentage:');
define('TEXT_INFO_EXPIRES_DATE', 'Expires At:');
define('TEXT_INFO_STATUS_CHANGE', 'Status Change:');

define('TEXT_INFO_HEADING_DELETE_SPECIALS', 'Delete Special');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete the special products price?');

define('TEXT_IMAGE_NONEXISTENT','No image available!'); 
?>