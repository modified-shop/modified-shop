<?php
/* -----------------------------------------------------------------------------------------
   $Id: coupon_admin.php 1502 2010-11-16 18:13:52Z dokuman $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(coupon_admin.php,v 1.1.2.5 2003/05/13); www.oscommerce.com

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contributions:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c  Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('TOP_BAR_TITLE', 'Statistics');
define('HEADING_TITLE', 'Discount Coupons');
define('HEADING_TITLE_STATUS', 'Status : ');
define('TEXT_CUSTOMER', 'Customer:');
define('TEXT_COUPON', 'Coupon Name');
define('TEXT_COUPON_ALL', 'All Coupons');
define('TEXT_COUPON_ACTIVE', 'Active Coupons');
define('TEXT_COUPON_INACTIVE', 'Inactive Coupons');
define('TEXT_SUBJECT', 'Subject:');
define('TEXT_FROM', 'From:');
define('TEXT_FREE_SHIPPING', 'Free Shipping');
define('TEXT_MESSAGE', 'Message:');
define('TEXT_SELECT_CUSTOMER', 'Select Customer');
define('TEXT_ALL_CUSTOMERS', 'All Customers');
define('TEXT_NEWSLETTER_CUSTOMERS', 'To All Newsletter Subscribers');
define('TEXT_CONFIRM_DELETE', 'By klicking <b>Confirm</b> the selected coupon will be set <b>inactive</b>. Note: This coupon can not be reactivated for further usage. <br /><br />Do you really want to deactivate this coupon?');

define('TEXT_TO_REDEEM', 'You can redeem this coupon during checkout. Just enter the code in the box provided, and click on the redeem button.');
define('TEXT_IN_CASE', ' in case you have any problems. ');
define('TEXT_VOUCHER_IS', 'The coupon code is ');
define('TEXT_REMEMBER', 'Don\'t lose the coupon code, make sure to keep the code safe so you can benefit from this special offer.');
define('TEXT_VISIT', 'when you visit ' . HTTP_SERVER . DIR_WS_CATALOG);
define('TEXT_ENTER_CODE', ' and enter the code ');

define('TABLE_HEADING_ACTION', 'Action');

define('CUSTOMER_ID', 'Customer ID');
define('CUSTOMER_NAME', 'Customer Name');
define('REDEEM_DATE', 'Date Redeemed');
define('IP_ADDRESS', 'IP Address');

define('TEXT_REDEMPTIONS', 'Redemptions');
define('TEXT_REDEMPTIONS_TOTAL', 'In Total');
define('TEXT_REDEMPTIONS_CUSTOMER', 'For this Customer');
define('TEXT_NO_FREE_SHIPPING', 'No Free Shipping');

define('NOTICE_EMAIL_SENT_TO', 'Notice: email sent to: %s');
define('ERROR_NO_CUSTOMER_SELECTED', 'Error: No customer has been selected.');
define('COUPON_NAME', 'Coupon Name');
define('COUPON_AMOUNT', 'Coupon Amount');
define('COUPON_CODE', 'Coupon Code');
define('COUPON_STARTDATE', 'Start Date');
define('COUPON_FINISHDATE', 'End Date');
define('COUPON_FREE_SHIP', 'Free Shipping');
define('COUPON_DESC', 'Coupon Description');
define('COUPON_MIN_ORDER', 'Coupon Minimum Order');
define('COUPON_USES_COUPON', 'Uses per Coupon');
define('COUPON_USES_USER', 'Uses per Customer');
define('COUPON_PRODUCTS', 'Valid Product List');
define('COUPON_CATEGORIES', 'Valid Categories List');
define('VOUCHER_NUMBER_USED', 'Number Used');
define('DATE_CREATED', 'Date Created');
define('DATE_MODIFIED', 'Date Modified');
define('TEXT_HEADING_NEW_COUPON', 'Create New Coupon');
define('TEXT_NEW_INTRO', 'Please fill out the following information for the new coupon.<br />');


define('COUPON_NAME_HELP', 'A short name for the coupon');
define('COUPON_AMOUNT_HELP', 'The value of the discount for the coupon, either fixed or add a % on the end for a percentage discount.');
define('COUPON_CODE_HELP', 'You can enter your own code here, or leave blank for an auto generated one.');
define('COUPON_STARTDATE_HELP', 'The date the coupon will be valid from');
define('COUPON_FINISHDATE_HELP', 'The date the coupon expires');
define('COUPON_FREE_SHIP_HELP', 'The coupon gives free shipping on an order. Note. This overrides the coupon_amount figure but respects the minimum order value');
define('COUPON_DESC_HELP', 'A description of the coupon for the customer');
define('COUPON_MIN_ORDER_HELP', 'The minimum order value before the coupon is valid');
define('COUPON_USES_COUPON_HELP', 'The maximum number of times the coupon can be used, leave blank if you want no limit.');
define('COUPON_USES_USER_HELP', 'Number of times a user can use the coupon, leave blank for no limit.');
define('COUPON_PRODUCTS_HELP', 'A comma separated list of product_ids that this coupon can be used with. Leave blank for no restrictions.');
define('COUPON_CATEGORIES_HELP', 'A comma separated list of cpaths that this coupon can be used with, leave blank for no restrictions.');

define('COUPON_ID', 'cID');
define('BUTTON_DELETE_NO_CONFIRM', 'Delete without confirmation');
define('TEXT_NONE', 'no restrictions');
define('TEXT_COUPON_DELETE', 'Delete');
define('TEXT_COUPON_STATUS', 'Status');
define('TEXT_COUPON_DETAILS', 'Coupon details');
define('TEXT_COUPON_EMAIL', 'send email');
define('TEXT_COUPON_OVERVIEW', 'Overview');
define('TEXT_COUPON_EMAIL_PREVIEW', 'Confirmation');
define('TEXT_COUPON_MINORDER', 'min. Order Value');
define('TEXT_VIEW', 'List view');
define('TEXT_VIEW_SHORT', 'Show');

//BOF - web28 - 2011-04-13 - ADD Coupon message infos
define('COUPON_MINORDER_INFO', "\nCoupon Minimum Order: ");
define('COUPON_RESTRICT_INFO', "\nThis coupon is only valid for certain products!"); 
define('COUPON_INFO', "\nCoupon Amount: "); 
define('COUPON_FREE_SHIPPING', 'Free Shipping');
define('COUPON_LINK_TEXT', '\n\nDetails');
define('COUPON_CATEGORIES_RESTRICT', '\nValid for these categories');
define('COUPON_PRODUCTS_RESTRICT', '\nValid for these products');
define('COUPON_NO_RESTRICT', '\nValid for all products');; 
//EOF - web28 - 2011-04-13 - ADD Coupon message infos

//BOF - web28 - 2011-07-05 - ADD error message
define('ERROR_NO_COUPON_NAME', 'ERROR: No Coupon Name ');
define('ERROR_NO_COUPON_AMOUNT', 'ERROR: No Coupon Amount ');
//EOF - web28 - 2011-07-05 - ADD error message
?>