<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


defined('TABLE_KLARNA_PAYMENTS') or define('TABLE_KLARNA_PAYMENTS', 'klarna_payments');


// the category modules of earlier versions, still available but marked as old
function klarna_legacy_modules() {
  return array(
    'klarna_paylater',
    'klarna_paynow',
    'klarna_payovertime',
    'klarna_directdebit',
    'klarna_directbanktransfer',
  );
}


// every payment code a Klarna order can carry
function klarna_payment_modules() {
  return array_merge(klarna_legacy_modules(), array(
    'klarna',
    'klarna_express',
  ));
}


// texts of klarna_shared.php as array, without defining constants, so one request can serve several languages
function klarna_language_array($language = '') {
  static $cache_array = array();

  if ($language == '' && isset($_SESSION['language'])) {
    $language = $_SESSION['language'];
  }
  if (isset($cache_array[$language])) {
    return $cache_array[$language];
  }
  $language_file = DIR_FS_CATALOG.'lang/'.basename($language).'/modules/payment/klarna_shared.php';
  if (!is_file($language_file)) {
    $language_file = DIR_FS_CATALOG.'lang/german/modules/payment/klarna_shared.php';
  }

  $klarna_code = 'KLARNA';
  include($language_file);

  $cache_array[$language] = ((isset($lang_array) && is_array($lang_array)) ? $lang_array : array());

  return $cache_array[$language];
}


// Klarna payment method type (lowercase) => code of the dedicated module the existing mappings know
function klarna_payment_code_map() {
  return array(
    'invoice' => 'klarna_paylater',
    'b2b_invoice' => 'klarna_paylater',
    'invoice_business' => 'klarna_paylater',
    'pay_later_by_card' => 'klarna_paylater',
    'fixed_amount' => 'klarna_payovertime',
    'fixed_sum_credit' => 'klarna_payovertime',
    'base_account' => 'klarna_payovertime',
    'account' => 'klarna_payovertime',
    'slice_it_by_card' => 'klarna_payovertime',
    'fixed_amount_by_card' => 'klarna_payovertime',
    'deferred_interest' => 'klarna_payovertime',
    'pay_in_x' => 'klarna_payovertime',
    'direct_debit' => 'klarna_directdebit',
    'direct_bank_transfer' => 'klarna_directbanktransfer',
    'bank_transfer' => 'klarna_directbanktransfer',
    'card' => 'klarna_card',
    'pay_by_card' => 'klarna_card',
  );
}


// label of the way the customer paid inside Klarna, '' for an unknown or empty type
function klarna_payment_method_label($payment_method, $language = '') {
  $map_array = klarna_payment_code_map();
  if (!is_string($payment_method) || !isset($map_array[$payment_method])) {
    return '';
  }

  // the groups of the map give the labels
  $label_array = array(
    'klarna_paylater' => 'INVOICE',
    'klarna_payovertime' => 'FINANCING',
    'klarna_directdebit' => 'DIRECT_DEBIT',
    'klarna_directbanktransfer' => 'BANK_TRANSFER',
    'klarna_card' => 'CARD',
  );
  $label_group = $map_array[$payment_method];
  // Afterbuy and Trusted Shops book it as invoice, but the customer pays with a card
  if ($payment_method == 'pay_later_by_card') {
    $label_group = 'klarna_card';
  }
  if (!isset($label_array[$label_group])) {
    return '';
  }

  $lang_array = klarna_language_array($language);
  $key = 'MODULE_PAYMENT_KLARNA_METHOD_'.$label_array[$label_group];

  return ((isset($lang_array[$key])) ? $lang_array[$key] : '');
}


// the shopper can switch the method inside Klarna with every Klarna module, so the code follows the final choice
function klarna_payment_code($payment_code, $orders_id) {
  if (!in_array($payment_code, klarna_payment_modules(), true) || (int)$orders_id < 1) {
    return $payment_code;
  }

  $map_array = klarna_payment_code_map();
  $payment_method = klarna_order_payment_method($orders_id);
  if (is_string($payment_method) && isset($map_array[$payment_method])) {
    return $map_array[$payment_method];
  }

  return $payment_code;
}


// method type Klarna stored for the order
function klarna_order_payment_method($orders_id) {
  // the key exists once klarna_update() has added the column
  $payment_method = '';
  $row_found = false;
  if (defined('MODULE_PAYMENT_KLARNA_DB_VERSION')) {
    $check_query = xtc_db_query("SELECT payment_method
                                   FROM ".TABLE_KLARNA_PAYMENTS."
                                  WHERE orders_id = '".(int)$orders_id."'");
    if ($check_query !== false && xtc_db_num_rows($check_query) > 0) {
      $check = xtc_db_fetch_array($check_query);
      $payment_method = $check['payment_method'];
      $row_found = true;
    }
  }
  if ($row_found === false
      && isset($GLOBALS['insert_id'])
      && (int)$GLOBALS['insert_id'] === (int)$orders_id
      && isset($_SESSION['klarna']['payment_method'])
      )
  {
    // checkout_process sends the order before after_process() inserts the row
    $payment_method = $_SESSION['klarna']['payment_method'];
  }

  return $payment_method;
}
