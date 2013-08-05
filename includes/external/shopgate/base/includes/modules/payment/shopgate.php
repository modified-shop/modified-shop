<?php

##### XTCM BOF #####
include_once DIR_FS_CATALOG.'includes/external/shopgate/base/shopgate_config.php';
##### XTCM EOF #####

class shopgate {
	var $code, $title, $description, $enabled, $sort_order;

	function shopgate() {
		global $order;

		$this->code = 'shopgate';
		$this->title = MODULE_PAYMENT_SHOPGATE_TEXT_TITLE;
		$this->description = MODULE_PAYMENT_SHOPGATE_TEXT_DESCRIPTION;
###### XTC3 | XTCM | GambioGX | osCommerce BOF #####
//
//
//
//
		$this->enabled = ((MODULE_PAYMENT_SHOPGATE_STATUS == 'True') ? true : false);
###### XTC3 | XTCM | GambioGX | osCommerce BOF #####
	}
	
	function mobile_payment() {
		global $order;
	
		$this->code = 'shopgate';
		$this->title = MODULE_PAYMENT_SHOPGATE_TEXT_TITLE;
		$this->description = MODULE_PAYMENT_SHOPGATE_TEXT_DESCRIPTION;
###### XTC3 | XTCM | GambioGX | osCommerce BOF #####
		$this->enabled = false;
		$this->sort_order = 88457;
###### XTC3 | XTCM | GambioGX | osCommerce BOF #####
	}

	function update_status() {
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
		if ($this->order_status){
			xtc_db_query("UPDATE ".TABLE_ORDERS." SET orders_status='".$this->order_status."' WHERE orders_id='".$insert_id."'");
		}
	}

	function get_error() {
		return false;
	}

	function check() {
		if (!isset ($this->_check)) {
			$check_query = xtc_db_query("select configuration_value from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_SHOPGATE_STATUS'");
			$this->_check = xtc_db_num_rows($check_query);
		}
		return $this->_check ? true : false;
	}

	/**
	 * install the module
	 *
	 * -- KEYS --:
	 * MODULE_PAYMENT_SHOPGATE_STATUS - The state of the module ( true / false )
	 * MODULE_PAYMENT_SHOPGATE_ALLOWED - Is the module allowed on frontend
	 * MODULE_PAYMENT_SHOPGATE_ORDER_STATUS_ID - (DEPRECATED) keep it for old installations
	 */
	function install() {
		if(!defined('TABLE_ORDERS_SHOPGATE_ORDER')) {
			define('TABLE_ORDERS_SHOPGATE_ORDER', 'orders_shopgate_order');
		}
		xtc_db_query("delete from ".TABLE_CONFIGURATION." where configuration_key in ('MODULE_PAYMENT_SHOPGATE_STATUS', 'MODULE_PAYMENT_SHOPGATE_ALLOWED', 'MODULE_PAYMENT_SHOPGATE_ORDER_STATUS_ID')");
		xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_SHOPGATE_STATUS', 'True',  '6', '".$this->sort_order."', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SHOPGATE_ALLOWED', '0',   '6', '".$this->sort_order."', now())");
		
		$this->installTable();
		$this->updateDatabase();
##### XTC3 | XTCM | GambioGX BOF #####
		$this->grantAdminAccess();
##### XTC3 | XTCM | GambioGX EOF #####
	}

	/**
	 * remove the shopgate module
	 */
	function remove() {
		// MODULE_PAYMENT_SHOPGATE_ORDER_STATUS_ID - Keep this on removing for old installation
		xtc_db_query("delete from ".TABLE_CONFIGURATION." where configuration_key in ('MODULE_PAYMENT_SHOPGATE_STATUS', 'MODULE_PAYMENT_SHOPGATE_ALLOWED', 'MODULE_PAYMENT_SHOPGATE_ORDER_STATUS_ID')");
		
###### XTC3 | XTCM | GambioGX | osCommerce BOF #####
//
//
###### XTC3 | XTCM | GambioGX | osCommerce EOF #####
		
##### XTC3 | XTCM | GambioGX BOF #####
		if( !$this->checkColumn("shopgate", TABLE_ADMIN_ACCESS) ) {
			xtc_db_query("alter table ".TABLE_ADMIN_ACCESS." DROP COLUMN shopgate");
		}
##### XTC3 | XTCM | GambioGX EOF #####
	}

	/**
	 * Keep the array empty to disable all configuration options
	 *
	 * @return multitype:
	 */
	function keys() {
		return array('MODULE_PAYMENT_SHOPGATE_STATUS');
	}
	
