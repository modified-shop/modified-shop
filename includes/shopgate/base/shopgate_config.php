<?php

include_once DIR_FS_CATALOG.'includes/shopgate/shopgate_library/shopgate.php';

class ShopgateConfigModified extends ShopgateConfig {
	protected $country;
	protected $language;
	protected $currency;
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
		$this->enable_clear_logfile = 1;
		$this->encoding = 'ISO-8859-15';
		
		// initialize plugin specific stuff
		$this->country = 'DE';
		$this->language = 'de';
		$this->currency = 'EUR';
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
	
	
	public function getCountry() {
		return $this->country;
	}
	
	public function getLanguage() {
		return $this->language;
	}
	
	public function getCurrency() {
		return $this->currency;
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
	
	
	public function setCountry($value) {
		$this->country = $value;
	}
	
	public function setLanguage($value) {
		$this->language = $value;
	}
	
	public function setCurrency($value) {
		$this->currency = $value;
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