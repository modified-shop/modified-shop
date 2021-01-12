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
  'MODULE_PAYMENT_'.$klarna_code.'_TEXT_TITLE' => '',
  'MODULE_PAYMENT_'.$klarna_code.'_TEXT_DESCRIPTION' => 'Before you can set up Klarna Payments payment methods, it is necessary to open a merchant account with Klarna. You will then receive information and login details needed to set up the account. If you already have a Klarna customer number but it is not in the Kxxxxxx scheme, please send an e-mail to <a href="mailto:vertrieb@klarna.com">vertrieb@klarna.com</a>.<br /><br />
    <img src="../lang/english/admin/images/icon.gif" border="0" />
    <a href="https://www.klarna.com/uk/business/" target="_blank" style="text-decoration: underline; font-weight: bold;">Create Klarna account now.</a>
    <img src="images/icon_popup.gif" border="0" />',
  'MODULE_PAYMENT_'.$klarna_code.'_TEXT_INFO' => '',
  'MODULE_PAYMENT_'.$klarna_code.'_ALLOWED_TITLE' => 'Allowed zones',
  'MODULE_PAYMENT_'.$klarna_code.'_ALLOWED_DESC' => 'Please enter the zones <b>separately</b> which should be allowed to use this module (e.g. AT,DE (leave empty if you want to allow all zones))',
  'MODULE_PAYMENT_'.$klarna_code.'_STATUS_TITLE' => 'Enable Module',
  'MODULE_PAYMENT_'.$klarna_code.'_STATUS_DESC' => 'Do you want to accept payments through this module?',
  'MODULE_PAYMENT_'.$klarna_code.'_SORT_ORDER_TITLE' => 'Sort order',
  'MODULE_PAYMENT_'.$klarna_code.'_SORT_ORDER_DESC' => 'Sort order of display. Lowest is displayed first.',
  'MODULE_PAYMENT_'.$klarna_code.'_ZONE_TITLE' => 'Payment zone',
  'MODULE_PAYMENT_'.$klarna_code.'_ZONE_DESC' => 'If a zone is choosen, the payment method will be valid for this zone only.',
  'MODULE_PAYMENT_'.$klarna_code.'_ORDER_STATUS_ID_TITLE' => 'Set Order Status',
  'MODULE_PAYMENT_'.$klarna_code.'_ORDER_STATUS_ID_DESC' => 'Set the status of orders made with this payment module to this value',
  'MODULE_PAYMENT_'.$klarna_code.'_CAPTURE_TITLE' => 'Activate',
  'MODULE_PAYMENT_'.$klarna_code.'_CAPTURE_DESC' => 'Shall the order be activated automatically?',

  'MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_HEADING' => 'Klarna',
  'MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_MESSAGE' => 'The payment was cancelled.',

  'MODULE_PAYMENT_'.$klarna_code.'_TEXT_VERSION' => '<b>Module version</b><br/>',

  'MODULE_PAYMENT_KLARNA_MERCHANT_ID_TITLE' => 'Username',
  'MODULE_PAYMENT_KLARNA_MERCHANT_ID_DESC' => 'Klarna API Username',
  'MODULE_PAYMENT_KLARNA_SHARED_SECRET_TITLE' => 'Password',
  'MODULE_PAYMENT_KLARNA_SHARED_SECRET_DESC' => 'Klarna API Password',
  'MODULE_PAYMENT_KLARNA_MODE_TITLE' => 'Mode',
  'MODULE_PAYMENT_KLARNA_MODE_DESC' => 'Klarna Mode',
);
