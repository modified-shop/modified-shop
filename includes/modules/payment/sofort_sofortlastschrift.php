<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 14:27:56 +0200 (Thu, 06 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: sofort_sofortlastschrift.php 3751 2012-10-10 08:36:20Z gtb-modified $
 */

require_once(DIR_FS_CATALOG.'callback/sofort/sofort.php');
require_once(DIR_FS_CATALOG.'callback/sofort/library/sofortLib.php');

class sofort_sofortlastschrift extends sofort{

	var $code, $title, $description, $enabled, $sofort, $paymentMethod;

	function sofort_sofortlastschrift() {
		global $order;
		
		parent::sofort();

		$this->_checkExistingSofortConstants('sl');

		$this->code = 'sofort_sofortlastschrift';
		$this->title = MODULE_PAYMENT_SOFORT_SL_TEXT_TITLE;
		$this->title_extern = MODULE_PAYMENT_SOFORT_SOFORTLASTSCHRIFT_TEXT_TITLE;
		$this->paymentMethod = 'SL';

		if (MODULE_PAYMENT_SOFORT_SL_RECOMMENDED_PAYMENT == 'True') {
			$this->title_extern .= ' ' . MODULE_PAYMENT_SOFORT_SL_RECOMMENDED_PAYMENT_TEXT;
		}

		$this->description = MODULE_PAYMENT_SOFORT_SL_TEXT_DESCRIPTION.'<br />'.MODULE_PAYMENT_SOFORT_MULTIPAY_VERSIONNUMBER.': '.HelperFunctions::getSofortmodulVersion();
		$this->sort_order = MODULE_PAYMENT_SOFORT_SL_SORT_ORDER;
		$this->enabled = ((MODULE_PAYMENT_SOFORT_SL_STATUS == 'True') ? true : false);
		$this->icons_available = '';
		
		if (is_object($order)) {
			$this->update_status();
		}
		$this->defaultCurrency = DEFAULT_CURRENCY;
		$this->sofort = new SofortLib_Multipay(MODULE_PAYMENT_SOFORT_MULTIPAY_APIKEY);
		$this->sofort->setVersion(HelperFunctions::getSofortmodulVersion());
	}


	function selection () {
		
		if (!parent::selection()) {
			$this->enabled = false;
			return false;
		}
		
		$title = '';

		switch (MODULE_PAYMENT_SOFORT_MULTIPAY_IMAGE) {
			case 'Logo & Text':
				$title = $this->setImageText('logo_155x50.png', MODULE_PAYMENT_SOFORT_SL_CHECKOUT_TEXT);
				break;
			case 'Infographic':
				$title = $this->setImageText('banner_300x100.png', '');
				break;
		}

		$cost = '';

		if(array_key_exists('ot_sofort',  $GLOBALS)) {
			$cost = $GLOBALS['ot_sofort']->get_percent($this->code, 'price');
		}
		
		//commerce:SEO - Bugfix
		if (isset($_REQUEST['xajax']) && !empty($_REQUEST['xajax'])) {
			return array('id' => $this->code , 'module' => utf8_decode($this->title_extern), 'description' => utf8_decode($title), 'module_cost' => utf8_decode($cost));
		}else{
			return array('id' => $this->code , 'module' => $this->title_extern , 'description' => $title, 'module_cost' => $cost);
		}
	}


	function setImageText($image, $text) {
		$lng = HelperFunctions::getShortCode($_SESSION['language']);
		$image = 'https://images.sofort.com/'.$lng.'/sl/'.$image;

		$image = xtc_image($image, MODULE_PAYMENT_SOFORT_SL_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT);
		$title = MODULE_PAYMENT_SOFORT_SL_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE;
		$title = str_replace('{{image}}', $image, $title);
		$title = str_replace('{{text}}', $text, $title);
		return $title;
	}


	function install() {
		$sofortStatuses = $this->_insertAndReturnSofortStatus();
		$confirmedStatus = 	(isset($sofortStatuses['confirmed'])&& !empty($sofortStatuses['confirmed']))? $sofortStatuses['confirmed'] : '';

		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_SOFORT_SL_STATUS', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SOFORT_SL_SORT_ORDER', '0', '6', '20', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_SOFORT_SL_RECOMMENDED_PAYMENT', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_SOFORT_SL_ORDER_STATUS_ID', '".HelperFunctions::escapeSql($confirmedStatus)."',  '6', '10', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SOFORT_SOFORTLASTSCHRIFT_ALLOWED', '', '6', '0', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_SOFORT_SL_ZONE', '0', '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");

		//install shared keys, that are used by all/most multipay-modules
		parent::install();
	}


	function remove() {
		xtc_db_query("delete from " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " where configuration_key LIKE 'MODULE_PAYMENT_SOFORT_SL%'");
		xtc_db_query("delete from " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " where configuration_key LIKE 'MODULE_PAYMENT_SOFORT_SOFORTLASTSCHRIFT%'");

		//if this is the last removing of a multipay-paymentmethod --> we also remove all shared keys, that are used by all/most multipay-modules
		parent::remove();
	}


	function keys() {
		
		parent::keys();
		
		return array('MODULE_PAYMENT_SOFORT_SL_STATUS',
		'MODULE_PAYMENT_SOFORT_MULTIPAY_APIKEY',
		'MODULE_PAYMENT_SOFORT_MULTIPAY_AUTH',
		'MODULE_PAYMENT_SOFORT_SL_RECOMMENDED_PAYMENT',
		'MODULE_PAYMENT_SOFORT_SOFORTLASTSCHRIFT_ALLOWED' ,
		'MODULE_PAYMENT_SOFORT_SL_ZONE' ,
		'MODULE_PAYMENT_SOFORT_MULTIPAY_REASON_1',
		'MODULE_PAYMENT_SOFORT_MULTIPAY_TEXT_REASON_2',
		'MODULE_PAYMENT_SOFORT_MULTIPAY_IMAGE',
		'MODULE_PAYMENT_SOFORT_SL_ORDER_STATUS_ID',
		'MODULE_PAYMENT_SOFORT_MULTIPAY_CHECK_STATUS_ID',
		'MODULE_PAYMENT_SOFORT_SL_SORT_ORDER');
	}
}
?>