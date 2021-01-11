<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ot_coupon.php,v 1.1.2.37.3); www.oscommerce.com
   (c) 2006 xt:Commerce (ot_coupon.php 1002 2005-07-10); www.xt-commerce.de

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

   BUGFIXES & MODIFIED rev1.3.11 by web28 - www.rpa-com.de
   1.3.11 add new coupon_type = 'T' : coupon_amount percent and shipping_free
   1.3.10 fix tax calculation
   1.3.9 fix linked products at categories restrictions// fix tax calculation at restrictions
   1.3.8 add minimum order message // change get_order_total() // remove get_product_price()
   1.3.7 remove //KORREKTUR wenn Kunde Nettopreise und Steuer in Rechnung
   1.3.6 fix $od_amount for customers with no tax and percent coupon
   1.3.5 fix xtc_db_fetch_array cache, new restrict_to_categories check
   1.3.4 fix tax deduction
   1.3.3 optimize code
   1.3.2 fix different currencies
   ---------------------------------------------------------------------------------------*/

class ot_coupon {
  var $title, $output;


  function __construct() {
    global $xtPrice;

    $this->code = 'ot_coupon';
    $this->header = MODULE_ORDER_TOTAL_COUPON_HEADER;
    $this->title = MODULE_ORDER_TOTAL_COUPON_TITLE;
    $this->description = MODULE_ORDER_TOTAL_COUPON_DESCRIPTION;
    $this->user_prompt = '';
    $this->enabled = ((defined('MODULE_ORDER_TOTAL_COUPON_STATUS') && MODULE_ORDER_TOTAL_COUPON_STATUS == 'true') ? true : false);
    $this->sort_order = ((defined('MODULE_ORDER_TOTAL_COUPON_SORT_ORDER')) ? MODULE_ORDER_TOTAL_COUPON_SORT_ORDER : '');

    if ($this->check() > 0) {
      $this->include_shipping = 'false';
      $this->include_tax = 'true';
      $this->calculate_tax = MODULE_ORDER_TOTAL_COUPON_CALC_TAX;
      $this->tax_class = MODULE_ORDER_TOTAL_COUPON_TAX_CLASS;
    }

    $this->deduction = 0;
    $this->credit_class = true;
    $this->output = array ();

    $this->products_price = array();
    $this->products_tax_rate = array();
    $this->products_tax_description = array();

    $this->tax_groups = array();
    $this->price_total_by_tax_groups = array();
    $this->price_total_by_tax_rate = array();
  }


  function process() {
    global $order, $xtPrice;

    $order_total = $this->get_order_total();
    $od_amount = $this->calculate_credit($order_total);

    if ($od_amount > 0) {
      if ($od_amount > $order->info['total']) {
        $od_amount = $order->info['total'];
      }

      $this->deduction = $od_amount;

      if ($this->calculate_tax != 'None') {
        $this->new_calculate_tax_deduction($od_amount, $order_total);
      }
      $order->info['total'] = $xtPrice->xtcFormat($order->info['total'] - $od_amount, false);
      $order->info['deduction'] = $od_amount;
      $order->info['subtotal'] = $order->info['subtotal'] - $od_amount;

      $this->output[] = array(
        'title' => $this->description.' '.$this->coupon_code.':',
        'text'  => '<span class="color_ot_total"><b>'.$xtPrice->xtcFormat($od_amount * (-1), true).'</b></span>',
        'value' => $od_amount * (-1)
      );
    }
  }


  function selection_test() {
    return false;
  }


  function pre_confirmation_check($order_total) {
    $order_total = $this->get_order_total();
    return $this->calculate_credit($order_total);
  }


  function use_credit_amount() {
    $output_string = '';
    return $output_string;
  }


  function credit_selection() {
    return false;
  }


