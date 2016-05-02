<?php
/* -----------------------------------------------------------------------------------------
   $Id: ot_loworderfee.php 1002 2005-07-10 16:11:37Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ot_loworderfee.php,v 1.11 2003/02/14); www.oscommerce.com 
   (c) 2003	 nextcommerce (ot_loworderfee.php,v 1.7 2003/08/24); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   

  class ot_loworderfee {
    var $title, $output;

    function __construct() {
    	global $xtPrice;
      $this->code = 'ot_loworderfee';
      $this->title = MODULE_ORDER_TOTAL_LOWORDERFEE_TITLE;
      $this->description = MODULE_ORDER_TOTAL_LOWORDERFEE_DESCRIPTION;
      $this->enabled = ((MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS == 'true') ? true : false);
      $this->sort_order = MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER;

      $this->output = array();
    }

    function process() {
      global $order, $xtPrice;
      
      //include needed functions
      require_once(DIR_FS_INC . 'xtc_calculate_tax.inc.php');
      
      if (MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE == 'true') {

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

        switch (MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION) {
          case 'national':
            if ($order->delivery['country_id'] == STORE_COUNTRY) $pass = true; 
            $low_order_fee_value_under = MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER;
            $low_order_fee_value = MODULE_ORDER_TOTAL_LOWORDERFEE_FEE;
           break;
          case 'international':
            if ($order->delivery['country_id'] != STORE_COUNTRY) $pass = true; 
            $low_order_fee_value_under = MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER_INTERNATIONAL;
            $low_order_fee_value = MODULE_ORDER_TOTAL_LOWORDERFEE_FEE_INTERNATIONAL;
            break;
          case 'both':
            if ($order->delivery['country_id'] == STORE_COUNTRY) {
              $low_order_fee_value_under = MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER;
              $low_order_fee_value = MODULE_ORDER_TOTAL_LOWORDERFEE_FEE;
            } else {
              $low_order_fee_value_under = MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER_INTERNATIONAL;
              $low_order_fee_value = MODULE_ORDER_TOTAL_LOWORDERFEE_FEE_INTERNATIONAL;
            }
            $pass = true; 
            break;
          default:
            $pass = false; 
            break;
        }

        if ($pass == true 
            && $xtPrice->xtcRemoveCurr($order->info['total'] - $order->info['shipping_cost']) < $low_order_fee_value_under
            )
        {
          $tax = xtc_get_tax_rate(MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);
          $tax_description = xtc_get_tax_description(MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);
          
          if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 1) {
            $order->info['tax'] += xtc_calculate_tax($low_order_fee_value, $tax);
            $order->info['tax_groups'][TAX_ADD_TAX . "$tax_description"] += xtc_calculate_tax($low_order_fee_value, $tax);
            $order->info['total'] += $low_order_fee_value + xtc_calculate_tax($low_order_fee_value, $tax);
            $low_order_fee = xtc_add_tax($low_order_fee_value, $tax);
          }
        
          if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 
              && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1
              ) 
          {
            $low_order_fee = $low_order_fee_value;
            $order->info['tax'] += xtc_calculate_tax($low_order_fee_value, $tax);
            $order->info['tax_groups'][TAX_NO_TAX . "$tax_description"] += xtc_calculate_tax($low_order_fee_value, $tax);
            $order->info['subtotal'] += $low_order_fee;
            $order->info['total'] += $low_order_fee;
          }
        
          if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 
              && $_SESSION['customers_status']['customers_status_add_tax_ot'] != 1
              ) 
          {
            $low_order_fee = $low_order_fee_value;
            $order->info['subtotal'] += $low_order_fee;
            $order->info['total'] += $low_order_fee;
          }
          
          $this->output[] = array('title' => $this->title . ':',
                                  'text' => $xtPrice->xtcFormat($low_order_fee, true),
                                  'value' => $low_order_fee);
        }
      }
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }

      return $this->_check;
    }

    function keys() {
      return array(
        'MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS', 
        'MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER', 
        'MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE',
        'MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER',
        'MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER_INTERNATIONAL',
        'MODULE_ORDER_TOTAL_LOWORDERFEE_FEE', 
        'MODULE_ORDER_TOTAL_LOWORDERFEE_FEE_INTERNATIONAL', 
        'MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION', 
        'MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS'
      );
    }

    function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS', 'true', '6', '1','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER', '4', '6', '2', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE', 'false', '6', '3', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, date_added) values ('MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER', '50','6', '4', 'currencies->format', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, date_added) values ('MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER_INTERNATIONAL', '50','6', '4', 'currencies->format', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, date_added) values ('MODULE_ORDER_TOTAL_LOWORDERFEE_FEE', '5','6', '5', 'currencies->format', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, date_added) values ('MODULE_ORDER_TOTAL_LOWORDERFEE_FEE_INTERNATIONAL', '5','6', '5', 'currencies->format', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION', 'both','6', '6', 'xtc_cfg_select_option(array(\'national\', \'international\', \'both\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS', '0','6', '7', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
  }
?>