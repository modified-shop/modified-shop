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
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_TEXT_TITLE' => 'GooglePay via PayPal',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_TEXT_ADMIN_TITLE' => 'GooglePay via PayPal',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_TEXT_INFO' => '<img src="https://developers.google.com/static/pay/api/images/brand-guidelines/google-pay-mark.png" style="max-height: 60px;"/>',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_TEXT_DESCRIPTION' => 'After "confirm" your will be routet to GooglePay to pay your order.<br />Back in shop you will get your order-mail.<br />PayPal is the safer way to pay online. We keep your details safe from others and can help you get your money back if something ever goes wrong.',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_ALLOWED_TITLE' => 'Allowed zones',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_ALLOWED_DESC' => 'The module can be used for the following zones.',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_STATUS_TITLE' => 'Enable GooglePay via PayPal',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_STATUS_DESC' => 'Do you want to accept PayPal GooglePay payments?',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_SORT_ORDER_TITLE' => 'Sort order',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_SORT_ORDER_DESC' => 'Sort order of the view. Lowest numeral will be displayed first',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_ZONE_TITLE' => 'Payment zone',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_ZONE_DESC' => 'If a zone is choosen, the payment method will be valid for this zone only.',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_LP' => '<br /><br /><a target="_blank" href="http://www.paypal.com/de/webapps/mpp/referral/paypal-business-account2?partner_id=EHALBVD4M2RQS"><strong>Create PayPal account now.</strong></a>',

  'MODULE_PAYMENT_PAYPALGOOGLEPAY_TEXT_EXTENDED_DESCRIPTION' => '<strong><font color="red">ATTENTION:</font></strong> Please setup PayPal configuration under "Partner Modules" -> "PayPal" -> <a href="'.xtc_href_link('paypal_config.php').'"><strong>"PayPal Configuration"</strong></a>!',

  'MODULE_PAYMENT_PAYPALGOOGLEPAY_TEXT_ERROR_HEADING' => 'Note',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_TEXT_ERROR_MESSAGE' => 'The payment with GooglePay via PayPal was cancelled',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
