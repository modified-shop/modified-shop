<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 14:27:56 +0200 (Thu, 06 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: sofort_sofortueberweisung.php 3751 2012-10-10 08:36:20Z gtb-modified $
 */

require_once(DIR_FS_CATALOG.'callback/sofort/sofort.php');
require_once(DIR_FS_CATALOG.'callback/sofort/library/sofortLib.php');

class sofort_sofortueberweisung extends sofort{

	var $code, $title, $description, $enabled, $sofort, $paymentMethod;

	function sofort_sofortueberweisung () {
		global $order;
		
		parent::sofort();
		
		$this->_checkExistingSofortConstants('su');

		$this->code = 'sofort_sofortueberweisung';
		$this->title = MODULE_PAYMENT_SOFORT_SU_TEXT_TITLE ;
		$this->title_extern = MODULE_PAYMENT_SOFORT_SOFORTUEBERWEISUNG_TEXT_TITLE;
		$this->paymentMethod = 'SU';

		if(MODULE_PAYMENT_SOFORT_SU_KS_STATUS == 'True'){
			$this->title_extern = MODULE_PAYMENT_SOFORT_SU_KS_TEXT_TITLE;
		}

		if(MODULE_PAYMENT_SOFORT_SU_RECOMMENDED_PAYMENT == 'True') {
			$this->title_extern .= ' ' . MODULE_PAYMENT_SOFORT_SU_RECOMMENDED_PAYMENT_TEXT;
		}

		$this->description = MODULE_PAYMENT_SOFORT_SU_TEXT_DESCRIPTION.'<br />'.MODULE_PAYMENT_SOFORT_MULTIPAY_VERSIONNUMBER.': '.HelperFunctions::getSofortmodulVersion();
		$this->sort_order = MODULE_PAYMENT_SOFORT_SU_SORT_ORDER;
		$this->enabled = ((MODULE_PAYMENT_SOFORT_SU_STATUS == 'True') ? true : false);
		$this->icons_available = '';

		if ((int) MODULE_PAYMENT_SOFORT_SU_ORDER_STATUS_ID > 0) {
			$this->order_status = MODULE_PAYMENT_SOFORT_SU_ORDER_STATUS_ID;
		}
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
				if(MODULE_PAYMENT_SOFORT_SU_KS_STATUS == 'True') {
					$title = $this->setImageText('logo_155x50.png', MODULE_PAYMENT_SOFORT_MULTIPAY_SU_CHECKOUT_TEXT_KS);
				} else {
					$title = $this->setImageText('logo_155x50.png', MODULE_PAYMENT_SOFORT_MULTIPAY_SU_CHECKOUT_TEXT);
				}
				break;
			case 'Infographic':
				if(MODULE_PAYMENT_SOFORT_SU_KS_STATUS == 'True') {
					$title = $this->setImageText('banner_400x100_ks.png', '');
				} else {
					$title = $this->setImageText('banner_300x100.png', '');
				}
				break;
		}
		
		//add ks-link, if ks is active
		$title = str_replace('[[link_beginn]]', '<a href="'.MODULE_PAYMENT_SOFORT_MULTIPAY_SU_CHECKOUT_INFOLINK_KS.'" target="_blank">', $title);
		$title = str_replace('[[link_end]]', '</a>', $title);

		$cost = '';
		if(array_key_exists('ot_sofort',  $GLOBALS)) {
			$cost = $GLOBALS['ot_sofort']->get_percent($this->code, 'price');
		}
		
		//commerce:SEO - Bugfix
		if (isset($_REQUEST['xajax']) && !empty($_REQUEST['xajax'])) {
			return array('id' => $this->code , 'module' => utf8_decode($this->title_extern), 'description' => utf8_decode($title), 'module_cost' => utf8_decode($cost));
		}else{
			return array('id' => $this->code , 'module' => $this->title_extern, 'description' => $title, 'module_cost' => $cost);
		}
	}


	function setImageText($image, $text) {
		$lng = HelperFunctions::getShortCode($_SESSION['language']);
		$image = 'https://images.sofort.com/'.$lng.'/su/'.$image;
		
		$image = xtc_image($image, MODULE_PAYMENT_SOFORT_SU_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT);
		$title = MODULE_PAYMENT_SOFORT_SU_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE;
		$title = str_replace('{{image}}', $image, $title);
		$title = str_replace('{{text}}', $text, $title);
		return $title;
	}


	function install () {
		$sofortStatuses = $this->_insertAndReturnSofortStatus();
		$confirmedStatus = 	(isset($sofortStatuses['confirmed'])&& !empty($sofortStatuses['confirmed']))? $sofortStatuses['confirmed'] : '';

		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_SOFORT_SU_STATUS', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_SOFORT_SU_KS_STATUS', 'False', '6', '100', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SOFORT_SOFORTUEBERWEISUNG_ALLOWED', '', '6', '0', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SOFORT_SU_SORT_ORDER', '0', '6', '20', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_SOFORT_SU_RECOMMENDED_PAYMENT', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_SOFORT_SU_ORDER_STATUS_ID', '".HelperFunctions::escapeSql($confirmedStatus)."',  '6', '10', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_SOFORT_SU_ZONE', '0', '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");

		//install shared keys, that are used by all/most multipay-modules
		parent::install();
	}

	function remove () {
		xtc_db_query("delete from " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " where configuration_key LIKE 'MODULE_PAYMENT_SOFORT_SU%'");
		xtc_db_query("delete from " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " where configuration_key LIKE 'MODULE_PAYMENT_SOFORT_SOFORTUEBERWEISUNG%'");

		//if this is the last removing of a multipay-paymentmethod --> we also remove all shared keys, that are used by all/most multipay-modules
		parent::remove();
	}

	function keys () {
		
		parent::keys();
		
		return array('MODULE_PAYMENT_SOFORT_SU_STATUS' ,
		'MODULE_PAYMENT_SOFORT_MULTIPAY_APIKEY',
		'MODULE_PAYMENT_SOFORT_MULTIPAY_AUTH',
		'MODULE_PAYMENT_SOFORT_SU_RECOMMENDED_PAYMENT',
		'MODULE_PAYMENT_SOFORT_SU_KS_STATUS',
		'MODULE_PAYMENT_SOFORT_SOFORTUEBERWEISUNG_ALLOWED' ,
		'MODULE_PAYMENT_SOFORT_SU_ZONE' ,
		'MODULE_PAYMENT_SOFORT_MULTIPAY_REASON_1',
		'MODULE_PAYMENT_SOFORT_MULTIPAY_TEXT_REASON_2' ,
		'MODULE_PAYMENT_SOFORT_MULTIPAY_IMAGE' ,
		'MODULE_PAYMENT_SOFORT_SU_ORDER_STATUS_ID' ,
		'MODULE_PAYMENT_SOFORT_MULTIPAY_CHECK_STATUS_ID',
		'MODULE_PAYMENT_SOFORT_SU_SORT_ORDER'
		);
	}
}
?>