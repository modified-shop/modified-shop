<?php
/* -----------------------------------------------------------------------------------------
   $Id: ot_tax.php 1002 2005-07-10 16:11:37Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ot_tax.php,v 1.14 2003/02/14); www.oscommerce.com  
   (c) 2003	 nextcommerce (ot_tax.php,v 1.11 2003/08/24); www.nextcommerce.org

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
      while (list($key, $value) = each($order->info['tax_groups'])) {
        if ($value > 0) {
          if ($_SESSION['customers_status']['customers_status_show_price_tax'] != 0) {
            $this->output[] = array('title' => $key . ':',
                                    'text' => $xtPrice->xtcFormat($value,true),
                                    'value' => $xtPrice->xtcFormat($value, false));
          }
          if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 
              && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1
              ) 
          {
            $this->output[] = array('title' => $key .':',
                                    'text' => $xtPrice->xtcFormat($value,true),
                                    'value' => $xtPrice->xtcFormat($value, false));
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
      return array(
        'MODULE_ORDER_TOTAL_TAX_STATUS', 
        'MODULE_ORDER_TOTAL_TAX_SORT_ORDER'
      );
    }

    function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_ORDER_TOTAL_TAX_STATUS', 'true', '6', '1','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_TAX_SORT_ORDER', '50', '6', '2', now())");
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
  }
?>