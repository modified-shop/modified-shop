<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 14:27:56 +0200 (Thu, 06 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: sofort_lastschrift.php 3761 2012-10-10 12:39:54Z gtb-modified $
 */

require_once(DIR_FS_CATALOG.'callback/sofort/sofort.php');
require_once(DIR_FS_CATALOG.'callback/sofort/library/sofortLib.php');

class sofort_lastschrift extends sofort{

	var $code, $title, $description, $enabled, $sofort, $paymentMethod;

	function sofort_lastschrift() {
		global $order;
		
		parent::sofort(); //call parent constructor

		$this->_checkExistingSofortConstants('ls');
		
		if(isset($_SESSION['sofort']['sofort_conditions_ls'])) unset($_SESSION['sofort']['sofort_conditions_ls']);

		$this->code = 'sofort_lastschrift';
		$this->title = MODULE_PAYMENT_SOFORT_LS_TEXT_TITLE;
		$this->title_extern = MODULE_PAYMENT_SOFORT_LASTSCHRIFT_TEXT_TITLE;
		$this->paymentMethod = 'LS';

		if (MODULE_PAYMENT_SOFORT_LS_RECOMMENDED_PAYMENT == 'True') {
			$this->title_extern .= ' ' . MODULE_PAYMENT_SOFORT_LS_RECOMMENDED_PAYMENT_TEXT ;
		}

		$this->description = MODULE_PAYMENT_SOFORT_LS_TEXT_DESCRIPTION.'<br />'.MODULE_PAYMENT_SOFORT_MULTIPAY_VERSIONNUMBER.': '.HelperFunctions::getSofortmodulVersion();
		$this->sort_order = MODULE_PAYMENT_SOFORT_LS_SORT_ORDER;
		$this->enabled = ((MODULE_PAYMENT_SOFORT_LS_STATUS == 'True') ? true : false);
		$this->icons_available = '';
		
		if (is_object($order)) {
			$this->update_status();
		}
		$this->defaultCurrency = DEFAULT_CURRENCY;
		$this->sofort = new SofortLib_Multipay(MODULE_PAYMENT_SOFORT_MULTIPAY_APIKEY);
		$this->sofort->setVersion(HelperFunctions::getSofortmodulVersion());
	}


	function selection () {
		global $order;
		
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
				$title .= $this->setImageText(MODULE_PAYMENT_SOFORT_LS_LOGO, MODULE_PAYMENT_SOFORT_LS_CHECKOUT_TEXT);
				break;
			case 'Infographic':
				$title .= $this->setImageText(MODULE_PAYMENT_SOFORT_LS_BANNER, '');
				break;
		}

		$cost = '';
		if(array_key_exists('ot_sofort',  $GLOBALS)) {
			$cost = $GLOBALS['ot_sofort']->get_percent($this->code, 'price');
		}

		$conditionsChecked = false;
		if(isset($_SESSION['sofort']['sofort_conditions_ls']) && $_SESSION['sofort']['sofort_conditions_ls'] == 'sofort_conditions_ls') {
			$conditionsChecked = true;
		}

		$fields = array(
			array('title' => MODULE_PAYMENT_SOFORT_LS_TEXT_HOLDER,
					'field' => xtc_draw_input_field('ls_sender_holder', array_key_exists('ls_sender_holder', $_SESSION['sofort']) ? strip_tags($_SESSION['sofort']['ls_sender_holder']) : strip_tags($order->billing['firstname'] . ' ' . $order->billing['lastname']))),
			array('title' => MODULE_PAYMENT_SOFORT_LS_TEXT_ACCOUNT_NUMBER,
					'field' => xtc_draw_input_field('ls_account_number', array_key_exists('ls_account_number', $_SESSION['sofort']) ? strip_tags($_SESSION['sofort']['ls_account_number']) : '')),
			array('title' => MODULE_PAYMENT_SOFORT_LS_TEXT_BANK_CODE,
					'field' => xtc_draw_input_field('ls_bank_code', array_key_exists('ls_bank_code', $_SESSION['sofort']) ? strip_tags($_SESSION['sofort']['ls_bank_code']) : '')),
			array('title' => MODULE_PAYMENT_SOFORT_LS_CHECKOUT_CONDITIONS,
					'field' => xtc_draw_checkbox_field('sofort_conditions_ls', 'sofort_conditions_ls', $conditionsChecked))
			);

		//commerce:SEO - Bugfix
		if (isset($_REQUEST['xajax']) && !empty($_REQUEST['xajax'])) {
			$fields[0]['title'] = utf8_decode($fields[0]['title']); //holder
			$fields[1]['title'] = utf8_decode($fields[1]['title']); //account-nr
			$fields[2]['title'] = utf8_decode($fields[2]['title']); //bankcode
			$fields[3]['title'] = utf8_decode($fields[3]['title']); //conditions
			return array('id' => $this->code , 'module' => utf8_decode($this->title_extern), 'fields' => $fields, 'description' => utf8_decode($title), 'module_cost' => utf8_decode($cost));
		}else{
			return array('id' => $this->code , 'module' => $this->title_extern , 'fields' => $fields, 'description' => $title, 'module_cost' => $cost);
		}
	}


	function setImageText($image, $text) {
		$lng = HelperFunctions::getShortCode($_SESSION['language']);
		$image = 'https://images.sofort.com/'.$lng.'/ls/'.$image;

		$image = xtc_image($image, MODULE_PAYMENT_SOFORT_LS_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT);
		$title = MODULE_PAYMENT_SOFORT_LS_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE;
		return $title;
	}


	function pre_confirmation_check ($vars = '') {

		parent::pre_confirmation_check ($vars);

		//in CommerceSEO check is done with Ajax
		if (isset ($_POST['xajax']) && $_POST['xajax'] == 'updatePaymentModule' ) {
			$data_arr = $vars;
			if (!$data_arr['sofort_conditions_ls'] == 'sofort_conditions_ls') {
				unset($_SESSION['sofort']['sofort_conditions_ls']);
			}
			$is_ajax = true;
		} else {
			$data_arr = $_POST;
		}

		$data_arr['ls_sender_holder'] 	= trim ($data_arr['ls_sender_holder']);
		$data_arr['ls_account_number']	= trim ($data_arr['ls_account_number']);
		$data_arr['ls_bank_code'] 		= trim ($data_arr['ls_bank_code']);

		if ($data_arr['ls_sender_holder']) {
			$_SESSION['sofort']['ls_sender_holder'] = $data_arr['ls_sender_holder'];
		}
		if ($data_arr['ls_account_number']) {
			$_SESSION['sofort']['ls_account_number'] = $data_arr['ls_account_number'];
		}
		if ($data_arr['ls_bank_code']) {
			$_SESSION['sofort']['ls_bank_code'] = $data_arr['ls_bank_code'];
		}
		if ($data_arr['sofort_conditions_ls']) {
			$_SESSION['sofort']['sofort_conditions_ls'] = $data_arr['sofort_conditions_ls'];
		}
		$errorCodes = array();
		$errorFound = false;
		$payment_ajax_error_return = '';
		if ($data_arr['sofort_conditions_ls'] != 'sofort_conditions_ls' && $_SESSION['sofort']['sofort_conditions_ls'] != 'sofort_conditions_ls') {
			$payment_ajax_error_return = '&payment_error='.$this->code.'&error='.urlencode(MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_10002);
			$errorCodes[] = '10002';
			$errorFound = true;
		}

		if ( (!$data_arr['ls_sender_holder'] && !$_SESSION['sofort']['ls_sender_holder']) ||
			 (!$data_arr['ls_account_number'] && !$_SESSION['sofort']['ls_account_number']) ||
			 (!$data_arr['ls_bank_code'] && !$_SESSION['sofort']['ls_bank_code']) ) {
			$payment_ajax_error_return =
				'&payment_error='.$this->code.
				'&error='.urlencode(MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_10001).
				'&ls_sender_holder='.urlencode($data_arr['ls_sender_holder']).
				'&ls_account_number='.urlencode($data_arr['ls_account_number']).
				'&ls_bank_code='.urlencode($data_arr['ls_bank_code']);
			$errorFound = true;
			$errorCodes[] = '10001';
		}

		if ($errorFound) {
			if ($is_ajax) {
				$_SESSION['checkout_payment_error'] = $payment_ajax_error_return;
			} else {
				$error_string = 'payment_error='.$this->code.'&error_codes='.implode(',', $errorCodes);
				xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $error_string, 'SSL', true, false));
			}
		}

		return false;
	}


	function install() {
		$sofortStatuses = $this->_insertAndReturnSofortStatus();
		$confirmedStatus = 	(isset($sofortStatuses['confirmed'])&& !empty($sofortStatuses['confirmed']))? $sofortStatuses['confirmed'] : '';

		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_SOFORT_LS_STATUS', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SOFORT_LS_SORT_ORDER', '0', '6', '20', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_SOFORT_LS_RECOMMENDED_PAYMENT', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_SOFORT_LS_ORDER_STATUS_ID', '".HelperFunctions::escapeSql($confirmedStatus)."',  '6', '10', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SOFORT_LASTSCHRIFT_ALLOWED', '', '6', '0', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_SOFORT_LS_ZONE', '0', '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");

		//install shared keys, that are used by all/most multipay-modules
		parent::install();
	}


	function remove() {
		xtc_db_query("delete from " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " where configuration_key LIKE 'MODULE_PAYMENT_SOFORT_LS%'");
		xtc_db_query("delete from " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " where configuration_key LIKE 'MODULE_PAYMENT_SOFORT_LASTSCHRIFT%'");

		//if this is the last removing of a multipay-paymentmethod --> we also remove all shared keys, that are used by all/most multipay-modules
		parent::remove();
	}


	function keys() {
		
		parent::keys();
		
		return array('MODULE_PAYMENT_SOFORT_LS_STATUS',
		'MODULE_PAYMENT_SOFORT_MULTIPAY_APIKEY',
		'MODULE_PAYMENT_SOFORT_MULTIPAY_AUTH',
		'MODULE_PAYMENT_SOFORT_LS_RECOMMENDED_PAYMENT',
		'MODULE_PAYMENT_SOFORT_LASTSCHRIFT_ALLOWED' ,
		'MODULE_PAYMENT_SOFORT_LS_ZONE' ,
		'MODULE_PAYMENT_SOFORT_MULTIPAY_REASON_1',
		'MODULE_PAYMENT_SOFORT_MULTIPAY_TEXT_REASON_2',
		'MODULE_PAYMENT_SOFORT_LS_ORDER_STATUS_ID',
		'MODULE_PAYMENT_SOFORT_MULTIPAY_CHECK_STATUS_ID',
		'MODULE_PAYMENT_SOFORT_LS_SORT_ORDER');
	}
}
?>