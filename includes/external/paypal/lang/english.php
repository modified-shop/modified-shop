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
  'TEXT_PAYPAL_ORDERS_HEADING' => 'PayPal Details',
  'TEXT_PAYPAL_NO_INFORMATION' => 'no paymentdetails available',
  
  // transaction
  'TEXT_PAYPAL_TRANSACTION' => 'Paymentdetails',
  'TEXT_PAYPAL_TRANSACTION_ADDRESS' => 'Address:',
  'TEXT_PAYPAL_TRANSACTION_METHOD' => 'Payment:',
  'TEXT_PAYPAL_TRANSACTION_EMAIL' => 'E-Mail Address:',
  'TEXT_PAYPAL_TRANSACTION_ACCOUNT_STATE' => 'Account Status:',
  'TEXT_PAYPAL_TRANSACTION_INTENT' => 'Intent:',
  'TEXT_PAYPAL_TRANSACTION_STATE' => 'Status:',
  
  
  // transactions
  'TEXT_PAYPAL_TRANSACTIONS_STATUS' => 'Transaktions',
  'TEXT_PAYPAL_TRANSACTIONS_PAYMENT' => 'Payment:',
  'TEXT_PAYPAL_TRANSACTIONS_REASON' => 'Reason:',
  'TEXT_PAYPAL_TRANSACTIONS_STATE' => 'Status:',
  'TEXT_PAYPAL_TRANSACTIONS_TOTAL' => 'Amount:',
  'TEXT_PAYPAL_TRANSACTIONS_VALID' => 'valid to:',
  'TEXT_PAYPAL_TRANSACTIONS_ID' => 'ID:',
  
  
  // instruction
  'TEXT_PAYPAL_INSTRUCTIONS' => 'Money order',
  'TEXT_PAYPAL_INSTRUCTIONS_CHECKOUT' => 'Please transfer the amount of %s at least to %s to the following account:',
  'TEXT_PAYPAL_INSTRUCTIONS_AMOUNT' => 'Amount:',
  'TEXT_PAYPAL_INSTRUCTIONS_REFERENCE' => 'Usage:',
  'TEXT_PAYPAL_INSTRUCTIONS_PAYDATE' => 'Payable to:',
  'TEXT_PAYPAL_INSTRUCTIONS_ACCOUNT' => 'Account:',
  'TEXT_PAYPAL_INSTRUCTIONS_HOLDER' => 'Holder:',
  'TEXT_PAYPAL_INSTRUCTIONS_IBAN' => 'IBAN:',
  'TEXT_PAYPAL_INSTRUCTIONS_BIC' => 'BIC:',
  
  
  // refund
  'TEXT_PAYPAL_REFUND' => 'Refund',
  'TEXT_PAYPAL_REFUND_LEFT' => 'Amount possible refunds: ',
  'TEXT_PAYPAL_REFUND_COMMENT' => 'Comment:',
  'TEXT_PAYPAL_REFUND_AMOUNT' => 'Amount:',
  'TEXT_PAYPAL_REFUND_SUBMIT' => 'Refund',
  
  
  // capture
  'TEXT_PAYPAL_CAPTURE' => 'Capture',
  'TEXT_PAYPAL_CAPTURE_LEFT' => 'Amount possible captures: ',
  'TEXT_PAYPAL_CAPTURE_IS_FINAL' => 'Final capture:',
  'TEXT_PAYPAL_CAPTURE_AMOUNT' => 'Amount:',
  'TEXT_PAYPAL_CAPTURE_SUBMIT' => 'Capture',
  'TEXT_PAYPAL_CAPTURED' => 'Payment captured',
  
  
  // error
  'TEXT_PAYPAL_ERROR_AMOUNT' => 'Please enter an valid amount',
  
  
  // diverse
  'MODULE_PAYMENT_PAYPAL_TEXT_ORDER' => 'Your order at '.STORE_NAME,


  // status
  'TEXT_PAYPAL_NO_STATUS_CHANGE' => 'no status change',
  
);


// define 
foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
?>