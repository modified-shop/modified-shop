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
  'MODULE_PAYMENT_PAYPALCART_TEXT_TITLE' => 'PayPal',
  'MODULE_PAYMENT_PAYPALCART_TEXT_ADMIN_TITLE' => 'PayPal Express-Button on shopping cart &amp; product page<span style="background:#dd2400;color: #fff;font-weight: bold;padding: 2px 5px;border-radius: 4px;margin: 0 0 0 5px;">OLD</span>',
  'MODULE_PAYMENT_PAYPALCART_TEXT_INFO' => ((!defined('RUN_MODE_ADMIN') && function_exists('xtc_href_link')) ? '<img src="'.xtc_href_link(DIR_WS_ICONS.'paypal.png', '', 'SSL', false).'" />' : ''),
  'MODULE_PAYMENT_PAYPALCART_TEXT_DESCRIPTION' => 'PayPal Express Checkout - the PayPal button in the shopping cart and on the product page for maximum conversion.<br/>You can find more information about PayPal Express Shortcut <a target="_blank" href="https://www.paypal.com/de/webapps/mpp/express-checkout">here</a>.<br /><br /><strong><font color="red">ATTENTION:</font></strong> In order for the order status to be set correctly, the following <a href="'.xtc_href_link('paypal_webhook.php').'">webhooks</a> must be set in the PayPal configuration so that the status is changed correctly:<ul><li>PAYMENT.SALE.COMPLETED</li><li>PAYMENT.SALE.DENIED</li><li>PAYMENT.SALE.PENDING</li></ul>',
  'MODULE_PAYMENT_PAYPALCART_ALLOWED_TITLE' => 'Allowed zones',
  'MODULE_PAYMENT_PAYPALCART_ALLOWED_DESC' => 'Please enter the zones <b>separately</b> which should be allowed to use this module (e.g. AT,DE (leave empty if you want to allow all zones))',
  'MODULE_PAYMENT_PAYPALCART_STATUS_TITLE' => 'Enable PayPal Express',
  'MODULE_PAYMENT_PAYPALCART_STATUS_DESC' => 'Do you want to accept PayPal Express payments?',
  'MODULE_PAYMENT_PAYPALCART_SORT_ORDER_TITLE' => 'Sort order',
  'MODULE_PAYMENT_PAYPALCART_SORT_ORDER_DESC' => 'Sort order of the view. Lowest numeral will be displayed first',
  'MODULE_PAYMENT_PAYPALCART_ZONE_TITLE' => 'Payment zone',
  'MODULE_PAYMENT_PAYPALCART_ZONE_DESC' => 'If a zone is choosen, the payment method will be valid for this zone only.',
  'MODULE_PAYMENT_PAYPALCART_LP' => '<br /><br />For this payment method you need a PayPal merchant account.<br /><a target="_blank" href="https://www.paypal.com/business"><strong>Create PayPal account now.</strong></a>',

  'MODULE_PAYMENT_PAYPALCART_TEXT_EXTENDED_DESCRIPTION' => '<strong><font color="red">ATTENTION:</font></strong> Please setup PayPal configuration under "Partner Modules" -> "PayPal" -> <a href="'.xtc_href_link('paypal_config.php').'"><strong>"PayPal Configuration"</strong></a>!',

  'MODULE_PAYMENT_PAYPALCART_TEXT_ERROR_HEADING' => 'Note',
  'MODULE_PAYMENT_PAYPALCART_TEXT_ERROR_MESSAGE' => 'PayPal payment has been cancelled',

  'TEXT_PAYPAL_CART_ACCOUNT_CREATED' => 'We have created a customer account with your PayPal E-Mail address. You can request the password for your new customer account later using the "Forgotten Password" function.',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
