<?php
/**

 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 13:49:09 +0200 (Thu, 06 Sep 2012) $

 * @author SOFORT AG (integration@sofort.com)

 * @link http://www.sofort.com/

 *

 * Copyright (c) 2012 SOFORT AG

 *

 * $Id: helperFunctions.php 3767 2012-10-10 13:48:20Z gtb-modified $

 */

/******************************************************************
 * This file includes functions used by module and by callback.php
 ******************************************************************/

class HelperFunctions {
	
	
	/**
	 * Fill table sofort_orders with a new order
	 * @param int $ordersId
	 * @param string $paymentSecret
	 * @param string $transactionId
	 * @param string $paymentMethod
	 * @return last insert_id ELSE data could not be saved
	 */
	function insertSofortOrder($ordersId, $paymentSecret, $transactionId, $paymentMethod, $sessiondata) {
		if(!$transactionId || !$paymentMethod) return false;
		
		switch($paymentMethod){
			case 'SR': $paymentMethod = 'rechnung_by_sofort'; break;
			case 'SU': $paymentMethod = 'sofortueberweisung'; break;
			case 'SV': $paymentMethod = 'vorkasse_by_sofort'; break;
			case 'SL': $paymentMethod = 'sofortlastschrift'; break;
			case 'LS': $paymentMethod = 'lastschrift_by_sofort'; break;
			default: return false;
		}
		
		$sqlDataArray = array(
				'orders_id' => $ordersId,
				'transaction_id' => $transactionId,
				'payment_method' => $paymentMethod,
				'payment_secret' => $paymentSecret,
				'serialized_session' => serialize($sessiondata),
				'data_acquired' => 0,
		);
		xtc_db_query(HelperFunctions::getEscapedInsertInto('sofort_orders', $sqlDataArray));
		return xtc_db_insert_id(); // fetch and return the last insert id
	}
	
	
	/**
	 * only SR: Fill table sofort_orders_notification, e.g. in case of status-changes
	 * @see updateTimeline()
	 * @param int $sofortOrdersId - key from table sofort_orders
	 * @param object $invoice with complete transactiondata
	 * @param string $customerComment (optional)
	 * @param string $sellerComment (optional)
	 * @return last insert_id
	 */
	function insertSofortOrdersNotification($sofortOrdersId, PnagInvoice $PnagInvoice, $customerComment = '', $sellerComment = '') {
		if (!$sofortOrdersId || !is_object($PnagInvoice->getTransactionData()) || $PnagInvoice->getTransactionData()->getPaymentMethod() != 'sr') {
			return false;
		}
		
		$sqlDataArray = array(
			'sofort_orders_id' => $sofortOrdersId,
			'items' => serialize($PnagInvoice->getItems()),
			'amount' => $PnagInvoice->getAmount(),
			'customer_comment' => $customerComment,
			'seller_comment' => $sellerComment,
			'status_id' => $PnagInvoice->getState(),
			'status' => $PnagInvoice->getStatus(),
			'status_reason' => $PnagInvoice->getStatusReason(),
			'invoice_status' => $PnagInvoice->getStatusOfInvoice(),
			'invoice_objection' => $PnagInvoice->getInvoiceObjection()
		);
		xtc_db_query(HelperFunctions::getEscapedInsertInto('sofort_orders_notification', $sqlDataArray));
		return xtc_db_insert_id(); // fetch and return the last insert id
	}
	
	
	/**
	 * All PaymentMethods without SR: Fill table sofort_orders_notification
	 * @param int $sofortOrdersId - key from table sofort_orders
	 * @return last insert_id
	 * @see insertSofortOrdersNotification()
	 */
	function updateTimeline($sofortOrdersId, $orderStatus, $comment) {
		if (!$sofortOrdersId) {
			return false;
		}
		
		$sqlDataArray = array(
				'sofort_orders_id' => $sofortOrdersId,
				'items' => '',
				'amount' => 0,
				'customer_comment' => $comment,
				'seller_comment' => $comment,
				'status_id' => 0,
				'status' => $orderStatus,
				'status_reason' => '',
				'invoice_status' => '',
				'invoice_objection' => ''
		);
		xtc_db_query(HelperFunctions::getEscapedInsertInto('sofort_orders_notification', $sqlDataArray));
		return xtc_db_insert_id(); // fetch and return the last insert id
	}
	
	
	/**
	 * get current order-status from table orders and table orders_sofort
	 * @return array with order_status from both tables
	 */
	function getAllCurrentOrderStatus($ordersId){
		
		if (!$ordersId) return false;
		
		$sofortStatus = HelperFunctions::getLastFieldValueFromSofortTable($ordersId,'status');
		$query = xtc_db_query('SELECT orders_status FROM orders WHERE orders_id = '.HelperFunctions::escapeSql($ordersId));
		$result = xtc_db_fetch_array($query);
		$coreStatus = $result['orders_status'];
		
		return array(
				'sofortOrdersStatus' => $sofortStatus,
				'coreStatus' => $coreStatus,
		);
	}
	
	
	function getLastFieldValueFromSofortTable($ordersId,$field){
		$query = xtc_db_query( 'SELECT id FROM sofort_orders WHERE orders_id = '.HelperFunctions::escapeSql($ordersId));
		$result = xtc_db_fetch_array($query);
		$sofortOrdersId = $result['id'];
		$query = xtc_db_query( 'SELECT '.$field.' FROM sofort_orders_notification WHERE sofort_orders_id = "'.HelperFunctions::escapeSql($sofortOrdersId).'" ORDER BY date_time DESC LIMIT 1');
		$result = xtc_db_fetch_array($query);
		return $result[$field];
	}
	
	
	/**
	 * Converts a given String $string to any specified encoding (if supported)
	 *
	 * @param String $string
	 * @param String $to ; 2 = from utf-8 to shopencoding set in sofort.ini ; 3 = from shopencoding set in sofort.ini to utf-8
	 * @return String $string
	 */
	function convertEncoding($string, $to, $fromEncoding = '') {
		$shopEncoding = HelperFunctions::getIniValue('shopEncoding');
		
		if ($shopEncoding == 'UTF-8'){
			return $string;
		} elseif ($to == 1) {
			return mb_convert_encoding($string, $shopEncoding, $fromEncoding);
		} elseif ($to == 2) {
			return mb_convert_encoding($string, $shopEncoding, 'UTF-8');
		} elseif ($to == 3){
			return mb_convert_encoding($string, 'UTF-8', $shopEncoding);
		} elseif ($to == 4) {
		  return html_entity_decode($string);
		}
	}
	
	
	/**
	 * escapes the given string via mysql_real_esacpe_string (if function exists & a db-connection is available) or mysql_escape_string
	 * @param string $string
	 * @return string $string
	 */
	function escapeSql($string) {
		if (function_exists('mysql_real_escape_string') && mysql_ping()) {
			return mysql_real_escape_string($string);
		} else {
			return mysql_escape_string($string);
		}
	}
	
	
	/**
	 * Combination of functions escapeSql() and convertEncoding()
	 */
	function escapeConvert($string, $to) {
		return HelperFunctions::escapeSql(HelperFunctions::convertEncoding($string,$to));
	}
	
	
	/**
	 * This function uses htmlentities() regarding the shopEncoding
	 */
	
