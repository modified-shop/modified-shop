<?php
/* -----------------------------------------------------------------------------------------
   $Id: ot_gv.php 2099 2011-08-17 18:22:16Z dokuman $

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
  var $title, $output;

  function __construct() {
    global $xtPrice;
    
    $this->code = 'ot_gv';
    $this->title = MODULE_ORDER_TOTAL_GV_TITLE;
    $this->header = MODULE_ORDER_TOTAL_GV_HEADER;
    $this->description = MODULE_ORDER_TOTAL_GV_DESCRIPTION;
    $this->info = MODULE_ORDER_TOTAL_GV_USER_PROMPT;
    $this->user_prompt = MODULE_ORDER_TOTAL_GV_USER_PROMPT;
    $this->enabled = MODULE_ORDER_TOTAL_GV_STATUS;
    $this->sort_order = MODULE_ORDER_TOTAL_GV_SORT_ORDER;
    $this->include_shipping = MODULE_ORDER_TOTAL_GV_INC_SHIPPING;
    $this->include_tax = MODULE_ORDER_TOTAL_GV_INC_TAX;
    $this->calculate_tax = MODULE_ORDER_TOTAL_GV_CALC_TAX;
    $this->credit_tax = MODULE_ORDER_TOTAL_GV_CREDIT_TAX;
    $this->tax_class = MODULE_ORDER_TOTAL_GV_TAX_CLASS;
    $this->credit_class = true;
    $this->checkbox = '<input type="checkbox" onclick="submitFunction()" name="'.'c'.$this->code.'"> '.$this->user_prompt;
    
    $this->output = array ();
  }

  function process() {
    global $order, $xtPrice;

    if (isset ($_SESSION['cot_gv']) && $_SESSION['cot_gv'] == true) {
      $order_total = $this->get_order_total();

      $od_amount = $this->calculate_credit($order_total);
      if ($this->calculate_tax != "None") {
        $tod_amount = $this->calculate_tax_deduction($order_total, $od_amount, $this->calculate_tax);
        $od_amount = $this->calculate_credit($order_total);
      }

      $this->deduction = $od_amount * (-1);

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
        $tod_amount = $this->calculate_tax_deduction($order_total, $od_amount, $this->calculate_tax);
        $od_amount = $this->calculate_credit($order_total) + $tod_amount;
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
      $gv_order_amount = $gv_order_amount * 100 / 100;
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
    global $order, $coupon_no, $xtPrice;
    
    if (isset ($_SESSION['cot_gv']) && $_SESSION['cot_gv'] == true) {
      $gv_query = xtc_db_query("SELECT amount 
                                  FROM ".TABLE_COUPON_GV_CUSTOMER." 
                                 WHERE customer_id = '".(int)$_SESSION['customer_id']."'");
      $gv_result = xtc_db_fetch_array($gv_query);
      $gv_amount = $gv_result['amount'] + $xtPrice->xtcRemoveCurr($this->deduction);
      xtc_db_query("UPDATE ".TABLE_COUPON_GV_CUSTOMER." 
                       SET amount = '".$gv_amount."' 
                     WHERE customer_id = '".(int)$_SESSION['customer_id']."'");
    }
    return $gv_payment_amount;
  }

  function collect_posts() {
    global $xtPrice, $coupon_no, $REMOTE_ADDR;
    if (isset($_POST['gv_redeem_code'])) {
      $gv_query = xtc_db_query("select coupon_id, coupon_type, coupon_amount from ".TABLE_COUPONS." where coupon_code = '".xtc_db_input($_POST['gv_redeem_code'])."'");
      $gv_result = xtc_db_fetch_array($gv_query);
      if (xtc_db_num_rows($gv_query) != 0) {
        $redeem_query = xtc_db_query("select * from ".TABLE_COUPON_REDEEM_TRACK." where coupon_id = '".$gv_result['coupon_id']."'");
        if ((xtc_db_num_rows($redeem_query) != 0) && ($gv_result['coupon_type'] == 'G')) {
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message='.urlencode(ERROR_NO_INVALID_REDEEM_GV), 'SSL'));
        }
      }
      if ($gv_result['coupon_type'] == 'G') {
        $gv_amount = $gv_result['coupon_amount'];
        // Things to set
        // ip address of claimant
        // customer id of claimant
        // date
        // redemption flag
        // now update customer account with gv_amount
        $gv_amount_query = xtc_db_query("select amount from ".TABLE_COUPON_GV_CUSTOMER." where customer_id = '".$_SESSION['customer_id']."'");
        $customer_gv = false;
        $total_gv_amount = $gv_amount;
        if ($gv_amount_result = xtc_db_fetch_array($gv_amount_query)) {
          $total_gv_amount = $gv_amount_result['amount'] + $gv_amount;
          $customer_gv = true;
        }
        $gv_update = xtc_db_query("update ".TABLE_COUPONS." set coupon_active = 'N' where coupon_id = '".$gv_result['coupon_id']."'");
        $gv_redeem = xtc_db_query("INSERT INTO  ".TABLE_COUPON_REDEEM_TRACK." (coupon_id, customer_id, redeem_date, redeem_ip) values ('".$gv_result['coupon_id']."', '".$SESSION['customer_id']."', now(),'".$REMOTE_ADDR."')");
        if ($customer_gv) {
          // already has gv_amount so update
          $gv_update = xtc_db_query("update ".TABLE_COUPON_GV_CUSTOMER." set amount = '".$total_gv_amount."' where customer_id = '".$_SESSION['customer_id']."'");
        } else {
          // no gv_amount so insert
          $gv_insert = xtc_db_query("INSERT INTO ".TABLE_COUPON_GV_CUSTOMER." (customer_id, amount) values ('".$_SESSION['customer_id']."', '".$total_gv_amount."')");
        }
      }
    }
    if (isset($_POST['submit_redeem_x']) && $gv_result['coupon_type'] == 'G') {
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message='.urlencode(ERROR_NO_REDEEM_CODE), 'SSL'));
    }
  }

  function calculate_credit($amount) {
    global $order;
    
    $gv_query = xtc_db_query("SELECT amount 
                                FROM ".TABLE_COUPON_GV_CUSTOMER." 
                               WHERE customer_id = '".(int)$_SESSION['customer_id']."'");
    $gv_result = xtc_db_fetch_array($gv_query);
    $gv_payment_amount = $gv_result['amount'];
    
    if (($amount - $gv_payment_amount) <= 0) {
      $gv_payment_amount = $amount;
    }
    
    return $gv_payment_amount;
  }

  function calculate_tax_deduction($amount, $od_amount, $method) {
    global $order;
    
    switch ($method) {
      case 'Standard':
        $ratio1 = number_format($od_amount / $amount, 2);
        $tod_amount = 0;
        reset($order->info['tax_groups']);
        while (list ($key, $value) = each($order->info['tax_groups'])) {
          $tax_rate = xtc_get_tax_rate_from_desc($key);
          $total_net += $tax_rate * $order->info['tax_groups'][$key];
        }
        if ($od_amount > $total_net)
          $od_amount = $total_net;
        reset($order->info['tax_groups']);
        while (list ($key, $value) = each($order->info['tax_groups'])) {
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
    if (!isset ($this->check)) {
      $check_query = xtc_db_query("SELECT configuration_value 
                                     FROM ".TABLE_CONFIGURATION." 
                                    WHERE configuration_key = 'MODULE_ORDER_TOTAL_GV_STATUS'");
      $this->check = xtc_db_num_rows($check_query);
    }

    return $this->check;
  }

  function keys() {
    return array (
      'MODULE_ORDER_TOTAL_GV_STATUS', 
      'MODULE_ORDER_TOTAL_GV_SORT_ORDER', 
      'MODULE_ORDER_TOTAL_GV_QUEUE', 
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
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function ,date_added) VALUES ('', 'MODULE_ORDER_TOTAL_GV_INC_SHIPPING', 'true', '6', '5', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function ,date_added) VALUES ('', 'MODULE_ORDER_TOTAL_GV_INC_TAX', 'true', '6', '6','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function ,date_added) VALUES ('', 'MODULE_ORDER_TOTAL_GV_CALC_TAX', 'None', '6', '7','xtc_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'), ', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('', 'MODULE_ORDER_TOTAL_GV_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function ,date_added) VALUES ('', 'MODULE_ORDER_TOTAL_GV_CREDIT_TAX', 'false', '6', '8','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
  }

  function remove() {
    xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key IN ('".implode("', '", $this->keys())."')");
  }
}
?>