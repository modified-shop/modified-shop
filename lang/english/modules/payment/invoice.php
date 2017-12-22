<?php
/* -----------------------------------------------------------------------------------------
   $Id: invoice.php 1101 2005-07-24 14:51:13Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.28 2003/02/14); www.oscommerce.com
   (c) 2003	 nextcommerce (invoice.php,v 1.4 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('MODULE_PAYMENT_INVOICE_TEXT_DESCRIPTION', 'Invoice');
define('MODULE_PAYMENT_INVOICE_TEXT_TITLE', 'Invoice');
define('MODULE_PAYMENT_INVOICE_TEXT_INFO','');
define('MODULE_PAYMENT_INVOICE_STATUS_TITLE' , 'Enable Invoices Module');
define('MODULE_PAYMENT_INVOICE_STATUS_DESC' , 'Do you want to accept Invoices as payments?');
define('MODULE_PAYMENT_INVOICE_ORDER_STATUS_ID_TITLE' , 'Set Order Status');
define('MODULE_PAYMENT_INVOICE_ORDER_STATUS_ID_DESC' , 'Set the status of orders made with this payment module to this value.');
define('MODULE_PAYMENT_INVOICE_SORT_ORDER_TITLE' , 'Sort order of display.');
define('MODULE_PAYMENT_INVOICE_SORT_ORDER_DESC' , 'Sort order of display. Lowest is displayed first.');
define('MODULE_PAYMENT_INVOICE_ZONE_TITLE' , 'Payment Zone');
define('MODULE_PAYMENT_INVOICE_ZONE_DESC' , 'If a zone is selected, only enable this payment method for that zone.');
define('MODULE_PAYMENT_INVOICE_ALLOWED_TITLE' , 'Allowed zones');
define('MODULE_PAYMENT_INVOICE_ALLOWED_DESC' , 'Please enter the zones <b>separately</b> which should be allowed to use this modul (e. g. AT,DE (leave empty if you want to allow all zones))');
define('MODULE_PAYMENT_INVOICE_MIN_ORDER_TITLE' , 'Minimum Orders');
define('MODULE_PAYMENT_INVOICE_MIN_ORDER_DESC' , 'Minimum orders for a Customer to view this payment method.');
define('MODULE_PAYMENT_INVOICE_MIN_ORDER_STATUS_ID_TITLE' , 'Order status for calculation');
define('MODULE_PAYMENT_INVOICE_MIN_ORDER_STATUS_ID_DESC' , 'Select the status of the purchase orders here, which should be considered for the number entered under "Minimum Orders".');
define('MODULE_PAYMENT_INVOICE_MAX_AMOUNT_TITLE' , 'Maximum amount');
define('MODULE_PAYMENT_INVOICE_MAX_AMOUNT_DESC' , 'Please enter the maximum amount for paying per invoice.');
?>