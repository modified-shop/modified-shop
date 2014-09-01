<?php
/* -----------------------------------------------------------
   $Id: ot_payment.php 3453 2012-08-21 14:11:56Z franky-n-xtcm $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------

   based on:
   - Andreas Zimmermann / IT eSolutions http://www.it-esolutions.de
     Copyright (c) 2004 IT eSolutions
   - v. 1.7 (c) by rpa-com.de
     Add: Anzeige bei der Zahlungsauswahl JA/NEIN
          Anzeigeart bei Zahlungsauswahl  STANDARD/PREIS
   - v. 1.6 (c) by rpa-com.de
     Fix: falsche Steuerberechnung
   - ot_payment.php
     Estelco - Ebusiness & more http://www.estelco.de
     Copyright (c) 2007 Estelco

   -----------------------------------------------------------
         Released under the GNU General Public License
   -----------------------------------------------------------*/

class ot_payment
{
    var $title, $output;

    function ot_payment()
    {
        $this->code = 'ot_payment';
        $this->num = 3;
        $this->title = MODULE_ORDER_TOTAL_PAYMENT_TITLE;
        $this->description = MODULE_ORDER_TOTAL_PAYMENT_DESCRIPTION;
        $this->enabled = MODULE_ORDER_TOTAL_PAYMENT_STATUS=='true' ? true : false;
        $this->sort_order = MODULE_ORDER_TOTAL_PAYMENT_SORT_ORDER;
        $this->include_shipping = MODULE_ORDER_TOTAL_PAYMENT_INC_SHIPPING;
        $this->include_tax = MODULE_ORDER_TOTAL_PAYMENT_INC_TAX;
        $this->calculate_tax = MODULE_ORDER_TOTAL_PAYMENT_CALC_TAX;
        // is not used
        // $this->howto_calc = MODULE_ORDER_TOTAL_PAYMENT_HOWTO_CALC;
        $this->tax_class = MODULE_ORDER_TOTAL_PAYMENT_TAX_CLASS;
        $this->output = array();
        $this->amount = 0;
        $this->original_total = 0;
        $this->discount = array();
        $this->amounts = array();
        $this->show_in_checkout_payment = MODULE_ORDER_TOTAL_PAYMENT_SHOW_IN_CHECKOUT_PAYMENT=='true' ? true : false;
        $this->show_type = MODULE_ORDER_TOTAL_PAYMENT_SHOW_TYPE;
        // Rabattfelder
        if ($this->enabled) {
            for ($k=1; $k<=$this->num; $k++) {
                $this->percentage[$k] = constant('MODULE_ORDER_TOTAL_PAYMENT_PERCENTAGE' . $k);
                $this->payment[$k] = constant('MODULE_ORDER_TOTAL_PAYMENT_TYPE' . $k);
            }
        }
    }

    function process()
    {
        global $order, $xtPrice;

        $allowed_zones = explode(',', MODULE_ORDER_TOTAL_PAYMENT_ALLOWED);

        if ($this->enabled && (in_array($_SESSION['delivery_zone'], $allowed_zones) == true || MODULE_ORDER_TOTAL_PAYMENT_ALLOWED == '')) {
            $this->xtc_order_total();
            $this->calculate_credit();
            if (isset($this->discount['sum']) && $this->discount['sum']!=0) {
                for ($i=1; $i<=$this->num; $i++) {
                    if (isset($this->discount['amount' . $i]) && $this->discount['amount' . $i]!=0) {
                        $this->output[] = array('title' =>
                        ($this->discount['pro' . $i] != 0.0 ? number_format(abs($this->discount['pro' . $i]), 2, $xtPrice->currencies[$_SESSION['currency']]['decimal_point'], '') . '% ' .
                        ($this->discount['fee' . $i] != 0 ? ($this->discount['pro' . $i] != 0.0 ? ' +' : '') . $xtPrice->xtcFormat(abs($this->discount['fee' . $i]), true) . ' ':'') : '') .
                        ($this->discount['amount' . $i] < 0 ? MODULE_ORDER_TOTAL_PAYMENT_DISCOUNT:MODULE_ORDER_TOTAL_PAYMENT_FEE) . ':',
                        'text' => $this->discount['amount' . $i] < 0 ? '<span style="color: red;">' . $xtPrice->xtcFormat($this->discount['amount' . $i], true).'</span>':$xtPrice->xtcFormat($this->discount['amount' . $i], true),
                        'value' => $this->discount['amount' . $i]);
                        $order->info['subtotal'] += $this->discount['amount' . $i];
                        $order->info['total'] += $this->discount['amount' . $i];
                    }
                }
            }
        }
    }

