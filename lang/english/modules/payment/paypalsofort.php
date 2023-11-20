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
  'MODULE_PAYMENT_PAYPALSOFORT_TEXT_TITLE' => 'Sofort via PayPal',
  'MODULE_PAYMENT_PAYPALSOFORT_TEXT_ADMIN_TITLE' => 'Sofort via PayPal',
  'MODULE_PAYMENT_PAYPALSOFORT_TEXT_INFO' => '<img src="https://www.paypalobjects.com/images/checkout/alternative_payments/paypal_sofort_black.svg" />',
  'MODULE_PAYMENT_PAYPALSOFORT_TEXT_DESCRIPTION' => 'After "confirm" your will be routet to Sofort to pay your order.<br />Back in shop you will get your order-mail.<br />PayPal is the safer way to pay online. We keep your details safe from others and can help you get your money back if something ever goes wrong.<br /><br /><strong><font color="red">ATTENTION:</font></strong> In order for the order status to be set correctly, the following <a href="'.xtc_href_link('paypal_webhook.php', '').'">webhooks</a> must be set in the PayPal configuration so that the status is changed correctly:<ul><li>PAYMENT.CAPTURE.COMPLETED</li><li>PAYMENT.CAPTURE.DECLINED</li><li>PAYMENT.CAPTURE.DENIED</li><li>PAYMENT.CAPTURE.PENDING</li></ul>',
  'MODULE_PAYMENT_PAYPALSOFORT_ALLOWED_TITLE' => 'Allowed zones',
  'MODULE_PAYMENT_PAYPALSOFORT_ALLOWED_DESC' => 'The module can be used for the following zones.',
  'MODULE_PAYMENT_PAYPALSOFORT_STATUS_TITLE' => 'Enable Sofort via PayPal',
  'MODULE_PAYMENT_PAYPALSOFORT_STATUS_DESC' => 'Do you want to accept PayPal Sofort payments?',
  'MODULE_PAYMENT_PAYPALSOFORT_SORT_ORDER_TITLE' => 'Sort order',
  'MODULE_PAYMENT_PAYPALSOFORT_SORT_ORDER_DESC' => 'Sort order of the view. Lowest numeral will be displayed first',
  'MODULE_PAYMENT_PAYPALSOFORT_ZONE_TITLE' => 'Payment zone',
  'MODULE_PAYMENT_PAYPALSOFORT_ZONE_DESC' => 'If a zone is choosen, the payment method will be valid for this zone only.',
  'MODULE_PAYMENT_PAYPALSOFORT_LP' => '<br /><br /><a target="_blank" href="http://www.paypal.com/de/webapps/mpp/referral/paypal-business-account2?partner_id=EHALBVD4M2RQS"><strong>Create PayPal account now.</strong></a>',

  'MODULE_PAYMENT_PAYPALSOFORT_TEXT_EXTENDED_DESCRIPTION' => '<strong><font color="red">ATTENTION:</font></strong> Please setup PayPal configuration under "Partner Modules" -> "PayPal" -> <a href="'.xtc_href_link('paypal_config.php').'"><strong>"PayPal Configuration"</strong></a>!',

  'MODULE_PAYMENT_PAYPALSOFORT_TEXT_ERROR_HEADING' => 'Note',
  'MODULE_PAYMENT_PAYPALSOFORT_TEXT_ERROR_MESSAGE' => 'The payment with Sofort via PayPal was cancelled',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
?>