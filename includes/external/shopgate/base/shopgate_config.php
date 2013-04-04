<?php

include_once DIR_FS_CATALOG.'includes/external/shopgate/shopgate_library/shopgate.php';

class ShopgateConfigModified extends ShopgateConfig {
	protected $redirect_languages;
	protected $shipping;
	protected $tax_zone_id;
	protected $customers_status_id;
	protected $customer_price_group;
	protected $order_status_open;
	protected $order_status_shipped;
	protected $order_status_shipping_blocked;
	protected $order_status_cancled;
	protected $reverse_categories_sort_order;
	protected $reverse_items_sort_order;
	
	public function startup() {
		// overwrite some library defaults
		$this->plugin_name = 'Modified';
		$this->enable_redirect_keyword_update = 24;
		$this->enable_ping = 1;
		$this->enable_add_order = 1;
		$this->enable_update_order = 1;
		$this->enable_get_orders = 0;
		$this->enable_get_customer = 1;
		$this->enable_get_items_csv = 1;
		$this->enable_get_categories_csv = 1;
		$this->enable_get_reviews_csv = 1;
		$this->enable_get_pages_csv = 0;
		$this->enable_get_log_file = 1;
		$this->enable_mobile_website = 1;
		$this->enable_cron = 1;
		$this->enable_clear_log_file = 1;
		$this->enable_clear_cache = 1;
		$this->shop_is_active = 1;
		$this->encoding = 'ISO-8859-15';
		
		// default filenames if no language was selected
		$this->items_csv_filename = 'items-undefined.csv';
		$this->categories_csv_filename = 'categories-undefined.csv';
		$this->reviews_csv_filename = 'reviews-undefined.csv';
		$this->pages_csv_filename = 'pages-undefined.csv';
		
		$this->access_log_filename = 'access-undefined.log';
		$this->request_log_filename = 'request-undefined.log';
		$this->error_log_filename = 'error-undefined.log';
		$this->debug_log_filename = 'debug-undefined.log';
		
		$this->redirect_keyword_cache_filename = 'redirect_keywords-undefined.txt';
		$this->redirect_skip_keyword_cache_filename = 'skip_redirect_keywords-undefined.txt';
		
		// initialize plugin specific stuff
		$this->redirect_languages = array();
		$this->shipping = '';
		$this->tax_zone_id = 5;
		$this->customers_status_id = 1;
		$this->customer_price_group = 0;
		$this->order_status_open = 1;
		$this->order_status_shipped = 3;
		$this->order_status_shipping_blocked = 1;
		$this->order_status_cancled = 0;
		$this->reverse_categories_sort_order = false;
		$this->reverse_items_sort_order = false;
	}
	
