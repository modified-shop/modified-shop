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
define('TEXT_SAVE_ORDER', 'Complete and recalculate order.&nbsp;');

define('TEXT_EDIT_ADDRESS', 'Address and customer details');
define('TEXT_EDIT_PRODUCTS', 'Products, products options and prices');
define('TEXT_EDIT_OTHER', 'Shipping costs, payment methods, currencies, languages, totals, VAT, discounts, etc.');
define('TEXT_EDIT_GIFT', 'Gift Vouchers and discount');
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
define('TEXT_ORDERS_ADDRESS_EDIT_INFO', 'Please note that the data you provide here will only be changed in the orders data and not in the customer account!');

// Artikelbearbeitung

define('TEXT_SMALL_NETTO', '(net)');
define('TEXT_PRODUCT_ID', 'pID:');
define('TEXT_PRODUCTS_MODEL', 'Product #:');
define('TEXT_QUANTITY', 'Qty:');
define('TEXT_WEIGHT', 'Weight:');
define('TEXT_PRODUCT', 'Product:');
define('TEXT_TAX', 'Tax:');
define('TEXT_PRICE', 'Price:');
define('TEXT_FINAL', 'Total:');
define('TEXT_PRODUCT_SEARCH', 'Search Products:');

define('TEXT_PRODUCT_OPTION', 'Attributes:');
define('TEXT_PRODUCT_OPTION_VALUE', 'Option Value:');
define('TEXT_PRICE_PREFIX', 'Price Prefix:');
define('TEXT_WEIGHT_PREFIX', 'Weight Prefix:');
define('TEXT_INS', 'Add:');
define('TEXT_COD_COSTS', 'COD Costs');
define('TEXT_VALUE', 'Price');
define('TEXT_DESC', 'insert');

// Sonstiges

define('TEXT_PAYMENT', 'Payment:');
define('TEXT_SHIPPING', 'Shipping Method:');
define('TEXT_LANGUAGE', 'Language:');
define('TEXT_CURRENCIES', 'Currency:');
define('TEXT_ORDER_TOTAL', 'Total:');
define('TEXT_SAVE', 'Save');
define('TEXT_ACTUAL', 'actual:');
define('TEXT_NEW', 'new:');

define('TEXT_ORDERS_EDIT_INFO', '<b>Important Notes:</b><br />
Please chose the right customer group with the address/customer data!<br />
When changing the customer group, every invoice item has to be newly saved!<br />
Shipping costs must be changed manually!<br />
In this case, shipping costs have to be entered gross or net depending on the customer group!<br />
');

define('TEXT_CUSTOMER_GROUP_INFO', ' When you change the customer group, all invoice items are newly save!');

define('TEXT_ORDER_TITLE', 'Title:');
define('TEXT_ORDER_VALUE', 'Value:');
define('ERROR_INPUT_TITLE', 'No title input');
define('ERROR_INPUT_EMPTY', 'No title and value input');
define('ERROR_INPUT_SHIPPING_TITLE', 'It has not yet selected a shipping module!');

// note for graduated prices
define('TEXT_ORDERS_PRODUCT_EDIT_INFO', '<b>Note:</b> For volume discounts must be manually adjusted the unit price!');

define('TEXT_FIRSTNAME', 'Firstname:');
define('TEXT_LASTNAME', 'Lastname:');

define('TEXT_GENDER', 'Salutation:'); 
define('TEXT_MR', 'Mr'); 
define('TEXT_MRS', 'Mrs');

define('TEXT_SAVE_CUSTOMERS_DATA', 'Save Customers Data');

define('TEXT_PRODUCTS_SEARCH_INFO', ' Products name or products model or GTIN/EAN');
define('TEXT_PRODUCTS_STATUS', 'Status:');
define('TEXT_PRODUCTS_IMAGE', 'Product images:');
define('TEXT_PRODUCTS_QTY', 'Stock:');
define('TEXT_PRODUCTS_EAN', 'GTIN/EAN:');
define('TEXT_PRODUCTS_TAX_RATE', 'Tax:');
define('TEXT_PRODUCTS_DATE_AVAILABLE', 'Date available:');
define('TEXT_IMAGE_NONEXISTENT', '---');

define('ERROR_STATUS_CHANGE', 'ATTENTION: After changing the customer group, shipping costs, surcharges and discounts may need to be adjusted manually.');