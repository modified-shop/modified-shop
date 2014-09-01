<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-13 11:51:22 +0200 (Thu, 13 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: sofort.php 3770 2012-10-10 14:44:41Z gtb-modified $
 */

require_once(DIR_FS_CATALOG.'callback/sofort/helperFunctions.php');
require_once(HelperFunctions::getSofortOrderhandlingLink());

$language = HelperFunctions::getSofortLanguage($_SESSION['language']);
require_once(DIR_FS_CATALOG.'lang/'.$language.'/modules/payment/sofort_general.php');
/**
 * helper function to mask given array items
 * @param mixed $item
 */
function maskSpecialChars(&$item){
	HelperFunctions::htmlMaskArray($item);
}

/**
 * Superclass of xt-commerce modules
 */
class sofort {
	
	function sofort(){
		if (!isset($_SESSION['sofort'])) {
			$_SESSION['sofort'] = array();
		}
		
		$this->SofortOrderhandling = new SofortOrderhandling();
	}

	/**
	 * check if payment method is allowed in the payment zone
	 * if not: module will be disabled
	 */
	function update_status() {
		global $order;

		$constantValue = constant ('MODULE_PAYMENT_SOFORT_'.$this->paymentMethod.'_ZONE');
		if (($this->enabled == true) && ((int) $constantValue > 0)) {
			$check_flag = false;
			$check_query = xtc_db_query("SELECT zone_id FROM " . HelperFunctions::escapeSql(TABLE_ZONES_TO_GEO_ZONES) . " WHERE geo_zone_id = '" . HelperFunctions::escapeSql($constantValue) . "' and zone_country_id = '" . HelperFunctions::escapeSql($order->billing['country']['id']) . "' ORDER BY zone_id");
			while ($check = xtc_db_fetch_array($check_query)) {
				if ($check['zone_id'] < 1) {
					$check_flag = true;
					break;
				} elseif ($check['zone_id'] == $order->billing['zone_id']) {
					$check_flag = true;
					break;
				}
			}
			if ($check_flag == false) {
				$this->enabled = false;
			}
		}
	}


	function javascript_validation () {
		return false;
	}


	/**
	 * extended in all modules "by sofort"
	 */
	function pre_confirmation_check ($vars = '') {
	}

	
	/**
	 * call with parent::selection() in child
	 */
	function selection() {
		if (isset($_SESSION['sofort']['sofort_paymentSecret'])) unset($_SESSION['sofort']['sofort_paymentSecret']);
		if (isset($_SESSION['sofort']['sofort_transactionId'])) unset($_SESSION['sofort']['sofort_transactionId']);
		if (isset($_SESSION['sofort']['sofort_sofortboxjs'])) unset($_SESSION['sofort']['sofort_sofortboxjs']);
		
		if (isset($_SESSION['sofort']['apiKeyIsValid'])) {
			return $_SESSION['sofort']['apiKeyIsValid'];
		} else if (!isset($_SESSION['sofort']['apiKeyIsValid'])) {
			$apiTestResult = HelperFunctions::apiKeyIsValid(MODULE_PAYMENT_SOFORT_MULTIPAY_APIKEY);
			$_SESSION['sofort']['apiKeyIsValid'] = $apiTestResult;
			return $apiTestResult;
		}
		
		$_SESSION['sofort']['checkout_process'] = true;
	}

	function confirmation () {
		return false;
	}


	function process_button() {
		return false;
	}
	
	
	/**
	 * 1st job: send order-data to SOFORT and in case of no errors save $_SESSION in sofort_orders and redirect to SOFORT-payment-url
	 * 2nd job: manage orderInsertion, cleanup the module an other things while buyer came back from SOFORT after successful payment
	 * @return redirect to errorPage OR redirect to paymentUrl OR redirect to successURL
	 */
	function before_process() {
		global $order, $smarty;
	
		// if paymentSecret and transId are set, the customer has just successfully finished the SOFORT-wizard
		if (isset($_SESSION['sofort']['sofort_paymentSecret']) && 
				$_SESSION['sofort']['sofort_paymentSecret'] && 
				isset($_SESSION['sofort']['sofort_transactionId']) && 
				$_SESSION['sofort']['sofort_transactionId'] &&
				isset($_SESSION['sofort']['checkout_process']) &&
				$_SESSION['sofort']['checkout_process'] == false) {
			$transactionId = $_SESSION['sofort']['sofort_transactionId'];
			$paymentSecret = $_SESSION['sofort']['sofort_paymentSecret'];
			unset($_SESSION['sofort']['sofort_transactionId']);
			unset($_SESSION['sofort']['sofort_paymentSecret']);
			$this->_finalizeOrderprocessAfterSuccessfulPayment($transactionId, $paymentSecret);
		}
		
		if ($this->code == "sofort_sofortrechnung") {
			if ($this->_orderHasVirtualProducts($order) || $this->_deliveryAddressDoesNotExist($order)) {
				$errors = array(0 => array('code' => '10003'));
				xtc_redirect(HelperFunctions::getCancelUrl($this->code, $errors));
			}
		}
		
		$apiCallResult = $this->_makeSofortApiCall();
		$apiCallErrors = $apiCallResult['apiCallErrors'];
		$paymentUrl = $apiCallResult['paymentUrl'];
		$transactionId = $apiCallResult['transactionId'];
		$paymentSecret = $apiCallResult['paymentSecret'];
		$orderTotalModules = $apiCallResult['orderTotalModules'];
		$orderTotals = $apiCallResult['orderTotals'];
		
		if ($apiCallErrors) {
			xtc_redirect(HelperFunctions::getCancelUrl($this->code, $apiCallErrors));
		}
		
		$sessionDataToSave = array();
		$sessionDataToSave['smarty'] = $smarty;
		$sessionDataToSave['order'] = $order;
		$sessionDataToSave['orderTotalModules'] = $orderTotalModules;
		$sessionDataToSave['orderTotals'] = $orderTotals;
		$sessionDataToSave['session'] = $_SESSION;
		$sessionDataToSave['globals'] = $GLOBALS;
		
		$sofortOrdersId = HelperFunctions::insertSofortOrder(0, $paymentSecret, $transactionId, $this->paymentMethod, $sessionDataToSave);
		
		if (!$sofortOrdersId) {
			$errors = array(0 => array('code' => '10004')); //saving in sofort_orders failed
			xtc_redirect(HelperFunctions::getCancelUrl($this->code, $errors));
		}
		
		$_SESSION['sofort']['sofort_transactionId'] = $transactionId;
		$_SESSION['sofort']['sofort_paymentSecret'] = $paymentSecret;
		$_SESSION['sofort']['sofort_payment_url'] = $paymentUrl;
		$_SESSION['sofort']['sofort_payment_method'] = $this->code;
		
		/*
		if (ENABLE_SSL == true) {
			xtc_redirect(HTTPS_SERVER.DIR_WS_CATALOG.'callback/sofort/ressources/scripts/processSofortPayment.php');
		} else {
			xtc_redirect(HTTP_SERVER.DIR_WS_CATALOG.'callback/sofort/ressources/scripts/processSofortPayment.php');
		}
		*/
		
		xtc_redirect(xtc_href_link('callback/sofort/ressources/scripts/processSofortPayment.php', '', 'SSL'));
	}


	function get_error () {
		$this->_checkCancelOrder();
		
		if (!isset($_GET['payment_error']) || $_GET['payment_error'] != $this->code) {
			return false;
		}
		
		$this->enabled = false;
		
		$errormsgArray = array();
		$title = MODULE_PAYMENT_SOFORT_MULTIPAY_TEXT_ERROR_HEADING;
		
		if(isset($_GET['error_codes'])) {
			$langConstantExist = true;
			$errorCodes = array_unique (explode(',', HelperFunctions::htmlMask($_GET['error_codes'])));
			
			foreach ($errorCodes as $errorCode) {
				$errorCode = trim($errorCode);
				$code = substr($errorCode, 0, strpos($errorCode, '.'));
				
				if($code === false || empty($code)) {
					$code = $errorCode;
				}
				
				if (defined ('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_' . strtoupper($errorCode) ) ) {
					$errormsgArray[] = constant ('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_' . strtoupper($errorCode) ) . ' (' . $code . ')';
				} else if (defined('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_' . $code) ) {
					$errormsgArray[] = constant ('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_' . strtoupper($code) ) . ' (' . $code . ')';
				} else {
					$langConstantExist = false;
				}

				$dontDisableCodes = array('10000', '10001', '10002');
				
				if(in_array($code, $dontDisableCodes) ) {
					$this->enabled = true;
				}
			}
			
			if (!$errormsgArray && $langConstantExist == false) {
				$errormsgArray[] = constant('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_'.$this->paymentMethod);
			}
		}else{
			$errormsgArray[] = constant('MODULE_PAYMENT_SOFORT_'.$this->paymentMethod.'_TEXT_ERROR_MESSAGE');
		}
		
		if (!$errormsgArray) {
			$errormsgArray[] = constant('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_'.$this->paymentMethod);
		}

		$errormsgArray = array_unique($errormsgArray);

		return array('title' => $title,
			'error' => implode(' ', $errormsgArray) );
	}


	/**
	 * Module is active and set "enabled"?
	 */
	function check() {
		if (!isset($this->_check)) {
			$constantName = 'MODULE_PAYMENT_SOFORT_'.$this->paymentMethod.'_STATUS';
			$check_query = xtc_db_query("SELECT configuration_value FROM " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " WHERE configuration_key = '".HelperFunctions::escapeSql($constantName)."'");
			$this->_check = xtc_db_num_rows($check_query);
		}
		
		if($this->_check) {
			$this->_installSofortOrdersTable();
			$this->_installSofortOrdersNotificationTable();
			$this->_installSofortProductsTable();
			
			$check_query = xtc_db_query('SHOW COLUMNS FROM sofort_orders LIKE "serialized_session"');
			
			if (xtc_db_num_rows($check_query) == 0) {
				xtc_db_query('ALTER TABLE sofort_orders ADD serialized_session LONGTEXT COLLATE utf8_unicode_ci NOT NULL');
			}
			
			$check_query = xtc_db_query('SHOW COLUMNS FROM sofort_orders LIKE "data_acquired"');

				

			if (xtc_db_num_rows($check_query) == 0) {

				xtc_db_query('ALTER TABLE sofort_orders ADD data_acquired TINYINT(1) COLLATE utf8_unicode_ci NOT NULL');

			}
			
			$sofortStatuses = $this->_insertAndReturnSofortStatus();
			
			if(!defined('MODULE_PAYMENT_SOFORT_MULTIPAY_CHECK_STATUS_ID')) {
				$checkStatus = (isset($sofortStatuses['check']) && !empty($sofortStatuses['check'])) ? $sofortStatuses['check'] :  '';
				xtc_db_query("INSERT INTO ".HelperFunctions::escapeSql(TABLE_CONFIGURATION)." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_SOFORT_MULTIPAY_CHECK_STATUS_ID', '".HelperFunctions::escapeSql($checkStatus)."',  '6', '10', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
				define('MODULE_PAYMENT_SOFORT_MULTIPAY_CHECK_STATUS_ID', $checkStatus);
			}
			
			if(!defined('MODULE_PAYMENT_SOFORT_SR_UNCONFIRMED_STATUS_ID')) {
				$unconfirmedStatus = (isset($sofortStatuses['unconfirmed']) && !empty($sofortStatuses['unconfirmed'])) ? $sofortStatuses['unconfirmed'] :  '';
				xtc_db_query("INSERT INTO ".HelperFunctions::escapeSql(TABLE_CONFIGURATION)." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_SOFORT_SR_UNCONFIRMED_STATUS_ID', '".HelperFunctions::escapeSql($unconfirmedStatus)."',  '6', '10', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
				define('MODULE_PAYMENT_SOFORT_SR_UNCONFIRMED_STATUS_ID', $unconfirmedStatus);
			}
		}
		return $this->_check;
	}


	/**
	 * install shared keys, that are used by all/most multipay-modules
	 * called by module with parent::install();
	 */
	function install() {
		if(!defined('MODULE_PAYMENT_SOFORT_MULTIPAY_APIKEY')) {
			xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SOFORT_MULTIPAY_APIKEY', '',  '6', '4', now())");
			xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_SOFORT_MULTIPAY_AUTH', '".MODULE_PAYMENT_SOFORT_KEYTEST_DEFAULT."',  '6', '4', 'xtc_cfg_select_option(array(),', now())");  //hide the input-field with an empty <select>
			xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_SOFORT_MULTIPAY_REASON_1', '-TRANSACTION-',  '6', '4', 'xtc_cfg_select_option(array(\'Kd-Nr. {{customer_id}}\',\'-TRANSACTION-\'), ', now())");
			xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SOFORT_MULTIPAY_TEXT_REASON_2', '" . HelperFunctions::escapeSql(STORE_NAME) . "', '6', '4', now())");
			xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_SOFORT_MULTIPAY_IMAGE', 'Infographic',  '6', '6', 'xtc_cfg_select_option(array(\'Infographic\',\'Logo & Text\'), ', now())");
			
			$this->_installSofortOrdersTable();
			$this->_installSofortOrdersNotificationTable();
			$this->_installSofortProductsTable();
		}
		return true;
	}


	/**
	 * if this is the last removing of a multipay-paymentmethod --> we also remove all shared keys, that are used by all/most multipay-modules
	 * called by module with parent::remove()
	 */
	function remove() {
		$check_query = xtc_db_query("SELECT * FROM " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " WHERE configuration_key like 'MODULE_PAYMENT_SOFORT_%_STATUS'");
		if (xtc_db_num_rows ($check_query) === 0 ) {
			xtc_db_query("delete from " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " where configuration_key LIKE 'MODULE_PAYMENT_SOFORT_%'");
			
			//We don't want to delete data, but we could in some cases...
			//Notice: Sofort-orderhistory will also be deleted and cannot be shown anymore
			//$this->_removeSofortOrdersTable();
			//$this->_removeSofortOrdersNotificationTable();
			//$this->_removeSofortProductsTable();
		}
		return true;
	}
	
	
	function keys() {
		if (isset($_SESSION['sofort']['apiKeyIsValid'])) {
			unset($_SESSION['sofort']['apiKeyIsValid']);
		}
	}


	function _getGenderFromAddressBook($firstname, $lastname, $company, $streetAddress, $postcode, $city, $countryId, $zoneId) {
		$query = 'SELECT entry_gender
				  FROM	 ' . HelperFunctions::escapeSql(TABLE_ADDRESS_BOOK) . '
				  WHERE	 entry_firstname = "'.HelperFunctions::escapeSql($firstname) . '"
				  AND	 entry_lastname = "'.HelperFunctions::escapeSql($lastname) .'"
				  AND	 entry_company = "'.HelperFunctions::escapeSql($company) .'"
				  AND	 entry_street_address = "'.HelperFunctions::escapeSql($streetAddress) .'"
				  AND	 entry_postcode = "'.HelperFunctions::escapeSql($postcode) .'"
				  AND	 entry_city = "'.HelperFunctions::escapeSql($city) .'"
				  AND	 entry_country_id = "'.HelperFunctions::escapeSql($countryId) .'"
				  AND	 entry_zone_id = "'.HelperFunctions::escapeSql($zoneId).'" LIMIT 1';

		$sqlResult = xtc_db_query($query);
		$result = xtc_db_fetch_array($sqlResult);

		switch ($result['entry_gender']) {
			case 'm': $salutation = 2;
				break;
			case 'f': $salutation = 3;
				break;
			default:  $salutation = '';
				break;
		}
		return $salutation;
	}


	function _checkCancelOrder(){
		global $order;
		
		if (isset($_SESSION['sofort']['sofort_transactionId']) && $_SESSION['sofort']['sofort_transactionId'] && isset($_SESSION['sofort']['sofort_paymentSecret']) && $_SESSION['sofort']['sofort_paymentSecret']) {
			$transactionId = $_SESSION['sofort']['sofort_transactionId'];
			$paymentSecret = $_SESSION['sofort']['sofort_paymentSecret'];
			unset($_SESSION['sofort']['sofort_transactionId']);
			unset($_SESSION['sofort']['sofort_paymentSecret']);
			
			$this->SofortOrderhandling->deleteSavedSessionFromDb($transactionId, $paymentSecret);
		}
	}


	/**
	 * Check, if needed sofort-lang-constants exists.
	 * If not, include the english-lang-file(s).
	 * @return always true
	 */
	function _checkExistingSofortConstants($paymentMethod) {

		$paymentMethod = strtoupper($paymentMethod);

		$allowedPaymentMethods = array('SU', 'SL', 'LS', 'SR', 'SV');
		if(!in_array($paymentMethod, $allowedPaymentMethods)) {
			return true;
		}

		//security check - constant exists if lang-file exists
		if(defined('MODULE_PAYMENT_SOFORT_'.$paymentMethod.'_TEXT_TITLE')) {
			return true;
		}
		
		$lngdir = DIR_FS_CATALOG.'lang/';
		
		foreach (new DirectoryIterator($lngdir) as $file){
			if (file_exists($lngdir.$file->getFilename().'/modules/payment/sofort_general.php')) $installedModulLangs[] = $file->getFilename();
		}
		//currently installed in this module
		if(!in_array($_SESSION['language'], $installedModulLangs)) {
			switch ($paymentMethod) {
				case 'SU':
					include_once('lang/english/modules/payment/sofort_sofortueberweisung.php');
					break;
				case 'SV':
					include_once('lang/english/modules/payment/sofort_sofortvorkasse.php');
					break;
				case 'SL':
					include_once('lang/english/modules/payment/sofort_sofortlastschrift.php');
					break;
				case 'LS':
					include_once('lang/english/modules/payment/sofort_lastschrift.php');
					break;
				case 'SR':
					include_once('lang/english/modules/payment/sofort_sofortrechnung.php');
					break;
				default:
			}
		}
		return true;
	}


	function _insertAndReturnSofortStatus() {
		require_once('sofortInstall.php');
		
		//SOFORT-langs in this module
		$lngdir = DIR_FS_CATALOG.'lang/';

		
		foreach (new DirectoryIterator($lngdir) as $file){
		  if (is_dir($lngdir.$file->getFilename())) {
			  if (file_exists($lngdir.$file->getFilename().'/modules/payment/sofort_general.php')) $sofortLangs[] = strtoupper($file->getFilename());
		  }
		}
		
		//current installed langs
		$installedLangs = array();
		$orderQuery = xtc_db_query("select languages_id, directory from " . HelperFunctions::escapeSql(TABLE_LANGUAGES));
		while ($result = xtc_db_fetch_array($orderQuery)) {
			$installedLangs[$result['languages_id']] = strtoupper($result['directory']);
		}

		//get the current highest orders_status_id
		$orderQuery = xtc_db_query("SELECT MAX(orders_status_id) AS max_orders_status_id FROM orders_status");
		$maxOrdersStatusIdTemp = xtc_db_fetch_array($orderQuery);
		$orders_status_id_temp = $maxOrdersStatusIdTemp['max_orders_status_id'] + 1;
		$orders_status_id_confirmed = $orders_status_id_temp + 1;
		$orders_status_id_canceled = $orders_status_id_confirmed + 1;
		$orders_status_id_check = $orders_status_id_canceled + 1;
		$orders_status_id_unconfirmed = $orders_status_id_check + 1;
		$orders_status_id_invoice_confirmed = $orders_status_id_unconfirmed + 1;

		$sofortStatuses = array();
		foreach($installedLangs as $installedLang) {
			
			//insert english for languages, which are not included in this module
			if(in_array($installedLang, $sofortLangs)) {
				$sofortLang = $installedLang;
				$langId = array_search($sofortLang, $installedLangs);
			} else {
				$sofortLang = 'ENGLISH';
				$langId = array_search($installedLang, $installedLangs);
			}
			
			//insert temp-Status if not exists
			$newOrdersStatusName = $this->_getNewOrdersStatusName('temp', $sofortLang);
			$sofortStatuses['temp'] = $this->_getStatusIdIfExistInDb($newOrdersStatusName, $langId);
			
			if($sofortStatuses['temp'] === false || $sofortStatuses['temp'] == '') {
				$sofortStatuses['temp'] = $this->_insertStatusInDb($newOrdersStatusName, $orders_status_id_temp, $langId);
			}

			//insert confirmed-Status if not exists
			$newOrdersStatusName = $this->_getNewOrdersStatusName('confirmed', $sofortLang);
			$sofortStatuses['confirmed'] = $this->_getStatusIdIfExistInDb($newOrdersStatusName, $langId);
			
			if($sofortStatuses['confirmed'] === false || $sofortStatuses['confirmed'] == '') {
				$sofortStatuses['confirmed'] = $this->_insertStatusInDb($newOrdersStatusName, $orders_status_id_confirmed, $langId);
			}
			
			//insert canceled-Status if not exists
			$newOrdersStatusName = $this->_getNewOrdersStatusName('canceled', $sofortLang);
			$sofortStatuses['canceled'] = $this->_getStatusIdIfExistInDb($newOrdersStatusName, $langId);
			
			if($sofortStatuses['canceled'] === false || $sofortStatuses['canceled'] == '') {
				$sofortStatuses['canceled'] = $this->_insertStatusInDb($newOrdersStatusName, $orders_status_id_canceled, $langId);
			}
			
			//insert check-Status if not exists
			$newOrdersStatusName = $this->_getNewOrdersStatusName('check', $sofortLang);
			$sofortStatuses['check'] = $this->_getStatusIdIfExistInDb($newOrdersStatusName, $langId);
			
			if($sofortStatuses['check'] === false || $sofortStatuses['check'] == '') {
				$sofortStatuses['check'] = $this->_insertStatusInDb($newOrdersStatusName, $orders_status_id_check, $langId);
			}
			
			//insert unconfirmed-Status if not exists
			$newOrdersStatusName = $this->_getNewOrdersStatusName('unconfirmed', $sofortLang);
			$sofortStatuses['unconfirmed'] = $this->_getStatusIdIfExistInDb($newOrdersStatusName, $langId);
			
			if($sofortStatuses['unconfirmed'] === false || $sofortStatuses['unconfirmed'] == '') {
				$sofortStatuses['unconfirmed'] = $this->_insertStatusInDb($newOrdersStatusName, $orders_status_id_unconfirmed, $langId);
			}
			
			//insert invoice-confirmed-Status if not exists
			$newOrdersStatusName = $this->_getNewOrdersStatusName('invoice_confirmed', $sofortLang);
			$sofortStatuses['invoice_confirmed'] = $this->_getStatusIdIfExistInDb($newOrdersStatusName, $langId);
			
			if($sofortStatuses['invoice_confirmed'] === false || $sofortStatuses['invoice_confirmed'] == '') {
				$sofortStatuses['invoice_confirmed'] = $this->_insertStatusInDb($newOrdersStatusName, $orders_status_id_invoice_confirmed, $langId);
			}
		}
		return  $sofortStatuses;
	}


	function _getStatusIdIfExistInDb($newOrdersStatusName, $langId) {
		if(!$newOrdersStatusName) return false;
		
		$checkQuery = xtc_db_query('SELECT orders_status_id 
				FROM orders_status 
				WHERE language_id = "' . HelperFunctions::escapeSql($langId) . '" 
				AND orders_status_name = "' . HelperFunctions::escapeSql($newOrdersStatusName) . '" 
				LIMIT 1');
		
		if (xtc_db_num_rows($checkQuery) < 1) {
			return false;
		} else {
			$neededOrdersStatusId = xtc_db_fetch_array($checkQuery);
			return $neededOrdersStatusId['orders_status_id'];
		}
	}


	/**
	 * insert given statusstring into DB - empty strings will not be inserted
	 * @return $orders_status_id from DB OR false
	 */
	function _insertStatusInDb($newOrdersStatusName, $orders_status_id, $langId) {
		if (!$newOrdersStatusName) return false;
		xtc_db_query("INSERT INTO orders_status (orders_status_id, language_id, orders_status_name)
			values ('".HelperFunctions::escapeSql($orders_status_id)."', '".HelperFunctions::escapeSql($langId)."', '".HelperFunctions::escapeSql($newOrdersStatusName)."')");
		return $orders_status_id;
	}


	/**
	 * returns the statusname for the given $status and $lang
	 * return max the first 32 chars! (because db-field = varchar(32) )
	 */
	function _getNewOrdersStatusName($status, $lang) {
		switch ($status) {
			case 'temp':
				$newOrdersStatusName = constant('MODULE_PAYMENT_SOFORT_MULTIPAY_STATE_TEMP_'.$lang);
				break;
			case 'confirmed':
				$newOrdersStatusName = constant('MODULE_PAYMENT_SOFORT_MULTIPAY_STATE_CONFIRMED_'.$lang);
				break;
			case 'invoice_confirmed':
				$newOrdersStatusName = constant('MODULE_PAYMENT_SOFORT_MULTIPAY_STATE_INVOICE_CONFIRMED_'.$lang);
				break;
			case 'unconfirmed':
				$newOrdersStatusName = constant('MODULE_PAYMENT_SOFORT_MULTIPAY_STATE_UNCONFIRMED_'.$lang);
				break;
			case 'canceled':
				$newOrdersStatusName = constant('MODULE_PAYMENT_SOFORT_MULTIPAY_STATE_CANCELED_'.$lang);
				break;
			case 'check':
				$newOrdersStatusName = constant('MODULE_PAYMENT_SOFORT_MULTIPAY_STATE_CHECK_'.$lang);
				break;
		}
		// if string is not cut to 32 chars, status will be reinserted with every installation
		$newOrdersStatusName = substr($newOrdersStatusName, 0, 32);

		return $newOrdersStatusName;
	}
	
	
	function _installSofortOrdersTable() {
		$sql = '
			CREATE TABLE IF NOT EXISTS `sofort_orders` (
				`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`orders_id` int(11) unsigned NOT NULL,
				`transaction_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
				`payment_method` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
				`payment_secret` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
				`date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`serialized_session` LONGTEXT COLLATE utf8_unicode_ci NOT NULL,
				`data_acquired` TINYINT(1) COLLATE utf8_unicode_ci NOT NULL,
				PRIMARY KEY (`id`)
			)
		';
		xtc_db_query($sql);
	}
	
	
	function _removeSofortOrdersTable() {
		$sql = 'DROP TABLE `sofort_orders`';
		xtc_db_query($sql);
	}


	function _installSofortOrdersNotificationTable() {
		$sql = '
			CREATE TABLE IF NOT EXISTS `sofort_orders_notification` (
				`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`sofort_orders_id` int(11) unsigned NOT NULL,
				`items` text COLLATE utf8_unicode_ci NOT NULL,
				`amount` float NOT NULL,
				`customer_comment` text COLLATE utf8_unicode_ci NOT NULL,
				`seller_comment` text COLLATE utf8_unicode_ci NOT NULL,
				`status_id` int(11) unsigned NOT NULL,
				`status` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
				`status_reason` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
				`invoice_status` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
				`invoice_objection` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
				`date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`)
			)
		';
		xtc_db_query($sql);
	}


	function _removeSofortOrdersNotificationTable() {
		$sql = 'DROP TABLE `sofort_orders_notification`';
		xtc_db_query($sql);
	}
	
	
	function _installSofortProductsTable() {
		$sql = '
			CREATE TABLE IF NOT EXISTS `sofort_products` (
				`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`orders_id` int(11) unsigned NOT NULL,
				`orders_products_id` int(11) unsigned NOT NULL,
				`item_id` text COLLATE utf8_unicode_ci NOT NULL,
				PRIMARY KEY (`id`)
			)
		';
		xtc_db_query($sql);
	}


	function _removeSofortProductsTable() {
		$sql = 'DROP TABLE `sofort_products`';
		xtc_db_query($sql);
	}
	
	
	/**
	 * send data to SOFORT and check SOFORT-response - overwriten by Rbs-Modul
	 * @return array with paymentUrl, api-errors, trans-id, payment-secret
	 */
	function _makeSofortApiCall(){
		global $order, $xtPrice;

		$customer_id = $_SESSION['customer_id'];

		$currency = $_SESSION['currency'];

		$reasons = $this->_getReasons($this->paymentMethod, $customer_id, $order);
		
		$user_variable_0 = '';
		$user_variable_1 = $customer_id;

/*
		$session = session_name() . '=' . session_id();

		if (ENABLE_SSL == true)
			$server = HTTPS_SERVER;
		else
			$server = HTTP_SERVER;
*/
		
		$paymentSecret = md5(mt_rand().microtime());
		
		//important notice: following lines also modify the shippingcosts
		$orderTotals = array();
		if (MODULE_ORDER_TOTAL_INSTALLED) {
			require_once (DIR_WS_CLASSES.'order_total.php');
			$orderTotalModules = new order_total();
			//Following function-call manipulates variables (e.g. prices) within $order! Never call more than once!
			$orderTotals = $orderTotalModules->process();
		}
		
		$amount = $this->_getShopTotal($orderTotals);
		
		//$success_url = $server.DIR_WS_CATALOG.'callback/sofort/ressources/scripts/sofortReturn.php?sofortaction=success&sofortcode='.$this->code;
		//$cancel_url = $server.DIR_WS_CATALOG.'callback/sofort/ressources/scripts/sofortReturn.php?sofortaction=cancel&sofortcode='.$this->code;
		//$notification_url = $server . DIR_WS_CATALOG . 'callback/sofort/callback.php?paymentSecret='.$paymentSecret.'&action=multipay';

		$success_url = xtc_href_link('callback/sofort/ressources/scripts/sofortReturn.php', 'sofortaction=success&sofortcode='.$this->code, 'SSL');
		$cancel_url = xtc_href_link('callback/sofort/ressources/scripts/sofortReturn.php', 'sofortaction=cancel&sofortcode='.$this->code, 'SSL');
		$notification_url = xtc_href_link('callback/sofort/callback.php', 'paymentSecret='.$paymentSecret.'&action=multipay', 'SSL');


		$this->sofort->setAmount($amount, $currency);
		$this->sofort->setReason(HelperFunctions::convertEncoding($reasons[0],3), HelperFunctions::convertEncoding($reasons[1],3));
		$this->sofort->setSuccessUrl(HelperFunctions::convertEncoding($success_url,4));
		$this->sofort->setAbortUrl(HelperFunctions::convertEncoding($cancel_url,4));
		$this->sofort->setTimeoutUrl(HelperFunctions::convertEncoding($cancel_url,4));
		$this->sofort->setNotificationUrl(HelperFunctions::convertEncoding($notification_url,4));
		$this->sofort->addUserVariable(HelperFunctions::convertEncoding($user_variable_0,3));
		$this->sofort->addUserVariable(HelperFunctions::convertEncoding($user_variable_1,3));
		$this->sofort->setEmailCustomer(HelperFunctions::convertEncoding($order->customer['email_address'],3));
		$this->sofort->setPhoneNumberCustomer($order->customer['telephone']);

		switch($this->paymentMethod) {
			case 'SU' :
				$this->sofort->setSofortueberweisung($amount);
				// see if customer protection is enabled, set it as parameter to sofortlib
				$this->sofort->setSofortueberweisungCustomerprotection(MODULE_PAYMENT_SOFORT_SU_KS_STATUS == 'True');
				break;
			case 'SL' :
				$this->sofort->setSofortlastschrift();
				$this->sofort->setSenderAccount('', '', HelperFunctions::convertEncoding($order->customer['firstname'],3) . ' ' . HelperFunctions::convertEncoding($order->customer['lastname'],3));
				break;
			case 'LS' :
				$this->sofort->setLastschrift();
				$this->sofort->setSenderAccount(HelperFunctions::convertEncoding($_SESSION['sofort']['ls_bank_code'],3), HelperFunctions::convertEncoding($_SESSION['sofort']['ls_account_number'],3),  HelperFunctions::convertEncoding($_SESSION['sofort']['ls_sender_holder'],3));

				$billingSalutation = $this->_getGenderFromAddressBook($order->billing['firstname'], $order->billing['lastname'], $order->billing['company'], $order->billing['street_address'],
					$order->billing['postcode'], $order->billing['city'], $order->billing['country_id'], $order->billing['zone_id']);

				//split street and number
				if(!preg_match('#(.+)[ .](.+)#i', trim($order->billing['street_address']), $streetparts)) {
					$streetparts = array();
					$streetparts[1] = trim($order->billing['street_address']);
					$streetparts[2] = '';
				}
				//if there is an entry in "suburb" (german: "Adresszusatz"), put it in front of the streetname
				if ($order->billing['suburb']) {
					$streetparts[1] = $order->billing['suburb'] . ' - ' . $streetparts[1];
				}

				$this->sofort->setLastschriftAddress(HelperFunctions::convertEncoding($order->billing['firstname'],3), HelperFunctions::convertEncoding($order->billing['lastname'],3), HelperFunctions::convertEncoding($streetparts[1],3), HelperFunctions::convertEncoding($streetparts[2],3),  $order->billing['postcode'], HelperFunctions::convertEncoding($order->billing['city'],3), HelperFunctions::convertEncoding($billingSalutation,3), HelperFunctions::convertEncoding($order->billing['country']['iso_code_2'],3));
				break;
			case 'SV' :
				$this->sofort->setSofortvorkasse();
				// if this is called a 'test transaction', add a sender account
				if(getenv('test_sv') == true) {
					$this->sofort->setSenderAccount('00000', '12345', 'Tester Testaccount');
				}
				$this->sofort->setSofortvorkasseCustomerprotection(MODULE_PAYMENT_SOFORT_SV_KS_STATUS == 'True');
				break;
		}

		$this->sofort->sendRequest();

		$return = array();
		$return['apiCallErrors'] = $this->sofort->getErrors();
		$return['paymentUrl'] = $this->sofort->getPaymentUrl();
		$return['transactionId'] = $this->sofort->getTransactionId();
		$return['paymentSecret'] = $paymentSecret;
		$return['orderTotalModules'] = $orderTotalModules;
		$return['orderTotals'] = $orderTotals;
		return $return;
	}
	
	
	/**
	 * get shop order total
	 * @return float $shopEndprice
	 */
	function _getShopTotal($orderTotals) {
		global $order;
		
		//Frequent sources of errors: shipping-tax, external modules, sort order of shown 'ot_'-modules
		$ot_totalTotal = 0;
		
		if (MODULE_ORDER_TOTAL_INSTALLED) {
			foreach ($orderTotals as $oneTotal) {
				if ($oneTotal['code'] == 'ot_total') {
					$ot_totalTotal = $oneTotal['value'];
				}
			}
		}
		
		$orderObjectTotal = 0;
		
		if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
			$orderObjectTotal = $order->info['total'] + $order->info['tax'];
		} else {
			$orderObjectTotal = $order->info['total'];
		}
		
		//use the higher one
		$shopTotal = 0;
		
		if ($ot_totalTotal >= $orderObjectTotal) {
			$shopTotal = $ot_totalTotal;
		} else if ($orderObjectTotal > $ot_totalTotal) {
			$shopTotal = $orderObjectTotal;
		}
		
		$shopTotal = number_format($shopTotal, 2, '.','');
		
		return $shopTotal;
	}
	
	
	/**
	 * save bankdata in users history and return link to sv-bankdata-page
	 * @return string - link to sv-bankdata-page
	 */
	function _insertSvBankdataAndGetLinkToBankdataPage($orderId) {
		//save sofortvorkasse-bankdata in customer history and show bankdata-page
		$bankdata = 
			MODULE_PAYMENT_SOFORT_SV_CHECKOUT_HOLDER_TEXT.' '.HelperFunctions::htmlMask($_GET['holder']).' -- '.
			MODULE_PAYMENT_SOFORT_SV_CHECKOUT_ACCOUNT_NUMBER_TEXT.' '.HelperFunctions::htmlMask($_GET['account_number']).' -- '.
			MODULE_PAYMENT_SOFORT_SV_CHECKOUT_IBAN_TEXT.' '.HelperFunctions::htmlMask($_GET['iban']).' -- '.
			MODULE_PAYMENT_SOFORT_SV_CHECKOUT_BANK_CODE_TEXT.' '.HelperFunctions::htmlMask($_GET['bank_code']).' -- '.
			MODULE_PAYMENT_SOFORT_SV_CHECKOUT_BIC_TEXT.' '.HelperFunctions::htmlMask($_GET['bic']).' -- '.
			MODULE_PAYMENT_SOFORT_SV_CHECKOUT_AMOUNT_TEXT.' '.HelperFunctions::htmlMask($_GET['amount']).' -- '.
			MODULE_PAYMENT_SOFORT_SV_CHECKOUT_REASON_1_TEXT.' '.HelperFunctions::htmlMask($_GET['reason_1']).' -- '.
			MODULE_PAYMENT_SOFORT_SV_CHECKOUT_REASON_2_TEXT.' '.HelperFunctions::htmlMask($_GET['reason_2']);

		$sql_data_array = array(
				'orders_id' => $orderId,
				'orders_status_id' => DEFAULT_ORDERS_STATUS_ID,
				'date_added' => 'sqlcommand:now()',
				'customer_notified' => 0, //(SEND_EMAILS == 'true') ? '1' : '0',
				'comments' => $bankdata
		);
		xtc_db_query(HelperFunctions::getEscapedInsertInto(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array));
		
		/*
		//create link to bankdata-page and return this link
		$server = (ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER;
		
		$session = '&' . session_name() . '=' . session_id();
		$get = 'holder='.HelperFunctions::htmlMask($_GET['holder']).'&account_number='.HelperFunctions::htmlMask($_GET['account_number']).'&iban='.HelperFunctions::htmlMask($_GET['iban']).'&bank_code='.HelperFunctions::htmlMask($_GET['bank_code']).'&bic='.HelperFunctions::htmlMask($_GET['bic']).'&amount='.HelperFunctions::htmlMask($_GET['amount']).'&reason_1='.HelperFunctions::htmlMask($_GET['reason_1']).'&reason_2='.HelperFunctions::htmlMask($_GET['reason_2']);
		
		return $server.DIR_WS_CATALOG.'callback/sofort/ressources/scripts/confirmVorkasse.php?'.$get.$session;
		*/
		
		$get = 'vorkasse=sofort&holder='.HelperFunctions::htmlMask($_GET['holder']).'&account_number='.HelperFunctions::htmlMask($_GET['account_number']).'&iban='.HelperFunctions::htmlMask($_GET['iban']).'&bank_code='.HelperFunctions::htmlMask($_GET['bank_code']).'&bic='.HelperFunctions::htmlMask($_GET['bic']).'&amount='.HelperFunctions::htmlMask($_GET['amount']).'&reason_1='.HelperFunctions::htmlMask($_GET['reason_1']).'&reason_2='.HelperFunctions::htmlMask($_GET['reason_2']);
		//return xtc_href_link('callback/sofort/ressources/scripts/confirmVorkasse.php', $get, 'SSL');
	  return xtc_href_link(FILENAME_CHECKOUT_SUCCESS, $get, 'SSL');
	}
	
	
	/**
	 * manager for: save orderdata in shop-DB, emails, cleanup sofort-tables (if a notification has not done it before)
	 * @return nothing (always redirects)
	 */
	function _finalizeOrderprocessAfterSuccessfulPayment($transactionId, $paymentSecret) {
		//get serialized session
		$savedSession = $this->SofortOrderhandling->getSavedSessionData($transactionId, $paymentSecret);
		
		//Order was already saved
		if (!$savedSession){
			usleep(10000); //avoid race-conditions between success-url and notification and needless error-mails
			$orderId = $this->SofortOrderhandling->getOrderId($transactionId, $paymentSecret);
			
			if(!$orderId) {
				//saved sessiondata was not found and no order-id exists
				$errors = array(
						'Description' => 'Order could not be saved in shop-DB and orderdata could not be found.',
						'Transaction-ID' => $transactionId,
						'Customer-ID' => $_SESSION['customer_id'],
						'Paymentmethod' => $this->code
				);
				HelperFunctions::sendAdminErrorMail($errors);
				$errors = array(0 => array('code' => '10006')); //Fatal error: saving in sofort_orders failed, seller informed
				xtc_redirect(HelperFunctions::getCancelUrl($this->code, $errors));
			}else{
				// order was saved by notification
				$this->SofortOrderhandling->deleteShopSessionData();
				$this->SofortOrderhandling->deleteSofortSessionData();
				$this->_redirectToSuccessPage($orderId);
			}
		} else {
			$this->SofortOrderhandling->restoreGivenSessionDataToSession($savedSession);
			$insertData = $this->SofortOrderhandling->insertOrderIntoShop();
			$orderId = $insertData['orderId'];
			$sofortData = $insertData['sofortData'];
			
			if (!$orderId){
				xtc_db_query('UPDATE sofort_orders SET data_acquired = "0" WHERE payment_secret = "'.HelperFunctions::escapeSql($paymentSecret).'" AND transaction_id = "'.HelperFunctions::escapeSql($transactionId).'"');
				$errors = array(
						'description' => 'Order may not have been successfully saved in shop-DB or Order-ID is unknown. Please check the order for completeness!',
						'transactionId' => $transactionId,
						'paymentmethod' => $this->code,
						'customerId' => $_SESSION['customer_id'],
						'orderdata' => $savedSession
				);
				HelperFunctions::sendAdminErrorMail($errors);
				$errors = array(0 => array('code' => '10005')); //Fatal error: saving in sofort_orders might have failed, seller informed
				xtc_redirect(HelperFunctions::getCancelUrl($this->code, $errors));
			} else {
				//order was successfully saved, now delete serialized session from db, cleanup $_SESSION and send email to seller/customer
				//Notice: success-message will always be set by notification into history!
				
				$this->SofortOrderhandling->insertOrderIdInSofortTables($transactionId, $paymentSecret, $orderId);
				
				//save articleattributes (required for order-sync with SR)
				if ($this->code == 'sofort_sofortrechnung') {
					$this->SofortOrderhandling->insertOrderAttributesInSofortTables($orderId, $sofortData);
				}
				
				$this->SofortOrderhandling->deleteSavedSessionFromDb($transactionId, $paymentSecret);
				
				$this->SofortOrderhandling->insertTransIdInTableOrders($transactionId, $orderId);
				
				if ($this->code == 'sofort_sofortrechnung') {
					HelperFunctions::sendOrderIdToSofort(MODULE_PAYMENT_SOFORT_MULTIPAY_APIKEY, $transactionId, $orderId);
				}
				
				$this->SofortOrderhandling->deleteShopSessionData();
				$this->SofortOrderhandling->deleteSofortSessionData();
				
				if(SEND_EMAILS == 'true') {
					$this->SofortOrderhandling->sendOrderEmails($orderId);
				}
				
				$this->SofortOrderhandling->doSpecialThingsAfterSuccessfulInsertion();
				$this->_redirectToSuccessPage($orderId);
			}
		}
	}
	
	
	/**
	 * redirects directly to normal checkout_success-page (if SV: redirect to bankdata-page)
	 * @return nothing (always redirects)
	 */
	function _redirectToSuccessPage($orderId) {
		if ($this->code == 'sofort_sofortvorkasse'){
			$redirectUrl = $this->_insertSvBankdataAndGetLinkToBankdataPage($orderId);
			xtc_redirect($redirectUrl);
		}else{
			xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
		}
	}
	
	
	function _orderHasVirtualProducts($order) {
		if ($order->content_type == 'virtual') {
			return true;
		}
		
		if (is_object($_SESSION['cart'])) {
			if ($_SESSION['cart']->count_contents() != $_SESSION['cart']->count_contents_virtual()) {
				return true;
			}
		}
		
		//search for Gift-Voucher, they (must) start with "GIFT..."
		foreach ($order->products as $oneProduct) {
			if (strpos($oneProduct['model'], 'GIFT') === 0) {
				return true;
			}
		}
		
		//search for downloads
		if (is_object($_SESSION['cart'])) {
			$cartContents = $_SESSION['cart']->contents;
			reset($cartContents);
			
			if ($cartContents) {
				foreach ($cartContents as $key => $value){
					if (isset ($cartContents[$key]['attributes'])) {
						$productId = explode('{',$key);
						reset($cartContents[$key]['attributes']);
						
						foreach ($cartContents[$key]['attributes'] as $value) {
							$virtualCheck = xtc_db_fetch_array(
													xtc_db_query("SELECT count(*) AS total
																  FROM	 ".TABLE_PRODUCTS_ATTRIBUTES." pa,
																		 ".TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD." pad
																  WHERE	 pa.products_id = '".$productId[0]."'
																  AND	 pa.options_values_id = '".$value."'
																  AND	 pa.products_attributes_id = pad.products_attributes_id")
							);
							
							if ($virtualCheck['total'] > 0) {
								return true;
							}
						}
						
						for($i = 1; $i < count($productId); ++$i){
							$attributeId = explode('}',$productId[$i]);
							
							$stringCheckQry = xtc_db_query("SELECT	products_options_name
															FROM	".TABLE_PRODUCTS_OPTIONS."
															WHERE	products_options_id = ".$attributeId[0]);
							
							while ($stringCheckRes = xtc_db_fetch_array($stringCheckQry)){
								$stringCheck = $stringCheckRes['products_options_name'];
								
								if ($stringCheck == 'downloads' || $stringCheck == 'Downloads' || $stringCheck == 'download' || $stringCheck == 'Download'){
									return true;
								}
							}
						}
					}
				}
			}
		}
		
		//no virtual products found
		return false;
	}
	
	
	/**
	 * check if delivery address exists - doesnt exist, if there are only downloads in cart
	 * @return bool
	 */
	function _deliveryAddressDoesNotExist($order) {
		if (!is_object($order) || !isset($order->delivery) || !is_array($order->delivery)) {
			return true; //delivery address does not exist
		}
		
		$delivery = $order->delivery;
		if (!isset($delivery['firstname']) || !isset($delivery['lastname']) || !isset($delivery['company']) ||
				!isset($delivery['street_address']) || !isset($delivery['city']) || !isset($delivery['postcode']) || 
				!isset($delivery['country']['iso_code_2'])) {
			return true; //delivery address does not exist
		}
		
		if (empty($delivery['firstname']) && empty($delivery['lastname']) && empty($delivery['company']) &&
				empty($delivery['street_address']) && empty($delivery['city']) && empty($delivery['postcode']) && 
				empty($delivery['country']['iso_code_2'])) {
			return true; //delivery address does not exist
		}
		
		return false; //delivery address exists
	}
	
	
	/**
	 * replace special chars in $reason e.g. ä=>ae, ß=>ss incl. foreign non-ascii-signs etc.
	 * @param string $reason like 'Käufer A. Müller'
	 * @return string - converted String
	 */
	function _convertReason($reason) {
		$oldLocale = setlocale(LC_ALL, 0);
		setlocale(LC_ALL, "de_DE.utf8");
		$shopEncoding = HelperFunctions::getIniValue('shopEncoding');
		//$encoding = mb_detect_encoding('aaa'.$reason, "ISO-8859-1, UTF-8");
		$convertedReason = substr(iconv($shopEncoding, "ASCII//TRANSLIT", 'aaa'.$reason),3);
		setlocale(LC_ALL, $oldLocale);
		return $convertedReason;
	}
	
	
	/**
	 * get the reasons for the given payment method
	 */
	function _getReasons($paymentMethod, $customerId, $order) {
		
		if ($paymentMethod == 'SV') {  //SV has only one reason
			$reason_1 = str_replace('{{order_id}}', '', MODULE_PAYMENT_SOFORT_SV_REASON_2);
			$reason_1 = str_replace('{{customer_id}}', $customerId, $reason_1);
			$reason_1 = str_replace('{{transaction_id}}', '-TRANSACTION-', $reason_1);
			$reason_1 = $this->_convertReason($reason_1);
			$reason_1 = substr($reason_1, 0, 27);
			
			$reason_2 = ''; //SV has only one reason, the 2nd is set by SOFORT
		} else {
			$reason_1 = str_replace('{{order_id}}', '', MODULE_PAYMENT_SOFORT_MULTIPAY_REASON_1);
			$reason_1 = str_replace('{{customer_id}}', $customerId, $reason_1);
			$reason_1 = str_replace('{{transaction_id}}', '-TRANSACTION-', $reason_1);
			$reason_1 = $this->_convertReason($reason_1);
			$reason_1 = substr($reason_1, 0, 27);
			
			$reason_2 = str_replace('{{order_id}}', '', MODULE_PAYMENT_SOFORT_MULTIPAY_TEXT_REASON_2);
			$reason_2 = str_replace('{{customer_id}}', $customerId, $reason_2);
			$reason_2 = str_replace('{{order_date}}', strftime(DATE_FORMAT_SHORT), $reason_2);
			$reason_2 = str_replace('{{customer_name}}', $order->customer['firstname'] . ' ' . $order->customer['lastname'], $reason_2);
			$reason_2 = str_replace('{{customer_company}}', $order->customer['company'], $reason_2);
			$reason_2 = str_replace('{{customer_email}}', $order->customer['email_address'], $reason_2);
			$reason_2 = str_replace('{{transaction_id}}', '-TRANSACTION-', $reason_2);
			$reason_2 = $this->_convertReason($reason_2);
			$reason_2 = substr($reason_2, 0, 27);
		}
		
		$reasons = array();
		$reasons[0] = $reason_1;
		$reasons[1] = $reason_2;
		
		return $reasons;
	}
}
?>