<?php
/* -----------------------------------------------------------------------------------------
   $Id: ot_ps_fee.php,v 1.0 

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ot_ps_fee.php,v 1.02 2003/02/24); www.oscommerce.com
   (C) 2001 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers ; http://www.themedia.at & http://www.oscommerce.at

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contributions:

   Adapted for xtcommerce 2003/09/30 by Benax (axel.benkert@online-power.de)

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c  Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  class ot_ps_fee {
    var $title, $output;

    function __construct() {
    	global $xtPrice;
      
      $this->code = 'ot_ps_fee';
      $this->title = MODULE_ORDER_TOTAL_PS_FEE_TITLE;
      $this->description = MODULE_ORDER_TOTAL_PS_FEE_DESCRIPTION;
      $this->enabled = ((MODULE_ORDER_TOTAL_PS_FEE_STATUS == 'true') ? true : false);
      $this->sort_order = MODULE_ORDER_TOTAL_PS_FEE_SORT_ORDER;

      $this->default_values = 'AT:3.00,DE:3.58,00:9.99';
      
      $this->properties['button_update'] = '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MODULES, 'set=' . 'ordertotal' . '&module=' . $this->code . '&action=update') . '">' . BUTTON_UPDATE. '</a>';
      $this->properties['button_reset'] = '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MODULES, 'set=' . 'ordertotal' . '&module=' . $this->code . '&action=reset') . '">' . BUTTON_RESET. '</a>';

      $this->output = array();
    }

    function process() {
      global $order, $xtPrice, $ps_cost, $ps_country, $shipping;

      if (MODULE_ORDER_TOTAL_PS_FEE_STATUS == 'true') {

        //Will become true, if ps can be processed.
        $ps_country = false;
        
        $product_id_array = $_SESSION['cart']->get_product_id_array();
        
				$count_query = xtc_db_query("SELECT count(*) as count 
				                               FROM ".TABLE_PRODUCTS."  
				                              WHERE products_id IN ('".implode("', '", $product_id_array)."')
				                                AND products_fsk18 = '1'");
				$count = xtc_db_fetch_array($count_query);

        //check if payment method is ps. If yes, check if ps is possible.
        if (isset($_SESSION['shipping']['id']) && $count['count'] > 0) {
          //process installed shipping modules
          $shipping_code = strtoupper(array_shift(explode('_',$_SESSION['shipping']['id'])));
          $shipping_code = ($shipping_code == 'FREEAMOUNT') ? 'FREEAMOUNT_FREE' : 'FEE_' . $shipping_code;
          $ps_zones = preg_split("/[:,]/", constant('MODULE_ORDER_TOTAL_PS_'. $shipping_code));
          for ($i = 0; $i < count($ps_zones); $i++) {
            if ($ps_zones[$i] == $order->billing['country']['iso_code_2']) {
              $ps_cost = $ps_zones[$i + 1];
              $ps_country = true;
              break;
            } elseif ($ps_zones[$i] == '00') {
              $ps_cost = $ps_zones[$i + 1];
              $ps_country = true;
              break;
            }
            $i++;
          }
        } else {
          //PS selected, but no shipping module which offers PS
        }
        
        if ($ps_country) {
          $ps_cost = $xtPrice->xtcCalculateCurr($ps_cost);
          $ps_tax = xtc_get_tax_rate(MODULE_ORDER_TOTAL_PS_FEE_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);
          $ps_tax_description = xtc_get_tax_description(MODULE_ORDER_TOTAL_PS_FEE_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);

          if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 1) {
              $order->info['tax'] += xtc_add_tax($ps_cost, $ps_tax)-$ps_cost;
              $order->info['tax_groups'][TAX_ADD_TAX . "$ps_tax_description"] += xtc_add_tax($ps_cost, $ps_tax)-$ps_cost;
              $order->info['total'] += $ps_cost + (xtc_add_tax($ps_cost, $ps_tax)-$ps_cost);
              $ps_cost_value = xtc_add_tax($ps_cost, $ps_tax);
              $ps_cost= $xtPrice->xtcFormat($ps_cost_value, true);
              $order->info['subtotal'] += $ps_cost_value;
          }
          if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
              $order->info['tax'] += xtc_add_tax($ps_cost, $ps_tax)-$ps_cost;
              $order->info['tax_groups'][TAX_NO_TAX . "$ps_tax_description"] += xtc_add_tax($ps_cost, $ps_tax)-$ps_cost;
              $ps_cost_value = $ps_cost;
              $ps_cost = $xtPrice->xtcFormat($ps_cost, true);
              $order->info['subtotal'] += $ps_cost_value;
              $order->info['total'] += $ps_cost_value;
          }
          if (!$ps_cost_value) {
             $ps_cost_value = $ps_cost;
             $ps_cost = $xtPrice->xtcFormat($ps_cost, true);
             $order->info['subtotal'] += $ps_cost_value;
             $order->info['total'] += $ps_cost_value;
          }
          $this->output[] = array('title' => $this->title . ':',
                                  'text' => $ps_cost,
                                  'value' => $ps_cost_value);
        } else {
//Following pse should be improved if we can't get the shipping modules disabled, who don't allow PS
// as well as countries who do not have ps
//          $this->output[] = array('title' => $this->title . ':',
//                                  'text' => 'No PS for this module.',
//                                  'value' => '');
        }
      }
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_PS_FEE_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function keys() {
      $installed_shipping_modules = $this->get_installed_shipping_modules();
      $modules = array();
      $modules[] = 'MODULE_ORDER_TOTAL_PS_FEE_STATUS';
      $modules[] = 'MODULE_ORDER_TOTAL_PS_FEE_SORT_ORDER';
      if (count($installed_shipping_modules) > 0) {
        foreach($installed_shipping_modules as $shipping_code) {
          $shipping_code = strtoupper($shipping_code);
          $shipping_code = ($shipping_code == 'FREEAMOUNT') ? 'FREEAMOUNT_FREE' : 'FEE_' . $shipping_code;
          if(defined('MODULE_ORDER_TOTAL_PS_'.$shipping_code)) {           
            $modules[] = 'MODULE_ORDER_TOTAL_PS_'.$shipping_code;
          }
        }
      }
      $modules[] = 'MODULE_ORDER_TOTAL_PS_FEE_TAX_CLASS';
      
      return $modules;
    }

    function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_ORDER_TOTAL_PS_FEE_STATUS', 'true', '6', '0', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_PS_FEE_SORT_ORDER', '35', '6', '0', now())");
      $this->update();
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_ORDER_TOTAL_PS_FEE_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
    }

    function update($reset = false) {
      $installed_shipping_modules = $this->get_installed_shipping_modules();
      if (count($installed_shipping_modules) > 0) {
        foreach($installed_shipping_modules as $shipping_code) {
          $shipping_code = strtoupper($shipping_code);
          $shipping_code = ($shipping_code == 'FREEAMOUNT') ? 'FREEAMOUNT_FREE' : 'FEE_' . $shipping_code;          
          if(!defined('MODULE_ORDER_TOTAL_PS_'.$shipping_code)) {            
            $sql_data_array = array(
                'configuration_key' => 'MODULE_ORDER_TOTAL_PS_'.$shipping_code, 
                'configuration_value' => $this->default_values, 
                'configuration_group_id' => '6', 
                'sort_order' => '0',
                'date_added' => 'now()'
                );
            xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);            
          }
          if ($reset) {
            $sql_data_array['configuration_value'] = $this->default_values;
            xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array,'update', "configuration_key='".'MODULE_ORDER_TOTAL_PS_'.$shipping_code."'");
          }
        }
      }
    }
    
    function reset() {
      $this->update(true);
    }
    
    function get_installed_shipping_modules() {
      $module_keys = str_replace('.php','',MODULE_SHIPPING_INSTALLED);
      $installed_shipping_modules = explode(';',$module_keys);
      //support for ot_shipping
      $installed_shipping_modules[] = 'free';
      return $installed_shipping_modules;    
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
  }
?>