	function makeEntities($string){

		return htmlentities($string,ENT_QUOTES,HelperFunctions::getIniValue('shopEncoding'));

	}
	
	
	/**
	 * creates an escaped "INSERT INTO" sql-string
	 * @param string $table
	 * @param array $data with key=column_name and value=column_value
	 * for sql-commands set value like "sqlcommand:now()"
	 * @return string $returnString
	 */
	function getEscapedInsertInto($table, $data) {
		$table = trim($table);
		
		if (!is_string($table) || !$table) {
			return '';
		}
		
		if (!is_array($data) || !$data) {
			return '';
		}
		
		$returnString = 'INSERT INTO `'.HelperFunctions::escapeSql($table).'` (`';
		$columns = array_keys($data);
		$returnString .= implode('`, `', $columns);
		$returnString .= '`) VALUES (';
		
		foreach ($data as $value) {
			if ((strpos($value, 'sqlcommand:') === 0)) {
				$returnString .= HelperFunctions::escapeSql(substr($value, 11)).", ";  //its a sql-command
			}else{
				$returnString .= "'".HelperFunctions::escapeSql($value)."', ";  //its a normal string or int
			}
		}
		
		$returnString = substr($returnString, 0, -2); //deletes comma and whitespace
		$returnString .= ')'; //dont add ';'
		return $returnString;
	}
	
	
	/**
	 * masks the given variable via strip_tags() and htmlentities()
	 * @param mixed $var
	 * @return mixed $var
	 */
	function htmlMask($var){
		return htmlentities(strip_tags($var),ENT_QUOTES,HelperFunctions::getIniValue('shopEncoding'));
	}
	
	
	/**
	 * masks the given arrayitem via strip_tags() and htmlentities()
	 * @param mixed $item
	 * @return mixed $item
	 */
	function htmlMaskArray(&$item){
		return htmlentities(strip_tags($item),ENT_QUOTES,HelperFunctions::getIniValue('shopEncoding'));
	}
	
	
	/**
	 * return from the sofort.ini the value of the given key
	 * @return mixed $value - if key is not found: false
	 */
	function getIniValue($key){
		$iniArray = parse_ini_file('sofort.ini');
		if (!isset($iniArray[$key])) {
			return false;
		}else{
			return $iniArray[$key];
		}
	}
	
	
	function getSofortLanguage($language){
		$lngdir = DIR_FS_CATALOG.'lang/';

		foreach (new DirectoryIterator($lngdir) as $file){
      if (is_dir($lngdir.$file->getFilename())) {
			  if (file_exists($lngdir.$file->getFilename().'/modules/payment/sofort_general.php')) $lngarr[] = $file->getFilename();
      }
		}
		
		return (!in_array($language,$lngarr)) ? 'english' : $language;
	}
	
	
	function getShortCode($lng){
		switch ($lng){
			case 'english': return 'en';
			case 'german' :
			default		    : return 'de';
		}
	}
	
	
	/**
	 * Getter for the sofortmodulVersion, set in sofort.ini
	 * @return String
	 */
	function getSofortmodulVersion() {
		return HelperFunctions::getIniValue('sofortmodulVersion');
	}
	
	
	function sendAdminErrorMail($importantData = array()){
		$subject = "Error in Payment-Modul";
		$message = "Error in Payment-Modul\r\n";
		
		if (isset($importantData['description']))
			$message .= "\r\nDescription: ".$importantData['description'];
		if (isset($importantData['transactionId']))
			$message .= "\r\nTransaction-ID: ".$importantData['transactionId'];
		if (isset($importantData['paymentmethod']))
			$message .= "\r\nPaymentmethod: ".$importantData['paymentmethod'];
		if (isset($importantData['customerId']))
			$message .= "\r\nCustomer-ID: ".$importantData['customerId'];
		if (isset($importantData['orderdata']))
			$message .= "\r\nOrder-Data: ".print_r($importantData['orderdata'], true);
		
		$message .= "\r\n\r\nMail is sent by function ".__METHOD__." in file ".__FILE__;
		
		xtc_php_mail(STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, '', '', STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, '', '', $subject, nl2br($message), $message);
	}
	
	
	/**
	 * validate given api-key against SOFORT and return result
	 * @return bool
	 */
	function apiKeyIsValid($apiKey) {
		preg_match('#([a-zA-Z0-9:]+)#', $apiKey, $matches);
		$configKey = $matches[1];
		$SofortLib_TransactionData = new SofortLib_TransactionData($configKey);
		$SofortLib_TransactionData->setTransaction('00000')->sendRequest();
		if ($SofortLib_TransactionData->isError()) {
			return false;
		} else {
			return true;
		}
	}
	
	
	function getSofortOrderhandlingLink() {
		$shopsystemVersion = HelperFunctions::getIniValue('shopsystemVersion');
		
		if (!$shopsystemVersion) return '';
		
		$shopsystemVersion = str_replace('..', '', $shopsystemVersion);
		return DIR_FS_CATALOG.'callback/sofort/ressources/scripts/'.$shopsystemVersion.'_sofortOrderhandling.php';
	}
	
	
	/**
	 * only SR: send the given order-id for the given transId to SOFORT
	 */
	function sendOrderIdToSofort($sofortApiKey, $transactionId, $orderId) {
		$SofortEditSr = new SofortLib_EditSr($sofortApiKey);
		$SofortEditSr->setTransaction($transactionId);
		$SofortEditSr->setOrderNumber($orderId);
		$SofortEditSr->sendRequest();
		return true;
	}
	
	
	/**
	 * get the cancel-URL
	 * @param $paymentMethod - assign $this->code
	 * @param array (optional) $errors with error-codes
	 */
	function getCancelUrl($paymentMethod, $errors = array()) {
		/*
		$session = session_name().'='.session_id();
		if (ENABLE_SSL == true)
			$server = HTTPS_SERVER;
		else
			$server = HTTP_SERVER;

		$cancelUrl = $server.DIR_WS_CATALOG.FILENAME_CHECKOUT_PAYMENT.'?payment_error='.$paymentMethod.'&'.$session;
		*/
		
		$message = array();
		if (xtc_not_null($paymentMethod)) {
		  $message[] = 'payment_error='.$paymentMethod;
		}
		
		//if errors are given, add them to the url
		if ($errors) {
			$errorCodes = array();
			foreach ($errors as $oneError) {
				$errorCodes[] = $oneError['code'];
			}
			if($errorCodes){
				$message[] = 'error_codes='.implode(',', $errorCodes);
			}
		}
		//$message = implode('&', $message);
		//return $cancelUrl;
		return  xtc_href_link(FILENAME_CHECKOUT_PAYMENT, implode('&', $message), 'SSL');
	}
	
	
	/**
	 * do "include_once" for order_total.php and all files in MODULE_ORDER_TOTAL_INSTALLED (if defined)
	 * @return always true
	 */
	function includeOrderTotalFiles() {
		include_once (DIR_WS_CLASSES.'order_total.php');
		if (defined('MODULE_ORDER_TOTAL_INSTALLED') && xtc_not_null(MODULE_ORDER_TOTAL_INSTALLED)) {
			$modules = explode(';', MODULE_ORDER_TOTAL_INSTALLED);
			while (list (, $value) = each($modules)) {
				include_once (DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/order_total/'.$value);
				include_once (DIR_WS_MODULES.'order_total/'.$value);
			}
		}
		return true;
	}
}
?>