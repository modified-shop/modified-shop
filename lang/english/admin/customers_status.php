<?php
/* --------------------------------------------------------------
   $Id: customers_status.php 1062 2005-07-21 19:57:29Z gwinger $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(customers.php,v 1.76 2003/05/04); www.oscommerce.com
   (c) 2003	 nextcommerce (customers_status.php,v 1.12 2003/08/14); www.nextcommerce.org

   Released under the GNU General Public License
   --------------------------------------------------------------*/

define('HEADING_TITLE', 'Customer Groups');

define('ENTRY_CUSTOMERS_FSK18','Lock buy-function for FSK18 Products?');
define('ENTRY_CUSTOMERS_FSK18_DISPLAY','Display FSK18 Products?');
define('ENTRY_CUSTOMERS_STATUS_ADD_TAX','Show tax in order total');
define('ENTRY_CUSTOMERS_STATUS_MIN_ORDER','Minimum order value:');
define('ENTRY_CUSTOMERS_STATUS_MAX_ORDER','Maximum order value:');
define('ENTRY_CUSTOMERS_STATUS_BT_PERMISSION','Via Bank Collection');
define('ENTRY_CUSTOMERS_STATUS_CC_PERMISSION','Via Credit Card');
define('ENTRY_CUSTOMERS_STATUS_COD_PERMISSION','Via Cash on Delivery');
define('ENTRY_CUSTOMERS_STATUS_DISCOUNT_ATTRIBUTES','Discount');
define('ENTRY_CUSTOMERS_STATUS_PAYMENT_UNALLOWED','Enter not allowed Payment Methods');
define('ENTRY_CUSTOMERS_STATUS_PUBLIC','Public');
define('ENTRY_CUSTOMERS_STATUS_SHIPPING_UNALLOWED','Enter not allowed Shipping Modules');
define('ENTRY_CUSTOMERS_STATUS_SHOW_PRICE','Price');
define('ENTRY_CUSTOMERS_STATUS_SHOW_PRICE_TAX','Prices incl. Tax');
define('ENTRY_CUSTOMERS_STATUS_WRITE_REVIEWS','Customer group is allowed to write reviews?');
define('ENTRY_CUSTOMERS_STATUS_READ_REVIEWS','Customer group is allowed to read reviews?');
define('ENTRY_GRADUATED_PRICES','Graduated Prices');
define('ENTRY_NO','No');
define('ENTRY_OT_XMEMBER', 'Customer Discount on order total ? :');
define('ENTRY_YES','Yes');

define('ERROR_REMOVE_DEFAULT_CUSTOMER_STATUS', 'Error: You can not delete the default customer group. Please set another group to default customer group and try again.');
define('ERROR_REMOVE_DEFAULT_CUSTOMERS_STATUS','ERROR! You cant delete a standardgroup');
define('ERROR_STATUS_USED_IN_CUSTOMERS', 'Error: This customer group is actually in use.');
define('ERROR_STATUS_USED_IN_HISTORY', 'Error: This customer group is actually in use for order history.');

define('YES','yes');
define('NO','no');

define('TABLE_HEADING_ACTION','Action');
define('TABLE_HEADING_CUSTOMERS_GRADUATED','Graduated Price');
define('TABLE_HEADING_CUSTOMERS_STATUS','Customers Group');
define('TABLE_HEADING_CUSTOMERS_UNALLOW','not allowed Paymentmethods');
define('TABLE_HEADING_CUSTOMERS_UNALLOW_SHIPPING','not allowed Shipping');
define('TABLE_HEADING_DISCOUNT','Discount');
define('TABLE_HEADING_TAX_PRICE','Price / Tax');

define('TAX_NO','excl');
define('TAX_YES','incl');

define('TEXT_DISPLAY_NUMBER_OF_CUSTOMERS_STATUS', 'Existing customer groups:');

