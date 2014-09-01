<?php
/* -----------------------------------------------------------------------------------------
   $Id: ot_coupon.php 3661 2012-09-21 14:42:20Z web28 $

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

    BUGFIXES & MODIFIED rev1.3.6 by web28 - www.rpa-com.de
   1.3.6 fix $od_amount for customers with no tax and percent coupon
   1.3.5 fix xtc_db_fetch_array cache, new restrict_to_categories check
   1.3.4 fix tax deduction
   1.3.3 optimize code
   1.3.2 fix different currencies
   ---------------------------------------------------------------------------------------*/

class ot_coupon {
  var $title, $output;

///////////////////////////////////////////////////////////////////////

  function ot_coupon() {
    global $xtPrice;

    $this->code = 'ot_coupon';
    $this->header = MODULE_ORDER_TOTAL_COUPON_HEADER;
    $this->title = MODULE_ORDER_TOTAL_COUPON_TITLE;
    $this->description = MODULE_ORDER_TOTAL_COUPON_DESCRIPTION;
    $this->user_prompt = '';
    $this->enabled = MODULE_ORDER_TOTAL_COUPON_STATUS;
    $this->sort_order = MODULE_ORDER_TOTAL_COUPON_SORT_ORDER;
    $this->include_shipping = 'false'; //MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING;
    $this->include_tax = 'true'; //MODULE_ORDER_TOTAL_COUPON_INC_TAX;
    $this->calculate_tax = MODULE_ORDER_TOTAL_COUPON_CALC_TAX;
    $this->tax_class = MODULE_ORDER_TOTAL_COUPON_TAX_CLASS;
    $this->credit_class = true;
    $this->output = array ();
  }

///////////////////////////////////////////////////////////////////////


  function process() {
    global $order, $xtPrice;
    //BOF -web28- 2010-05-23 - BUGFIX - tax_deduction, $order->info['subtotal']
    $order_total = $this->get_order_total(); //Betrag,  der für die Kuponberechnung verwendet wird
    $od_amount = $this->calculate_credit($order_total);  //Kuponbetrag berechnen
    $this->deduction = $od_amount;

    if ($od_amount > 0) {
      //$od_amount = $xtPrice->xtcFormat($od_amount, false); //Rabatt runden ??? Rundungsfehler ???
      if ($this->calculate_tax != 'None') {
        $this->new_calculate_tax_deduction($od_amount,$order_total);
      }
      $order->info['total'] = $xtPrice->xtcFormat($order->info['total'] - $od_amount, false);
      $order->info['deduction'] = $od_amount;
      $order->info['subtotal'] = $order->info['subtotal'] - $od_amount;
      $this->output[] = array ('title' => $this->title.' '.$this->coupon_code.$this->tax_info.':',
                               'text' => '<strong><font color="#ff0000">'.$xtPrice->xtcFormat($od_amount*(-1), true).'</font></strong>',
                               'value' => $od_amount *(-1)); //2011-08-25 - web28 - fix negativ sign
    }
    //EOF -web28- 2010-05-23 - BUGFIX - tax_deduction, $order->info['subtotal']
  }

///////////////////////////////////////////////////////////////////////

  function selection_test() {
    return false;
  }

///////////////////////////////////////////////////////////////////////

  function pre_confirmation_check($order_total) {
    return $this->calculate_credit($order_total);
  }

///////////////////////////////////////////////////////////////////////

  function use_credit_amount() {
    $output_string = ''; //DokuMan - 2010-08-30 - set missing variable
    return $output_string;
  }

///////////////////////////////////////////////////////////////////////

  function credit_selection() {
    return false;
  }

///////////////////////////////////////////////////////////////////////

