<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ptebanktransfer.php,v 1.4.1 2003/09/25 19:57:14); www.oscommerce.com
   (c) 2003 xtCommerce www.xt-commerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class eustandardtransfer {
  var $code, $title, $description, $enabled;

  // class constructor
  function __construct() {
    global $order;
    $this->code = 'eustandardtransfer';
    $this->title = MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_TITLE;
    $this->description = MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_DESCRIPTION;
    $this->sort_order = MODULE_PAYMENT_EUSTANDARDTRANSFER_SORT_ORDER;
    $this->info = MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_INFO;
    $this->info_success = (MODULE_PAYMENT_EUSTANDARDTRANSFER_SUCCESS == 'True' ? $this->description : $this->info);
    $this->enabled = ((MODULE_PAYMENT_EUSTANDARDTRANSFER_STATUS == 'True') ? true : false);

		if ((int) MODULE_PAYMENT_EUSTANDARDTRANSFER_ORDER_STATUS_ID > 0) {
			$this->order_status = MODULE_PAYMENT_EUSTANDARDTRANSFER_ORDER_STATUS_ID;
		}

		if (is_object($order))
			$this->update_status();
  }

	function update_status() {
		global $order;

		if (($this->enabled == true) && ((int) MODULE_PAYMENT_EUSTANDARDTRANSFER_ZONE > 0)) {
			$check_flag = false;
			$check_query = xtc_db_query("select zone_id from ".TABLE_ZONES_TO_GEO_ZONES." where geo_zone_id = '".MODULE_PAYMENT_EUSTANDARDTRANSFER_ZONE."' and zone_country_id = '".$order->billing['country']['id']."' order by zone_id");
			while ($check = xtc_db_fetch_array($check_query)) {
				if ($check['zone_id'] < 1) {
					$check_flag = true;
					break;
				}
				elseif ($check['zone_id'] == $order->billing['zone_id']) {
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
    return array ('id' => $this->code, 'module' => $this->title, 'description' => $this->info);
  }

  function pre_confirmation_check() {
    return false;
  }

  function confirmation() {
    $confirmation = array ('title' => $this->title.': ', 
                           'fields' => array (array ('title' => '', 
                                                     'field' => $this->info)
                                              )
                           );

    return $confirmation;
  }

  function process_button() {
    return false;
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

  function success() {
    $confirmation = array(
      array ('title' => $this->title.': ', 
             'class' => $this->code,
             'fields' => array(array('title' => '',
                                     'field' => $this->info_success
                                     )
                               )
             )
    );
    return $confirmation;
  }

  function output_error() {
    return false;
  }

  function check() {
    if (!isset ($this->check)) {
      $check_query = xtc_db_query("select configuration_value from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_EUSTANDARDTRANSFER_STATUS'");
      $this->check = xtc_db_num_rows($check_query);
    }
    return $this->check;
  }

  function install() {
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_ALLOWED', '', '6', '0', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_STATUS', 'True', '6', '3', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now());");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKNAM', '---',  '6', '1', now());");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_BRANCH', '---', '6', '1', now());");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNAM', '---',  '6', '1', now());");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNUM', '---',  '6', '1', now());");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCIBAN', '---',  '6', '1', now());");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKBIC', '---',  '6', '1', now());");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_SORT_ORDER', '0',  '6', '0', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_ZONE', '0',  '6', '2', 'xtc_cfg_pull_down_zone_classes(', 'xtc_get_zone_class_title', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_ORDER_STATUS_ID', '0','6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_SUCCESS', 'False', '6', '3', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now());");
  }

  function remove() {
    xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE 'MODULE_PAYMENT_EUTRANSFER_%'");
    xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE 'MODULE_PAYMENT_EUSTANDARDTRANSFER_%'");
  }

  function keys() {
    $keys = array ('MODULE_PAYMENT_EUSTANDARDTRANSFER_STATUS', 
                   'MODULE_PAYMENT_EUSTANDARDTRANSFER_ALLOWED', 
                   'MODULE_PAYMENT_EUSTANDARDTRANSFER_ZONE',
                   'MODULE_PAYMENT_EUSTANDARDTRANSFER_ORDER_STATUS_ID', 
                   'MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKNAM', 
                   'MODULE_PAYMENT_EUSTANDARDTRANSFER_BRANCH', 
                   'MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNAM', 
                   'MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNUM', 
                   'MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCIBAN', 
                   'MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKBIC',
                   'MODULE_PAYMENT_EUSTANDARDTRANSFER_SORT_ORDER',
                   'MODULE_PAYMENT_EUSTANDARDTRANSFER_SUCCESS');

    return $keys;
  }
}
?>