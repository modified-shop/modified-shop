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


// the shopper can switch the method inside Klarna with every Klarna module, so the code follows the final choice
function klarna_payment_code($payment_code, $orders_id) {
  $klarna_modules = array(
    'klarna_paylater',
    'klarna_paynow',
    'klarna_payovertime',
    'klarna_directdebit',
    'klarna_directbanktransfer',
    'klarna_klarna',
    'klarna_express',
  );
  if (!in_array($payment_code, $klarna_modules, true) || (int)$orders_id < 1) {
    return $payment_code;
  }

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

  $map_array = klarna_payment_code_map();
  if (is_string($payment_method) && isset($map_array[$payment_method])) {
    return $map_array[$payment_method];
  }

  return $payment_code;
}
