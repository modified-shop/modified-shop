<?php

/* -----------------------------------------------------------------------------------------
   $Id: cod.php 1003 2005-07-10 18:58:52Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.28 2003/02/14); www.oscommerce.com
   (c) 2003  nextcommerce (cod.php,v 1.7 2003/08/24); www.nextcommerce.org

   third party contributions:
   - added max subtotal where cod allowed to config, noRiddle / web0null / web28
   - added not showing cod on checkout_payment when shipping module doesn't offer cod
     or when fee in ot_cod_fee empty, noRiddle / web0null

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class cod {

  var $code, $title, $description, $enabled;

  function __construct() {
    global $order,$xtPrice;

    $this->code = 'cod';
    $this->title = MODULE_PAYMENT_COD_TEXT_TITLE;
    $this->description = MODULE_PAYMENT_COD_TEXT_DESCRIPTION;
    $this->sort_order = MODULE_PAYMENT_COD_SORT_ORDER;
    $this->enabled = ((MODULE_PAYMENT_COD_STATUS == 'True') ? true : false);
    $this->info = MODULE_PAYMENT_COD_TEXT_INFO;
    $this->cost = '';    
    $this->limit_subtotal = MODULE_PAYMENT_COD_LIMIT_ALLOWED; // for comparison to be able to limit order subtotal sum where cod allowed

    if ((int) MODULE_PAYMENT_COD_ORDER_STATUS_ID > 0) {
      $this->order_status = MODULE_PAYMENT_COD_ORDER_STATUS_ID;
    }

    if (is_object($order))
      $this->update_status();
  }

  function update_status() {
    global $order;
    if ($_SESSION['shipping']['id'] == 'selfpickup_selfpickup') {
      $this->enabled = false;
    }
    if (($this->enabled == true) && ((int) MODULE_PAYMENT_COD_ZONE > 0)) {
      $check_flag = false;
      $check_query = xtc_db_query("select zone_id from ".TABLE_ZONES_TO_GEO_ZONES." where geo_zone_id = '".MODULE_PAYMENT_COD_ZONE."' and zone_country_id = '".$order->delivery['country']['id']."' order by zone_id");
      while ($check = xtc_db_fetch_array($check_query)) {
        if ($check['zone_id'] < 1) {
          $check_flag = true;
          break;
        }
        elseif ($check['zone_id'] == $order->delivery['zone_id']) {
          $check_flag = true;
          break;
        }
      }

      if ($check_flag == false) {
        $this->enabled = false;
      }
    }

  }

  function javascript_validation() {
    return false;
  }

  function selection() {
    global $xtPrice,$order;

      // limit sum where cod allowed
      if($this->limit_subtotal && ($xtPrice->xtcRemoveCurr($_SESSION['cart']->show_total()) >= $this->limit_subtotal)) {
        return;
      }
      
      if (MODULE_ORDER_TOTAL_COD_FEE_STATUS == 'true') {

        $cod_country = false;
        $cod_zones = array(); // added variable

        //process installed shipping modules
        $shipping_code = '';
        if (isset($_SESSION['shipping']['id'])) {
          $shipping_code = strtoupper(array_shift(explode('_',$_SESSION['shipping']['id'])));
        }
        $shipping_code = (isset($shipping_code) && $shipping_code == 'FREEAMOUNT') ? 'FREEAMOUNT_FREE' : 'FEE_' . $shipping_code;

        $cod_zones = array();
        if (defined('MODULE_ORDER_TOTAL_COD_'. $shipping_code)) {
          $cod_zones = preg_split("/[:,]/", constant('MODULE_ORDER_TOTAL_COD_'. $shipping_code));
        }
        // dont't show cod on checkout_payment when shipping module doesn't offer cod
        if (count($cod_zones) == 0 || (!in_array(($order->delivery['country']['iso_code_2']), $cod_zones) && !in_array('00', $cod_zones))) {
          return;
        }

        for ($i = 0; $i < count($cod_zones); $i++) {
          if ($cod_zones[$i] == $order->delivery['country']['iso_code_2'] || $cod_zones[$i] == '00') {
            $cod_cost = $cod_zones[$i + 1];
            if ($cod_cost == '') {
              return;
            }
            $cod_country = true;
            break;
          }
          $i++;
        }
      } else {
        //COD selected, but no shipping module which offers COD
      }

      if ($cod_country) {
        $cod_cost = $xtPrice->xtcCalculateCurr($cod_cost);
        $cod_tax = xtc_get_tax_rate(MODULE_ORDER_TOTAL_COD_FEE_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);
        $cod_tax_description = xtc_get_tax_description(MODULE_ORDER_TOTAL_COD_FEE_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);
        
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 1) {
            $cod_cost_value= xtc_add_tax($cod_cost, $cod_tax);
            $cod_cost= $xtPrice->xtcFormat($cod_cost_value,true);
        }
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
            $cod_cost_value=$cod_cost;
            $cod_cost= $xtPrice->xtcFormat($cod_cost,true);
        }
        if (!$cod_cost_value) {
            $cod_cost_value=$cod_cost;
            $cod_cost= $xtPrice->xtcFormat($cod_cost,true);
        }
        $this->cost = '+ '.$cod_cost;
      }
      
      return array ('id' => $this->code,
                    'module' => $this->title,
                    'description' => $this->info,
                    'module_cost'=>$this->cost
                   );
  }

  function pre_confirmation_check() {
    return false;
  }

  function confirmation() {
    return false;
  }

  function process_button() {
    $note = '';
    if (MODULE_PAYMENT_COD_DISPLAY_INFO == 'True') {
      $note = MODULE_PAYMENT_COD_DISPLAY_INFO_TEXT;
    }
    return $note;
  }

  function before_process() {
    return false;
  }

  function after_process() {
    global $insert_id;

    if (isset($this->order_status) && $this->order_status) {
      xtc_db_query("UPDATE ".TABLE_ORDERS." SET orders_status='".$this->order_status."' WHERE orders_id='".$insert_id."'");
      xtc_db_query("UPDATE ".TABLE_ORDERS_STATUS_HISTORY." SET orders_status_id='".$this->order_status."' WHERE orders_id='".$insert_id."'");
    }
  }

  function get_error() {
    return false;
  }

  function check() {
    if (!isset ($this->_check)) {
      $check_query = xtc_db_query("select configuration_value from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_COD_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }

  function install() {
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_COD_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_COD_ALLOWED', '', '6', '0', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_COD_ZONE', '0', '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_COD_SORT_ORDER', '0',  '6', '0', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_COD_ORDER_STATUS_ID', '0','6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_COD_LIMIT_ALLOWED', '600', '6', '3', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_COD_DISPLAY_INFO', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
  }

  function remove() {
    xtc_db_query("delete from ".TABLE_CONFIGURATION." where configuration_key in ('".implode("', '", $this->keys())."')");
  }

  function keys() {
    return array ('MODULE_PAYMENT_COD_STATUS',
                  'MODULE_PAYMENT_COD_ALLOWED',
                  'MODULE_PAYMENT_COD_ZONE',
                  'MODULE_PAYMENT_COD_ORDER_STATUS_ID',
                  'MODULE_PAYMENT_COD_SORT_ORDER',
                  'MODULE_PAYMENT_COD_LIMIT_ALLOWED',
                  'MODULE_PAYMENT_COD_DISPLAY_INFO',
                  );
  }
}
?>