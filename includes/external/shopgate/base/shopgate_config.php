<?php
/*
* Shopgate GmbH
*
* URHEBERRECHTSHINWEIS
*
* Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
* zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
* Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
* öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
* schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
*
* COPYRIGHT NOTICE
*
* This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
* for the purpose of facilitating communication between the IT system of the customer and the IT system
* of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
* transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
* of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
*
*  @author Shopgate GmbH <interfaces@shopgate.com>
*/

##### XTCM BOF #####
include_once DIR_FS_CATALOG.'includes/external/shopgate/shopgate_library/shopgate.php';
##### XTCM EOF #####

##### XTC3 | XTCM | osCommerce | ZenCart BOF #####
//
//
//
##### XTC3 | XTCM | osCommerce | ZenCart EOF #####

##### XTC3 | XTCM | GambioGX BOF #####
define('SHOPGATE_SETTING_EXPORT_DESCRIPTION', 0);
define('SHOPGATE_SETTING_EXPORT_SHORTDESCRIPTION', 1);
define('SHOPGATE_SETTING_EXPORT_DESCRIPTION_SHORTDESCRIPTION', 2);
define('SHOPGATE_SETTING_EXPORT_SHORTDESCRIPTION_DESCRIPTION', 3);
##### XTC3 | XTCM | GambioGX EOF #####

##### XTCM BOF #####
class ShopgateConfigModified extends ShopgateConfig {
##### XTCM EOF #####
	protected $redirect_languages;
##### XTC3 | XTCM | GambioGX BOF #####
	protected $shipping;
##### XTC3 | XTCM | GambioGX EOF #####
##### XTC3 | XTCM | GambioGX | osCommerce (Non-US) | ZenCart BOF #####
	protected $tax_zone_id;
##### XTC3 | XTCM | GambioGX | osCommerce (Non-US) | ZenCart EOF #####
	protected $customers_status_id;
	protected $customer_price_group;
##### XTC3 | XTCM | osCommerce | ZenCart BOF #####
//
##### XTC3 | XTCM | osCommerce | ZenCart EOF #####
	protected $order_status_open;
	protected $order_status_shipped;
	protected $order_status_shipping_blocked;
	protected $order_status_cancled;
##### XTC3 | XTCM | GambioGX BOF #####
	protected $reverse_categories_sort_order;
	protected $reverse_items_sort_order;
	protected $export_description_type;
##### XTC3 | XTCM | GambioGX EOF #####
##### GambioGX | osCommerce | ZenCart BOF #####
	protected $shopgate_table_version;
##### GambioGX | osCommerce | ZenCart EOF #####
	protected $maximum_category_export_depth;
	protected $send_order_confirmation_mail;
	protected $export_new_products_category;
	protected $export_new_products_category_id;
	protected $export_special_products_category;
	protected $export_special_products_category_id;

