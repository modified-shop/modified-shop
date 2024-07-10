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
  'MODULE_PAYMENT_PAYPAL_TEXT_TITLE' => 'PayPal',
  'MODULE_PAYMENT_PAYPAL_TEXT_ADMIN_TITLE' => 'PayPal auf Checkoutseite',
  'MODULE_PAYMENT_PAYPAL_TEXT_INFO' => ((!defined('RUN_MODE_ADMIN') && function_exists('xtc_href_link')) ? '<img src="'.xtc_href_link(DIR_WS_ICONS.'paypal.png', '', 'SSL', false).'" />' : ''),
  'MODULE_PAYMENT_PAYPAL_TEXT_DESCRIPTION' => 'After "confirm" the customer will be routet to PayPal to pay the order.<br />Back in shop he will get your order-mail.<br />PayPal is the safer way to pay online. We keep your details safe from others and can help you get your money back if something ever goes wrong.<br /><br /><strong><font color="red">ATTENTION:</font></strong> In order for the order status to be set correctly, the following <a href="'.xtc_href_link('paypal_webhook.php').'">webhooks</a> must be set in the PayPal configuration so that the status is changed correctly:<ul><li>PAYMENT.CAPTURE.COMPLETED</li><li>PAYMENT.CAPTURE.DECLINED</li><li>PAYMENT.CAPTURE.DENIED</li><li>PAYMENT.CAPTURE.PENDING</li></ul>',
  'MODULE_PAYMENT_PAYPAL_ALLOWED_TITLE' => 'Allowed zones',
  'MODULE_PAYMENT_PAYPAL_ALLOWED_DESC' => 'Please enter the zones <b>separately</b> which should be allowed to use this module (e.g. AT,DE (leave empty if you want to allow all zones))',
  'MODULE_PAYMENT_PAYPAL_STATUS_TITLE' => 'Enable PayPal',
  'MODULE_PAYMENT_PAYPAL_STATUS_DESC' => 'Do you want to accept PayPal payments?',
  'MODULE_PAYMENT_PAYPAL_SORT_ORDER_TITLE' => 'Sort order',
  'MODULE_PAYMENT_PAYPAL_SORT_ORDER_DESC' => 'Sort order of the view. Lowest numeral will be displayed first',
  'MODULE_PAYMENT_PAYPAL_ZONE_TITLE' => 'Payment zone',
  'MODULE_PAYMENT_PAYPAL_ZONE_DESC' => 'If a zone is choosen, the payment method will be valid for this zone only.',
  'MODULE_PAYMENT_PAYPAL_LP' => '<br /><br />For this payment method you need a PayPal merchant account.<br /><a target="_blank" href="http://www.paypal.com/de/webapps/mpp/referral/paypal-business-account2?partner_id=EHALBVD4M2RQS"><strong>Create PayPal account now.</strong></a>',

  'MODULE_PAYMENT_PAYPAL_TEXT_EXTENDED_DESCRIPTION' => '<strong><font color="red">ATTENTION:</font></strong> Please setup PayPal configuration under "Partner Modules" -> "PayPal" -> <a href="'.xtc_href_link('paypal_config.php').'"><strong>"PayPal Configuration"</strong></a>!',

  'MODULE_PAYMENT_PAYPAL_TEXT_ERROR_HEADING' => 'Note',
  'MODULE_PAYMENT_PAYPAL_TEXT_ERROR_MESSAGE' => 'PayPal payment has been cancelled',

  'MODULE_PAYMENT_PAYPAL_TEXT_SAVE_PAYMENT' => 'Save payment method securely. This means you can pay faster, easier and more conveniently for your next purchase.',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
