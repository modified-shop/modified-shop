<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ot_tax.php,v 1.14 2003/02/14); www.oscommerce.com  
   (c) 2003	 nextcommerce (ot_tax.php,v 1.11 2003/08/24); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

 
  class ot_tax {
    var $title, $output;

    function __construct() {
    	global $xtPrice;
      $this->code = 'ot_tax';
      $this->title = MODULE_ORDER_TOTAL_TAX_TITLE;
      $this->description = MODULE_ORDER_TOTAL_TAX_DESCRIPTION;
      $this->enabled = ((MODULE_ORDER_TOTAL_TAX_STATUS == 'true') ? true : false);
      $this->sort_order = MODULE_ORDER_TOTAL_TAX_SORT_ORDER;

      $this->output = array();
    }

    function process() {
      global $order, $xtPrice;
      reset($order->info['tax_groups']);
      
      // get tax class of shipping module
      $tax_class = 0;
      $module = strstr($order->info['shipping_class'], '_');
      $module = ltrim($module, '_');
      if (isset($GLOBALS[$module]->tax_class)) {
        $tax_class = $GLOBALS[$module]->tax_class;
      }
      
      $shipping_tax = array();
      foreach($order->info['tax_groups'] as $key => $value) {
        $shipping_tax[$key] = 0;
      }
      
      // add shipping tax to ot_tax groups if tax class is default and tax_class method is other than "none"
      if ($tax_class == 0) {
        $tax_class_method = 0;
        if (defined('SHIPPING_DEFAULT_TAX_CLASS_METHOD')) {
          $tax_class_method = constant('SHIPPING_DEFAULT_TAX_CLASS_METHOD');
        }
        $subtotal = $order->info['subtotal'];
        $shipping_cost = $order->info['shipping_cost'];
        
        if ($subtotal > 0 and $shipping_cost > 0) {
          // tax class methods: 1 - NONE, 2 - AUTO_PROPORTIONAL, 3 - AUTO_MAX
          if ($tax_class_method == 2) {
            // AUTO_PROPORTIONAL:
            $scaling_factor = $shipping_cost / $subtotal;
            foreach($order->info['tax_groups'] as $key => $value) {
              $shipping_tax[$key] = $value * $scaling_factor;
            }
          } else if ($tax_class_method == 3) {
            // AUTO_MAX:
            $max_group_total = 0;
            foreach($order->info['tax_groups'] as $key => $value) {
              if (!empty($value)) {
                // extract tax group rate from key
                $tax_rate = xtc_get_tax_rate_from_desc($key);
                if ($tax_rate > 0) {
                  // find biggest turnover tax group within this order
                  $tax_group_total = $value / $tax_rate * (100 + $tax_rate);
                  if ($tax_group_total > $max_group_total) {
                    $max_group_total = $tax_group_total;
                    $max_group_key = $key;
                    $max_group_tax_rate = $tax_rate;
                  }
                }  
              }
            }
            if (!empty($max_group_key)) {
              $shipping_tax[$max_group_key] = $shipping_cost / (100 + $max_group_tax_rate) * $max_group_tax_rate;
            }              
          }
        }
      }
      
      foreach($order->info['tax_groups'] as $key => $value) {
        if ($value > 0) {     
          if ($_SESSION['customers_status']['customers_status_show_price_tax'] != 0) {
            $this->output[] = array('title' => $key . ':',
                                    'text' =>$xtPrice->xtcFormat($value + $shipping_tax[$key], true),
                                    'value' => $xtPrice->xtcFormat($value + $shipping_tax[$key], false));
          }
          if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
            $this->output[] = array('title' => $key .':',
                                    'text' =>$xtPrice->xtcFormat($value + $shipping_tax[$key], true),
                                    'value' => $xtPrice->xtcFormat($value + $shipping_tax[$key], false));
          }
        }
      }
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_TAX_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }

      return $this->_check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_TAX_STATUS', 'MODULE_ORDER_TOTAL_TAX_SORT_ORDER');
    }

    function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_ORDER_TOTAL_TAX_STATUS', 'true', '6', '1','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_TAX_SORT_ORDER', '5', '6', '2', now())");
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
  }
?>