define('TEXT_INFO_CUSTOMERS_FSK18_DISPLAY_INTRO','<strong>FSK18 Products</strong>');
define('TEXT_INFO_CUSTOMERS_FSK18_INTRO','<strong>FSK18 Lock</strong>');
define('TEXT_INFO_CUSTOMERS_STATUS_ADD_TAX_INTRO','<strong>If prices incl. tax = set to "No"</strong>');
define('TEXT_INFO_CUSTOMERS_STATUS_MIN_ORDER_INTRO','<strong>Define a minimum order value or leave the field empty.</strong>');
define('TEXT_INFO_CUSTOMERS_STATUS_MAX_ORDER_INTRO','<strong>Define a maximum order value or leave the field empty.</strong>');
define('TEXT_INFO_CUSTOMERS_STATUS_BT_PERMISSION_INTRO', '<strong>Shall we allow customers of this group to pay via bank collection?</strong>');
define('TEXT_INFO_CUSTOMERS_STATUS_CC_PERMISSION_INTRO', '<strong>Shall we allow customers of this group to pay with credit cards?</strong>');
define('TEXT_INFO_CUSTOMERS_STATUS_COD_PERMISSION_INTRO', '<strong>Shall we allow customers of this group to pay COD?</strong>');
define('TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_ATTRIBUTES_INTRO','<strong>Discount on product attributes</strong><br />(Max. % Discount on single product)');
define('TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_OT_XMEMBER_INTRO','<strong>Discount on total order</strong>');
define('TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_PRICE', 'Discount (0 to 100%):');
define('TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_PRICE_INTRO', '<strong>Please define a discount between 0 and 100% which is used on each displayed product.</strong>');
define('TEXT_INFO_CUSTOMERS_STATUS_GRADUATED_PRICES_INTRO','<strong>Graduated Prices</strong>');
define('TEXT_INFO_CUSTOMERS_STATUS_IMAGE', '<strong>Customers Group Image:</strong>');
define('TEXT_INFO_CUSTOMERS_STATUS_NAME','<strong>Groupname</strong>');
define('TEXT_INFO_CUSTOMERS_STATUS_PAYMENT_UNALLOWED_INTRO','<strong>not allowed Payment Methods</strong>');
define('TEXT_INFO_CUSTOMERS_STATUS_PUBLIC_INTRO','<strong>Show Public ?</strong>');
define('TEXT_INFO_CUSTOMERS_STATUS_SHIPPING_UNALLOWED_INTRO','<strong>not allowed Shipping Modules</strong>');
define('TEXT_INFO_CUSTOMERS_STATUS_SHOW_PRICE_INTRO','<strong>Show price in shop</strong>');
define('TEXT_INFO_CUSTOMERS_STATUS_SHOW_PRICE_TAX_INTRO', '<strong>Do you want to display prices inclusive or exclusive tax?</strong>');
define('TEXT_INFO_CUSTOMERS_STATUS_WRITE_REVIEWS_INTRO','<strong>Productsreview write</strong>');
define('TEXT_INFO_CUSTOMERS_STATUS_READ_REVIEWS_INTRO', '<strong>Productsreview read</strong>');

define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this customer group?');
define('TEXT_INFO_EDIT_INTRO', 'Please make all neccessary changes');
define('TEXT_INFO_INSERT_INTRO', 'Please create a new customer group within all neccessary values.');

define('TEXT_INFO_HEADING_DELETE_CUSTOMERS_STATUS', 'Delete Customer Group');
define('TEXT_INFO_HEADING_EDIT_CUSTOMERS_STATUS','Edit Group Data');
define('TEXT_INFO_HEADING_NEW_CUSTOMERS_STATUS', 'New Customer Group');

define('TEXT_INFO_CUSTOMERS_STATUS_BASE', '<strong>Basis Kundengruppe für Artikelpreise</strong>');
define('ENTRY_CUSTOMERS_STATUS_BASE', 'wird als Grundlage für die Preise der neuen Kundengruppe gewählt. Wenn Auswahl = Admin werden keine Preise für die neue Kundengruppe angelegt.');

define('IMAGE_ICON_INFO','action');
?>