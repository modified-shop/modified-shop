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

///////////////////////////////////////////////////////////////////////

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
      $this->include_shipping = 'false'; //MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING;
      $this->include_tax = 'true'; //MODULE_ORDER_TOTAL_COUPON_INC_TAX;
      $this->calculate_tax = MODULE_ORDER_TOTAL_COUPON_CALC_TAX;
      $this->tax_class = MODULE_ORDER_TOTAL_COUPON_TAX_CLASS;
    }
    
    $this->credit_class = true;
    $this->output = array ();
    $this->products_price = array();
    $this->products_tax_description = array();
    $this->tax_groups = array();
    $this->price_total_by_tax_groups  = array();
  }

///////////////////////////////////////////////////////////////////////


  function process() {
    global $order, $xtPrice;
    $order_total = $this->get_order_total(); //Betrag, der für die Kuponberechnung verwendet wird
    $od_amount = $this->calculate_credit($order_total);  //Kuponbetrag berechnen
    
    $this->deduction = $od_amount;

    if ($od_amount > 0) {
      if ($this->calculate_tax != 'None') {
        $this->new_calculate_tax_deduction($od_amount, $order_total);
      }
      $order->info['total'] = $xtPrice->xtcFormat($order->info['total'] - $od_amount, false);
      $order->info['deduction'] = $od_amount;
      $order->info['subtotal'] = $order->info['subtotal'] - $od_amount;
      $this->output[] = array (
          'title' => $this->title.' '.$this->coupon_code.$this->tax_info.':',
          'text'  => '<span class="color_ot_total"><b>'.$xtPrice->xtcFormat($od_amount*(-1), true).'</b></span>',
          'value' => $od_amount *(-1) // web28 - 2011-08-25 - fix negativ sign
        );
    }
  }

///////////////////////////////////////////////////////////////////////

  function selection_test() {
    return false;
  }

///////////////////////////////////////////////////////////////////////

  function pre_confirmation_check($order_total) {
    $this->get_order_total();
    return $this->calculate_credit($order_total);
  }

