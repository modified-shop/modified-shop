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
  'MODULE_PAYMENT_PAYPALINSTALLMENT_TEXT_TITLE' => 'Ratenzahlung Powered by PayPal',
  'MODULE_PAYMENT_PAYPALINSTALLMENT_TEXT_INFO' => '<img src="https://www.paypal.com/de_DE/DE/i/logo/lockbox_150x47.gif" />',
  'MODULE_PAYMENT_PAYPALINSTALLMENT_TEXT_DESCRIPTION' => 'Installment Powered by PayPal<br/>You can find more information about Installment powered by PayPal <a target="_blank" href="https://www.paypal.com/de/webapps/mpp/installments">here</a>.',
  'MODULE_PAYMENT_PAYPALINSTALLMENT_ALLOWED_TITLE' => 'Allowed zones',
  'MODULE_PAYMENT_PAYPALINSTALLMENT_ALLOWED_DESC' => 'Please enter the zones <b>separately</b> which should be allowed to use this module (e.g. AT,DE (leave empty if you want to allow all zones))',
  'MODULE_PAYMENT_PAYPALINSTALLMENT_STATUS_TITLE' => 'Enable Ratenzahlung Powered by PayPal',
  'MODULE_PAYMENT_PAYPALINSTALLMENT_STATUS_DESC' => 'Do you want to accept Ratenzahlung Powered by PayPal payments?',
  'MODULE_PAYMENT_PAYPALINSTALLMENT_SORT_ORDER_TITLE' => 'Sort order',
  'MODULE_PAYMENT_PAYPALINSTALLMENT_SORT_ORDER_DESC' => 'Sort order of the view. Lowest numeral will be displayed first',
  'MODULE_PAYMENT_PAYPALINSTALLMENT_ZONE_TITLE' => 'Payment zone',
  'MODULE_PAYMENT_PAYPALINSTALLMENT_ZONE_DESC' => 'If a zone is choosen, the payment method will be valid for this zone only.',
  'MODULE_PAYMENT_PAYPALINSTALLMENT_LP' => '<br /><br /><a target="_blank" href="http://www.paypal.com/de/webapps/mpp/referral/paypal-business-account2?partner_id=EHALBVD4M2RQS"><strong>Create PayPal account now.</strong></a>',

  'MODULE_PAYMENT_PAYPALINSTALLMENT_TEXT_EXTENDED_DESCRIPTION' => '<strong><font color="red">ATTENTION:</font></strong> Please setup PayPal configuration under "Partner Modules" -> "PayPal" -> <a href="'.xtc_href_link('paypal_config.php').'"><strong>"PayPal Configuration"</strong></a>!',

  'MODULE_PAYMENT_PAYPALINSTALLMENT_TEXT_ERROR_HEADING' => 'Note',
  'MODULE_PAYMENT_PAYPALINSTALLMENT_TEXT_ERROR_MESSAGE' => 'PayPal payment has been canceled',

  'MODULE_PAYMENT_PAYPALINSTALLMENT_TEXT_CHECKBOX' => 'I accept that my data will be shared with PayPal.',
  'MODULE_PAYMENT_PAYPALINSTALLMENT_TEXT_ERROR_CHECKBOX' => 'Please accept that your data will be shared with PayPal.',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
?>