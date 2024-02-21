<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

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

    var $code;
    var $title;
    var $description;
    var $enabled;
    var $sort_order;
    var $output;
    var $_check;

    function __construct() {    	
      $this->code = 'ot_tax';
      $this->title = MODULE_ORDER_TOTAL_TAX_TITLE;
      $this->description = MODULE_ORDER_TOTAL_TAX_DESCRIPTION;
      $this->enabled = ((defined('MODULE_ORDER_TOTAL_TAX_STATUS') && MODULE_ORDER_TOTAL_TAX_STATUS == 'true') ? true : false);
      $this->sort_order = ((defined('MODULE_ORDER_TOTAL_TAX_SORT_ORDER')) ? MODULE_ORDER_TOTAL_TAX_SORT_ORDER : '');

      $this->output = array();
    }

    function process() {
      global $order, $xtPrice, $main, $PHP_SELF;

      if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 
          && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0
          && $order->delivery['country_id'] != STORE_COUNTRY
          ) 
      {
        $order->info['allow_tax'] = 4; //merchant EU, shipping to EU
      }

      if ($main->getDeliveryDutyInfo($order->delivery['country']['iso_code_2'])) {
        $order->info['allow_tax'] = 0; //customer, shipping outside EU
      }

      foreach ($order->info['tax_groups'] as $key => $value) {
        if ($value > 0) {
          if (defined('MODULE_ORDER_TOTAL_SUBTOTAL_NO_TAX_STATUS')
              && MODULE_ORDER_TOTAL_SUBTOTAL_NO_TAX_STATUS == 'true'
              && strpos(basename($PHP_SELF), 'checkout') !== false
              && $xtPrice->xtcRemoveCurr($order->info['total']) >= $_SESSION['customers_status']['customers_status_show_tax_total']
              )
          {
            $key = str_replace(TAX_ADD_TAX, TAX_NO_TAX, $key);
          }

          if ($_SESSION['customers_status']['customers_status_show_price_tax'] != 0) {
            $order->info['allow_tax'] = 1; //customer
            $this->output[] = array('title' => $key . ':',
                                    'text' => $xtPrice->xtcFormat($value,true),
                                    'value' => $xtPrice->xtcFormat($value, false));
          }

          if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 
              && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1
              ) 
          {
            $order->info['allow_tax'] = 2; //merchant
            $this->output[] = array('title' => $key .':',
                                    'text' => $xtPrice->xtcFormat($value,true),
                                    'value' => $xtPrice->xtcFormat($value, false));
          }

          if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 
              && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0
              && $order->delivery['country_id'] == STORE_COUNTRY
              ) 
          {
            $order->info['allow_tax'] = 3; //merchant EU, shipping to store country
            $this->output[] = array('title' => $key .':',
                                    'text' => $xtPrice->xtcFormat($value,true),
                                    'value' => $xtPrice->xtcFormat($value, false));
          }
        }
      }
    }

    function check() {
      if (!isset($this->_check)) {
        if (defined('MODULE_ORDER_TOTAL_TAX_STATUS')) {
          $this->_check = true;
        } else {
          $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_TAX_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
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
