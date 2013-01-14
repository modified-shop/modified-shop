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
   (c) 2003	 nextcommerce (cod.php,v 1.7 2003/08/24); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

class cod {

	var $code, $title, $description, $enabled;

	function cod() {
		global $order,$xtPrice;

		$this->code = 'cod';
		$this->title = MODULE_PAYMENT_COD_TEXT_TITLE;
		$this->description = MODULE_PAYMENT_COD_TEXT_DESCRIPTION;
		$this->sort_order = MODULE_PAYMENT_COD_SORT_ORDER;
		$this->enabled = ((MODULE_PAYMENT_COD_STATUS == 'True') ? true : false);
		$this->info = MODULE_PAYMENT_COD_TEXT_INFO;
		$this->cost = '';

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
		
      if (MODULE_ORDER_TOTAL_COD_FEE_STATUS == 'true') {


        $cod_country = false;

          //process installed shipping modules
		  // BOF - Hetfield - 2009-08-18 - replaced deprecated function split with preg_split to be ready for PHP >= 5.3
          if ($_SESSION['shipping']['id'] == 'flat_flat') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_FLAT);
          if ($_SESSION['shipping']['id'] == 'item_item') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_ITEM);
          if ($_SESSION['shipping']['id'] == 'table_table') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_TABLE);
          if ($_SESSION['shipping']['id'] == 'zones_zones') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_ZONES);
          if ($_SESSION['shipping']['id'] == 'ap_ap') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_AP);
          if ($_SESSION['shipping']['id'] == 'dp_dp') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_DP);


          if ($_SESSION['shipping']['id'] == 'chp_ECO') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_CHP);
          if ($_SESSION['shipping']['id'] == 'chp_PRI') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_CHP);
          if ($_SESSION['shipping']['id'] == 'chp_URG') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_CHP);


          if ($_SESSION['shipping']['id'] == 'chronopost_chronopost') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_CHRONOPOST);


          if ($_SESSION['shipping']['id'] == 'dhl_ECX') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_DHL);
          if ($_SESSION['shipping']['id'] == 'dhl_DOX') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_DHL);
          if ($_SESSION['shipping']['id'] == 'dhl_SDX') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_DHL);
          if ($_SESSION['shipping']['id'] == 'dhl_MDX') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_DHL);
          if ($_SESSION['shipping']['id'] == 'dhl_WPX') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_DHL);

          if ($_SESSION['shipping']['id'] == 'ups_ups') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_UPS);
          if ($_SESSION['shipping']['id'] == 'upse_upse') $cod_zones = preg_split("/[:,]/", MODULE_ORDER_TOTAL_COD_FEE_UPSE);

 
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
		
		
		return array ('id' => $this->code, 'module' => $this->title, 'description' => $this->info,'module_cost'=>$this->cost);
	}

	function pre_confirmation_check() {
		return false;
	}

	function confirmation() {
		return false;
	}

	function process_button() {
		return false;
	}

	function before_process() {
		return false;
	}

	function after_process() {
		global $insert_id;
		if ($this->order_status)
			xtc_db_query("UPDATE ".TABLE_ORDERS." SET orders_status='".$this->order_status."' WHERE orders_id='".$insert_id."'");

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
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_COD_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_COD_ALLOWED', '', '6', '0', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_COD_ZONE', '0', '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_COD_SORT_ORDER', '0',  '6', '0', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_COD_ORDER_STATUS_ID', '0','6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
	}

	function remove() {
		xtc_db_query("delete from ".TABLE_CONFIGURATION." where configuration_key in ('".implode("', '", $this->keys())."')");
	}

	function keys() {
		return array ('MODULE_PAYMENT_COD_STATUS', 'MODULE_PAYMENT_COD_ALLOWED', 'MODULE_PAYMENT_COD_ZONE', 'MODULE_PAYMENT_COD_ORDER_STATUS_ID', 'MODULE_PAYMENT_COD_SORT_ORDER');
	}
}
?>