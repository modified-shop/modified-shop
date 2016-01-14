<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


$lang_array = array(
  'TEXT_PAYPAL_CONFIG_HEADING_TITLE' => 'PayPal Configuration',

  'TEXT_PAYPAL_CONFIG_CLIENT' => 'Client ID:',
  'TEXT_PAYPAL_CONFIG_CLIENT_INFO' => 'Create in your PayPal account a new app for that data',

  'TEXT_PAYPAL_CONFIG_SECRET' => 'Secret:',
  'TEXT_PAYPAL_CONFIG_SECRET_INFO' => 'Create in your PayPal account a new app for that data',

  'TEXT_PAYPAL_CONFIG_MODE' => 'Mode:',
  'TEXT_PAYPAL_CONFIG_MODE_INFO' => '',

  'TEXT_PAYPAL_CONFIG_TRANSACTION' => 'Transaction:',
  'TEXT_PAYPAL_CONFIG_TRANSACTION_INFO' => 'Chhose type of Transaction.<br/><br/><b>Note:</b> With PayPal Plus always a Sale is made.',

  'TEXT_PAYPAL_CONFIG_CAPTURE' => 'Capture manually:',
  'TEXT_PAYPAL_CONFIG_CAPTURE_INFO' => 'Manually capture PayPal payments?<br/><br/><b>Note:</b> Therefore it is necessary that the Transaction is set to Authorize.',

  'TEXT_PAYPAL_CONFIG_CART' => 'Cart:',
  'TEXT_PAYPAL_CONFIG_CART_INFO' => 'Transfer cart details to PayPal?',

  'TEXT_PAYPAL_CONFIG_STATE_SUCCESS' => 'Status success:',
  'TEXT_PAYPAL_CONFIG_STATE_SUCCESS_INFO' => 'Status for success order',

  'TEXT_PAYPAL_CONFIG_STATE_REJECTED' => 'Status rejected:',
  'TEXT_PAYPAL_CONFIG_STATE_REJECTED_INFO' => 'Status for rejected order',

  'TEXT_PAYPAL_CONFIG_STATE_PENDING' => 'Status pending:',
  'TEXT_PAYPAL_CONFIG_STATE_PENDING_INFO' => 'Status after successful order, but it was not yet confirmed by PayPal',

  'TEXT_PAYPAL_CONFIG_STATE_CAPTURED' => 'Status captured:',
  'TEXT_PAYPAL_CONFIG_STATE_CAPTURED_INFO' => 'Status for captured order',
  
  'TEXT_PAYPAL_CONFIG_STATE_REFUNDED' => 'Status refunded:',
  'TEXT_PAYPAL_CONFIG_STATE_REFUNDED_INFO' => 'Status for refunded order',
  
  'TEXT_PAYPAL_CONFIG_STATE_TEMP' => 'Status temp:',
  'TEXT_PAYPAL_CONFIG_STATE_TEMP_INFO' => 'Status for temp order',

  'TEXT_PAYPAL_CONFIG_LOG' => 'Log:',
  'TEXT_PAYPAL_CONFIG_LOG_INFO' => 'write log?',

  'TEXT_PAYPAL_CONFIG_LOG_LEVEL' => 'Log Level:',
  'TEXT_PAYPAL_CONFIG_LOG_LEVEL_INFO' => '<b>Note:</b> In live mode, only to level FINE is logged.',
  
  'BUTTON_PAYPAL_STATUS_INSTALL' => 'install orders status',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}


// orders status
$PAYPAL_INST_ORDER_STATUS_TMP_NAME = 'PayPal canceled';
$PAYPAL_INST_ORDER_STATUS_SUCCESS_NAME = 'PayPal success';
$PAYPAL_INST_ORDER_STATUS_PENDING_NAME = 'PayPal pending';
$PAYPAL_INST_ORDER_STATUS_CAPTURED_NAME = 'PayPal captured';
$PAYPAL_INST_ORDER_STATUS_REFUNDED_NAME = 'PayPal refunded';
$PAYPAL_INST_ORDER_STATUS_REJECTED_NAME = 'PayPal rejected';
?>