	/**
	 * Checks for duplicate shop numbers in multiple configurations.
	 *
	 * This checks all files in the configuration folder and shop numbers in all
	 * configuration files.
	 *
	 * @param string $shopNumber The shop number to test or null to test all shop numbers found.
	 * @return bool true if there are duplicates, false otherwise.
	 */
	public function checkDuplicates() {
		$shopNumbers = array();
		$files = scandir($this->config_folder_path);
		
		foreach ($files as $file) {
			if (!is_file($this->config_folder_path.DS.$file)) {
				continue;
			}
				
			$shopgate_config = null;
			include($this->config_folder_path.DS.$file);
			if (isset($shopgate_config) && isset($shopgate_config['shop_number'])) {
				if (in_array($shopgate_config['shop_number'], $shopNumbers)) {
					return true;
				} else {
					$shopNumbers[] = $shopgate_config['shop_number'];
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Checks if there is more than one configuration file available.
	 */
	public function checkMultipleConfigs() {
		$files = scandir($this->config_folder_path);
		$counter = 0;
		
		foreach ($files as $file) {
			if (!is_file($this->config_folder_path.DS.$file)) {
				continue;
			}
			$counter++;
		}
		
		return ($counter > 1);
	}

	/**
	 * Checks if there is a configuration for the language requested.
	 *
	 * @param string $language the ISO-639 code of the language or null to load global configuration
	 * @return bool true if global configuration should be used, false if the language has separate configuration
	 */
	public function checkUseGlobalFor($language) {
		return !file_exists($this->config_folder_path.DS.'myconfig-'.$language.'.php');
	}
	
	/**
	 * Removes the configuration for the language requested.
	 *
	 * @param string $language the ISO-639 code of the language or null to load global configuration
	 * @throws ShopgateLibraryException in case the file exists but cannot be deleted.
	 */
	public function useGlobalFor($language) {
		$fileName = $this->config_folder_path.DS.'myconfig-'.$language.'.php';
		if (file_exists($fileName)) {
			if (!@unlink($fileName)) {
				throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_READ_WRITE_ERROR, 'Error deleting configuration file "'.$fileName."'.");
			}
		}
	}
	
	/**
	 * Loads the configuration file by a given language or the global configuration file.
	 *
	 * @param string|null $language the ISO-639 code of the language or null to load global configuration
	 *
	 * @override
	 * @see ShopgateConfig::loadByLanguage()
	 */
	public function loadByLanguage($language) {
		if (!is_null($language)) {
			if (!file_exists($this->config_folder_path.DS.'myconfig-'.$language.'.php')) {
				return false;
			}
			
			parent::loadByLanguage($language);
		} else {
			parent::loadFile();
		}
	}
	
	/**
	 * Saves the desired fields to the configuration file for a given language or global configuration
	 *
	 * @param string[] $fieldList the list of fieldnames that should be saved to the configuration file.
	 * @param string $language the ISO-639 code of the language or null to save to global configuration
	 * @param bool $validate true to validate the fields that should be set.
	 *
	 * @override
	 * @throws ShopgateLibraryException in case the configuration can't be loaded or saved.
	 * @see ShopgateConfig::saveFileForLanguage()
	 */
	public function saveFileForLanguage(array $fieldList, $language = null, $validate = true) {
		if (!is_null($language)) {
			$this->setLanguage($language);
			$fieldList[] = 'language';
			parent::saveFileForLanguage($fieldList, $language, $validate);
		} else {
			parent::saveFile($fieldList, null, $validate);
		}
	}
	
	protected function validateCustom(array $fieldList = array()) {
		$failedFields = array();
		
		foreach ($fieldList as $field) {
			switch ($field) {
				case 'redirect_languages':
					// at least one redirect language must be selected
					if (empty($this->redirect_languages)) {
						$failedFields[] = $field;
					}
				break;
			}
		}
		
		return $failedFields;
	}
	
	
	public function getRedirectLanguages() {
		return $this->redirect_languages;
	}
	
	public function getShipping() {
		return $this->shipping;
	}
	
	public function getTaxZoneId() {
		return $this->tax_zone_id;
	}
	
	public function getCustomersStatusId() {
		return $this->customers_status_id;
	}
	
	public function getCustomerPriceGroup() {
		return $this->customer_price_group;
	}
	
	public function getOrderStatusOpen() {
		return $this->order_status_open;
	}
	
	public function getOrderStatusShipped() {
		return $this->order_status_shipped;
	}
	
	public function getOrderStatusShippingBlocked() {
		return $this->order_status_shipping_blocked;
	}
	
	public function getOrderStatusCancled() {
		return $this->order_status_cancled;
	}
	
	public function getReverseCategoriesSortOrder() {
		return $this->reverse_categories_sort_order;
	}
	
	public function getReverseItemsSortOrder() {
		return $this->reverse_items_sort_order;
	}
	
	public function setRedirectLanguages($value) {
		$this->redirect_languages = $value;
	}
	
	public function setShipping($value) {
		$this->shipping = $value;
	}
	
	public function setTaxZoneId($value) {
		$this->tax_zone_id = $value;
	}
	
	public function setCustomersStatusId($value) {
		$this->customers_status_id = $value;
	}
	
	public function setCustomerPriceGroup($value) {
		$this->customer_price_group = $value;
	}
	
	public function setOrderStatusOpen($value) {
		$this->order_status_open = $value;
	}
	
	public function setOrderStatusShipped($value) {
		$this->order_status_shipped = $value;
	}
	
	public function setOrderStatusShippingBlocked($value) {
		$this->order_status_shipping_blocked = $value;
	}
	
	public function setOrderStatusCancled($value) {
		$this->order_status_cancled = $value;
	}
	
	public function setReverseCategoriesSortOrder($value) {
		$this->reverse_categories_sort_order = $value;
	}
	
	public function setReverseItemsSortOrder($value) {
		$this->reverse_items_sort_order = $value;
	}
}