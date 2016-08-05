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

  'TEXT_PAYPAL_CONFIG_CLIENT_LIVE' => 'Client ID Live:',
  'TEXT_PAYPAL_CONFIG_CLIENT_LIVE_INFO' => 'Create a new app for that data in your PayPal account.',

  'TEXT_PAYPAL_CONFIG_SECRET_LIVE' => 'Secret Live:',
  'TEXT_PAYPAL_CONFIG_SECRET_LIVE_INFO' => 'Create a new app for that data in your PayPal account.',

  'TEXT_PAYPAL_CONFIG_CLIENT_SANDBOX' => 'Client ID Sandbox:',
  'TEXT_PAYPAL_CONFIG_CLIENT_SANDBOX_INFO' => 'Create a new app for that data in your PayPal account.',

  'TEXT_PAYPAL_CONFIG_SECRET_SANDBOX' => 'Secret Sandbox:',
  'TEXT_PAYPAL_CONFIG_SECRET_SANDBOX_INFO' => 'Create a new app for that data in your PayPal account.',

  'TEXT_PAYPAL_CONFIG_MODE' => 'Mode:',
  'TEXT_PAYPAL_CONFIG_MODE_INFO' => '',

  'TEXT_PAYPAL_CONFIG_INVOICE_PREFIX' => 'Prefix for order ID:',
  'TEXT_PAYPAL_CONFIG_INVOICE_PREFIX_INFO' => 'Arbitrary string of letters (prefix), which is placed in front of each order number and is used for generating the PayPal invoice number.<br />This allows multiple store operation with only one PayPal App. Conflicts regarding the order numbers are avoided. Each order has its own invoice numbers within the PayPal account.',

  'TEXT_PAYPAL_CONFIG_TRANSACTION' => 'Transaction:',
  'TEXT_PAYPAL_CONFIG_TRANSACTION_INFO' => 'Chose type of Transaction.<br/><br/><b>Note:</b> With PayPal Plus always a Sale is made.',

  'TEXT_PAYPAL_CONFIG_CAPTURE' => 'Capture manually:',
  'TEXT_PAYPAL_CONFIG_CAPTURE_INFO' => 'Manually capture PayPal payments?<br/><br/><b>Note:</b> Therefore it is necessary that the Transaction is set to Authorize.',

  'TEXT_PAYPAL_CONFIG_CART' => 'Cart:',
  'TEXT_PAYPAL_CONFIG_CART_INFO' => 'Transfer cart details to PayPal?<br/><br/><b>Note:</b> This setting can cause problems when using ot-modules under "Modules" -> "Order Total", which grant a discount or surcharge ("Discount [ot_discount]", "Discount Coupons [ot_coupon]", "Gift Vouchers [ot_gv]", "Payment type discount &amp; surcharge [ot_payment]", etc.).<br/>Recommended setting: "no"',

  'TEXT_PAYPAL_CONFIG_STATE_SUCCESS' => 'Status success:',
  'TEXT_PAYPAL_CONFIG_STATE_SUCCESS_INFO' => 'Status for success order',

  'TEXT_PAYPAL_CONFIG_STATE_REJECTED' => 'Status rejected:',
  'TEXT_PAYPAL_CONFIG_STATE_REJECTED_INFO' => 'Status for rejected order',

  'TEXT_PAYPAL_CONFIG_STATE_PENDING' => 'Status pending:',
  'TEXT_PAYPAL_CONFIG_STATE_PENDING_INFO' => 'Status after successful order, that was not yet confirmed by PayPal',

  'TEXT_PAYPAL_CONFIG_STATE_CAPTURED' => 'Status captured:',
  'TEXT_PAYPAL_CONFIG_STATE_CAPTURED_INFO' => 'Status for captured order',
  
  'TEXT_PAYPAL_CONFIG_STATE_REFUNDED' => 'Status refunded:',
  'TEXT_PAYPAL_CONFIG_STATE_REFUNDED_INFO' => 'Status for refunded order',
  
  'TEXT_PAYPAL_CONFIG_STATE_TEMP' => 'Status temp:',
  'TEXT_PAYPAL_CONFIG_STATE_TEMP_INFO' => 'Status for unconfirmed order',

  'TEXT_PAYPAL_CONFIG_LOG' => 'Log:',
  'TEXT_PAYPAL_CONFIG_LOG_INFO' => 'Shall a log be written?',

  'TEXT_PAYPAL_CONFIG_LOG_LEVEL' => 'Log Level:',
  'TEXT_PAYPAL_CONFIG_LOG_LEVEL_INFO' => '<b>Note:</b> In live mode, it is only logged up to level FINE.',
  
  'BUTTON_PAYPAL_STATUS_INSTALL' => 'Install orders status',
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