##### XTC3 | XTCM | GambioGX BOF #####
	/**
	 * set grant access to shopgate configuration
	 * to the current user and main administrator
	 */
	private function grantAdminAccess() {
		if( $this->checkColumn("shopgate", TABLE_ADMIN_ACCESS) ) {
			// Create column shopgate in admin_access...
			xtc_db_query("alter table ".TABLE_ADMIN_ACCESS." ADD shopgate INT( 1 ) NOT NULL");
			
			// ... grant access to to shopgate for main administrator
			xtc_db_query("update ".TABLE_ADMIN_ACCESS." SET shopgate=1 where customers_id=1 LIMIT 1");
			
			if(!empty($_SESSION['customer_id']) && $_SESSION['customer_id'] !=1) {
				// grant access also to current user
				xtc_db_query("update ".TABLE_ADMIN_ACCESS." SET shopgate = 1 where customers_id='".$_SESSION['customer_id']."' LIMIT 1");
			}
			
			xtc_db_query("update ".TABLE_ADMIN_ACCESS." SET shopgate = 5 where customers_id = 'groups'");
		}
	}
##### XTC3 | XTCM | GambioGX EOF #####
	
	/**
	 * Install the shopgate order table
	 */
	private function installTable() {
		xtc_db_query("
			CREATE TABLE IF NOT EXISTS `".TABLE_ORDERS_SHOPGATE_ORDER."` (
					`shopgate_order_id` INT(11) NOT NULL AUTO_INCREMENT,
					`orders_id` INT(11) NOT NULL,
					`shopgate_order_number` BIGINT(20) NOT NULL,
					`shopgate_shop_number` BIGINT(20) UNSIGNED DEFAULT NULL,
					`is_paid` tinyint(1) UNSIGNED DEFAULT NULL,
					`is_shipping_blocked` tinyint(1) UNSIGNED DEFAULT NULL,
					`payment_infos` TEXT NULL,
					`is_sent_to_shopgate` tinyint(1) UNSIGNED DEFAULT NULL,
					`modified` datetime DEFAULT NULL,
					`created` datetime DEFAULT NULL,
					PRIMARY KEY (`shopgate_order_id`)
			) ENGINE=MyISAM; ");
###### XTC3 | XTCM | GambioGX | osCommerce BOF #####

//
//
//
//
//
//
//
###### XTC3 | XTCM | GambioGX | osCommerce EOF #####

	}
	
	/**
	 * update existing database
	 */
	private function updateDatabase() {
		if($this->checkColumn('shopgate_shop_number')) {
			$qry = 'ALTER TABLE `'.TABLE_ORDERS_SHOPGATE_ORDER.'` ADD `shopgate_shop_number` BIGINT(20) UNSIGNED NULL AFTER `shopgate_order_number`;';
			xtc_db_query($qry);
		}
		
		if($this->checkColumn('is_paid')) {
			$qry = 'ALTER TABLE  `'.TABLE_ORDERS_SHOPGATE_ORDER.'` ADD  `is_paid` TINYINT(1) UNSIGNED NULL AFTER `shopgate_shop_number`;';
			xtc_db_query($qry);
		}
		
		if($this->checkColumn('is_shipping_blocked')) {
			$qry = 'ALTER TABLE `'.TABLE_ORDERS_SHOPGATE_ORDER.'` ADD  `is_shipping_blocked` TINYINT(1) UNSIGNED NULL AFTER  `is_paid`;';
			xtc_db_query($qry);
		}
		
		if($this->checkColumn('payment_infos')) {
			$qry = 'ALTER TABLE `'.TABLE_ORDERS_SHOPGATE_ORDER.'` ADD  `payment_infos` TEXT NULL AFTER  `is_shipping_blocked`;';
			xtc_db_query($qry);
		}
		
		if($this->checkColumn('is_sent_to_shopgate')) {
			$qry = 'ALTER TABLE `'.TABLE_ORDERS_SHOPGATE_ORDER.'` ADD  `is_sent_to_shopgate` TINYINT(1) UNSIGNED NULL AFTER `payment_infos`;';
			xtc_db_query($qry);
		}
		
		if($this->checkColumn('modified')) {
			$qry = 'ALTER TABLE `'.TABLE_ORDERS_SHOPGATE_ORDER.'` ADD  `modified` DATETIME NULL AFTER `is_sent_to_shopgate`;';
			ShopgateWrapper::db_query($qry);
		}
		
		if($this->checkColumn('created')) {
			$qry = 'ALTER TABLE `'.TABLE_ORDERS_SHOPGATE_ORDER.'` ADD  `created` DATETIME NULL AFTER `modified`;';
			ShopgateWrapper::db_query($qry);
		}
		
		$languages = xtc_db_query('SELECT `languages_id`, `code` FROM `'.TABLE_LANGUAGES.'`;');
		if(empty($languages)) {
			echo MODULE_PAYMENT_SHOPGATE_ERROR_READING_LANGUAGES;
			return;
		}
		
		// load global configuration
		try {
##### XTCM BOF #####
			$config = new ShopgateConfigModified();
##### XTCM EOF #####
			$config->loadFile();
		} catch (ShopgateLibraryException $e) {
			if (!($config instanceof ShopgateConfig)) {
				echo MODULE_PAYMENT_SHOPGATE_ERROR_LOADING_CONFIG;
				return;
			}
		}
		
		$languageCodes = array();
		$configFieldList = array('language', 'redirect_languages');
		while($language = xtc_db_fetch_array($languages)) {
			// collect language codes to enable redirect
			$languageCodes[] = $language['code'];
			
			switch($language['code']) {

				case 'de':
					$statusNameNew = 'Versand blockiert (Shopgate)';
					$statusNameSearch = '%shopgate%';
					break;
				case 'en':
					$statusNameNew = 'Shipping blocked (Shopgate)';
					$statusNameSearch = '%shopgate%';
					break;

				default: continue 2;
			}
			
			$checkShippingBlocked = xtc_db_fetch_array(xtc_db_query(
				"SELECT `orders_status_id`, `orders_status_name` ".
				"FROM `".TABLE_ORDERS_STATUS."` ".

				"WHERE LOWER(`orders_status_name`) LIKE '".xtc_db_input($statusNameSearch)."' ".

				"AND `language_id` = ".xtc_db_input($language['languages_id']).";"
			));
			
			if(!empty($checkShippingBlocked)) {
				$orderStatusShippingBlockedId = $checkShippingBlocked['orders_status_id'];
			} else {
				// if no orders_status_id has been determined yet and the status could not be found, create a new one
				if(!isset($orderStatusShippingBlockedId)) {
					$nextId = xtc_db_fetch_array(xtc_db_query("SELECT max(orders_status_id) AS orders_status_id FROM " . TABLE_ORDERS_STATUS));
					$orderStatusShippingBlockedId = $nextId['orders_status_id'] + 1;
				}
				
				// insert the status into the database
				xtc_db_query(
					"INSERT INTO `".TABLE_ORDERS_STATUS."` ".
					"(`orders_status_id`, `language_id`, `orders_status_name`) VALUES ".
					"(".xtc_db_input($orderStatusShippingBlockedId).", ".xtc_db_input($language['languages_id']).", '".xtc_db_input($statusNameNew)."');"
				);
			}
			
			// set global order status id
			if($language['code'] == DEFAULT_LANGUAGE) {
				$config->setOrderStatusShippingBlocked($orderStatusShippingBlockedId);
				$configFieldList[] = 'order_status_shipping_blocked';
			}
		}
		
		// get the actual definition of the plugin version
		if(!defined("SHOPGATE_PLUGIN_VERSION")) {
##### XTCM BOF #####
			require_once(DIR_FS_CATALOG.'includes/external/shopgate/plugin.php');
##### XTCM EOF #####
		}
		// shopgate table version equals to the SHOPGATE_PLUGIN_VERSION, save that version to the config file
		$config->setShopgateTableVersion(SHOPGATE_PLUGIN_VERSION);
		$configFieldList[] = 'shopgate_table_version';
		// save default language, order_status_id and redirect languages in the configuration
		try {
			$config->setLanguage(DEFAULT_LANGUAGE);
			$config->setRedirectLanguages($languageCodes);
			$config->saveFile($configFieldList);
		} catch (ShopgateLibraryException $e) {
			echo MODULE_PAYMENT_SHOPGATE_ERROR_SAVING_CONFIG;
		}
	}
	
	/**
	 * Check if the column exists in the specified table
	 *
	 * @param string $columnName
	 * @param string $table
	 */
	private function checkColumn($columnName, $table = TABLE_ORDERS_SHOPGATE_ORDER) {
		$result = xtc_db_query("show columns from `{$table}`");
		
		$exists = false;
		while($field = xtc_db_fetch_array($result)) {
			if($field['Field'] == $columnName) {
				$exists = true;
				break;
			}
		}
		
		return !$exists;
	}
}
