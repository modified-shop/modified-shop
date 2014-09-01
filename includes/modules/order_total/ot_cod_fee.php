<?php
/* -----------------------------------------------------------------------------------------
   $Id: ot_cod_fee.php 1002 2005-07-10 16:11:37Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ot_cod_fee.php,v 1.02 2003/02/24); www.oscommerce.com
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


  class ot_cod_fee {
    var $title, $output;

    function ot_cod_fee() {
    		global $xtPrice;
      $this->code = 'ot_cod_fee';
      $this->title = MODULE_ORDER_TOTAL_COD_FEE_TITLE;
      $this->description = MODULE_ORDER_TOTAL_COD_FEE_DESCRIPTION;
      $this->enabled = ((MODULE_ORDER_TOTAL_COD_FEE_STATUS == 'true') ? true : false);
      $this->sort_order = MODULE_ORDER_TOTAL_COD_FEE_SORT_ORDER;


      $this->output = array();
    }

    function process() {
      global $order, $xtPrice, $cod_cost, $cod_country, $shipping;

      if (MODULE_ORDER_TOTAL_COD_FEE_STATUS == 'true') {

        //Will become true, if cod can be processed.
        $cod_country = false;

        //check if payment method is cod. If yes, check if cod is possible.
        if ($_SESSION['payment'] == 'cod') {
          //process installed shipping modules
		   // BOF - Hetfield - 2009-08-18 - replaced deprecated function split with preg_split to be ready for PHP >= 5.3
          if ($_SESSION['shipping']['id'] == 'flat_flat') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_FLAT);
          if ($_SESSION['shipping']['id'] == 'item_item') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_ITEM);
          if ($_SESSION['shipping']['id'] == 'table_table') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_TABLE);
          if ($_SESSION['shipping']['id'] == 'zones_zones') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_ZONES);
          if ($_SESSION['shipping']['id'] == 'ap_ap') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_AP);
          if ($_SESSION['shipping']['id'] == 'dp_dp') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_DP);

                    // module chp
          if ($_SESSION['shipping']['id'] == 'chp_ECO') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_CHP);
          if ($_SESSION['shipping']['id'] == 'chp_PRI') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_CHP);
          if ($_SESSION['shipping']['id'] == 'chp_URG') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_CHP);

          // module chronopost
          if ($_SESSION['shipping']['id'] == 'chronopost_chronopost') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_CHRONOPOST);

          // module DHL
          if ($_SESSION['shipping']['id'] == 'dhl_ECX') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_DHL);
          if ($_SESSION['shipping']['id'] == 'dhl_DOX') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_DHL);
          if ($_SESSION['shipping']['id'] == 'dhl_SDX') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_DHL);
          if ($_SESSION['shipping']['id'] == 'dhl_MDX') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_DHL);
          if ($_SESSION['shipping']['id'] == 'dhl_WPX') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_DHL);
         // UPS
          if ($_SESSION['shipping']['id'] == 'ups_ups') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_UPS);
          if ($_SESSION['shipping']['id'] == 'upse_upse') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_UPSE);

          // Free Shipping
          if ($_SESSION['shipping']['id'] == 'free_free') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_FREE);
          if ($_SESSION['shipping']['id'] == 'freeamount_freeamount') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_FREEAMOUNT_FREE);
		  // EOF - Hetfield - 2009-08-18 - replaced deprecated function split with preg_split to be ready for PHP >= 5.3

            for ($i = 0; $i < count($cod_zones); $i++) {
            if ($cod_zones[$i] == $order->delivery['country']['iso_code_2']) {
                  $cod_cost = $cod_zones[$i + 1];
                  $cod_country = true;
                  break;
                } elseif ($cod_zones[$i] == '00') {
                  $cod_cost = $cod_zones[$i + 1];
                  $cod_country = true;
                  break;
                } else {
                }
              $i++;
            }
          } else {
            //COD selected, but no shipping module which offers COD
          }

        if ($cod_country) {
            $cod_tax = xtc_get_tax_rate(MODULE_ORDER_TOTAL_COD_FEE_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);
            $cod_tax_description = xtc_get_tax_description(MODULE_ORDER_TOTAL_COD_FEE_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 1) {
            $order->info['tax'] += xtc_add_tax($cod_cost, $cod_tax)-$cod_cost;
            $order->info['tax_groups'][TAX_ADD_TAX . "$cod_tax_description"] += xtc_add_tax($cod_cost, $cod_tax)-$cod_cost;
            $order->info['total'] += $cod_cost + (xtc_add_tax($cod_cost, $cod_tax)-$cod_cost);
            $cod_cost_value= xtc_add_tax($cod_cost, $cod_tax);
            $cod_cost= $xtPrice->xtcFormat($cod_cost_value,true);
        }
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
            $order->info['tax'] += xtc_add_tax($cod_cost, $cod_tax)-$cod_cost;
            $order->info['tax_groups'][TAX_NO_TAX . "$cod_tax_description"] += xtc_add_tax($cod_cost, $cod_tax)-$cod_cost;
            $cod_cost_value=$cod_cost;
            $cod_cost= $xtPrice->xtcFormat($cod_cost,true);
            $order->info['subtotal'] += $cod_cost_value;
            $order->info['total'] += $cod_cost_value;
        }
        if (!$cod_cost_value) {
           $cod_cost_value=$cod_cost;
           $cod_cost= $xtPrice->xtcFormat($cod_cost,true);
           $order->info['total'] += $cod_cost_value;
        }
            $this->output[] = array('title' => $this->title . ':',
                                    'text' => $cod_cost,
                                    'value' => $cod_cost_value);
        } else {
//Following code should be improved if we can't get the shipping modules disabled, who don't allow COD
// as well as countries who do not have cod
//          $this->output[] = array('title' => $this->title . ':',
//                                  'text' => 'No COD for this module.',
//                                  'value' => '');
        }
      }
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_COD_FEE_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_COD_FEE_STATUS', 'MODULE_ORDER_TOTAL_COD_FEE_SORT_ORDER', 'MODULE_ORDER_TOTAL_COD_FEE_FLAT', 'MODULE_ORDER_TOTAL_COD_FEE_ITEM', 'MODULE_ORDER_TOTAL_COD_FEE_TABLE','MODULE_ORDER_TOTAL_COD_FEE_CHRONOPOST','MODULE_ORDER_TOTAL_COD_FEE_DHL','MODULE_ORDER_TOTAL_COD_FEE_CHP', 'MODULE_ORDER_TOTAL_COD_FEE_ZONES', 'MODULE_ORDER_TOTAL_COD_FEE_AP', 'MODULE_ORDER_TOTAL_COD_FEE_UPS', 'MODULE_ORDER_TOTAL_COD_FEE_UPSE', 'MODULE_ORDER_TOTAL_COD_FEE_DP', 'MODULE_ORDER_TOTAL_COD_FEE_FREE', 'MODULE_ORDER_TOTAL_FREEAMOUNT_FREE', 'MODULE_ORDER_TOTAL_COD_FEE_TAX_CLASS');
    }

    function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_ORDER_TOTAL_COD_FEE_STATUS', 'true', '6', '0', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_COD_FEE_SORT_ORDER', '35', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_COD_FEE_FLAT', 'AT:3.00,DE:3.58,00:9.99', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_COD_FEE_ITEM', 'AT:3.00,DE:3.58,00:9.99', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_COD_FEE_TABLE', 'AT:3.00,DE:3.58,00:9.99', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_COD_FEE_ZONES', 'CA:4.50,US:3.00,00:9.99', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_COD_FEE_AP', 'AT:3.63,00:9.99', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_COD_FEE_DP', 'DE:4.00,00:9.99', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_COD_FEE_CHP', 'CH:4.00,00:9.99', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_COD_FEE_CHRONOPOST', 'FR:4.00,00:9.99', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_COD_FEE_DHL', 'AT:3.00,DE:3.58,00:9.99', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_COD_FEE_UPS', 'AT:3.00,DE:3.58,00:9.99', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_COD_FEE_UPSE', 'AT:3.00,DE:3.58,00:9.99', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_COD_FEE_FREE', 'AT:3.00,DE:3.58,00:9.99', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_FREEAMOUNT_FREE', 'AT:3.00,DE:3.58,00:9.99', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_ORDER_TOTAL_COD_FEE_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
  }
?>