  function collect_posts() {
    global $xtPrice;
    if (isset($_POST['gv_redeem_code']) && $_POST['gv_redeem_code']) {

      // INFOS ÜBER KUPON AUSLESEN
      $coupon_query = xtc_db_query("select coupon_id, coupon_amount,
                                           coupon_type, coupon_minimum_order,
                                           coupon_start_date, coupon_expire_date,
                                           uses_per_coupon, uses_per_user,
                                           restrict_to_products, restrict_to_categories
                                      from ".TABLE_COUPONS."
                                     where coupon_code='".$_POST['gv_redeem_code']."'
                                       and coupon_active='Y'");
      $coupon_result = xtc_db_fetch_array($coupon_query);

      if ($coupon_result['coupon_type'] != 'G') {

        if (xtc_db_num_rows($coupon_query) == 0) {
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message='.urlencode(ERROR_NO_INVALID_REDEEM_COUPON), 'SSL'));
        }

        // ERROR : LAUFZEIT HAT NOCH NICHT BEGONNEN
        if ($coupon_result['coupon_start_date'] > date('Y-m-d H:i:s')) {
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_INVALID_STARTDATE_COUPON), 'SSL'));
        }

        // ERROR : LAUFZEIT BEENDET
        if ($coupon_result['coupon_expire_date'] < date('Y-m-d H:i:s')) {
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_INVALID_FINISDATE_COUPON), 'SSL'));
        }

        // ERROR : GESAMTES VERWENDUNGSLIMIT ÜBERSCHRITTEN
        $coupon_count = xtc_db_query("select coupon_id from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . $coupon_result['coupon_id'] . "'");
        if (xtc_db_num_rows($coupon_count) >= $coupon_result['uses_per_coupon'] && $coupon_result['uses_per_coupon'] > 0) {
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_INVALID_USES_COUPON . $coupon_result['uses_per_coupon'] . TIMES), 'SSL'));
        }

        // ERROR : VERWENDUNGSLIMIT FÜR EINZELNEN KUNDEN ÜBERSCHRITTEN
        $coupon_count_customer = xtc_db_query("select coupon_id from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . $coupon_result['coupon_id'] . "' and customer_id = '" . (int) $_SESSION['customer_id'] . "'");
        if (xtc_db_num_rows($coupon_count_customer) >= $coupon_result['uses_per_user'] && $coupon_result['uses_per_user'] > 0) {
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_INVALID_USES_USER_COUPON . $coupon_result['uses_per_user'] . TIMES), 'SSL'));
        }

        // ERROR : MINDESTBESTELLWERT NICHT ERREICHT //FIX - web28 - 2012-04-24 - calculate currencies
        if ($xtPrice->xtcCalculateCurr($coupon_result['coupon_minimum_order']) > $_SESSION['cart']->show_total()) {
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'info_message=' . urlencode(ERROR_MINIMUM_ORDER_COUPON_1 . ' ' . $xtPrice->xtcFormat($coupon_result['coupon_minimum_order'], true, 0, true) . ' ' . ERROR_MINIMUM_ORDER_COUPON_2), 'SSL'));
        }

      }
      if ($_POST['submit_redeem_coupon_x'] && !$_POST['gv_redeem_code'])
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message='.urlencode(ERROR_NO_REDEEM_CODE), 'SSL'));
      }
  }

