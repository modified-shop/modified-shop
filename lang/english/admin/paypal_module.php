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
  'TEXT_PAYPAL_MODULE_HEADING_TITLE' => 'PayPal Products',
  
  'TABLE_HEADING_MODULES' => 'Module',
  'TABLE_HEADING_FILENAME' => 'Module name (for internal usage)',
  'TABLE_HEADING_SORT_ORDER' => 'Sorting',
  'TABLE_HEADING_STATUS' => 'Status',
  'TABLE_HEADING_ACTION' => 'Action',

  'TABLE_HEADING_WALL_STATUS' => 'Display at Paymentwall',
  'TABLE_HEADING_WALL_DESCRIPTION' => 'Description',
  
  'TEXT_PAYPAL_MODULE_PROFILE' => 'Profile',
  'TEXT_PAYPAL_NO_PROFILE' => 'no Webprofile',
  'TEXT_PAYPAL_STANDARD_PROFILE' => 'Standard Webprofile',
  
  'TEXT_PAYPAL_MODULE_LINK_SUCCESS' => 'Link at checkout',
  'TEXT_PAYPAL_MODULE_LINK_SUCCESS_INFO' => 'Shall the payment link be displayed in the checkout?',

  'TEXT_PAYPAL_MODULE_LINK_ACCOUNT' => 'Link at account',
  'TEXT_PAYPAL_MODULE_LINK_ACCOUNT_INFO' => 'Shall the payment link be displayed in the account?',

  'TEXT_PAYPAL_MODULE_PRODUCT' => 'Button at product',
  'TEXT_PAYPAL_MODULE_PRODUCT_INFO' => 'Shall the PayPal button be displayed in the product details?',

  'TEXT_PAYPAL_MODULE_CART_BNPL' => 'BNPL Button in cart',
  'TEXT_PAYPAL_MODULE_CART_BNPL_INFO' => 'Shall the PayPal button be displayed in the shopping cart?',

  'TEXT_PAYPAL_MODULE_PRODUCT_BNPL' => 'BNPL Button at product',
  'TEXT_PAYPAL_MODULE_PRODUCT_BNPL_INFO' => 'Shall the PayPal button be displayed in the product details?',

  'TEXT_PAYPAL_MODULE_CHECKOUT_BNPL' => 'BNPL Button in checkout',
  'TEXT_PAYPAL_MODULE_CHECKOUT_BNPL_INFO' => 'Shall the PayPal button be displayed in the checkout?',

  'TEXT_PAYPAL_MODULE_SAVE_PAYMENT' => 'Save payment method in the checkout',
  'TEXT_PAYPAL_MODULE_SAVE_PAYMENT_INFO' => 'Should the payment method be saved for a faster checkout for another order?',

  'TEXT_PAYPAL_MODULE_ACDC_EXTEND_CARDS' => 'Allow Creditcards without 3D Secure',
  'TEXT_PAYPAL_MODULE_ACDC_EXTEND_CARDS_INFO' => 'There is no liability shift without 3D Secure.',

  'TEXT_PAYPAL_MODULE_USE_TABS' => 'Accordion / Tabs',
  'TEXT_PAYPAL_MODULE_USE_TABS_INFO' => 'Does the template use accordion or tabs in the checkout?',

  'TEXT_PAYPAL_MODULE_SHIPPING_COST' => 'provisional shipping costs',
  'TEXT_PAYPAL_MODULE_SHIPPING_COST_INFO' => 'Amount for provisional shipping costs.',

  'TEXT_PAYPAL_MODULE_ORDER_STATUS_ACCEPTED' => 'Order status',
  'TEXT_PAYPAL_MODULE_ORDER_STATUS_ACCEPTED_INFO' => 'Select the order status.<br/><b>Important:</b> the name must not contain the word "paid".',
  
  'TEXT_PAYPAL_MODULE_UPSTREAM_PRODUCT' => 'Show at product',
  'TEXT_PAYPAL_MODULE_UPSTREAM_PRODUCT_INFO' => 'Show details for Installment at product?',

  'TEXT_PAYPAL_MODULE_UPSTREAM_CART' => 'Show at cart',
  'TEXT_PAYPAL_MODULE_UPSTREAM_CART_INFO' => 'Show details for Installment at cart?',

  'TEXT_PAYPAL_MODULE_UPSTREAM_PAYMENT' => 'Show at checkout',
  'TEXT_PAYPAL_MODULE_UPSTREAM_PAYMENT_INFO' => 'Show details for Installment during checkout?',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