///////////////////////////////////////////////////////////////////////

  function use_credit_amount() {
    $output_string = '';
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
      $coupon_query = xtc_db_query("select *
                                      from ".TABLE_COUPONS."
                                     where coupon_code='".$_POST['gv_redeem_code']."'
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

///////////////////////////////////////////////////////////////////////
// RABATT BERECHNEN
///////////////////////////////////////////////////////////////////////

  function calculate_credit($amount) {
    global $order, $xtPrice, $tax_info_excl;

    $od_amount = 0;
    if (isset ($_SESSION['cc_id'])) {

      $coupon_query = xtc_db_query("SELECT *
                                      FROM ".TABLE_COUPONS."
                                     WHERE coupon_id = '".$_SESSION['cc_id']."'
                                       AND coupon_active = 'Y'");
      if (xtc_db_num_rows($coupon_query) != 0) {
        $coupon_array = xtc_db_fetch_array($coupon_query);

        // ERROR_INVALID_MINIMUM_ORDER_COUPON
        $cc_min_amount = $xtPrice->xtcCalculateCurr($coupon_array['coupon_minimum_order']);
        if ( $cc_min_amount > $amount && isset($_SESSION['cc_id'])) {
          unset($_SESSION['cc_id']);
          $_SESSION['error_invalid_coupon_minimum_order'] = sprintf(ERROR_INVALID_MINIMUM_ORDER_COUPON,$xtPrice->xtcFormat($cc_min_amount,true));            
          return 0;
        }
        
        // KUPON CODE
        $this->coupon_code = $coupon_array['coupon_code'];

        $c_deduct = $xtPrice->xtcCalculateCurr($coupon_array['coupon_amount']); //FIX - web28 - 2012-04-24 - calculate currencies

        // KUPON VERSANDKOSTENFREI
        if ($coupon_array['coupon_type'] == 'S') {
          //$c_deduct = $order->info['shipping_cost'];
          $c_deduct = $this->get_shipping_cost();
        }
        
        $flag_s = false;
        if ($coupon_array['coupon_type']=='S' && $coupon_array['coupon_amount'] > 0 ) {
          $c_deduct = $c_deduct + $xtPrice->xtcCalculateCurr($coupon_array['coupon_amount']); //FIX - web28 - 2012-04-24 - calculate currencies
          $flag_s = true;
        }
        
        $flag_t = false;
        if ($coupon_array['coupon_type'] == 'T') {
          $coupon_array['coupon_type'] = 'P'; //Berechung auf Prozent setzen
          $flag_t = true;
        }

        //echo 'VK'. $c_deduct;
        if ($coupon_array['restrict_to_products'] || $coupon_array['restrict_to_categories']) {

          //BOF -web28- 2010-06-19 - FIX - new calculate coupon amount

          $pr_c = 0; //web28- 2010-05-21 - FIX - restrict  max coupon amount

          //allowed products
          $coupon_array['restrict_to_products'] = preg_replace("'[\r\n\s]+'", '', $coupon_array['restrict_to_products']);
          if (trim($coupon_array['restrict_to_products']) != '') {
            $pr_ids = explode(",", $coupon_array['restrict_to_products']);
            $pr_ids = array_unique($pr_ids);
            for ($i = 0, $n = sizeof($order->products); $i < $n; ++$i) {
              for ($ii = 0, $nn = count($pr_ids); $ii < $nn; $ii ++) {
                if ($pr_ids[$ii] == xtc_get_prid($order->products[$i]['id'])) {
                  if ($coupon_array['coupon_type'] == 'P') {
                    $pr_c = $this->product_price($order->products[$i]['id']); //web28- 2010-07-29 - $order->products[$i]['id']
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
          $_c_products_ids = array();
          $coupon_array['restrict_to_categories'] = preg_replace("'[\r\n\s]+'", '', $coupon_array['restrict_to_categories']);
          if (trim($coupon_array['restrict_to_categories']) != '') {
            $cat_ids = explode(",", $coupon_array['restrict_to_categories']);
            $cat_ids = array_unique($cat_ids);
            for ($i = 0, $n = sizeof($order->products); $i < $n; ++$i) {
              // web28 - 2010-06-19 - test for product_id to prevent double counting
              $p_flag = $coupon_array['restrict_to_products'] && in_array(xtc_get_prid($order->products[$i]['id']) ,$pr_ids) ? true : false;
              
              //BOF - web28 - 2012-01-10 - new restrict_to_categories check
              //$cat_path = xtc_get_product_path(xtc_get_prid($order->products[$i]['id']));
              //$prod_cat_ids_array = explode("_", $cat_path);
              $prod_cat_ids_array = $this->get_cat_ids_array(xtc_get_prid($order->products[$i]['id']));
              for ($ii = 0 , $nn = count($cat_ids); $ii < $nn ; $ii ++) {
                if (in_array($cat_ids[$ii], $prod_cat_ids_array) && !$p_flag && !in_array(xtc_get_prid($order->products[$i]['id']),$_c_products_ids)) {
                  $_c_products_ids[] = xtc_get_prid($order->products[$i]['id']);//prevent double counting
                  if ($coupon_array['coupon_type'] == 'P') {
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

          if ($coupon_array['coupon_type'] == 'F' && $od_amount > $pr_c )  {$od_amount = $pr_c;} //web28- 2010-05-21 - FIX - restrict  max coupon amount

          //EOF -web28- 2010-06-19 - FIX - new calculate coupon amount
        } else {
          if ($coupon_array['coupon_type'] != 'P') {
            $od_amount = $c_deduct;
          } else {
            $od_amount = $amount * $coupon_array['coupon_amount'] / 100; //Calculation of percentage
          }
        }

        //echo 'OD'.$od_amount;

        //BOF  - web28- 2010-06-19 - ADD no discount for special offers
        if (MODULE_ORDER_TOTAL_COUPON_SPECIAL_PRICES != 'true'
            && (!isset($_SESSION['customers_status']['customers_status_specials'])
                || $_SESSION['customers_status']['customers_status_specials'] == '1'
                )
            )
        {
          $pr_c = 0;
          for ($i = 0; $i < sizeof($order->products); $i ++) {
            $product_query = "SELECT specials_new_products_price 
                                FROM ".TABLE_SPECIALS." 
                               WHERE products_id = '".xtc_get_prid($order->products[$i]['id'])."' 
                                     ".SPECIALS_CONDITIONS;
            $product_query = xtDBquery($product_query);
            $product = xtc_db_fetch_array($product_query, true);
            if($product['specials_new_products_price']) {
              if ($coupon_array['coupon_type'] == 'P') {
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

      if ($flag_t) {
        $od_amount += $this->get_shipping_cost();
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
    /*
    if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1 && $amount > 0 && $get_result['coupon_type'] != 'P') {
      $od_amount = $od_amount / (1 + $order->info['tax'] / $amount);
      $this->tax_info =  ' ('. trim(str_replace(array(' %s',','), array('',''),TAX_INFO_EXCL)) .')';
    }
    */

    return $od_amount;
  }

///////////////////////////////////////////////////////////////////////
// STEUER NEU BERECHNEN
///////////////////////////////////////////////////////////////////////

  function new_calculate_tax_deduction($od_amount, $order_total) {
    global $order;

    //Wenn der Kupon ohne Steuer definiert wurde, muss die Bestellsumme korrigiert werden
    if ($this->include_tax == 'false'){
      //$order_total = $order_total + $order->info['tax'];
    }
    
    //Einschränkungen
    $restriction = isset($this->tax_groups) && count($this->tax_groups) ? true : false;
    //echo '<pre>'.print_r($this->tax_groups,1).'</pre>';
    
    //Gutscheinwert in % berechnen, vereinheitlicht die Berechnungen
    $od_amount_pro = $od_amount/$order_total * 100;

    reset($order->info['tax_groups']);
    $tod_amount = 0;
    
    //Steuer für jede Steuergruppe korrigieren
    foreach ($order->info['tax_groups'] as $key => $value) {
      // Bei Einschränkungen Gutscheinwert in % neu berechnen,  vereinheitlicht die Berechnungen
      $od_amount_pro = $restriction ? $od_amount/$this->price_total_by_tax_groups[$key] * 100 : $od_amount_pro;
      $t_flag = true;
      //Steuer neu berechnen
      if ($t_flag && (!$restriction || $restriction && isset($this->tax_groups[$key]))) {
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] != '1') { //NETTO Preise
          if ($restriction) {
            $od_amount_diff = $this->price_total_by_tax_groups[$key] * $od_amount_pro / 100;
            $god_amount = ($od_amount_diff * ((100 + $this->price_total_by_tax_rate[$key]) / 100)) - $od_amount_diff;
            $god_amount = $order->info['tax_groups'][$key] - $god_amount;
          } else {
            $god_amount = $order->info['tax_groups'][$key] - $order->info['tax_groups'][$key] * $od_amount_pro / 100;
          }
          $order->info['tax_groups'][$key] = $god_amount; //bei NETTO Preisen ersetzen
        } else { //BRUTTO Preise
          if ($restriction) {
            $od_amount_diff = $this->price_total_by_tax_groups[$key] * $od_amount_pro / 100;
            $god_amount = $od_amount_diff - ($od_amount_diff / ((100 + $this->price_total_by_tax_rate[$key]) / 100));
          } else {
            $god_amount = $order->info['tax_groups'][$key] * $od_amount_pro / 100;
          }
          $order->info['tax_groups'][$key] -= $god_amount; //bei BRUTTO Preisen abziehen
        }
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
    
    if ($shipping_cost > 0) {
      //Steuergruppe feststellen und setzen
      $shipping_tax_class = constant('MODULE_SHIPPING_'.strtoupper($shipping_module).'_TAX_CLASS');
      $shipping_tax_rate_description = xtc_get_tax_description($shipping_tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
      $tax_index = $this->set_tax_group_index($shipping_tax_rate_description);

      //BRUTTO PREISE - Steuer bei Versandkosten hinzufügen
      if ($_SESSION['customers_status']['customers_status_show_price_tax'] == '1') {
        $shipping_tax_rate = xtc_get_tax_rate($shipping_tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
        $shipping_tax = $order->info['shipping_cost'] * ($shipping_tax_rate / 100 +1) - $order->info['shipping_cost'];
        $shipping_cost = $order->info['shipping_cost'] + $shipping_tax;
        $shipping_cost = $xtPrice->xtcFormat($shipping_cost, false); //RUNDEN
      }
    }
    return $shipping_cost;
  }

///////////////////////////////////////////////////////////////////////

  function update_credit_account($i) {
    return false;
  }
///////////////////////////////////////////////////////////////////////

  function apply_credit() {
    global $insert_id;

    if ($this->deduction != 0) {
      $sql_data_array = array(
         'coupon_id' => $_SESSION['cc_id'], 
         'redeem_date' => 'now()',  
         'redeem_ip' => ((isset($_SESSION['tracking']['ip'])) ? xtc_db_prepare_input($_SESSION['tracking']['ip']) : ''),
         'customer_id' => $_SESSION['customer_id'],  
         'order_id' => $insert_id 
      );
      xtc_db_perform(TABLE_COUPON_REDEEM_TRACK, $sql_data_array);
    }
    unset ($_SESSION['cc_id']);
  }

///////////////////////////////////////////////////////////////////////
// GESAMT BESTELLSUMME BERECHNEN
///////////////////////////////////////////////////////////////////////

  function get_order_total() {
    global $order;
    
    $order_total = $_SESSION['cart']->show_total();
    $this->products_price = array();
    $this->products_tax_description = array();
    $this->products_tax_rate = array();
    
    // Check if gift voucher is in cart and adjust total
    $products = $order->products; //use order objekt
    for ($i = 0; $i < sizeof($products); $i ++) {
      //create products_prices with id index for function product_price()
      $product_id = $products[$i]['id'];
      $products_price = $products[$i]['price'] * $products[$i]['qty'];
      $this->products_price["$product_id"] = $products_price;
      $this->products_tax_description["$product_id"] = $products[$i]['tax_description'];
      $this->products_tax_rate["$product_id"] = xtc_get_tax_rate($products[$i]['tax_class_id'], $order->delivery['country']['id'], $order->delivery['zone_id']);

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

///////////////////////////////////////////////////////////////////////

  function product_price($product_id) {
    $products_price = isset($this->products_price["$product_id"]) ? $this->products_price["$product_id"] : 0;
    if (isset($this->products_tax_description["$product_id"])) {
        $tax_index = $this->set_tax_group_index($this->products_tax_description["$product_id"]);
    }
    $this->price_total_by_tax_rate[$tax_index] = $this->products_tax_rate["$product_id"];
    $this->price_total_by_tax_groups[$tax_index] += $products_price;
    return $products_price;
  }

///////////////////////////////////////////////////////////////////////

  function set_tax_group_index($tax_description) {
      $tax_index = ($_SESSION['customers_status']['customers_status_show_price_tax'] == '1' ? TAX_ADD_TAX : TAX_NO_TAX) . $tax_description;
      $this->tax_groups[$tax_index] = true;
      return $tax_index;
      //echo $tax_index.'<br>';
  }

///////////////////////////////////////////////////////////////////////
  
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
      //echo '<pre>'.print_r($cat_ids_array,1).'</pre>';
    }
    return $cat_ids_array;
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