///////////////////////////////////////////////////////////////////////
// RABATT BERECHNEN
///////////////////////////////////////////////////////////////////////

  function calculate_credit($amount) {
    global $order, $xtPrice, $tax_info_excl;

    $od_amount = 0;
    if (isset ($_SESSION['cc_id'])) {

      //web28 -2011-11-06 - FIX: only active coupon
      $coupon_query = xtc_db_query("select coupon_code
                                      from ".TABLE_COUPONS."
                                      where coupon_id = '".$_SESSION['cc_id']."'
                                      and coupon_active = 'Y'
                                   ");
      if (xtc_db_num_rows($coupon_query) != 0) {
        $coupon_result = xtc_db_fetch_array($coupon_query);

        // KUPON CODE
        $this->coupon_code = $coupon_result['coupon_code'];

        // INFOS ÜBER DEN KUPON AUSLESEN
        //web28 -2011-11-06 - FIX: only active coupon
        $coupon_get = xtc_db_query("select coupon_amount, coupon_minimum_order,
                                           restrict_to_products, restrict_to_categories,
                                           coupon_type
                                      from ".TABLE_COUPONS."
                                      where coupon_code = '".$coupon_result['coupon_code']."'
                                      and coupon_active = 'Y'
                                  ");

        $get_result = xtc_db_fetch_array($coupon_get);
        $c_deduct = $xtPrice->xtcCalculateCurr($get_result['coupon_amount']); //FIX - web28 - 2012-04-24 - calculate currencies

        // KUPON VERSANDKOSTENFREI
        if ($get_result['coupon_type'] == 'S') {
          //$c_deduct = $order->info['shipping_cost'];
          $c_deduct = $this->get_shipping_cost();
        }

        if ($get_result['coupon_type']=='S' && $get_result['coupon_amount'] > 0 ) {
          $c_deduct = $c_deduct + $xtPrice->xtcCalculateCurr($get_result['coupon_amount']); //FIX - web28 - 2012-04-24 - calculate currencies
          $flag_s = true;
        }

        //echo 'VK'. $c_deduct;

        if ($xtPrice->xtcCalculateCurr($get_result['coupon_minimum_order']) <= $this->get_order_total()) {

          if ($get_result['restrict_to_products'] || $get_result['restrict_to_categories']) {

            //BOF -web28- 2010-06-19 - FIX - new calculate coupon amount

            $pr_c = 0; //web28- 2010-05-21 - FIX - restrict  max coupon amount

            //allowed products
            if ($get_result['restrict_to_products']) {
              $pr_ids = explode(",", $get_result['restrict_to_products']);
              for ($i = 0, $n = sizeof($order->products); $i < $n; ++$i) {
                for ($ii = 0, $nn = count($pr_ids); $ii < $nn; $ii ++) {
                  if ($pr_ids[$ii] == xtc_get_prid($order->products[$i]['id'])) {
                    if ($get_result['coupon_type'] == 'P') {
                      $pr_c = $this->product_price($order->products[$i]['id']); //web28- 2010-07-29 - $order->products[$i]['id']  //Fred 2003-10-28, fix for the row above, otherwise the discount is calc based on price excl VAT!
                      $pod_amount = round($pr_c*10)/10*$c_deduct/100;
                      $od_amount = $od_amount + $pod_amount;

                    } else {
                      $od_amount = $c_deduct;
                      $pr_c += $this->product_price($order->products[$i]['id']); //web28- 2010-07-29 - FIX $order->products[$i]['id']  //web28- 2010-05-21 - FIX - restrict  max coupon amount
                    }
                  }
                }
              }
            }

            //allowed categories
            if ($get_result['restrict_to_categories']) {
              $cat_ids = explode(",", $get_result['restrict_to_categories']);
              for ($i = 0, $n = sizeof($order->products); $i < $n; ++$i) {
                //BOF - web28 - 2010-06-19 - test for product_id to prevent double counting
                if ($get_result['restrict_to_products']  && in_array(xtc_get_prid($order->products[$i]['id']) ,$pr_ids) ) {
                  $p_flag = true;
                } else $p_flag = false;
                //EOF - web28 - 2010-06-19 - test for product_id to prevent double counting

                //BOF - web28 - 2012-01-10 - new restrict_to_categories check
                $cat_path = xtc_get_product_path(xtc_get_prid($order->products[$i]['id']));
                $prod_cat_ids_array = explode("_", $cat_path);
                for ($ii = 0 , $nn = count($cat_ids); $ii < $nn ; $ii ++) {
                  if (in_array($cat_ids[$ii], $prod_cat_ids_array) && !$p_flag) {
                    if ($get_result['coupon_type'] == 'P') {
                      $pr_c = $this->product_price($order->products[$i]['id']);//web28- 2010-07-29 - FIX no xtc_get_prid
                      $pod_amount = round($pr_c*10)/10*$c_deduct/100;
                      $od_amount = $od_amount + $pod_amount;
                    } else {
                      $od_amount = $c_deduct;
                      $pr_c += $this->product_price($order->products[$i]['id']);  //web28- 2010-07-29 - FIX no xtc_get_prid  //web28- 2010-05-21 - FIX - restrict  max coupon amount
                    }
                  }
                }
                //EOF - web28 - 2012-01-10 - new restrict_to_categories check
              }
            }

            if ($get_result['coupon_type'] == 'F' && $od_amount > $pr_c )  {$od_amount = $pr_c;} //web28- 2010-05-21 - FIX - restrict  max coupon amount

            //EOF -web28- 2010-06-19 - FIX - new calculate coupon amount
          } else {
            if ($get_result['coupon_type'] != 'P') {
              $od_amount = $c_deduct;
            } else {
              $od_amount = $amount * $xtPrice->xtcCalculateCurr($get_result['coupon_amount']) / 100; //FIX - web28 - 2012-04-24 - calculate currencies

            }
          }

          //echo 'OD'.$od_amount;

          //BOF  - web28- 2010-06-19 - ADD no discount for special offers
          if (MODULE_ORDER_TOTAL_COUPON_SPECIAL_PRICES != 'true'){
            $pr_c = 0;
            for ($i = 0; $i < sizeof($order->products); $i ++) {
              $product_query = "select specials_new_products_price from ".TABLE_SPECIALS." where products_id = '".xtc_get_prid($order->products[$i]['id'])."' and status=1";
              $product_query = xtDBquery($product_query);
              $product = xtc_db_fetch_array($product_query, true);
              if($product['specials_new_products_price']) {
                if ($get_result['coupon_type'] == 'P') {
                  $pr_c = $this->product_price($order->products[$i]['id']);  //web28- 2010-07-29 - FIX no xtc_get_prid
                  $pod_amount = round($pr_c*10)/10*$c_deduct/100;
                  $od_amount -= $pod_amount;
                } else {
                  $pr_c += $this->product_price($order->products[$i]['id']);  //web28- 2010-07-29 - FIX no xtc_get_prid
                }
              }
            }
            if ($od_amount < 0) $od_amount = 0;
            if ($amount  <= $pr_c) $od_amount = 0;
          }
          //EOF  - web28- 2010-06-19 - ADD no discount for special offers

        }
      }

      if ($flag_s) {
        $amount += $this->get_shipping_cost(); //Wenn Versandkostenfrei: Versandkosten und Gutscheinwert addieren
      }

      // RABATT ÜBERSTEIGT DEN BESTELLWERT, DANN RABATT GLEICH BESTELLWERT
      if ($od_amount > $amount) {
        $od_amount = $amount;
      }
      //echo 'OD'.$od_amount;
    }

    //KORREKTUR wenn Kunde Nettopreise und Steuer in Rechnung: Couponwert mit Steuersatz prozentual korrigiert
    $this->tax_info = '';
    if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1 && $amount > 0 && $get_result['coupon_type'] != 'P') {
      $od_amount = $od_amount / (1 + $order->info['tax'] / $amount);
      $this->tax_info =  ' ('. trim(str_replace(array(' %s',','), array('',''),TAX_INFO_EXCL)) .')';
    }

    return $od_amount;
  }

///////////////////////////////////////////////////////////////////////
// STEUER NEU BERECHNEN
///////////////////////////////////////////////////////////////////////

  function new_calculate_tax_deduction($od_amount, $order_total) {
    global $order;

    //Wenn der Kupon ohne Steuer definiert wurde, muss die Bestellsumme korrigiert werden
    if ($this->include_tax == 'false'){
      $order_total = $order_total + $order->info['tax'];
    }
    //Gutscheinwert in % berechnen, vereinheitlicht die Berechnungen
    $od_amount_pro = $od_amount/$order_total * 100;

    reset($order->info['tax_groups']);
    $tod_amount = 0;
    //Steuer für jede Steuergruppe korrigieren
    while (list ($key, $value) = each($order->info['tax_groups'])) {
      //Steuer neu berechnen
      $t_flag = true;
      if ($t_flag) {
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] != '1') { //NETTO Preise
            $god_amount = $order->info['tax_groups'][$key] - $order->info['tax_groups'][$key] * $od_amount_pro / 100;
            $order->info['tax_groups'][$key] = $god_amount; //bei NETTO Preisen ersetzen
        } else { //BRUTTO Preise
          $god_amount = $order->info['tax_groups'][$key] * $od_amount_pro / 100;
          $order->info['tax_groups'][$key] = $order->info['tax_groups'][$key] - $god_amount; //bei BRUTTO Preisen abziehen
        }
        //echo $god_amount . '<br>';
        $tod_amount += $god_amount; //hier wird die Steuer aufaddiert
      }
    }
    //Gesamtsteuer neu berechnen
    $order->info['tax'] -= $tod_amount; //bei BRUTTO Preisen abziehen
    if ($_SESSION['customers_status']['customers_status_show_price_tax'] != '1') {
      $order->info['tax'] = $tod_amount; //bei NETTO Preisen ersetzen
    }

  }

