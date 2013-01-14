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

  function ot_gv() {
    global $xtPrice;
    $this->code = 'ot_gv';
    $this->title = MODULE_ORDER_TOTAL_GV_TITLE;
    $this->header = MODULE_ORDER_TOTAL_GV_HEADER;
    $this->description = MODULE_ORDER_TOTAL_GV_DESCRIPTION;
    $this->user_prompt = MODULE_ORDER_TOTAL_GV_USER_PROMPT;
    $this->enabled = MODULE_ORDER_TOTAL_GV_STATUS;
    $this->sort_order = MODULE_ORDER_TOTAL_GV_SORT_ORDER;
    $this->include_shipping = MODULE_ORDER_TOTAL_GV_INC_SHIPPING;
    $this->include_tax = MODULE_ORDER_TOTAL_GV_INC_TAX;
    $this->calculate_tax = MODULE_ORDER_TOTAL_GV_CALC_TAX;
    $this->credit_tax = MODULE_ORDER_TOTAL_GV_CREDIT_TAX;
    $this->tax_class = MODULE_ORDER_TOTAL_GV_TAX_CLASS;
    //$this->show_redeem_box = MODULE_ORDER_TOTAL_GV_REDEEM_BOX; //DokuMan - 2010-10-14 - remove unused constant MODULE_ORDER_TOTAL_GV_REDEEM_BOX
    $this->credit_class = true;
    // BOF - Tomcraft - 2010-01-29 - Fix display of voucher
    //$this->checkbox = $this->user_prompt.'<input type="checkbox" onclick="submitFunction()" name="'.'c'.$this->code.'">';
    $this->checkbox = '<input type="checkbox" onclick="submitFunction()" name="'.'c'.$this->code.'"> '.$this->user_prompt;
    // EOF - Tomcraft - 2010-01-29 - Fix display of voucher
    $this->output = array ();
  }

  function process() {
    global $order, $xtPrice;
    //      if ($_SESSION['cot_gv']) {  // old code Strider
    if (isset ($_SESSION['cot_gv']) && $_SESSION['cot_gv'] == true) {
      $order_total = $this->get_order_total();

      $od_amount = $this->calculate_credit($order_total);
      if ($this->calculate_tax != "None") {
        $tod_amount = $this->calculate_tax_deduction($order_total, $od_amount, $this->calculate_tax);
        $od_amount = $this->calculate_credit($order_total);
      }

      $this->deduction = $od_amount;
      //        if (($this->calculate_tax == "Credit Note") && (DISPLAY_PRICE_WITH_TAX != 'true')) {
      //          $od_amount -= $tod_amount;
      //          $order->info['total'] -= $tod_amount;
      //        }

      $order->info['total'] = $order->info['total'] - $od_amount;

      if ($od_amount > 0) {
        $this->output[] = array ('title' => $this->title.':', 
                                 'text' => '<strong><font color="#ff0000">'.$xtPrice->xtcFormat($od_amount *(-1), true).'</font></strong>', //2011-08-25 - web28 - fix negativ sign
                                 'value' => $xtPrice->xtcFormat($od_amount *(-1), false)); //2011-08-25 - web28 - fix negativ sign
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
    //    if ($_SESSION['cot_gv']) {  // old code Strider
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
  // original code
  /*function pre_confirmation_check($order_total) {
    if ($SESSION['cot_gv']) {
      $gv_payment_amount = $this->calculate_credit($order_total);
    }
    return $gv_payment_amount;
  } */

  function use_credit_amount() {
    $output_string = ''.

    $_SESSION['cot_gv'] = false;
    if ($this->selection_test()) {
      //BOF - DokuMan - 2010-09-29 - remove layout formatting from checkbox
      //$output_string .= '    <td nowrap align="right" class="main">';
      //$output_string .= '<strong>'.$this->checkbox.'</strong>'.'</td>'."\n";
      $output_string = $this->checkbox;
      //EOF - DokuMan - 2010-09-29 - remove layout formatting from checkbox
    }
    return $output_string;
  }

  function update_credit_account($i) {
    global $order, $insert_id, $REMOTE_ADDR;
    if (preg_match('/^GIFT/', addslashes($order->products[$i]['model']))) { // Hetfield - 2009-08-19 - replaced deprecated function ereg with preg_match to be ready for PHP >= 5.3
      $gv_order_amount = ($order->products[$i]['final_price']);
      if ($this->credit_tax == 'true')
        $gv_order_amount = $gv_order_amount * (100 + $order->products[$i]['tax']) / 100;
      //        $gv_order_amount += 0.001;
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
          $gv_insert = xtc_db_query("insert into ".TABLE_COUPON_GV_CUSTOMER." (customer_id, amount) values ('".$_SESSION['customer_id']."', '".$total_gv_amount."')");
        }
      } else {
        // GV_QUEUE is true - so queue the gv for release by store owner
        $gv_insert = xtc_db_query("insert into ".TABLE_COUPON_GV_QUEUE." (customer_id, order_id, amount, date_created, ipaddr) values ('".$_SESSION['customer_id']."', '".$insert_id."', '".$gv_order_amount."', NOW(), '".$REMOTE_ADDR."')");
      }
    }
  }

  function credit_selection() {
    global $currencies;
    $selection_string = '';
    $gv_query = xtc_db_query("select coupon_id from ".TABLE_COUPONS." where coupon_type = 'G' and coupon_active='Y'");
    /*
    if (xtc_db_num_rows($gv_query)) {
      $selection_string .= '<tr>' . "\n";
      $selection_string .= '  <td width="10">' .  xtc_draw_separator('pixel_trans.gif', '10', '1') .'</td>';
      $selection_string .= '  <td class="main">' . "\n";
      $selection_string .= TEXT_ENTER_GV_CODE . xtc_draw_input_field('gv_redeem_code') . '</td>';
      $selection_string .= ' <td align="right"></td>';
      $selection_string .= '  <td width="10">' . xtc_draw_separator('pixel_trans.gif', '10', '1') . '</td>';
      $selection_string .= '</tr>' . "\n";
    }
    */
    return $selection_string;
  }

  function apply_credit() {
    global $order, $coupon_no,$xtPrice;
    if (isset ($_SESSION['cot_gv']) && $_SESSION['cot_gv'] == true) {
      $gv_query = xtc_db_query("select amount from ".TABLE_COUPON_GV_CUSTOMER." where customer_id = '".$_SESSION['customer_id']."'");
      $gv_result = xtc_db_fetch_array($gv_query);
      $gv_payment_amount = $this->deduction;
      $gv_amount = $gv_result['amount'] - $xtPrice->xtcRemoveCurr($gv_payment_amount);
      //prepare for DB insert
      $gv_amount = str_replace(",", ".", $gv_amount);
      $gv_update = xtc_db_query("update ".TABLE_COUPON_GV_CUSTOMER." set amount = '".$gv_amount."' where customer_id = '".$_SESSION['customer_id']."'");
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
        $gv_redeem = xtc_db_query("insert into  ".TABLE_COUPON_REDEEM_TRACK." (coupon_id, customer_id, redeem_date, redeem_ip) values ('".$gv_result['coupon_id']."', '".$SESSION['customer_id']."', now(),'".$REMOTE_ADDR."')");
        if ($customer_gv) {
          // already has gv_amount so update
          $gv_update = xtc_db_query("update ".TABLE_COUPON_GV_CUSTOMER." set amount = '".$total_gv_amount."' where customer_id = '".$_SESSION['customer_id']."'");
        } else {
          // no gv_amount so insert
          $gv_insert = xtc_db_query("insert into ".TABLE_COUPON_GV_CUSTOMER." (customer_id, amount) values ('".$_SESSION['customer_id']."', '".$total_gv_amount."')");
        }
        //xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_REDEEMED_AMOUNT. $currencies->format($gv_amount)), 'SSL'));
      }
    }
    if (isset($_POST['submit_redeem_x']) && $gv_result['coupon_type'] == 'G')
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message='.urlencode(ERROR_NO_REDEEM_CODE), 'SSL'));
  }

  function calculate_credit($amount) {
    global $order;
    $gv_query = xtc_db_query("select amount from ".TABLE_COUPON_GV_CUSTOMER." where customer_id = '".$_SESSION['customer_id']."'");
    $gv_result = xtc_db_fetch_array($gv_query);
    $gv_payment_amount = $gv_result['amount'];
    $gv_amount = $gv_payment_amount;
    $save_total_cost = $amount;
    $full_cost = $save_total_cost - $gv_payment_amount;
    if ($full_cost <= 0) {
      $full_cost = 0;
      $gv_payment_amount = $save_total_cost;
    }
    return $gv_payment_amount;
  }

  function calculate_tax_deduction($amount, $od_amount, $method) {
    global $order;
    switch ($method) {
      case 'Standard' :
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
      case 'Credit Note' :
        $tax_rate = xtc_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
        $tax_desc = xtc_get_tax_description($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
        $tod_amount = $this->deduction / (100 + $tax_rate) * $tax_rate;
        $order->info['tax_groups'][$tax_desc] -= $tod_amount;
        //          $order->info['total'] -= $tod_amount;   //// ????? Strider
        break;
      default :
        }
    return $tod_amount;
  }

  function user_has_gv_account($c_id) {
    $gv_query = xtc_db_query("select amount from ".TABLE_COUPON_GV_CUSTOMER." where customer_id = '".$c_id."'");
    if ($gv_result = xtc_db_fetch_array($gv_query)) {
      if ($gv_result['amount'] > 0) {
        return true;
      }
    }
    return false;
  }

  function get_order_total() {
    global $order;
    if ($_SESSION['customers_status']['customers_status_show_price_tax'] != 0)
      $order_total = $order->info['total'];
    if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1)
      $order_total = $order->info['tax'] + $order->info['total'];
    if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0)
      $order_total = $order->info['total'];
    if ($this->include_tax == 'false')
      $order_total = $order_total - $order->info['tax'];
    if ($this->include_shipping == 'false')
      $order_total = $order_total - $order->info['shipping_cost'];
    return $order_total;
  }

  function check() {
    if (!isset ($this->check)) {
      $check_query = xtc_db_query("select configuration_value from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_ORDER_TOTAL_GV_STATUS'");
      $this->check = xtc_db_num_rows($check_query);
    }

    return $this->check;
  }

  function keys() {
    return array ('MODULE_ORDER_TOTAL_GV_STATUS', 'MODULE_ORDER_TOTAL_GV_SORT_ORDER', 'MODULE_ORDER_TOTAL_GV_QUEUE', 'MODULE_ORDER_TOTAL_GV_INC_SHIPPING', 'MODULE_ORDER_TOTAL_GV_INC_TAX', 'MODULE_ORDER_TOTAL_GV_CALC_TAX', 'MODULE_ORDER_TOTAL_GV_TAX_CLASS', 'MODULE_ORDER_TOTAL_GV_CREDIT_TAX');
  }

  function install() {
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_ORDER_TOTAL_GV_STATUS', 'true', '6', '1','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('', 'MODULE_ORDER_TOTAL_GV_SORT_ORDER', '80', '6', '2', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_ORDER_TOTAL_GV_QUEUE', 'true', '6', '3','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function ,date_added) values ('', 'MODULE_ORDER_TOTAL_GV_INC_SHIPPING', 'true', '6', '5', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function ,date_added) values ('', 'MODULE_ORDER_TOTAL_GV_INC_TAX', 'true', '6', '6','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function ,date_added) values ('', 'MODULE_ORDER_TOTAL_GV_CALC_TAX', 'None', '6', '7','xtc_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'), ', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('', 'MODULE_ORDER_TOTAL_GV_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function ,date_added) values ('', 'MODULE_ORDER_TOTAL_GV_CREDIT_TAX', 'false', '6', '8','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
  }

  function remove() {
    xtc_db_query("delete from ".TABLE_CONFIGURATION." where configuration_key in ('".implode("', '", $this->keys())."')");
  }
}
?>