	public function startup() {
		// overwrite some library defaults
##### XTCM BOF #####
		$this->plugin_name = 'Modified';
##### XTCM EOF #####
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
		$this->enable_get_settings = 1;
		$this->enable_check_cart = 1;
		$this->enable_register_customer = 1;
##### XTC3 | XTCM | GambioGX BOF #####
		$this->shop_is_active = 1;
##### XTC3 | XTCM | GambioGX EOF #####
		
##### XTC3 | XTCM | GambioGX BOF #####
		$this->encoding = 'ISO-8859-15';
//
//
//
//
//
##### XTC3 | XTCM | GambioGX EOF #####
		
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
##### XTC3 | XTCM | GambioGX BOF #####
		$this->shipping = '';
##### XTC3 | XTCM | GambioGX EOF #####
##### XTC3 | XTCM | GambioGX BOF #####
		$this->tax_zone_id = 5;
##### XTC3 | XTCM | GambioGX EOF #####
		$this->customers_status_id = 1;
		$this->customer_price_group = 0;
##### XTC3 | XTCM | osCommerce | ZenCart BOF #####
//
##### XTC3 | XTCM | osCommerce | ZenCart EOF #####
		$this->order_status_open = 1;
		$this->order_status_shipped = 3;
		$this->order_status_shipping_blocked = 1;
##### XTC3 | XTCM | osCommerce | ZenCart BOF #####
		$this->order_status_cancled = 0;
##### XTC3 | XTCM | osCommerce | ZenCart EOF #####
##### XTC3 | XTCM | GambioGX BOF #####
		$this->reverse_categories_sort_order = false;
		$this->reverse_items_sort_order = false;
		$this->export_description_type = SHOPGATE_SETTING_EXPORT_DESCRIPTION;
##### XTC3 | XTCM | GambioGX EOF #####
##### GambioGX | osCommerce | ZenCart BOF #####
		$this->shopgate_table_version = '';
##### GambioGX | osCommerce | ZenCart EOF #####
		$this->maximum_category_export_depth = '';
		$this->send_order_confirmation_mail = false;
		$this->export_new_products_category 		= 0;
		$this->export_new_products_category_id 		= 999;
		$this->export_special_products_category 	= 0;
		$this->export_special_products_category_id 	= 1000;
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
	
##### XTC3 | XTCM | GambioGX BOF #####
	public function getShipping() {
		return $this->shipping;
	}
##### XTC3 | XTCM | GambioGX EOF #####
	
##### XTC3 | XTCM | GambioGX | osCommerce (Non-US) BOF #####
	public function getTaxZoneId() {
		return $this->tax_zone_id;
	}
##### XTC3 | XTCM | GambioGX | osCommerce (Non-US) EOF #####
	
	public function getCustomersStatusId() {
		return $this->customers_status_id;
	}
	
	public function getCustomerPriceGroup() {
		return $this->customer_price_group;
	}
	
##### XTC3 | XTCM | osCommerce | ZenCart BOF #####
//
//
//
##### XTC3 | XTCM | osCommerce | ZenCart EOF #####
	
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
	
##### XTC3 | XTCM | GambioGX BOF #####
	public function getReverseCategoriesSortOrder() {
		return $this->reverse_categories_sort_order;
	}
##### XTC3 | XTCM | GambioGX EOF #####
	
##### XTC3 | XTCM | GambioGX BOF #####
	public function getReverseItemsSortOrder() {
		return $this->reverse_items_sort_order;
	}
##### XTC3 | XTCM | GambioGX EOF #####
	
##### XTC3 | XTCM | GambioGX BOF #####
	public function getExportDescriptionType() {
		return $this->export_description_type;
	}
##### XTC3 | XTCM | GambioGX EOF #####
	
##### XTC3 | XTCM | GambioGX | osCommerce | ZenCart BOF #####
	public function getShopgateTableVersion() {
		return $this->shopgate_table_version;
	}
##### XTC3 | XTCM | GambioGX | osCommerce | ZenCart EOF #####
	
	public function getMaximumCategoryExportDepth() {
		return $this->maximum_category_export_depth;
	}
	
	public function getSendOrderConfirmationMail(){
		return $this->send_order_confirmation_mail;
	}
	
	public function getExportNewProductsCategory(){
		return $this->export_new_products_category;
	}
	
	public function getExportNewProductsCategoryId() {
		return $this->export_new_products_category_id;
	}

	public function getExportSpecialProductsCategory() {
		return $this->export_special_products_category;
	}

	public function getExportSpecialProductsCategoryId() {
		return $this->export_special_products_category_id;
	}
	
	public function setRedirectLanguages($value) {
		$this->redirect_languages = $value;
	}
	
##### XTC3 | XTCM | GambioGX BOF #####
	public function setShipping($value) {
		$this->shipping = $value;
	}
##### XTC3 | XTCM | GambioGX EOF #####
	
##### XTC3 | XTCM | GambioGX | osCommerce (Non-US) BOF #####
	public function setTaxZoneId($value) {
		$this->tax_zone_id = $value;
	}
##### XTC3 | XTCM | GambioGX | osCommerce (Non-US) EOF #####
	
	public function setCustomersStatusId($value) {
		$this->customers_status_id = $value;
	}
	
	public function setCustomerPriceGroup($value) {
		$this->customer_price_group = $value;
	}
	
##### XTC3 | XTCM | osCommerce | ZenCart BOF #####
//
//
//
##### XTC3 | XTCM | osCommerce | ZenCart EOF #####
	
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
	
##### XTC3 | XTCM | GambioGX BOF #####
	public function setReverseCategoriesSortOrder($value) {
		$this->reverse_categories_sort_order = $value;
	}
##### XTC3 | XTCM | GambioGX EOF #####
	
##### XTC3 | XTCM | GambioGX BOF #####
	public function setReverseItemsSortOrder($value) {
		$this->reverse_items_sort_order = $value;
	}
##### XTC3 | XTCM | GambioGX EOF #####
	
##### XTC3 | XTCM | GambioGX BOF #####
	public function setExportDescriptionType($value) {
		$this->export_description_type = $value;
	}
##### XTC3 | XTCM | GambioGX EOF #####
	
##### XTC3 | XTCM | GambioGX | osCommerce | ZenCart BOF #####
	public function setShopgateTableVersion($value) {
		$this->shopgate_table_version = $value;
	}
##### XTC3 | XTCM | GambioGX | osCommerce | ZenCart EOF #####
	
	public function setMaximumCategoryExportDepth($value) {
		$this->maximum_category_export_depth = $value;
	}
	
	public function setSendOrderConfirmationMail($value){
		$this->send_order_confirmation_mail = $value;
	}
	
	public function setExportNewProductsCategory($value) {
		$this->export_new_products_category = $value;
	}

	public function setExportNewProductsCategoryId($value) {
		$this->export_new_products_category_id = $value;
	}

	public function setExportSpecialProductsCategory($value) {
		$this->export_special_products_category = $value;
	}

	public function setExportSpecialProductsCategoryId($value) {
		$this->export_special_products_category_id = $value;
	}

}
