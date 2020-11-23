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
  'MODULE_PAYMENT_EASYCREDIT_TEXT_TITLE' => 'ratenkauf by easyCredit',
  'MODULE_PAYMENT_EASYCREDIT_TEXT_INFO' => '',
  'MODULE_PAYMENT_EASYCREDIT_TEXT_DESCRIPTION' => '',
  'MODULE_PAYMENT_EASYCREDIT_ALLOWED_TITLE' => 'Allowed zones',
  'MODULE_PAYMENT_EASYCREDIT_ALLOWED_DESC' => 'Please enter the zones <b>separately</b> which should be allowed to use this module (e.g. AT,DE (leave empty if you want to allow all zones))',
  'MODULE_PAYMENT_EASYCREDIT_STATUS_TITLE' => 'Enable Module',
  'MODULE_PAYMENT_EASYCREDIT_STATUS_DESC' => 'Do you want to accept payments with ratenkauf by easyCredit?',
  'MODULE_PAYMENT_EASYCREDIT_SORT_ORDER_TITLE' => 'Sort order',
  'MODULE_PAYMENT_EASYCREDIT_SORT_ORDER_DESC' => 'Sort order of display. Lowest is displayed first.',
  'MODULE_PAYMENT_EASYCREDIT_ZONE_TITLE' => 'Payment zone',
  'MODULE_PAYMENT_EASYCREDIT_ZONE_DESC' => 'If a zone is choosen, the payment method will be valid for this zone only.',
  'MODULE_PAYMENT_EASYCREDIT_ORDER_STATUS_ID_TITLE' => 'Temporary order status',
  'MODULE_PAYMENT_EASYCREDIT_ORDER_STATUS_ID_DESC' => 'Specify the order status for unconfirmed orders',
  'MODULE_PAYMENT_EASYCREDIT_ORDER_STATUS_SUCCESS_ID_TITLE' => 'Successful order status',
  'MODULE_PAYMENT_EASYCREDIT_ORDER_STATUS_SUCCESS_ID_DESC' => 'Specify the order status for successful orders',
  'MODULE_PAYMENT_EASYCREDIT_SHOP_ID_TITLE' => 'Webshop-ID',
  'MODULE_PAYMENT_EASYCREDIT_SHOP_ID_DESC' => 'You will find your Webshop-ID in the easyCredit merchant interface in the sub-item Shopadministration',
  'MODULE_PAYMENT_EASYCREDIT_SHOP_TOKEN_TITLE' => 'API password',
  'MODULE_PAYMENT_EASYCREDIT_SHOP_TOKEN_DESC' => 'You can define your API password yourself in the easyCredit merchant interface in the sub-item Shop Administration',
  'MODULE_PAYMENT_EASYCREDIT_LOG_LEVEL_TITLE' => 'Loglevel',
  'MODULE_PAYMENT_EASYCREDIT_LOG_LEVEL_DESC' => 'Specify the log level. Default: "error"',

  'MODULE_PAYMENT_EASYCREDIT_TEXT_ERROR_HEADING' => 'Note',
  'MODULE_PAYMENT_EASYCREDIT_TEXT_ERROR_MESSAGE' => 'The payment with installment plan by easyCredit was cancelled',
  'MODULE_PAYMENT_EASYCREDIT_TEXT_ERROR_CHECKBOX' => 'Please accept the additional necessary agreements for installment plan by easyCredit',
  'MODULE_PAYMENT_EASYCREDIT_TEXT_LEGAL' => 'Get pre-contractual information on installment purchase here',

  'TEXT_EASYCREDIT_TBAID' => 'Activity identification',
  'TEXT_EASYCREDIT_RATING_PLAN' => 'Financing from %s in %s installments with installment plan by easyCredit',
  'TEXT_EASYCREDIT_RATING_PLAN_SHORT' => 'Financing from %s per month',
  'TEXT_EASYCREDIT_RATING_PLAN_CALC' => 'more information about installment purchase',
  'TEXT_EASYCREDIT_LEGAL' => 'Representative example according to &sect; 6a PAngV',
  'TEXT_EASYCREDIT_NOMINAL_RATE' => 'Debit interest rate p.a. fixed for the entire term',
  'TEXT_EASYCREDIT_EFFECTIVE_RATE' => 'Annual percentage rate of charge',
  'TEXT_EASYCREDIT_TOTAL_COST' => 'Total amount',
  'TEXT_EASYCREDIT_TOTAL_NETTO' => 'Net loan amount',
  'TEXT_EASYCREDIT_TOTAL_INTEREST' => 'Interest amount',
  'TEXT_EASYCREDIT_MONTHLY_PAYMENT' => 'monthly installments of each',
  'TEXT_EASYCREDIT_LAST_PAYMENT' => 'last installment',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
?>