///////////////////////////////////////////////////////////////////////
// VERSANDKOSTEN BERECHNEN MIT STEUER
///////////////////////////////////////////////////////////////////////
  function get_shipping_cost() {
    global $order, $xtPrice;

    $shipping_module = substr($_SESSION['shipping']['id'], 0, strpos($_SESSION['shipping']['id'], '_'));
    $shipping_cost = $order->info['shipping_cost'];

    //BRUTTO PREISE - Steuer bei Versandkosten hinzufügen
    if ($_SESSION['customers_status']['customers_status_show_price_tax'] == '1') {
      $shipping_tax_rate = xtc_get_tax_rate($GLOBALS[$shipping_module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
      $shipping_tax = $order->info['shipping_cost'] * ($shipping_tax_rate / 100 +1) - $order->info['shipping_cost'];
      $shipping_cost = $order->info['shipping_cost'] + $shipping_tax;
      $shipping_cost = $xtPrice->xtcFormat($shipping_cost, false); //RUNDEN
    }

    return $shipping_cost;
  }

///////////////////////////////////////////////////////////////////////

  function update_credit_account($i) {
    return false;
  }
///////////////////////////////////////////////////////////////////////

  function apply_credit() {
    global $insert_id, $REMOTE_ADDR;

    if ($this->deduction != 0) {
      xtc_db_query("insert into ".TABLE_COUPON_REDEEM_TRACK." (coupon_id, redeem_date, redeem_ip, customer_id, order_id) values ('".$_SESSION['cc_id']."', now(), '".$REMOTE_ADDR."', '".$_SESSION['customer_id']."', '".$insert_id."')");
    }
    unset ($_SESSION['cc_id']);
  }

///////////////////////////////////////////////////////////////////////
// GESAMT BESTELLSUMME BERECHNEN
///////////////////////////////////////////////////////////////////////

  function get_order_total() {
    global $order, $xtPrice;

    $order_total = $order->info['total'];

    // Check if gift voucher is in cart and adjust total
    $products = $_SESSION['cart']->get_products();
    for ($i = 0; $i < sizeof($products); $i ++) {
      $t_prid = xtc_get_prid($products[$i]['id']);
      $gv_query = xtc_db_query("select products_price, products_tax_class_id, products_model from ".TABLE_PRODUCTS." where products_id = '".$t_prid."'");
      $gv_result = xtc_db_fetch_array($gv_query);
      if (preg_match('/^GIFT/', addslashes($gv_result['products_model']))) { // Hetfield - 2009-08-19 - replaced depricated function ereg with preg_match to be ready for PHP >= 5.3
        $qty = $_SESSION['cart']->get_quantity($t_prid);
        $products_tax = $xtPrice->TAX[$gv_result['products_tax_class_id']];
        if ($this->include_tax == 'false') {
          $gv_amount = $gv_result['products_price'] * $qty;
        } else {
          $gv_amount = ($gv_result['products_price'] + $xtPrice->calcTax($gv_result['products_price'], $products_tax)) * $qty;
        }
        $order_total = $order_total - $gv_amount;
      }
    }
    if ($this->include_tax == 'false')
      $order_total = $order_total - $order->info['tax'];

    if ($this->include_shipping == 'false') {
      $order_total = $order_total - $order->info['shipping_cost'];
    }

    return $order_total;
  }

///////////////////////////////////////////////////////////////////////
// PRODUKTPREIS BERECHNEN - INKL ATTRIBUTEPREISE
///////////////////////////////////////////////////////////////////////

  function get_product_price($product_id) { //wird nur bei Einschränkung Produkte/Kategorie benutzt
    global $order,$xtPrice;
    $products_id = xtc_get_prid($product_id);
    // products price
    //BOF - 2010-01-19 - Dokuman - ot_coupon Bugfixes
    //$qty = $_SESSION['cart']->contents[$products_id]['qty'];
    $qty = $_SESSION['cart']->contents[$product_id]['qty'];
    //EOF - 2010-01-19 - Dokuman - ot_coupon Bugfixes

    $total_price = 0;

    $product_query = xtc_db_query("select products_id, products_model, products_price, products_tax_class_id, products_weight from ".TABLE_PRODUCTS." where products_id='".$products_id."'");
    if ($product = xtc_db_fetch_array($product_query)) {

      $prid = $product['products_id'];

      if ($this->include_tax == 'true') {
        $total_price += $qty * $xtPrice->xtcGetPrice($product['products_id'], $format = false, 1, $product['products_tax_class_id'], $product['products_price'], 1);
        $_SESSION['total_price']=$total_price;
      } else {
        $total_price += $qty * $xtPrice->xtcGetPrice($product['products_id'], $format = false, 1, 0, $product['products_price'], 1);
      }

      // attributes price
      // BOF - 2011-03-16 - web28 - use xtPrice function
      $attribute_price = 0;
      if (isset ($_SESSION['cart']->contents[$product_id]['attributes'])) {
        reset($_SESSION['cart']->contents[$product_id]['attributes']);
        while (list ($option, $value) = each($_SESSION['cart']->contents[$product_id]['attributes'])) {
          $values = $xtPrice->xtcGetOptionPrice($product['products_id'], $option, $value);
          $attribute_price += $qty * $values['price'];
        }
      }
      $total_price += $attribute_price;
      // EOF - 2011-03-16 - web28 - use xtPrice function

    }
    //if ($this->include_shipping == 'true') {
      //$total_price += $order->info['shipping_cost'];
    //}

    return $total_price;
  }

///////////////////////////////////////////////////////////////////////

  function product_price($product_id) {
    $total_price = $this->get_product_price($product_id);
    return $total_price;
  }

///////////////////////////////////////////////////////////////////////

  function check() {
    if (!isset ($this->check)) {
      $check_query = xtc_db_query("select configuration_value from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_ORDER_TOTAL_COUPON_STATUS'");
      $this->check = xtc_db_num_rows($check_query);
    }

    return $this->check;
  }

///////////////////////////////////////////////////////////////////////
  function keys() {
    return array ('MODULE_ORDER_TOTAL_COUPON_STATUS',
                  'MODULE_ORDER_TOTAL_COUPON_SORT_ORDER',
                  //'MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING',
                  //'MODULE_ORDER_TOTAL_COUPON_INC_TAX',
                  'MODULE_ORDER_TOTAL_COUPON_CALC_TAX',
                  //'MODULE_ORDER_TOTAL_COUPON_TAX_CLASS' // web28- 2010-05-23 - FIX - unnecessary  COUPON_TAX_CLASS
                  'MODULE_ORDER_TOTAL_COUPON_SPECIAL_PRICES' // web28- 2010-06-19 - ADD no discount for special offers
                  );
  }

///////////////////////////////////////////////////////////////////////

  function install() {
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_ORDER_TOTAL_COUPON_STATUS', 'true', '6', '1','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('', 'MODULE_ORDER_TOTAL_COUPON_SORT_ORDER', '25', '6', '2', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function ,date_added) values ('', 'MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING', 'false', '6', '5', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function ,date_added) values ('', 'MODULE_ORDER_TOTAL_COUPON_INC_TAX', 'true', '6', '6','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    //BOF -web28- 2010-05-23 - FIX - unnecessary  Credit Note
    //xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function ,date_added) values ('', 'MODULE_ORDER_TOTAL_COUPON_CALC_TAX', 'Standard', '6', '7','xtc_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'), ', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function ,date_added) values ('', 'MODULE_ORDER_TOTAL_COUPON_CALC_TAX', 'Standard', '6', '7','xtc_cfg_select_option(array(\'None\', \'Standard\'), ', now())");
    //EOF -web28- 2010-05-23 - FIX - unnecessary  Credit Note
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('', 'MODULE_ORDER_TOTAL_COUPON_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
    //BOF  - web28- 2010-06-19 - ADD no discount for special offers
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, set_function ,date_added) values ('', 'MODULE_ORDER_TOTAL_COUPON_SPECIAL_PRICES', 'false', '6', '5', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    //EOF  - web28- 2010-06-19 - ADD no discount for special offers
  }
///////////////////////////////////////////////////////////////////////

  function remove() {
    xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE 'MODULE_ORDER_TOTAL_COUPON_%'");
  }
}
?>