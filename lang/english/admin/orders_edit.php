<?php
/* --------------------------------------------------------------
   $Id: orders_edit.php,v 1.0 

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(orders.php,v 1.27 2003/02/16); www.oscommerce.com 
   (c) 2003	 nextcommerce (orders.php,v 1.7 2003/08/14); www.nextcommerce.org
   (c) 2006 XT-Commerce (orders_edit.php)

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

// Allgemeine Texte
define('TABLE_HEADING', 'Edit Order');
define('TABLE_HEADING_ORDER', 'Order #:&nbsp;');
define('TEXT_SAVE_ORDER', 'Finish Editing and Recalculate Order');

define('TEXT_EDIT_ADDRESS', 'Edit Address');
define('TEXT_EDIT_PRODUCTS', 'Edit Products');
define('TEXT_EDIT_SHIPPING', 'Edit Shipping Information');
define('TEXT_EDIT_GIFT', 'Add or Edit Gift Vouchers or Discount');
define('TEXT_EDIT_ADDRESS_SUCCESS', 'Address Saved');

define('IMAGE_EDIT_ADDRESS', 'Edit Address');
define('IMAGE_EDIT_PRODUCTS', 'Edit Products');
define('IMAGE_EDIT_OTHER', 'Edit Shipping Costs, Payment Terms, Vouchers and more');

// Adressaenderung
define('TEXT_INVOICE_ADDRESS', 'Customer Address');
define('TEXT_SHIPPING_ADDRESS', 'Shipping Address');
define('TEXT_BILLING_ADDRESS', 'Billing Address');

define('TEXT_COMPANY', 'Company:');
define('TEXT_NAME', 'Name:');
define('TEXT_STREET', 'Street');
define('TEXT_ZIP', 'Postcode:');
define('TEXT_CITY', 'City:');
define('TEXT_COUNTRY', 'Country:');
define('TEXT_CUSTOMER_GROUP', 'Customer Group from Order');
define('TEXT_CUSTOMER_EMAIL', 'E-mail:');
define('TEXT_CUSTOMER_TELEPHONE', 'Phone:');
define('TEXT_CUSTOMER_UST', 'VAT Reg No:');
define('TEXT_CUSTOMER_CID', 'Customer ID:');
define('TEXT_ORDERS_ADDRESS_EDIT_INFO', 'Please note that the data you provide here will be changed only in the orders data and not in the customer account!');

// Artikelbearbeitung

define('TEXT_SMALL_NETTO', '(net)');
define('TEXT_PRODUCT_ID', 'pID:');
define('TEXT_PRODUCTS_MODEL', 'Product #:');
define('TEXT_QUANTITY', 'Qty:');
define('TEXT_PRODUCT', 'Product:');
define('TEXT_TAX', 'Tax:');
define('TEXT_PRICE', 'Price:');
define('TEXT_FINAL', 'Total:');
define('TEXT_PRODUCT_SEARCH', 'Search Products:');

define('TEXT_PRODUCT_OPTION', 'Attributes:');
define('TEXT_PRODUCT_OPTION_VALUE', 'Option Value:');
define('TEXT_PRICE_PREFIX', 'Price Prefix:');
define('TEXT_SAVE_ORDER', 'Finish and Recalculate');
define('TEXT_INS', 'Add:');
define('TEXT_SHIPPING', 'Shipping Costs');
define('TEXT_COD_COSTS', 'COD Costs');
define('TEXT_VALUE', 'Price');
define('TEXT_DESC', 'insert');

// Sonstiges

define('TEXT_PAYMENT', 'Payment:');
define('TEXT_SHIPPING', 'Shipping Module:');
define('TEXT_LANGUAGE', 'Language:');
define('TEXT_CURRENCIES', 'Currency:');
define('TEXT_ORDER_TOTAL', 'Total:');
define('TEXT_SAVE', 'Save');
define('TEXT_ACTUAL', 'actual:');
define('TEXT_NEW', 'new:');
define('TEXT_PRICE', 'Costs: ');

// web28 2010-12-07 add new defines
define('TEXT_ADD_TAX','incl. ');
define('TEXT_NO_TAX','plus ');

define('TEXT_ORDERS_EDIT_INFO', '<b> Important Notes: </ b> <br>
Please check with the address/customer data, the right customer group choose <br>
When you change the customer group, all invoice items are newly save! <br>
Shippincosts must be changed manually! <br>
In this case, depending on the customer group the shippingcost entered gross or net! <br>
');

define('TEXT_CUSTOMER_GROUP_INFO', ' <span style="background:#FFD6D6;padding:3px;border:solid 1px red;">When you change the customer group, all invoice items are newly save!</span>');

//web28 2011-05-08 - new error input handling
define('TEXT_ORDER_TITLE', 'Title:');
define('TEXT_ORDER_VALUE', 'Value:');
define('ERROR_INPUT_TITLE', 'No title input');
define('ERROR_INPUT_EMPTY', 'No title and value input');
define('ERROR_INPUT_SHIPPING_TITLE', 'It has not yet selected a shipping module!');

//web28 2011-07-11 - new note for graduated prices
define('TEXT_ORDERS_PRODUCT_EDIT_INFO', '<b>Note:</b> For volume discounts must be manually adjusted the unit price!');

//web28 2011-09-23 - add first- and lastname
define('TEXT_FIRSTNAME', 'Firstname:');
define('TEXT_LASTNAME', 'Lastname:');

define('TEXT_SAVE_CUSTOMERS_DATA', 'Save Customers Data');

define('TEXT_PRODUCTS_SEARCH_INFO', ' Products name or products model or EAN');
define('TEXT_PRODUCTS_STATUS', 'Status:');
define('TEXT_PRODUCTS_IMAGE', 'Product images:');
define('TEXT_PRODUCTS_QTY', 'Stock:');
define('TEXT_PRODUCTS_EAN', 'EAN:');
define('TEXT_PRODUCTS_TAX_RATE', 'Tax:');
define('TEXT_PRODUCTS_DATE_AVAILABLE', 'Date Available:');
define('TEXT_IMAGE_NONEXISTENT', '---');
?>