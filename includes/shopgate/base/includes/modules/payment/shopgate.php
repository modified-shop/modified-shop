<?php

class shopgate {
	var $code, $title, $description, $enabled;

	function shopgate() {
		global $order;

		$this->code = 'shopgate';
		$this->title = MODULE_PAYMENT_SHOPGATE_TEXT_TITLE;
		$this->description = MODULE_PAYMENT_SHOPGATE_TEXT_DESCRIPTION;
		$this->enabled = false;// ((MODULE_SHOPGATE_MONEYORDER_STATUS == 'True') ? true : false);
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
		return array ('title' => MODULE_PAYMENT_SHOPGATE_TEXT_DESCRIPTION);
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
			$check_query = xtc_db_query("select configuration_value from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_SHOPGATE_STATUS'");
			$this->_check = xtc_db_num_rows($check_query);
		}
		return $this->_check;
	}

	function install() {
		if(!defined('TABLE_ORDERS_SHOPGATE_ORDER'))define('TABLE_ORDERS_SHOPGATE_ORDER', 'orders_shopgate_order');
		xtc_db_query("
			CREATE TABLE IF NOT EXISTS `".TABLE_ORDERS_SHOPGATE_ORDER."` (
			  `shopgate_order_id` INT(11) NOT NULL AUTO_INCREMENT,
			  `orders_id` INT(11) NOT NULL,
			  `shopgate_order_number` BIGINT(20) NOT NULL,
			  `is_paid` tinyint(1) UNSIGNED DEFAULT NULL,
			  `is_shipping_blocked` tinyint(1) UNSIGNED DEFAULT NULL,
			  `payment_infos` TEXT NULL,
			  `is_sent_to_shopgate` tinyint(1) UNSIGNED DEFAULT NULL,
			  `modified` datetime DEFAULT NULL,
			  `created` datetime DEFAULT NULL,
			  PRIMARY KEY (`shopgate_order_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;
				");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_SHOPGATE_STATUS', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now());");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_SHOPGATE_ORDER_STATUS_ID', '0', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_SHOPGATE_ALLOWED', '0', '6', '0', 'NULL', 'NULL', now())");
		
		$qry = xtc_db_query("show columns from `".TABLE_ADMIN_ACCESS."` WHERE field = 'shopgate'");
		if( xtc_db_num_rows($qry) == 0 ) {
			xtc_db_query("alter table ".TABLE_ADMIN_ACCESS." ADD shopgate INT( 1 ) NOT NULL");
			xtc_db_query("update ".TABLE_ADMIN_ACCESS." SET shopgate=1 where customers_id=1 LIMIT 1");
			
			if($_SESSION['customer_id']!=1) {
				xtc_db_query("update ".TABLE_ADMIN_ACCESS." SET shopgate=1 where customers_id=".$_SESSION['customer_id']." LIMIT 1");
			}
			
			xtc_db_query("update ".TABLE_ADMIN_ACCESS." SET shopgate=5 where customers_id='groups'");
		}
	}

	function remove() {
		xtc_db_query("delete from ".TABLE_CONFIGURATION." where configuration_key in ('MODULE_PAYMENT_SHOPGATE_STATUS', '".implode("', '", $this->keys())."')");
		
		$qry = xtc_db_query("show columns from `".TABLE_ADMIN_ACCESS."` WHERE field = 'shopgate'");
		if( xtc_db_num_rows($qry) >= 1 ) {
			xtc_db_query("alter table ".TABLE_ADMIN_ACCESS." DROP COLUMN shopgate");
		}
	}

	function keys() {
		return array (
			'MODULE_PAYMENT_SHOPGATE_ORDER_STATUS_ID',
		);
	}
}