  function collect_posts() {
    global $xtPrice;

    if (isset($_POST['gv_redeem_code']) && $_POST['gv_redeem_code']) {

      // INFOS ÜBER KUPON AUSLESEN
      $coupon_query = xtc_db_query("select *
                                      from ".TABLE_COUPONS."
                                     where coupon_code='".xtc_db_input($_POST['gv_redeem_code'])."'
                                       and coupon_active='Y'");
      $coupon_array = xtc_db_fetch_array($coupon_query);

      if ($coupon_array['coupon_type'] != 'G') {

        if (xtc_db_num_rows($coupon_query) == 0) {
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message='.urlencode(ERROR_NO_INVALID_REDEEM_COUPON), 'SSL'));
        }

        // ERROR : LAUFZEIT HAT NOCH NICHT BEGONNEN
        if ($coupon_array['coupon_start_date'] > date('Y-m-d H:i:s')) {
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_INVALID_STARTDATE_COUPON), 'SSL'));
        }

        // ERROR : LAUFZEIT BEENDET
        if ($coupon_array['coupon_expire_date'] < date('Y-m-d H:i:s')) {
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_INVALID_FINISDATE_COUPON), 'SSL'));
        }

        // ERROR : GESAMTES VERWENDUNGSLIMIT ÜBERSCHRITTEN
        $coupon_count = xtc_db_query("select coupon_id from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . $coupon_array['coupon_id'] . "'");
        if (xtc_db_num_rows($coupon_count) >= $coupon_array['uses_per_coupon'] && $coupon_array['uses_per_coupon'] > 0) {
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_INVALID_USES_COUPON . $coupon_array['uses_per_coupon'] . TIMES), 'SSL'));
        }

        // ERROR : VERWENDUNGSLIMIT FÜR EINZELNEN KUNDEN ÜBERSCHRITTEN
        $coupon_count_customer = xtc_db_query("select coupon_id from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . $coupon_array['coupon_id'] . "' and customer_id = '" . (int) $_SESSION['customer_id'] . "'");
        if (xtc_db_num_rows($coupon_count_customer) >= $coupon_array['uses_per_user'] && $coupon_array['uses_per_user'] > 0) {
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_INVALID_USES_USER_COUPON . $coupon_array['uses_per_user'] . TIMES), 'SSL'));
        }

        // ERROR : MINDESTBESTELLWERT NICHT ERREICHT //FIX - web28 - 2012-04-24 - calculate currencies
        if ($xtPrice->xtcCalculateCurr($coupon_array['coupon_minimum_order']) > $_SESSION['cart']->show_total()) {
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'info_message=' . urlencode(ERROR_MINIMUM_ORDER_COUPON_1 . ' ' . $xtPrice->xtcFormat($coupon_array['coupon_minimum_order'], true, 0, true) . ' ' . ERROR_MINIMUM_ORDER_COUPON_2), 'SSL'));
        }
      }

      if ($_POST['submit_redeem_coupon_x'] && !$_POST['gv_redeem_code'])
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message='.urlencode(ERROR_NO_REDEEM_CODE), 'SSL'));
      }
  }


  function calculate_credit($amount) {
    global $order, $xtPrice, $tax_info_excl;

    $this->tax_groups = array();
    $this->price_total_by_tax_groups = array();
    $this->price_total_by_tax_rate = array();

    $od_amount = 0;
    if (isset ($_SESSION['cc_id'])) {
      $coupon_query = xtc_db_query("SELECT *
                                      FROM ".TABLE_COUPONS."
                                     WHERE coupon_id = '".(int)$_SESSION['cc_id']."'
                                       AND coupon_active = 'Y'
                                       AND (restrict_to_customers = ''
                                            OR restrict_to_customers IS NULL
                                            OR FIND_IN_SET ('". (int)$_SESSION['customers_status']['customers_status_id'] ."', restrict_to_customers)
                                            )");
      if (xtc_db_num_rows($coupon_query) != 0) {
        $coupon_array = xtc_db_fetch_array($coupon_query);

        $cc_min_amount = $xtPrice->xtcCalculateCurr($coupon_array['coupon_minimum_order']);
        if ( $cc_min_amount > $amount && isset($_SESSION['cc_id'])) {
          unset($_SESSION['cc_id']);
          $_SESSION['error_invalid_coupon_minimum_order'] = sprintf(ERROR_INVALID_MINIMUM_ORDER_COUPON,$xtPrice->xtcFormat($cc_min_amount,true));
          return 0;
        }

        $this->coupon_code = $coupon_array['coupon_code'];

        $c_deduct = $xtPrice->xtcCalculateCurr($coupon_array['coupon_amount']);

        if ($coupon_array['coupon_type'] == 'S') {
          $c_deduct = $this->get_shipping_cost();
        }

        $flag_s = false;
        if ($coupon_array['coupon_type']=='S' && $coupon_array['coupon_amount'] > 0 ) {
          $c_deduct = $c_deduct + $xtPrice->xtcCalculateCurr($coupon_array['coupon_amount']);
          $flag_s = true;
        }

        $flag_t = false;
        if ($coupon_array['coupon_type'] == 'T') {
          $coupon_array['coupon_type'] = 'P';
          $flag_t = true;
        }

        if ($coupon_array['restrict_to_products'] || $coupon_array['restrict_to_categories']) {

          $pr_c = 0;

          //allowed products
          $coupon_array['restrict_to_products'] = preg_replace("'[\r\n\s]+'", '', $coupon_array['restrict_to_products']);
          if (trim($coupon_array['restrict_to_products']) != '') {
            $pr_ids = explode(",", $coupon_array['restrict_to_products']);
            $pr_ids = array_unique($pr_ids);
            for ($i = 0, $n = sizeof($order->products); $i < $n; ++$i) {
              for ($ii = 0, $nn = count($pr_ids); $ii < $nn; $ii ++) {
                if ($pr_ids[$ii] == xtc_get_prid($order->products[$i]['id'])) {
                  if ($coupon_array['coupon_type'] == 'P') {
                    $pr_c = $this->product_price($order->products[$i]['id']);
                    $pod_amount = round($pr_c*10)/10*$c_deduct/100;
                    $od_amount = $od_amount + $pod_amount;

                  } else {
                    $od_amount = $c_deduct;
                    $pr_c += $this->product_price($order->products[$i]['id']);
                  }
                }
              }
            }
          }

          //allowed categories
          $_c_products_ids = array();
          $coupon_array['restrict_to_categories'] = preg_replace("'[\r\n\s]+'", '', $coupon_array['restrict_to_categories']);
          if (trim($coupon_array['restrict_to_categories']) != '') {
            $cat_ids = explode(",", $coupon_array['restrict_to_categories']);
            $cat_ids = array_unique($cat_ids);
            for ($i = 0, $n = sizeof($order->products); $i < $n; ++$i) {
              $p_flag = $coupon_array['restrict_to_products'] && in_array(xtc_get_prid($order->products[$i]['id']), $pr_ids) ? true : false;

              $prod_cat_ids_array = $this->get_cat_ids_array(xtc_get_prid($order->products[$i]['id']));
              for ($ii = 0 , $nn = count($cat_ids); $ii < $nn ; $ii ++) {
                if (in_array($cat_ids[$ii], $prod_cat_ids_array) && !$p_flag && !in_array($order->products[$i]['id'],$_c_products_ids)) {
                  $_c_products_ids[] = $order->products[$i]['id'];
                  if ($coupon_array['coupon_type'] == 'P') {
                    $pr_c = $this->product_price($order->products[$i]['id']);
                    $pod_amount = round($pr_c*10)/10*$c_deduct/100;
                    $od_amount = $od_amount + $pod_amount;
                  } else {
                    $od_amount = $c_deduct;
                    $pr_c += $this->product_price($order->products[$i]['id']);
                  }
                }
              }
            }
          }

          if ($coupon_array['coupon_type'] == 'F' && $od_amount > $pr_c ) {$od_amount = $pr_c;}

        } else {
          if ($coupon_array['coupon_type'] != 'P') {
            $od_amount = $c_deduct;
          } else {
            $od_amount = $amount * $coupon_array['coupon_amount'] / 100;
          }
        }

        if (MODULE_ORDER_TOTAL_COUPON_SPECIAL_PRICES == 'false'
            && (!isset($_SESSION['customers_status']['customers_status_specials'])
                || $_SESSION['customers_status']['customers_status_specials'] == '1'
                )
            )
        {
          $pr_c = 0;
          for ($i = 0; $i < sizeof($order->products); $i ++) {
            $product_query = xtc_db_query("SELECT specials_new_products_price
                                             FROM ".TABLE_SPECIALS."
                                            WHERE products_id = '".xtc_get_prid($order->products[$i]['id'])."'
                                                  ".SPECIALS_CONDITIONS);
            if (xtc_db_num_rows($product_query) > 0) {
              $product = xtc_db_fetch_array($product_query);
              if ($coupon_array['coupon_type'] == 'P') {
                $pr_c = $this->product_price($order->products[$i]['id'], false);
                $pod_amount = round($pr_c*10)/10*$c_deduct/100;
                $od_amount -= $pod_amount;
              } else {
                $pr_c += $this->product_price($order->products[$i]['id'], false);
              }
            } else {
              if (count($this->price_total_by_tax_rate) < 1) {
                $this->product_price($order->products[$i]['id']);
              }
            }
          }
          if ($od_amount < 0) $od_amount = 0;
          if ($amount <= $pr_c) $od_amount = 0;
        }
      }

      if ($flag_t) {
        $od_amount += $this->get_shipping_cost();
      }

      if ($flag_s) {
        $amount += $this->get_shipping_cost();
      }

      if ($od_amount > $amount) {
        $od_amount = $amount;
      }
    }

    return $od_amount;
  }


  function new_calculate_tax_deduction($od_amount, $order_total) {
    global $order;

    // restrictions
    $restriction = isset($this->tax_groups) && count($this->tax_groups) ? true : false;

    // reduction in percent
    $od_amount_pro = $od_amount/$order_total * 100;

    foreach ($order->info['tax_groups'] as $key => $value) {
      if (isset($this->tax_groups[$key])) {
        // restriction
        $od_amount_pro = $restriction ? ($od_amount / $this->price_total_by_tax_groups[$key] * 100) : $od_amount_pro;

        if ($_SESSION['customers_status']['customers_status_show_price_tax'] != '1') {
          // netto
          $god_amount = $order->info['tax_groups'][$key] - ($order->info['tax_groups'][$key] * $od_amount_pro / 100);
          $order->info['tax_groups'][$key] = $god_amount;
        } else {
          // brutto
          $god_amount = $order->info['tax_groups'][$key] * $od_amount_pro / 100;
          $order->info['tax_groups'][$key] -= $god_amount;
        }
      }
    }

    // recalculate tax
    $order->info['tax'] = array_sum($order->info['tax_groups']);
  }


  function get_shipping_cost() {
    global $order, $xtPrice;

    $shipping_module = '';
    if (isset($_SESSION['shipping'])
        && is_array($_SESSION['shipping'])
        && array_key_exists('id', $_SESSION['shipping'])
        )
    {
      $shipping_module = substr($_SESSION['shipping']['id'], 0, strpos($_SESSION['shipping']['id'], '_'));
    }
    $shipping_cost = $order->info['shipping_cost'];

    if ($shipping_cost > 0) {
      $shipping_tax_class = ((defined('MODULE_SHIPPING_'.strtoupper($shipping_module).'_TAX_CLASS')) ? constant('MODULE_SHIPPING_'.strtoupper($shipping_module).'_TAX_CLASS') : 0);
      $shipping_tax_rate_description = xtc_get_tax_description($shipping_tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
      $tax_index = $this->set_tax_group_index($shipping_tax_rate_description);

      if ($_SESSION['customers_status']['customers_status_show_price_tax'] == '1') {
        $shipping_tax_rate = xtc_get_tax_rate($shipping_tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
        $shipping_tax = $order->info['shipping_cost'] * ($shipping_tax_rate / 100 +1) - $order->info['shipping_cost'];
        $shipping_cost = $order->info['shipping_cost'] + $shipping_tax;
        $shipping_cost = $xtPrice->xtcFormat($shipping_cost, false);
      }
    }

    return $shipping_cost;
  }


  function update_credit_account($i) {
    return false;
  }


  function apply_credit() {
    global $insert_id;

    if ($this->deduction != 0) {
      $sql_data_array = array(
         'coupon_id' => (int)$_SESSION['cc_id'],
         'redeem_date' => 'now()',
         'redeem_ip' => ((isset($_SESSION['tracking']['ip'])) ? xtc_db_prepare_input($_SESSION['tracking']['ip']) : ''),
         'customer_id' => (int)$_SESSION['customer_id'],
         'order_id' => $insert_id
      );
      xtc_db_perform(TABLE_COUPON_REDEEM_TRACK, $sql_data_array);
    }
    unset ($_SESSION['cc_id']);
  }


  function get_order_total() {
    global $order;

    $order_total = $_SESSION['cart']->show_total();
    if (($_SESSION['customers_status']['customers_status_show_price_tax'] == 0
         && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1
         ) || ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0
               && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0
               )
        )
    {
      $order_total = $_SESSION['cart']->total_netto;
    }
    $this->products_price = array();
    $this->products_tax_description = array();
    $this->products_tax_rate = array();

    $products = $order->products;
    for ($i = 0; $i < sizeof($products); $i ++) {
      $product_id = $products[$i]['id'];
      $products_price = $products[$i]['price'] * $products[$i]['qty'];
      $this->products_price[$product_id] = $products_price;
      $this->products_tax_description[$product_id] = $products[$i]['tax_description'];
      $this->products_tax_rate[$product_id] = xtc_get_tax_rate($products[$i]['tax_class_id'], $order->delivery['country']['id'], $order->delivery['zone_id']);
      if (preg_match('/^GIFT/', addslashes($products[$i]['model']))) {
        $order_total -= $products_price;
      }
    }

    if ($this->include_shipping == 'true'
        && isset($order->info['shipping_cost'])
        && $order->info['shipping_cost'] > 0
        )
    {
      $order_total += $order->info['shipping_cost'];
    }

    return $order_total;
  }


  function product_price($product_id, $set_tax = true) {
    $products_price = isset($this->products_price[$product_id]) ? $this->products_price[$product_id] : 0;

    if ($set_tax === true) {
      $tax_index = $this->set_tax_group_index($this->products_tax_description[$product_id]);
      $this->price_total_by_tax_rate[$tax_index] = $this->products_tax_rate[$product_id];
      if (!isset($this->price_total_by_tax_groups[$tax_index])) {
        $this->price_total_by_tax_groups[$tax_index] = 0;
      }
      $this->price_total_by_tax_groups[$tax_index] += $products_price;
    }

    return $products_price;
  }


  function set_tax_group_index($tax_description) {
    $tax_index = (($_SESSION['customers_status']['customers_status_show_price_tax'] == '1') ? TAX_ADD_TAX : TAX_NO_TAX) . $tax_description;
    $this->tax_groups[$tax_index] = true;

    return $tax_index;
  }


  function get_cat_ids_array($products_id) {
    $cat_ids_array = array();
    $category_query = xtDBquery("SELECT p2c.categories_id
                                   FROM " . TABLE_PRODUCTS . " p
                                   JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                                        ON p.products_id = p2c.products_id
                                           AND p2c.categories_id != 0
                                   JOIN " . TABLE_CATEGORIES . " c
                                        ON c.categories_id = p2c.categories_id
                                           AND c.categories_status = '1'
                                  WHERE p.products_id = '" . (int)$products_id . "'
                                    AND p.products_status = '1'");
    if (xtc_db_num_rows($category_query, true)) {
      while ($category = xtc_db_fetch_array($category_query, true)) {
        $categories = array();
        xtc_get_parent_categories($categories, $category['categories_id']);
        $categories[] = $category['categories_id'];
        $categories = array_reverse($categories);
        foreach($categories as $cat_id) {
          if(!in_array($cat_id,$cat_ids_array)){
            $cat_ids_array[] = $cat_id;
          }
        }
      }
    }

    return $cat_ids_array;
  }


  function check() {
    if (!isset ($this->check)) {
      $check_query = xtc_db_query("SELECT configuration_value 
                                     FROM ".TABLE_CONFIGURATION." 
                                    WHERE configuration_key = 'MODULE_ORDER_TOTAL_COUPON_STATUS'");
      $this->check = xtc_db_num_rows($check_query);
    }

    return $this->check;
  }


  function keys() {
    return array (
      'MODULE_ORDER_TOTAL_COUPON_STATUS',
      'MODULE_ORDER_TOTAL_COUPON_SORT_ORDER',
      'MODULE_ORDER_TOTAL_COUPON_CALC_TAX',
      'MODULE_ORDER_TOTAL_COUPON_SPECIAL_PRICES'
    );
  }


  function install() {
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('', 'MODULE_ORDER_TOTAL_COUPON_STATUS', 'true', '6', '1','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('', 'MODULE_ORDER_TOTAL_COUPON_SORT_ORDER', '25', '6', '2', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function ,date_added) VALUES ('', 'MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING', 'false', '6', '5', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function ,date_added) VALUES ('', 'MODULE_ORDER_TOTAL_COUPON_INC_TAX', 'true', '6', '6','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function ,date_added) VALUES ('', 'MODULE_ORDER_TOTAL_COUPON_CALC_TAX', 'Standard', '6', '7','xtc_cfg_select_option(array(\'None\', \'Standard\'), ', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('', 'MODULE_ORDER_TOTAL_COUPON_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function ,date_added) VALUES ('', 'MODULE_ORDER_TOTAL_COUPON_SPECIAL_PRICES', 'false', '6', '5', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
  }


  function remove() {
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_ORDER_TOTAL_COUPON_%'");
  }
}
?>