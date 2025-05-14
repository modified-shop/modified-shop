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
  'MODULE_PAYMENT_PAYPALAPPLEPAY_TEXT_TITLE' => 'Apple Pay via PayPal',
  'MODULE_PAYMENT_PAYPALAPPLEPAY_TEXT_ADMIN_TITLE' => 'Apple Pay via PayPal',
  'MODULE_PAYMENT_PAYPALAPPLEPAY_TEXT_INFO' => ((!defined('RUN_MODE_ADMIN') && function_exists('xtc_href_link')) ? '<img src="'.xtc_href_link(DIR_WS_ICONS.'applepay.png', '', 'SSL', false).'" style="max-height: 60px;" />' : ''),
  'MODULE_PAYMENT_PAYPALAPPLEPAY_TEXT_DESCRIPTION' => 'After "confirm" the customer will be routet to Apple Pay to pay the order.<br />Back in shop he will get your order-mail.<br />PayPal is the safer way to pay online. We keep your details safe from others and can help you get your money back if something ever goes wrong.<br /><br /><strong><font color="red">ATTENTION:</font></strong> Apple Pay only works with Safari browsers and the following versions of operating systems on Apple devices:<br />- macOS 10.14.1 and above<br />- iOS 12.1 and above<br /><br /><strong><font color="red">ATTENTION:</font></strong> In order for the order status to be set correctly, the following <a href="'.xtc_href_link('paypal_webhook.php').'">webhooks</a> must be set in the PayPal configuration so that the status is changed correctly:<ul><li>PAYMENT.SALE.COMPLETED</li><li>PAYMENT.SALE.DENIED</li><li>PAYMENT.SALE.PENDING</li></ul>',
  'MODULE_PAYMENT_PAYPALAPPLEPAY_ALLOWED_TITLE' => 'Allowed zones',
  'MODULE_PAYMENT_PAYPALAPPLEPAY_ALLOWED_DESC' => 'The module can be used for the following zones.',
  'MODULE_PAYMENT_PAYPALAPPLEPAY_STATUS_TITLE' => 'Enable Apple Pay via PayPal',
  'MODULE_PAYMENT_PAYPALAPPLEPAY_STATUS_DESC' => 'Do you want to accept PayPal Apple Pay payments?',
  'MODULE_PAYMENT_PAYPALAPPLEPAY_SORT_ORDER_TITLE' => 'Sort order',
  'MODULE_PAYMENT_PAYPALAPPLEPAY_SORT_ORDER_DESC' => 'Sort order of the view. Lowest numeral will be displayed first',
  'MODULE_PAYMENT_PAYPALAPPLEPAY_ZONE_TITLE' => 'Payment zone',
  'MODULE_PAYMENT_PAYPALAPPLEPAY_ZONE_DESC' => 'If a zone is choosen, the payment method will be valid for this zone only.',
  'MODULE_PAYMENT_PAYPALAPPLEPAY_LP' => '<br /><br />For this payment method you need a PayPal merchant account.<br /><a target="_blank" href="https://www.paypal.com/business"><strong>Create PayPal account now.</strong></a>',

  'MODULE_PAYMENT_PAYPALAPPLEPAY_TEXT_EXTENDED_DESCRIPTION' => '<strong><font color="red">ATTENTION:</font></strong> Please setup PayPal configuration under "Partner Modules" -> "PayPal" -> <a href="'.xtc_href_link('paypal_config.php').'"><strong>"PayPal Configuration"</strong></a>!',

  'MODULE_PAYMENT_PAYPALAPPLEPAY_TEXT_ERROR_HEADING' => 'Note',
  'MODULE_PAYMENT_PAYPALAPPLEPAY_TEXT_ERROR_MESSAGE' => 'The payment with Apple Pay via PayPal was cancelled',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
