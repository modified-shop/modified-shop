<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ot_gv.php,v 1.37.3 2004/01/01); www.oscommerce.com
   (c) 2006 xt:Commerce (ot_gv.php 1185 2005-08-26); www.xt-commerce.de

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contributions:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c) Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class ot_gv {

  var $code;
  var $title;
  var $header;
  var $description;
  var $info;
  var $user_prompt;
  var $enabled;
  var $sort_order;
  var $credit_class;
  var $deduction;
  var $output;
  var $_check;

  var $include_shipping;
  var $include_tax;
  var $calculate_tax;
  var $credit_tax;
  var $tax_class;
  var $checkbox;

  // per order id, survives the new instance a payment module creates with order_total in the same request
  static $booked_deductions = array();

  function __construct() {    
    $this->code = 'ot_gv';
    $this->title = MODULE_ORDER_TOTAL_GV_TITLE;
    $this->header = MODULE_ORDER_TOTAL_GV_HEADER;
    $this->description = MODULE_ORDER_TOTAL_GV_DESCRIPTION;
    $this->info = MODULE_ORDER_TOTAL_GV_USER_PROMPT;
    $this->user_prompt = MODULE_ORDER_TOTAL_GV_USER_PROMPT;
    $this->enabled = ((defined('MODULE_ORDER_TOTAL_GV_STATUS') && MODULE_ORDER_TOTAL_GV_STATUS == 'true') ? true : false);
    $this->sort_order = ((defined('MODULE_ORDER_TOTAL_GV_SORT_ORDER')) ? MODULE_ORDER_TOTAL_GV_SORT_ORDER : '');

    if ($this->check() > 0) {
      $this->include_shipping = MODULE_ORDER_TOTAL_GV_INC_SHIPPING;
      $this->include_tax = MODULE_ORDER_TOTAL_GV_INC_TAX;
      $this->calculate_tax = MODULE_ORDER_TOTAL_GV_CALC_TAX;
      $this->credit_tax = MODULE_ORDER_TOTAL_GV_CREDIT_TAX;
      $this->tax_class = MODULE_ORDER_TOTAL_GV_TAX_CLASS;
    }
    
    $this->deduction = 0;
    $this->credit_class = true;
    $this->checkbox = '<input type="checkbox" onclick="submitFunction()" name="'.'c'.$this->code.'"> '.$this->user_prompt;
    
    $this->output = array ();
  }

  function process() {
    global $order, $xtPrice;

    if (isset ($_SESSION['cot_gv']) && $_SESSION['cot_gv'] == true) {
      $order_total = $this->get_order_total();

      // an order of this checkout that has its debit keeps it, the balance is lower by then
      $od_amount = $this->booked_credit();
      if ($od_amount === false) {
        $od_amount = $this->calculate_credit($order_total);
      }
      if ($this->calculate_tax != "None") {
        $this->calculate_tax_deduction($order_total, $od_amount, $this->calculate_tax);
      }

      $this->deduction = $od_amount * (-1);

      $order->info['subtotal'] = $order->info['subtotal'] + $this->deduction;
      $order->info['total'] = $order->info['total'] + $this->deduction;

      if ($this->deduction < 0) {
        $this->output[] = array (
            'title' => $this->title . ':', 
            'text'  => '<span class="color_ot_total"><b>'.$xtPrice->xtcFormat($this->deduction, true).'</b></span>', 
            'value' => $xtPrice->xtcFormat($this->deduction, false) 
          );
      }
    }
  }

  function selection_test() {
    if ($this->user_has_gv_account($_SESSION['customer_id'])) {
      return true;
    } else {
      return false;
    }
  }

  function pre_confirmation_check($order_total) {
    global $order;

    $od_amount = 0; // set the default amount we will send back
    if (isset ($_SESSION['cot_gv']) && $_SESSION['cot_gv'] == true) {
      // pre confirmation check doesn't do a true order process. It just attempts to see if
      // there is enough to handle the order. But depending on settings it will not be shown
      // all of the order so this is why we do this runaround jane. What do we know so far.
      // nothing. Since we need to know if we process the full amount we need to call get order total
      // if there has been something before us then

      if ($this->include_tax == 'false') {
        $order_total = $order_total - $order->info['tax'];
      }
      if ($this->include_shipping == 'false') {
        $order_total = $order_total - $order->info['shipping_cost'];
      }
      $od_amount = $this->calculate_credit($order_total);

      if ($this->calculate_tax != "None") {
        $od_amount += $this->calculate_tax_deduction($order_total, $od_amount, $this->calculate_tax);
      }
    }
    
    return $od_amount;
  }

  function use_credit_amount() {
    $_SESSION['cot_gv'] = false;
    if ($this->selection_test()) {
      return true;
    }
    return false;
  }

  function update_credit_account($i) {
    global $order, $insert_id, $REMOTE_ADDR;
    if (preg_match('/^GIFT/', addslashes($order->products[$i]['model']))) {
      $gv_order_amount = ($order->products[$i]['final_price']);
      if ($this->credit_tax == 'true') {
        $gv_order_amount = $gv_order_amount * (100 + $order->products[$i]['tax']) / 100;
      }
      if (MODULE_ORDER_TOTAL_GV_QUEUE == 'false') {
        // GV_QUEUE is false so release amount to account immediately
        $gv_query = xtc_db_query("select amount from ".TABLE_COUPON_GV_CUSTOMER." where customer_id = '".$_SESSION['customer_id']."'");
        $customer_gv = false;
        $total_gv_amount = 0;
        if ($gv_result = xtc_db_fetch_array($gv_query)) {
          $total_gv_amount = $gv_result['amount'];
          $customer_gv = true;
        }
        $total_gv_amount = $total_gv_amount + $gv_order_amount;
        if ($customer_gv) {
          $gv_update = xtc_db_query("update ".TABLE_COUPON_GV_CUSTOMER." set amount = '".$total_gv_amount."' where customer_id = '".$_SESSION['customer_id']."'");
        } else {
          $gv_insert = xtc_db_query("INSERT INTO ".TABLE_COUPON_GV_CUSTOMER." (customer_id, amount) values ('".$_SESSION['customer_id']."', '".$total_gv_amount."')");
        }
      } else {
        // GV_QUEUE is true - so queue the gv for release by store owner
        $gv_insert = xtc_db_query("INSERT INTO ".TABLE_COUPON_GV_QUEUE." (customer_id, order_id, amount, date_created, ipaddr) values ('".$_SESSION['customer_id']."', '".$insert_id."', '".$gv_order_amount."', NOW(), '".$REMOTE_ADDR."')");
      }
    }
  }

  function credit_selection() {
    return false;
  }

  function apply_credit() {
    global $insert_id;

    // book_credit() debits before the payment, only an order written before that still needs it here;
    // the stored order decides, not the session
    $gv_query = xtc_db_query("SELECT ot.value,
                                     o.currency,
                                     o.currency_value,
                                     o.orders_status
                                FROM ".TABLE_ORDERS_TOTAL." ot
                                JOIN ".TABLE_ORDERS." o
                                     ON o.orders_id = ot.orders_id
                           LEFT JOIN ".TABLE_ORDERS_BOOKINGS." ob
                                     ON ob.orders_id = ot.orders_id
                                        AND ob.class = '".$this->code."'
                               WHERE ot.orders_id = '".(int)$insert_id."'
                                 AND ot.class = '".$this->code."'
                                 AND o.customers_id = '".(int)$_SESSION['customer_id']."'
                                 AND ob.orders_id IS NULL");
    if (xtc_db_num_rows($gv_query) > 0) {
      $gv_order = xtc_db_fetch_array($gv_query);
      // the deduction the order carries, converted as the code that wrote it did: like xtcRemoveCurr(),
      // which keeps an amount in the default currency as it is
      $gv_deduction = (float)$gv_order['value'] * (-1);
      if ($gv_order['currency'] != DEFAULT_CURRENCY) {
        $gv_deduction = $this->to_default_currency($gv_deduction, $gv_order['currency_value']);
      }
      $gv_deduction = number_format($gv_deduction, 4, '.', '');
      if ((float)$gv_deduction > 0) {
        // the provider has the reduced total already, so the debit is taken as before this change
        xtc_db_query("UPDATE ".TABLE_COUPON_GV_CUSTOMER."
                         SET amount = amount - ".$gv_deduction."
                       WHERE customer_id = '".(int)$_SESSION['customer_id']."'", 'db_link', $gv_query_info);
        $gv_settled = (is_array($gv_query_info) && $gv_query_info['affected_rows'] == 1);
        if ($gv_settled === true && $this->insert_booking($gv_deduction) === false) {
          // without its record a repeated return would debit again, so the debit is undone
          xtc_db_query("UPDATE ".TABLE_COUPON_GV_CUSTOMER."
                           SET amount = amount + ".$gv_deduction."
                         WHERE customer_id = '".(int)$_SESSION['customer_id']."'");
          $gv_settled = false;
        }
        if ($gv_settled !== true) {
          // a language pack without this text gets the English one
          defined('TEXT_GV_SETTLEMENT_FAILED') OR define('TEXT_GV_SETTLEMENT_FAILED', 'The credit deduction of %s (default currency) for this order could not be booked completely. Please check the balance of the customer.');
          // the order is paid and stays, the shop gets a note to check the balance
          $sql_data_array = array(
            'orders_id' => (int)$insert_id,
            'orders_status_id' => (int)$gv_order['orders_status'],
            'date_added' => 'now()',
            'customer_notified' => 0,
            'comments' => sprintf(TEXT_GV_SETTLEMENT_FAILED, $gv_deduction),
          );
          xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        }
      }
    }
    unset($_SESSION['cot_gv']);
  }

  function book_credit() {
    global $insert_id;

    if (!isset($_SESSION['cot_gv']) || $_SESSION['cot_gv'] != true) {
      return true;
    }

    // one decimal string for the debit and its booking
    $gv_deduction = number_format($this->to_default_currency($this->deduction * (-1)), 4, '.', '');
    if ((float)$gv_deduction <= 0) {
      return true;
    }

    // the order carries its deduction before anything is debited, the totals computed later are taken from it
    $gv_query = xtc_db_query("SELECT value
                                FROM ".TABLE_ORDERS_TOTAL."
                               WHERE orders_id = '".(int)$insert_id."'
                                 AND class = '".$this->code."'");
    if ($gv_query === false || xtc_db_num_rows($gv_query) < 1) {
      return 'error';
    }

    // one statement that only takes a balance still covering it, so no other checkout spends it twice
    xtc_db_query("UPDATE ".TABLE_COUPON_GV_CUSTOMER."
                     SET amount = amount - ".$gv_deduction."
                   WHERE customer_id = '".(int)$_SESSION['customer_id']."'
                     AND amount >= ".$gv_deduction, 'db_link', $gv_query_info);
    // a failed statement debited nothing, no affected row means the balance does not cover it
    if (!is_array($gv_query_info) || $gv_query_info['success'] !== true) {
      return 'error';
    }
    if ($gv_query_info['affected_rows'] != 1) {
      return false;
    }

    // without its booking a removed order could not give the debit back, so the debit is undone
    if ($this->insert_booking($gv_deduction) === false) {
      xtc_db_query("UPDATE ".TABLE_COUPON_GV_CUSTOMER."
                       SET amount = amount + ".$gv_deduction."
                     WHERE customer_id = '".(int)$_SESSION['customer_id']."'", 'db_link', $gv_undo_info);
      // a failed undo is reported like every failed statement, the balance stays lower
      return ((is_array($gv_undo_info) && $gv_undo_info['affected_rows'] == 1) ? 'error' : 'lost');
    }

    // totals computed again in this request take the exact deduction, the stored one is rounded
    self::$booked_deductions[(int)$insert_id] = $this->deduction * (-1);

    return true;
  }

  function to_default_currency($amount, $rate = null) {
    global $xtPrice;

    // the counterpart of xtcCalculateCurr(), which applies the rate of the default currency as well
    if ($rate === null) {
      $rate = $xtPrice->currencies[$xtPrice->actualCurr]['value'];
    }

    return (((float)$rate > 0) ? (float)$amount / (float)$rate : (float)$amount);
  }

  function insert_booking($gv_deduction) {
    global $insert_id;

    // a removed order gives the debit back
    $sql_data_array = array(
      'orders_id' => (int)$insert_id,
      'orders_products_id' => 0,
      'class' => $this->code,
      'reference_id' => (int)$_SESSION['customer_id'],
      'value' => $gv_deduction,
    );
    return xtc_db_perform(TABLE_ORDERS_BOOKINGS, $sql_data_array);
  }

  function booked_credit() {
    if (!isset($_SESSION['tmp_oID']) || !is_numeric($_SESSION['tmp_oID'])) {
      return false;
    }

    if (isset(self::$booked_deductions[(int)$_SESSION['tmp_oID']])) {
      return self::$booked_deductions[(int)$_SESSION['tmp_oID']];
    }

    // the deduction stored with the order, in the currency of the order;
    // class is compared with literals only, the bookings table can have another collation
    $booked_query = xtc_db_query("SELECT ot.value
                                    FROM ".TABLE_ORDERS_BOOKINGS." ob
                                    JOIN ".TABLE_ORDERS_TOTAL." ot
                                         ON ot.orders_id = ob.orders_id
                                            AND ot.class = '".$this->code."'
                                   WHERE ob.orders_id = '".(int)$_SESSION['tmp_oID']."'
                                     AND ob.class = '".$this->code."'
                                     AND ob.reference_id = '".(int)$_SESSION['customer_id']."'
                                     AND ob.value != 0");
    if (xtc_db_num_rows($booked_query) < 1) {
      return false;
    }
    $booked = xtc_db_fetch_array($booked_query);

    return (float)$booked['value'] * (-1);
  }

  function credit_in_order_currency($amount, $total = null) {
    global $xtPrice;

    // a voucher that leaves tax or shipping to pay never pays the whole order, the payment page decides alike
    if ($total !== null && $this->pays_whole_order() !== true) {
      $total = null;
    }

    // a balance that covers the order is not rounded, the deduction is the order total then;
    // the check takes the total in the default currency at four decimals, as book_credit() debits it
    $credit = $xtPrice->xtcCalculateCurr($amount);
    if ($total !== null && round((float)$amount, 4) >= (float)number_format($this->to_default_currency((float)$total), 4, '.', '')) {
      return max($credit, (float)$total);
    }

    // without a conversion the balance is taken as it is, as before this change;
    // where it is more than the voucher may cover, calculate_credit() caps it at whole cents
    if ((float)$xtPrice->currencies[$xtPrice->actualCurr]['value'] == 1) {
      return $credit;
    }

    // otherwise whole cents of the order currency and never more than the balance, a part of a cent stays on it;
    // a deduction with half a cent would round differently in a total and in its breakdown
    $deduction = $this->whole_cents($credit);
    // the float error can lift it to a cent the debit at four decimals would not find on the balance
    if ($deduction > 0 && (float)number_format($this->to_default_currency($deduction), 4, '.', '') > round((float)$amount, 4)) {
      $deduction = $this->whole_cents($deduction - 0.5 / pow(10, (int)$xtPrice->currencies[$xtPrice->actualCurr]['decimal_places']));
    }

    return $deduction;
  }

  function pays_whole_order() {
    global $order;

    // the voucher pays the whole order only if neither tax nor shipping is left out of it
    return (($this->include_tax == 'true' || $order->info['tax'] == 0)
            && ($this->include_shipping == 'true' || $order->info['shipping_cost'] == 0));
  }

  function whole_cents($value) {
    global $xtPrice;

    $factor = pow(10, (int)$xtPrice->currencies[$xtPrice->actualCurr]['decimal_places']);

    return floor(round($value * $factor, 6)) / $factor;
  }

  function calculate_credit($amount) {
    $gv_query = xtc_db_query("SELECT amount 
                                FROM ".TABLE_COUPON_GV_CUSTOMER." 
                               WHERE customer_id = '".(int)$_SESSION['customer_id']."'");
    $gv_result = xtc_db_fetch_array($gv_query);
    $whole_order = $this->pays_whole_order();
    // the balance is in the default currency, the order total in the currency of the customer
    $gv_payment_amount = (isset($gv_result['amount']) ? $this->credit_in_order_currency($gv_result['amount'], $amount) : 0);
    
    if (($amount - $gv_payment_amount) <= 0) {
      // with something left to pay the deduction stays in whole cents
      $gv_payment_amount = (($whole_order === true) ? $amount : $this->whole_cents($amount));
    }
    
    return $gv_payment_amount;
  }

  function calculate_tax_deduction($amount, $od_amount, $method) {
    global $order;
    
    $tod_amount = 0;
    
    switch ($method) {
      case 'Standard':
        if ($amount <= 0) {
          break;
        }
        $ratio1 = $od_amount / $amount;
        $total_net = 0;
        foreach ($order->info['tax_groups'] as $key => $value) {
          $tax_rate = xtc_get_tax_rate_from_desc($key);
          $total_net += $tax_rate * $order->info['tax_groups'][$key];
        }
        if ($od_amount > $total_net)
          $od_amount = $total_net;
        foreach ($order->info['tax_groups'] as $key => $value) {
          $tax_rate = xtc_get_tax_rate_from_desc($key);
          $net = $tax_rate * $order->info['tax_groups'][$key];
          if ($net > 0) {
            $god_amount = $order->info['tax_groups'][$key] * $ratio1;
            $tod_amount += $god_amount;
            $order->info['tax_groups'][$key] = $order->info['tax_groups'][$key] - $god_amount;
          }
        }
        $order->info['tax'] -= $tod_amount;
        $order->info['total'] -= $tod_amount;
        break;
      case 'Credit Note':
        $tax_rate = xtc_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
        $tax_desc = xtc_get_tax_description($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
        // deduction is still 0 here, so this mode stays inert as it always was
        $tod_amount = $this->deduction / (100 + $tax_rate) * $tax_rate;
        $order->info['tax_groups'][$tax_desc] += $tod_amount;
        break;    
    }
    
    return $tod_amount;
  }

  function user_has_gv_account($c_id) {
    $gv_query = xtc_db_query("SELECT amount 
                                FROM ".TABLE_COUPON_GV_CUSTOMER." 
                               WHERE customer_id = '".(int)$c_id."'");
    if ($gv_result = xtc_db_fetch_array($gv_query)) {
      if ($gv_result['amount'] > 0) {
        return true;
      }
    }
    
    return false;
  }

  function get_credit_amount() {
    $gv_query = xtc_db_query("SELECT amount 
                                FROM ".TABLE_COUPON_GV_CUSTOMER." 
                               WHERE customer_id = '".(int)$_SESSION['customer_id']."'");
    if (xtc_db_num_rows($gv_query) > 0) {
      $gv_result = xtc_db_fetch_array($gv_query);
      if ($gv_result['amount'] > 0) {
        return $gv_result['amount'];
      }
    }
    
    return false;
  }

  function get_order_total() {
    global $order;
    
    if ($_SESSION['customers_status']['customers_status_show_price_tax'] != 0) {
      $order_total = $order->info['total'];
    }
    if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 
        && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1
        )
    {
      $order_total = $order->info['tax'] + $order->info['total'];
    }
    if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 
        && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0
        )
    {
      $order_total = $order->info['total'];
    }
    if ($this->include_tax == 'false') {
      $order_total = $order_total - $order->info['tax'];
    }
    if ($this->include_shipping == 'false') {
      $order_total = $order_total - $order->info['shipping_cost'];
    }
    
    return $order_total;
  }

  function check() {
    if (!isset($this->_check)) {
      if (defined('MODULE_ORDER_TOTAL_GV_STATUS')) {
        $this->_check = true;
      } else {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_GV_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
    }
    return $this->_check;
  }

  function keys() {
    return array (
      'MODULE_ORDER_TOTAL_GV_STATUS', 
      'MODULE_ORDER_TOTAL_GV_SORT_ORDER', 
      'MODULE_ORDER_TOTAL_GV_QUEUE', 
      'MODULE_ORDER_TOTAL_GV_UNALLOWED_PAYMENT',
      'MODULE_ORDER_TOTAL_GV_INC_SHIPPING', 
      'MODULE_ORDER_TOTAL_GV_INC_TAX', 
      'MODULE_ORDER_TOTAL_GV_CALC_TAX', 
      'MODULE_ORDER_TOTAL_GV_TAX_CLASS', 
      'MODULE_ORDER_TOTAL_GV_CREDIT_TAX'
    );
  }

  function install() {
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('', 'MODULE_ORDER_TOTAL_GV_STATUS', 'true', '6', '1','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('', 'MODULE_ORDER_TOTAL_GV_SORT_ORDER', '80', '6', '2', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('', 'MODULE_ORDER_TOTAL_GV_QUEUE', 'true', '6', '3','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('', 'MODULE_ORDER_TOTAL_GV_INC_SHIPPING', 'true', '6', '5', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('', 'MODULE_ORDER_TOTAL_GV_INC_TAX', 'true', '6', '6','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('', 'MODULE_ORDER_TOTAL_GV_CALC_TAX', 'None', '6', '7','xtc_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'), ', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('', 'MODULE_ORDER_TOTAL_GV_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('', 'MODULE_ORDER_TOTAL_GV_CREDIT_TAX', 'false', '6', '8','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('', 'MODULE_ORDER_TOTAL_GV_UNALLOWED_PAYMENT', '', '6', '5', '', 'xtc_cfg_checkbox_unallowed_module(\'payment\', \'configuration[MODULE_ORDER_TOTAL_GV_UNALLOWED_PAYMENT]\',', now())");
  }

  function remove() {
    xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key IN ('".implode("', '", $this->keys())."')");
  }
}