    function calculate_credit($payment = '')
    {
        global $order;
        $discount = array();
        $values = array();

        if ($payment == '' && isset($_SESSION['payment'])) {
            $payment = $_SESSION['payment'];
        }

        if ($this->include_shipping == 'false') {
            $module = substr($_SESSION['shipping']['id'], 0, strpos($_SESSION['shipping']['id'], '_'));
            $shipping_tax = 0;
            if (is_object($module) && array_key_exists($module, $GLOBALS)) {
              $shipping_tax = xtc_get_tax_rate($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
            }
            if ($_SESSION['customers_status']['customers_status_show_price_tax'] && !$_SESSION['customers_status']['customers_status_add_tax_ot']) {
                //$tod_shipping = $order->info['shipping_cost'] / (100 + $shipping_tax) * $shipping_tax;
                $tod_shipping = $order->info['shipping_cost'] * ($shipping_tax / 100 + 1) - $order->info['shipping_cost'];  //web28 - Fix shipping tax
            } else {
                $tod_shipping = $order->info['shipping_cost'] / 100 * $shipping_tax;
            }
        } else {
            $tod_shipping = 0;
        }

        // Fix shipping tax (web28)
        if ($this->sort_order < MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER) {
          $tod_shipping = 0;
        }

        for ($j=1; $j<=$this->num; $j++) {
            $do = false;
            if (strpos($this->percentage[$j], "|") !== false) {
                $strings = explode('|', $this->percentage[$j]);
                $allowed_zones = explode(',', $strings[0]);
                if (!in_array($_SESSION['delivery_zone'], $allowed_zones) == true && $strings[0] != '00') {
                    continue;
                }
                $string = $strings[1];
            } else {
                $string = $this->percentage[$j];
            }
            $discount_table = (preg_split("/[:,]/" , $string));
            //print_r($discount_table);
            for ($i=0; $i<sizeof($discount_table); $i+=2) {
                if ($this->amount >= $discount_table[$i]) {
                    $values[$j]['minimum'] = $discount_table[$i];
                    $fees = preg_split('/&/', $discount_table[$i+1]);
                    $values[$j]['percent'] = (isset($fees[0])?$fees[0]:'');
                    $values[$j]['fee'] = ((isset($fees[1]) && $fees[1]!='')?$fees[1]:0);
                } else {
                    break;
                }
            }

            if (isset($values[$j]['minimum']) && $this->amount >= $values[$j]['minimum']) {
                $od_amount = 0;
                $tod_amount = 0;
                $table = preg_split("/[,]/" , $this->payment[$j]);
                for ($i = 0; $i < count($table); $i++) {
                    if ($payment == $table[$i]) $do = true;
                }
                if ($do) {
                    // Calculate tax reduction if necessary
                    if($this->calculate_tax == 'true') {
                        // Calculate tax group deductions
                        reset($order->info['tax_groups']);
                        while (list($key, $value) = each($order->info['tax_groups'])) {
                            if (strpos($key, $shipping_tax . '%')) {
                                $god_amount = $this->get_discount(($value - $tod_shipping), $values[$j]['percent']);
                            } else {
                                $god_amount = $this->get_discount($value, $values[$j]['percent']);
                            }
                            if ($values[$j]['fee'] != 0 && count($this->amounts) > 0) {
                                foreach($this->amounts as $key2=>$value2) {
                                    if (strpos($key, $key2 . '%')) {
                                        $god_amount += $values[$j]['fee'] * $value2 / $this->amounts['total'] * $key2 / 100 / (100 + $key2) * 100;
                                    }
                                }
                            }
                            $order->info['tax_groups'][$key] -= $god_amount;
                            $tod_amount += $god_amount; //hier wird die Steuer aufaddiert
                        }
                        // Calculate main tax reduction
                        //$tod_amount = $this->get_discount(($order->info['tax'] - $tod_shipping), $values[$j]['percent']); // FIX web28 - falsche Steuerkorrektur
                        $order->info['tax'] -= $tod_amount;
                    }
                    $values[$j]['discount'] = $this->get_discount($this->amount, $values[$j]['percent']) + $values[$j]['fee'];
                }
            }
            (!isset($this->discount['sum'])?$this->discount['sum']='':'');
            (!isset($this->discount['amount' . $j])?$this->discount['amount' . $j]='':'');
            (!isset($this->discount['pro' . $j])?$this->discount['pro' . $j]='':'');
            (!isset($this->discount['fee' . $j])?$this->discount['fee' . $j]='':'');
            
            $this->discount['sum'] -= (isset($values[$j]['discount'])?$values[$j]['discount']:'');
            $this->discount['amount' . $j] = (isset($values[$j]['discount'])?-$values[$j]['discount']:'');
            $this->discount['pro' . $j] = (isset($values[$j]['percent'])?$values[$j]['percent']:'');
            $this->discount['fee' . $j] = (isset($values[$j]['fee'])?$values[$j]['fee']:'');
            if ($do && MODULE_ORDER_TOTAL_PAYMENT_BREAK != 'true') break;
        }
    }

    function xtc_order_total()
    {
        global $order;
        
        $this->amounts['total'] = 0;

        $order_total = $order->info['total'];

        // Check if gift voucher is in cart and adjust total
        $products = $_SESSION['cart']->get_products();
        for ($i=0; $i<sizeof($products); $i++) {
            $t_prid = xtc_get_prid($products[$i]['id']);
            $gv_query = xtc_db_query("select products_price, products_tax_class_id, products_model from " . TABLE_PRODUCTS . " where products_id = '" . $t_prid . "'");
            $gv_result = xtc_db_fetch_array($gv_query);
            $qty = $_SESSION['cart']->get_quantity($products[$i]['id']);
            $products_tax = xtc_get_tax_rate($gv_result['products_tax_class_id']);
            if (!isset($this->amounts[(string)$products_tax])) {
              $this->amounts[(string)$products_tax] = 0;
            }
            if (substr($gv_result['products_model'], 0, 4) == 'GIFT') {
                if ($this->include_tax =='false') {
                    $gv_amount = $gv_result['products_price'] * $qty;
                } else {
                    $gv_amount = ($gv_result['products_price'] + xtc_calculate_tax($gv_result['products_price'],$products_tax)) * $qty;
                }
                $order_total -= $gv_amount;
            } else {
                $this->amounts[(string)$products_tax] += $gv_result['products_price'] * (int)$qty;
                $this->amounts['total'] += $gv_result['products_price'] * $qty;
            }
        }
        if ($this->include_shipping == 'false') $order_total -= $order->info['shipping_cost'];
        if ($this->include_tax == 'false') $order_total -= $order->info['tax'];

        $this->amount = $order_total;
    }

    function get_percent($payment, $type = 'percent')
    {
        global $order, $xtPrice;
        $string = '';
        $allowed_zones = explode(',', MODULE_ORDER_TOTAL_PAYMENT_ALLOWED);

        if ($this->enabled && (in_array($_SESSION['delivery_zone'], $allowed_zones) == true || MODULE_ORDER_TOTAL_PAYMENT_ALLOWED == '')) {
            $this->calculate_credit($payment);
            if ($this->discount['sum']!=0) {
                for ($i=1; $i<=$this->num; $i++) {
                    if ($this->discount['amount' . $i]!=0) {
                        if ($type == 'price') {
                            $string .= $xtPrice->xtcFormat(abs($this->discount['amount' . $i]), true) . ' ' . ($this->discount['amount' . $i]<0?MODULE_ORDER_TOTAL_PAYMENT_DISCOUNT:MODULE_ORDER_TOTAL_PAYMENT_FEE);
                        } else {
                            $string .= ($this->discount['pro' . $i] != 0.0 ?
                            number_format(abs($this->discount['pro' . $i]), 2, $xtPrice->currencies[$_SESSION['currency']]['decimal_point'], '') . '% ' : '') .
                            ($this->discount['fee' . $i]!=0? ($this->discount['pro' . $i] != 0.0 ? ' +' : '') . $xtPrice->xtcFormat(abs($this->discount['fee' . $i]), true) . ' ' : '') .
                            ($this->discount['amount' . $i]<0?MODULE_ORDER_TOTAL_PAYMENT_DISCOUNT:MODULE_ORDER_TOTAL_PAYMENT_FEE);
                        }
                        if (MODULE_ORDER_TOTAL_PAYMENT_BREAK != 'true') break;
                    }
                }
            }
        }
        return $string;
    }

    function get_discount($value, $percent)
    {
        global $xtPrice;
        // return round($value * 100) / 100 * $percent / 100;
        //return $god_amount = $value * $percent / 100;
        return $xtPrice->xtcFormat($value * $percent / 100, false);
    }
    
    function get_module_cost($payment_modul)
    {
      if ($this->show_in_checkout_payment) {
        return $this->get_percent($payment_modul['id'], $this->show_type);
      }
    }

    function check()
    {
        if (!isset($this->check)) {
            $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_PAYMENT_STATUS'");
            $this->check = xtc_db_num_rows($check_query);
        }
        return $this->check;
    }

    function install()
    {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_ORDER_TOTAL_PAYMENT_STATUS', 'true', '6', '1','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_ORDER_TOTAL_PAYMENT_SHOW_IN_CHECKOUT_PAYMENT', 'false', '6', '100','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_ORDER_TOTAL_PAYMENT_SHOW_TYPE', 'default', '6', '101','xtc_cfg_select_option(array(\'default\', \'price\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_ORDER_TOTAL_PAYMENT_SORT_ORDER', '49', '6', '2', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function ,date_added) VALUES ('MODULE_ORDER_TOTAL_PAYMENT_INC_SHIPPING', 'false', '6', '100005', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function ,date_added) VALUES ('MODULE_ORDER_TOTAL_PAYMENT_INC_TAX', 'true', '6', '100006','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function ,date_added) VALUES ('MODULE_ORDER_TOTAL_PAYMENT_CALC_TAX', 'true', '6', '100005','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_ORDER_TOTAL_PAYMENT_ALLOWED', '',   '6', '2', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('MODULE_ORDER_TOTAL_PAYMENT_TAX_CLASS', '0','6', '100007', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_ORDER_TOTAL_PAYMENT_BREAK', 'false', '6', '3','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        for ($i=1; $i<=$this->num; $i++) {
            xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_ORDER_TOTAL_PAYMENT_PERCENTAGE" . $i . "', '100:4', '6', '" . $i . "1', now())");
            xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_ORDER_TOTAL_PAYMENT_TYPE" . $i . "', 'moneyorder', '6', '" . $i . "2', now())");
        }
    }

    function keys()
    {
        $keys = array();
        $check_query = xtc_db_query("SELECT configuration_key FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_ORDER_TOTAL_PAYMENT_%' ORDER BY sort_order");
        while($key = xtc_db_fetch_array($check_query)) {
            $keys[] = $key['configuration_key'];
        }
        return $keys;
    }

    function remove()
    {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_ORDER_TOTAL_PAYMENT_%'");
    }
}
?>