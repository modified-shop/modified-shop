<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$klarna_code = 'KLARNA_EXPRESS';
include(DIR_FS_CATALOG.'lang/english/modules/payment/klarna.php');

$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_TITLE'] = 'Klarna Express Checkout';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_SELECTION'] = 'Klarna';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_INFO'] = 'You have already chosen the payment method at Klarna.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_DESCRIPTION'] = 'The Klarna Express Checkout shows a button in the shopping cart. The customer enters the address in Klarna and continues the checkout in the shop from the shipping method on. The customer chooses the payment method at Klarna. The order is placed with this module, the other Klarna payment methods do not have to be enabled. The button is not shown for carts with virtual products only.<br /><br />Client ID: create it in the Klarna Partner Portal under <b>Payment settings &gt; Client Identifiers</b>. The shop domain (e.g. https://www.myshop.com, without path) must be listed there under <b>Allowed Origins</b>, otherwise the button does not load. Username and password are the same as in the other Klarna modules.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_MESSAGE'] = 'The Klarna Express Checkout was cancelled.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_CALLBACK'] = 'The Klarna Express Checkout could not be started. Please try again or continue to the checkout.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_ADDRESS'] = 'The address from Klarna is incomplete or the shop does not deliver there. Please continue to the checkout and enter your address there.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_CLIENT_ID_TITLE'] = 'Client ID';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_CLIENT_ID_DESC'] = 'Client ID from the Klarna Partner Portal (Payment settings &gt; Client Identifiers). The shop domain must be listed there under Allowed Origins.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_SHORT_CHECKOUT_TITLE'] = 'Short checkout';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_SHORT_CHECKOUT_DESC'] = 'Go straight to the order confirmation after the Express Checkout. Not implemented yet, the checkout always starts at the shipping method.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_BUTTON_CART_TITLE'] = 'Button in the shopping cart';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_BUTTON_CART_DESC'] = 'Show the Klarna Express button in the shopping cart.';

foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
