<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-13 11:51:22 +0200 (Thu, 13 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: sofort_sofortvorkasse.php 3751 2012-10-10 08:36:20Z gtb-modified $
 */

require_once(DIR_FS_CATALOG.'callback/sofort/sofort.php');
require_once(DIR_FS_CATALOG.'callback/sofort/library/sofortLib.php');

class sofort_sofortvorkasse extends sofort{

	var $code, $title, $description, $enabled, $sofort, $paymentMethod;

	function sofort_sofortvorkasse() {
		global $order;
		
		parent::sofort();

		$this->_checkExistingSofortConstants('sv');
		
		if(isset($_SESSION['sofort']['sofort_conditions_sv'])) unset($_SESSION['sofort']['sofort_conditions_sv']);

		$this->code = 'sofort_sofortvorkasse';
		$this->title = MODULE_PAYMENT_SOFORT_SV_TEXT_TITLE ;
		$this->title_extern = MODULE_PAYMENT_SOFORT_SOFORTVORKASSE_TEXT_TITLE;
		$this->paymentMethod = 'SV';

		if(MODULE_PAYMENT_SOFORT_SV_KS_STATUS == 'True'){
			$this->title_extern = MODULE_PAYMENT_SOFORT_SV_KS_TEXT_TITLE;
		}

		if (MODULE_PAYMENT_SOFORT_SV_RECOMMENDED_PAYMENT == 'True') {
			$this->title_extern .= ' ' . MODULE_PAYMENT_SOFORT_SV_RECOMMENDED_PAYMENT_TEXT;
		}

		$this->description = MODULE_PAYMENT_SOFORT_SV_TEXT_DESCRIPTION.'<br />'.MODULE_PAYMENT_SOFORT_MULTIPAY_VERSIONNUMBER.': '.HelperFunctions::getSofortmodulVersion();
		$this->sort_order = MODULE_PAYMENT_SOFORT_SV_SORT_ORDER;
		$this->enabled = ((MODULE_PAYMENT_SOFORT_SV_STATUS == 'True') ? true : false);
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
		
		if(!isset($_SESSION['sofort']['sofort_sofortboxjs'])){
			$title = MODULE_PAYMENT_SOFORT_MULTIPAY_JS_LIBS;
			$_SESSION['sofort']['sofort_sofortboxjs'] = true;
		} else {
			$title = '';
		}
		
		switch (MODULE_PAYMENT_SOFORT_MULTIPAY_IMAGE) {
			case 'Logo & Text':
				if(MODULE_PAYMENT_SOFORT_SV_KS_STATUS == 'True') {
					$title .= $this->setImageText('logo_155x50.png', MODULE_PAYMENT_SOFORT_MULTIPAY_SV_CHECKOUT_TEXT);
				} else {
					$title .= $this->setImageText('logo_155x50.png', MODULE_PAYMENT_SOFORT_MULTIPAY_SV_CHECKOUT_TEXT);
				}
				break;
			case 'Infographic':
				if(MODULE_PAYMENT_SOFORT_SV_KS_STATUS == 'True') {
					$title .= $this->setImageText('banner_400x100_ks.png', '');
				} else {
					$title .= $this->setImageText('banner_300x100.png', '');
				}
				break;
		}

		$cost = '';
		if(array_key_exists('ot_sofort',  $GLOBALS)) {
			$cost = $GLOBALS['ot_sofort']->get_percent($this->code, 'price');
		}

		$conditionsChecked = false;
		if(isset($_SESSION['sofort']['sofort_conditions_sv']) && $_SESSION['sofort']['sofort_conditions_sv'] == 'sofort_conditions_sv') {
			$conditionsChecked = true;
		}

		$fields = array(
				array('title' => MODULE_PAYMENT_SOFORT_SV_CHECKOUT_CONDITIONS,
						'field' => xtc_draw_checkbox_field('sofort_conditions_sv', 'sofort_conditions_sv', $conditionsChecked))
		);

		//commerce:SEO - Bugfix
		if (isset($_REQUEST['xajax']) && !empty($_REQUEST['xajax'])) {
			$fields[0]['title'] = utf8_decode($fields[0]['title']);
			return array('id' => $this->code , 'module' => utf8_decode($this->title_extern), 'fields' => $fields, 'description' => utf8_decode($title), 'module_cost' => utf8_decode($cost));
		}else{
			return array('id' => $this->code , 'module' => $this->title_extern , 'fields' => $fields, 'description' => $title, 'module_cost' => $cost);
		}

	}

	function setImageText($image, $text) {
		$lng = HelperFunctions::getShortCode($_SESSION['language']);
		$image = 'https://images.sofort.com/'.$lng.'/sv/'.$image;
		
		$image = xtc_image($image, MODULE_PAYMENT_SOFORT_SV_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT);
		$title = MODULE_PAYMENT_SOFORT_SV_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE;
		$title = str_replace('{{image}}', $image, $title);
		$title = str_replace('{{text}}', $text, $title);
		return $title;
	}

	function pre_confirmation_check ($vars = '') {

		parent::pre_confirmation_check ($vars);

		//in CommerceSEO check is done with Ajax
		if (isset ($_POST['xajax']) && $_POST['xajax'] == 'updatePaymentModule' ) {
			$data_arr = $vars;
			if (!$data_arr['sofort_conditions_sv'] == 'sofort_conditions_sv') {
				unset($_SESSION['sofort']['sofort_conditions_sv']);
			}
			$is_ajax = true;
		} else {
			$data_arr = $_POST;
		}

		if ($data_arr['sofort_conditions_sv']) {
			$_SESSION['sofort']['sofort_conditions_sv'] = $data_arr['sofort_conditions_sv'];
		}

		if ($data_arr['sofort_conditions_sv'] != 'sofort_conditions_sv' && $_SESSION['sofort']['sofort_conditions_sv'] != 'sofort_conditions_sv') {
			if ($is_ajax) {
				$payment_error_return = 'payment_error='.$this->code.'&error='.urlencode(MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_10002);
				$_SESSION['checkout_payment_error'] = $payment_error_return;
			} else {
				$payment_error_return = 'payment_error='.$this->code.'&error_codes=10002';
				xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
			}
		}

		return false;
	}


	function install() {
		$sofortStatuses = $this->_insertAndReturnSofortStatus();
		$tempStatus = 		(isset($sofortStatuses['temp']) 	&& !empty($sofortStatuses['temp']))		? $sofortStatuses['temp'] : '';
		$confirmedStatus = 	(isset($sofortStatuses['confirmed'])&& !empty($sofortStatuses['confirmed']))? $sofortStatuses['confirmed'] : '';

		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_SOFORT_SV_STATUS', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_SOFORT_SV_KS_STATUS', 'False', '6', '100', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SOFORT_SV_SORT_ORDER', '0', '6', '20', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_SOFORT_SV_ORDER_STATUS_ID', '".HelperFunctions::escapeSql($confirmedStatus)."',  '6', '10', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_SOFORT_SV_TMP_STATUS_ID', '".HelperFunctions::escapeSql($tempStatus)."',  '6', '8', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_SOFORT_SV_RECOMMENDED_PAYMENT', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SOFORT_SOFORTVORKASSE_ALLOWED', '', '6', '0', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_SOFORT_SV_ZONE', '0', '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SOFORT_SV_REASON_2', '{{transaction_id}}', '6', '4', now())");

		//install shared keys, that are used by all/most multipay-modules
		parent::install();
	}


	function remove() {
		xtc_db_query("delete from " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " where configuration_key LIKE 'MODULE_PAYMENT_SOFORT_SV%'");
		xtc_db_query("delete from " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " where configuration_key LIKE 'MODULE_PAYMENT_SOFORT_SOFORTVORKASSE%'");

		//if this is the last removing of a multipay-paymentmethod --> we also remove all shared keys, that are used by all/most multipay-modules
		parent::remove();
	}


	function keys() {
		
		parent::keys();
		
		return array('MODULE_PAYMENT_SOFORT_SV_STATUS',
				'MODULE_PAYMENT_SOFORT_MULTIPAY_APIKEY',
				'MODULE_PAYMENT_SOFORT_MULTIPAY_AUTH',
				'MODULE_PAYMENT_SOFORT_SV_RECOMMENDED_PAYMENT',
				'MODULE_PAYMENT_SOFORT_SOFORTVORKASSE_ALLOWED' ,
				'MODULE_PAYMENT_SOFORT_SV_ZONE' ,
				'MODULE_PAYMENT_SOFORT_SV_REASON_2',
				'MODULE_PAYMENT_SOFORT_SV_TMP_STATUS_ID',
				'MODULE_PAYMENT_SOFORT_SV_ORDER_STATUS_ID',
				'MODULE_PAYMENT_SOFORT_MULTIPAY_CHECK_STATUS_ID',
				'MODULE_PAYMENT_SOFORT_SV_SORT_ORDER');
	}
}
?>