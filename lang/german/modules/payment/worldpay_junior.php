<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2008 osCommerce(worldpay_junior.php 1807 2008-01-13 ); www.oscommerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_DESCRIPTION', '<img src="images/icon_popup.gif" border="0">&nbsp;<a href="http://www.worldpay.com" target="_blank" style="text-decoration: underline; font-weight: bold;">WorldPay Webseite besuchen</a>');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_WARNING_DEMO_MODE', 'In Review: Transaction performed in demo mode.');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_SUCCESSFUL_TRANSACTION', 'The payment transaction has been successfully performed!');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_UNSUCCESSFUL_TRANSACTION', 'Your payment has been unsuccessful!');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_CONTINUE_BUTTON', 'Click here to continue to %s');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_TITLE', 'WorldPay Junior');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_DESC', 'Worldpay Payment Module');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_STATUS_TITLE', 'Enable WorldPay Module');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_STATUS_DESC', 'Do you want to enable WorldPay payments?');
  
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_ALLOWED_TITLE' , 'Erlaubte Zonen');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID_TITLE', 'Worldpay Installation ID');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID_DESC', 'Your WorldPay Installation ID');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD_TITLE', 'Payment Response password');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD_DESC', 'A password that is sent back in the callback response (specified in the WorldPay Customer Management System)');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD_TITLE', 'MD5 secret for transactions Password');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD_DESC', 'The MD5 secret encryption password used to validate transaction responses with (specified in the WorldPay Customer Management System)');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TRANSACTION_METHOD_TITLE', 'Transaction Method');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TRANSACTION_METHOD_DESC', 'The processing method to use for each transaction');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE_TITLE', 'Test Mode');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE_DESC', 'Process transactions in test mode?');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_SORT_ORDER_TITLE', 'Sort order of display.');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_SORT_ORDER_DESC', 'Sort order of display. Lowest is displayed first.');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_PREAUTH_TITLE', 'Pre-Auth');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_PREAUTH_DESC', 'The mode you are working in (A = Pay Now, E = Pre Auth). Ignored if Use PreAuth is False.');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_ZONE_TITLE', 'Payment Zone');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_ZONE_DESC', 'If a zone is selected, only enable this payment method for that zone.');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID_TITLE', 'Set Preparing Order Status');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID_DESC', 'Set the status of prepared orders made with this payment module to this value');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID_TITLE', 'Set Order Status');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID_DESC', 'Set the status of orders made with this payment module to this value');

// BOF - Hendrik - 2010-08-11 - exlusion config for shipping modules
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_NEG_SHIPPING_TITLE', 'Exclusion in case of shipping');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_NEG_SHIPPING_DESC', 'deactivate this payment if one of these shippingtypes are selected (list separated by comma)');
// EOF - Hendrik - 2010-08-11 - exlusion config for shipping modules
?>