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
  'TEXT_PAYPAL_TRANSACTION_ACCOUNT_OWNER' => 'Account owner:',
  'TEXT_PAYPAL_TRANSACTION_ADDRESS' => 'Shipping Address:',
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
  'TEXT_PAYPAL_INSTRUCTIONS_CHECKOUT_SHORT' => 'Please transfer the amount of %s to the following account:',
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
  'TEXT_PAYPAL_REFUND_COMMENT' => 'Comment:<br />(max 127 characters)',
  'TEXT_PAYPAL_REFUND_AMOUNT' => 'Amount:',
  'TEXT_PAYPAL_REFUND_SUBMIT' => 'Refund',
  
  
  // capture
  'TEXT_PAYPAL_CAPTURE' => 'Capture',
  'TEXT_PAYPAL_CAPTURE_LEFT' => 'Amount possible captures: ',
  'TEXT_PAYPAL_CAPTURE_IS_FINAL' => 'Final capture:',
  'TEXT_PAYPAL_CAPTURE_AMOUNT' => 'Amount:',
  'TEXT_PAYPAL_CAPTURE_SUBMIT' => 'Capture',
  'TEXT_PAYPAL_CAPTURED' => 'Payment captured',
  
  
  // products
  'TEXT_PAYPAL_PRODUCTS_TYPE' => 'Products type',
  'TEXT_PAYPAL_CREATE_PRODUCT' => 'Create Product',
  
  
  // subscriptions
  'TEXT_PAYPAL_SUBSCRIPTIONS_HEADING' => 'PayPal Subscriptions',
  'TEXT_PAYPAL_PLANS' => 'Plan',
  'TEXT_PAYPAL_NEW_PLAN' => 'New Plan',
  'TEXT_PAYPAL_PLAN_SAVE' => 'Create Plan',
  'TEXT_PAYPAL_PLAN_PATCH' => 'Save Plan',
  'TEXT_PAYPAL_PLAN_STATUS' => 'Status',
  'TEXT_PAYPAL_PLAN_NAME' => 'Description',
  'TEXT_PAYPAL_PLAN_DAY_NAME_INFO' => 'The name is displayed to the customer and can not be changed.',
  'TEXT_PAYPAL_PLAN_INTERVAL' => 'Payment',
  'TEXT_PAYPAL_PLAN_CYCLE' => 'Duration',
  'TEXT_PAYPAL_PLAN_CYCLE_NO_LIMIT' => 'until further notice',
  'TEXT_PAYPAL_PLAN_FIXED_PRICE' => 'Price',
  'TEXT_PAYPAL_PLAN_SETUP_FEE' => 'Setup fee',
  'TEXT_PAYPAL_PLAN_TAX_CLASS' => 'Tax class',
  'TEXT_PAYPAL_PLAN_TAX_INCLUDE' => 'Tax included',
  
  'TEXT_PAYPAL_PLAN_DAY' => 'Daily',
  'TEXT_PAYPAL_PLAN_WEEK' => 'Weekly',
  'TEXT_PAYPAL_PLAN_MONTH' => 'Monthly',
  'TEXT_PAYPAL_PLAN_YEAR' => 'Yearly',
  
  'TEXT_PAYPAL_NO_CHANGE' => 'can not be changed',
  'TEXT_ACTIVE' => 'activated',
  'TEXT_INACTIVE' => 'deactivated',

  'TEXT_NONE' => '--none--',
  'TEXT_YES' => 'yes',
  'TEXT_NO' => 'no',
  
  'TEXT_PAYPAL_BILLING' => 'Payments',
  'TEXT_PAYPAL_BILLING_OUTSTANDING' => 'outstanding balance:',
  'TEXT_PAYPAL_BILLING_CYCLES_COMPLETED' => 'Payments completed:',
  'TEXT_PAYPAL_BILLING_CYCLES_REMAINING' => 'Payments remaining:',
  'TEXT_PAYPAL_BILLING_CYCLES_TOTAL' => 'Payments total:',
  'TEXT_PAYPAL_BILLING_TIME_NEXT' => 'next payment:',
  'TEXT_PAYPAL_BILLING_TIME_FINAL' => 'final payment:',
  'TEXT_PAYPAL_BILLING_FAILED' => 'failed:',
  'TEXT_PAYPAL_CANCEL' => 'cancel payment',
  'TEXT_PAYPAL_CANCEL_SUBMIT' => 'Confirm',

  // error
  'TEXT_PAYPAL_ERROR_AMOUNT' => 'Please enter an valid amount',
  'TEXT_PAYPAL_ERROR_ALREADY_PAID' => 'We have already received your payment. Thanks a lot!',
  'TEXT_PAYPAL_ERROR_NO_PLAN' => 'Please choose a plan.',
  'TEXT_PAYPAL_ERROR_MAX_PRODUCTS' => 'This product can only be purchased on its own.',
  'TEXT_PAYPAL_ERROR_CANCEL' => 'An error occurred while canceling.',
  'TEXT_PAYPAL_ERROR_SUBSCRIPTION_PRODUCTS' => 'You have a subscription product in your shopping cart which can only be bought alone.',  
  
  // diverse
  'MODULE_PAYMENT_PAYPAL_TEXT_ORDER' => 'Your order at '.STORE_NAME,

  // status
  'TEXT_PAYPAL_NO_STATUS_CHANGE' => 'no status change',
  
  // template
  'TEXT_PAYPALINSTALLMENT_HEADING' => 'Pay easily in monthly installments',
  'TEXT_PAYPALINSTALLMENT_DESCRIPTION' => 'You can choose your installment payment and the appropriate financing plan as part of the ordering process. Your application is completely online and will be completed in a few steps here in the shop.',

  'TEXT_PAYPALINSTALLMENT_RATING_PLAN' => 'Financing from %s with %s Installments Powered by PayPal',
  'TEXT_PAYPALINSTALLMENT_RATING_PLAN_SHORT' => 'Financing from %s in the month with',

  'TEXT_PAYPALINSTALLMENT_LEGAL' => 'Representative example according to &sect; 6a PAngV',
  'TEXT_PAYPALINSTALLMENT_NOMINAL_RATE' => 'Nominal rate',
  'TEXT_PAYPALINSTALLMENT_APR' => 'Effective interest rate',
  'TEXT_PAYPALINSTALLMENT_TOTAL_COST' => 'Total amount',
  'TEXT_PAYPALINSTALLMENT_TOTAL_NETTO' => 'Net loan amount',
  'TEXT_PAYPALINSTALLMENT_TOTAL_INTEREST' => 'Interest',
  'TEXT_PAYPALINSTALLMENT_MONTHLY_PAYMENT' => 'Monthly installments of each',

  'TEXT_PAYPALINSTALLMENT_NOTICE' => 'Financing available from %s to %s basket value with',
  'TEXT_PAYPALINSTALLMENT_NOTICE_PRODUCT' => 'You can also finance this product!',
  'TEXT_PAYPALINSTALLMENT_NOTICE_CART' => 'You can also finance this basket!',
  'TEXT_PAYPALINSTALLMENT_NOTICE_PAYMENT' => 'You can also finance this order!',
  
  'TEXT_PAYPALINSTALLMENT_CREDITOR' => 'Borrower',
  'TEXT_PAYPALINSTALLMENT_INFO_LINK' => 'Information on possible rates',

);


// define 
foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
?>