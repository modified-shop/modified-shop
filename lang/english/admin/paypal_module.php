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

  'TEXT_PAYPAL_MODULE_BOX_CART' => 'Button in box cart',
  'TEXT_PAYPAL_MODULE_BOX_CART_INFO' => 'Shall the PayPal button be displayed in the pbox cart?',

  'TEXT_PAYPAL_MODULE_BOX_CART_BNPL' => 'BNPL Button in box cart',
  'TEXT_PAYPAL_MODULE_BOX_CART_BNPL_INFO' => 'Shall the PayPal button be displayed in the box cart?',

  'TEXT_PAYPAL_MODULE_SAVE_PAYMENT' => 'Save payment method in the checkout (PayPal Vault)',
  'TEXT_PAYPAL_MODULE_SAVE_PAYMENT_INFO' => 'Should the payment method used for PayPal be saved for a faster checkout for another order?',

  'TEXT_PAYPAL_MODULE_OFFER_SAVE_PAYMENT' => 'Allow saved payment method (PayPal Vault)',
  'TEXT_PAYPAL_MODULE_OFFER_SAVE_PAYMENT_INFO' => 'Allow a saved payment method for PayPal for a faster checkout?',

  'TEXT_PAYPAL_MODULE_ACDC_EXTEND_CARDS' => 'Allow Creditcards without 3D Secure',
  'TEXT_PAYPAL_MODULE_ACDC_EXTEND_CARDS_INFO' => 'There is no liability shift without 3D Secure.',

  'TEXT_PAYPAL_MODULE_USE_TABS' => 'Accordion / Tabs',
  'TEXT_PAYPAL_MODULE_USE_TABS_INFO' => 'Does the template use accordion or tabs in the checkout?',

  'TEXT_PAYPAL_MODULE_SHIPPING_COST' => 'provisional shipping costs',
  'TEXT_PAYPAL_MODULE_SHIPPING_COST_INFO' => 'Amount for provisional shipping costs.',
  
  'TEXT_PAYPAL_MODULE_UPSTREAM_PRODUCT' => 'Show at product',
  'TEXT_PAYPAL_MODULE_UPSTREAM_PRODUCT_INFO' => 'Show details for Installment at product?',

  'TEXT_PAYPAL_MODULE_UPSTREAM_CART' => 'Show at cart',
  'TEXT_PAYPAL_MODULE_UPSTREAM_CART_INFO' => 'Show details for Installment at cart?',

  'TEXT_PAYPAL_MODULE_UPSTREAM_PAYMENT' => 'Show at checkout',
  'TEXT_PAYPAL_MODULE_UPSTREAM_PAYMENT_INFO' => 'Show details for Installment during checkout?',

  'TEXT_PAYPAL_BUTTON_LAYOUT' => 'Button Layout',
  'TEXT_PAYPAL_BUTTON_LAYOUT_INFO' => 'Select the button layout',

  'TEXT_PAYPAL_BUTTON_SHAPE' => 'Button Shape',
  'TEXT_PAYPAL_BUTTON_SHAPE_INFO' => 'Select the button shape',

  'TEXT_PAYPAL_BUTTON_PRIMARY_COLOR' => 'Button Color Paypal',
  'TEXT_PAYPAL_BUTTON_PRIMARY_COLOR_INFO' => 'Select the button color',

  'TEXT_PAYPAL_BUTTON_SECONDARY_COLOR' => 'Button Color BNPL',
  'TEXT_PAYPAL_BUTTON_SECONDARY_COLOR_INFO' => 'Select the button color',

  'TEXT_PAYPAL_BUTTON_HEIGHT' => 'Button Height',
  'TEXT_PAYPAL_BUTTON_HEIGHT_INFO' => 'Select the button height in pixels (min: 22 max: 55)',

  'TEXT_PAYPAL_COMPANY_LABEL' => 'Company Label',
  'TEXT_PAYPAL_COMPANY_LABEL_INFO' => 'Use the company name what should be displayed on the bank or credit card statement. <b>Attention:</b> no specialchars or german umlauts allowed!',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
