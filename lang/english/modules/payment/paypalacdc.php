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
  'MODULE_PAYMENT_PAYPALACDC_TEXT_TITLE' => 'Credit Card',
  'MODULE_PAYMENT_PAYPALACDC_TEXT_ADMIN_TITLE' => 'Credit Card (without note to PayPal)',
  'MODULE_PAYMENT_PAYPALACDC_TEXT_INFO' => ((!defined('RUN_MODE_ADMIN') && function_exists('xtc_href_link')) ? '<img src="'.xtc_href_link(DIR_WS_ICONS.'paypal_creditcard.png', '', 'SSL', false).'" />' : ''),
  'MODULE_PAYMENT_PAYPALACDC_TEXT_DESCRIPTION' => 'In the last step during the checkout, you will be asked to enter your credit card details to pay for your order.<br /><br /><strong><font color="red">ATTENTION:</font></strong> In order for the order status to be set correctly, the following <a href="'.xtc_href_link('paypal_webhook.php', '').'">webhooks</a> must be set in the PayPal configuration so that the status is changed correctly:<ul><li>PAYMENT.CAPTURE.COMPLETED</li><li>PAYMENT.CAPTURE.DECLINED</li><li>PAYMENT.CAPTURE.DENIED</li><li>PAYMENT.CAPTURE.PENDING</li></ul>',
  'MODULE_PAYMENT_PAYPALACDC_ALLOWED_TITLE' => 'Allowed zones',
  'MODULE_PAYMENT_PAYPALACDC_ALLOWED_DESC' => 'The module can be used for the following zones.',
  'MODULE_PAYMENT_PAYPALACDC_STATUS_TITLE' => 'Enable Credit Card',
  'MODULE_PAYMENT_PAYPALACDC_STATUS_DESC' => 'Do you want to accept PayPal Credit Card payments?',
  'MODULE_PAYMENT_PAYPALACDC_SORT_ORDER_TITLE' => 'Sort order',
  'MODULE_PAYMENT_PAYPALACDC_SORT_ORDER_DESC' => 'Sort order of the view. Lowest numeral will be displayed first',
  'MODULE_PAYMENT_PAYPALACDC_ZONE_TITLE' => 'Payment zone',
  'MODULE_PAYMENT_PAYPALACDC_ZONE_DESC' => 'If a zone is choosen, the payment method will be valid for this zone only.',
  'MODULE_PAYMENT_PAYPALACDC_LP' => '<br /><br /><a target="_blank" href="http://www.paypal.com/de/webapps/mpp/referral/paypal-business-account2?partner_id=EHALBVD4M2RQS"><strong>Create PayPal account now.</strong></a>',

  'MODULE_PAYMENT_PAYPALACDC_TEXT_EXTENDED_DESCRIPTION' => '<strong><font color="red">ATTENTION:</font></strong> Please setup PayPal configuration under "Partner Modules" -> "PayPal" -> <a href="'.xtc_href_link('paypal_config.php').'"><strong>"PayPal Configuration"</strong></a>!',

  'MODULE_PAYMENT_PAYPALACDC_TEXT_ERROR_HEADING' => 'Note',
  'MODULE_PAYMENT_PAYPALACDC_TEXT_ERROR_MESSAGE' => 'The payment with Credit Card was cancelled',
  'MODULE_PAYMENT_PAYPALACDC_TEXT_ERROR_MSG' => 'Unfortunately, the payment cannot be made.',

  'MODULE_PAYMENT_PAYPALACDC_TEXT_CARDNUMBER' => 'Card number',
  'MODULE_PAYMENT_PAYPALACDC_TEXT_CARDHOLDER' => 'Cardholder',
  'MODULE_PAYMENT_PAYPALACDC_TEXT_EXPIRATION' => 'Valid until',
  'MODULE_PAYMENT_PAYPALACDC_TEXT_CVV' => 'CVV',

  'MODULE_PAYMENT_PAYPALACDC_TEXT_CARDNUMBER_PLACEHOLDER' => 'Card number',
  'MODULE_PAYMENT_PAYPALACDC_TEXT_EXPIRATION_PLACEHOLDER' => 'MM/YY',
  'MODULE_PAYMENT_PAYPALACDC_TEXT_CVV_PLACEHOLDER' => 'CVV',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
