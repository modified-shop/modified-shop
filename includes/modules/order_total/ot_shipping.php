<?php
/* -----------------------------------------------------------------------------------------
   $Id: ot_shipping.php 5176 2013-07-19 14:26:57Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ot_shipping.php,v 1.15 2003/02/07); www.oscommerce.com
   (c) 2003 nextcommerce (ot_shipping.php,v 1.13 2003/08/24); www.nextcommerce.org
   (c) 2006 xt:Commerce (ot_shipping.php 1002 2005-07-10); www.xt-commerce.de

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
  class ot_shipping {
    var $title, $output;

    function ot_shipping() {
    	global $xtPrice;
      $this->code = 'ot_shipping';
      $this->title = MODULE_ORDER_TOTAL_SHIPPING_TITLE;
      $this->description = MODULE_ORDER_TOTAL_SHIPPING_DESCRIPTION;
      $this->enabled = ((MODULE_ORDER_TOTAL_SHIPPING_STATUS == 'true') ? true : false);
      $this->sort_order = MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER;


      $this->output = array();
    }

    function process() {
      global $order, $xtPrice, $free_shipping, $free_shipping_value_over;

      if (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') {
        switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
          case 'national':
            if ($order->delivery['country_id'] == STORE_COUNTRY) $pass = true;
            $free_shipping_value_over = MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER;
            break;
          case 'international':
            if ($order->delivery['country_id'] != STORE_COUNTRY) $pass = true; 
            $free_shipping_value_over = MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER_INTERNATIONAL;
            break;
          case 'both':
            if ($order->delivery['country_id'] == STORE_COUNTRY) {
              $free_shipping_value_over = MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER;
            } else {
              $free_shipping_value_over = MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER_INTERNATIONAL;
            }
            $pass = true; 
            break;
          default:
            $pass = false; 
            break;
        }
        
        if (($pass == true) && ($xtPrice->xtcRemoveCurr($order->info['total'] - $order->info['shipping_cost']) >= $free_shipping_value_over)) {
          $order->info['shipping_method'] = $this->title;
          $order->info['total'] -= $order->info['shipping_cost'];
          $order->info['shipping_cost'] = 0;
          $free_shipping = true;
        }
      }

      if (!isset($_SESSION['shipping'])) {
        return;
      }
     
      $module = substr($_SESSION['shipping']['id'], 0, strpos($_SESSION['shipping']['id'], '_'));

      if (xtc_not_null($order->info['shipping_method']) && isset($GLOBALS[$module]) && is_object($GLOBALS[$module])) {

        $tax = 0;
        $shipping_tax = 0;
        $shipping_tax_description = '';
        $shipping_tax = xtc_get_tax_rate($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
        $shipping_tax_description = xtc_get_tax_description($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
        $tax = xtc_add_tax($order->info['shipping_cost'], $shipping_tax)-$order->info['shipping_cost'];
        $tax = $xtPrice->xtcFormat($tax,false,0,true);
        
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 1) {
          // price with tax
          $order->info['shipping_cost'] = xtc_add_tax($order->info['shipping_cost'], $shipping_tax);
          $order->info['tax'] += $tax;
          $order->info['tax_groups'][TAX_ADD_TAX . "$shipping_tax_description"] += $tax;
          $order->info['total'] += $tax;
        } else {
          if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
            $order->info['tax'] = $order->info['tax'] += $tax;
            $order->info['tax_groups'][TAX_NO_TAX . "$shipping_tax_description"] = $order->info['tax_groups'][TAX_NO_TAX . "$shipping_tax_description"] += $tax;
          }
        }
        
        $this->output[] = array('title' => $order->info['shipping_method'] . ':',
                                'text' => $xtPrice->xtcFormat($order->info['shipping_cost'], true,0,true),
                                'value' => $xtPrice->xtcFormat($order->info['shipping_cost'], false,0,true));
      }
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_SHIPPING_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }

      return $this->_check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_SHIPPING_STATUS', 
                   'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', 
                   'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 
                   'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', 
                   'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER_INTERNATIONAL',
                   'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION', 
                   'MODULE_ORDER_TOTAL_SHIPPING_TAX_CLASS');
    }

    function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'true','6', '1','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', '30','6', '2', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 'false','6', '3', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, date_added) values ('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', '50', '6', '4', 'currencies->format', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, date_added) values ('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER_INTERNATIONAL', '50', '6', '4', 'currencies->format', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_ORDER_TOTAL_SHIPPING_DESTINATION', 'national','6', '5', 'xtc_cfg_select_option(array(\'national\', \'international\', \'both\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_ORDER_TOTAL_SHIPPING_TAX_CLASS', '0','6', '7', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");      
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
  }
?>