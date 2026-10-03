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
include(DIR_FS_CATALOG.'lang/german/modules/payment/klarna.php');

$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_TITLE'] = 'Klarna Express Checkout';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_MESSAGE'] = 'Der Klarna Express Checkout wurde abgebrochen.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_CLIENT_ID_TITLE'] = 'Client-ID';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_CLIENT_ID_DESC'] = 'Client-ID aus dem Klarna Partner Portal (Payment settings &gt; Client Identifiers). Die Shop-Domain muss dort unter Allowed Origins eingetragen sein.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_SHORT_CHECKOUT_TITLE'] = 'Kurz-Checkout';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_SHORT_CHECKOUT_DESC'] = 'Nach dem Express Checkout direkt zur Bestellbest&auml;tigung wechseln.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_BUTTON_CART_TITLE'] = 'Button im Warenkorb';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_BUTTON_CART_DESC'] = 'Klarna Express Button im Warenkorb anzeigen.';

foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
