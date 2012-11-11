<?php

/**
 * @version sofort�isung.de 1.2 - $Date: 2010-09-10 10:19:34 +0200 (Fr, 10 Sep 2010) $
 * @author Payment Network AG (integration@payment-network.com)
 * @link http://www.payment-network.com/
 * 
 * Copyright (c) 2010 Payment Network AG
 *
 * $Id: classPnSofortueberweisung.php 305 2010-09-10 08:19:34Z poser $
 * 
 */
class classPnSofortueberweisung {

	var $hashfunction;
	var $password;
	var $password2;
	var $formActionUrl;
	var $version;



	function classPnSofortueberweisung($password = '', $hashfunction = 'sha1') {

		$this->password = $password;
		$this->password2 = '';
		$this->hashfunction = $hashfunction;
		$this->version = 'pn_generic_1.1';
		$this->formActionUrl = 'https://www.sofortueberweisung.de/payment/start?';


		return true;
	}

	/**
	 * @param int $userId
	 * @param int $projectId
	 * @param string $amount
	 * @param string $currency EUR|CHF
	 * @param string [optional] $reason1
	 * @param string [optional] $reason2
	 * @param string [optional] $userVariable0
	 * @param string [optional] $userVariable1
	 * @param string [optional] $userVariable2
	 * @param string [optional] $userVariable3
	 * @param string [optional] $userVariable4
	 * @param string [optional] $userVariable5
	 * @param string [optional] $senderHolder
	 * @param string [optional] $senderAccountNumber
	 * @param string [optional] $senderBankCode
	 * @param string [optional] $senderCountryId
	 * @return string url with urlencoded variables
	 */
	function getPaymentUrl($userId, $projectId, $amount, $currency,
	$reason1 = '' , $reason2 = '' , $userVariable0 = '' , $userVariable1 = '' , $userVariable2 = '' ,
	$userVariable3 = '' , $userVariable4 = '' , $userVariable5 = '', 
	$senderHolder = '', $senderAccountNumber = '', $senderBankCode = '', $senderCountryId = ''){


		$data = $this->getPaymentParameters($userId, $projectId, $amount, $currency,
				$reason1, $reason2, $userVariable0, $userVariable1, $userVariable2,
				$userVariable3, $userVariable4, $userVariable5, 
				$senderHolder, $senderAccountNumber, $senderBankCode, $senderCountryId);

		$dataString = '';
		foreach ($data as $key => $value) {
			$dataString .= $key.'='.urlencode($value).'&';
		}
		$dataString = substr($dataString, 0, -1); //remove last &

		return $this->formActionUrl.$dataString;
	}

	function getPaymentUrlSV($userId, $projectId, $amount, $currency,
	$reason1 = '' , $reason2 = '' , $userVariable0 = '' , $userVariable1 = '' , $userVariable2 = '' ,
	$userVariable3 = '' , $userVariable4 = '' , $userVariable5 = ''){


		$data = $this->getPaymentParametersSV($userId, $projectId, $amount, $currency,
				$reason1, $userVariable0, $userVariable1, $userVariable2,
				$userVariable3, $userVariable4, $userVariable5);

		$dataString = '';
		foreach ($data as $key => $value) {
			$dataString .= $key.'='.urlencode($value).'&';
		}
		$dataString = substr($dataString, 0, -1); //remove last &

		return $this->formActionUrl.$dataString;
	}	
	
	/**
	 * @param int $userId
	 * @param int $projectId
	 * @param string $amount
	 * @param string $currency EUR|CHF
	 * @param string [optional] $reason1
	 * @param string [optional] $reason2
	 * @param string [optional] $userVariable0
	 * @param string [optional] $userVariable1
	 * @param string [optional] $userVariable2
	 * @param string [optional] $userVariable3
	 * @param string [optional] $userVariable4
	 * @param string [optional] $userVariable5
	 * @param string [optional] $senderHolder
	 * @param string [optional] $senderAccountNumber
	 * @param string [optional] $senderBankCode
	 * @param string [optional] $senderCountryId
	 * @return array array with parameters for payment message
	 */	
	function getPaymentParameters($userId, $projectId, $amount, $currency,
	$reason1 = '' , $reason2 = '' , $userVariable0 = '' , $userVariable1 = '' , $userVariable2 = '' ,
	$userVariable3 = '' , $userVariable4 = '' , $userVariable5 = '', 
	$senderHolder = '', $senderAccountNumber = '', $senderBankCode = '', $senderCountryId = '') {

	$tmparray = array(
		$userId,
		$projectId,
		$senderHolder,
		$senderAccountNumber,
		$senderBankCode,
		$senderCountryId,
		$amount,
		$currency,
		$reason1,
		$reason2,
		$userVariable0,
		$userVariable1,
		$userVariable2,
		$userVariable3,
		$userVariable4,
		$userVariable5,
		$this->password);

		$hash = $this->generateHash(implode("|", $tmparray));

		$data['user_id'] = $userId;
		$data['project_id'] = $projectId;
		$data['amount'] = $amount;
		$data['currency_id'] = $currency;
		$data['reason_1'] = $reason1;
		$data['reason_2'] = $reason2;
		$data['user_variable_0'] = $userVariable0;
		$data['user_variable_1'] = $userVariable1;
		$data['user_variable_2'] = $userVariable2;
		$data['user_variable_3'] = $userVariable3;
		$data['user_variable_4'] = $userVariable4;
		$data['user_variable_5'] = $userVariable5;
		$data['hash'] = $hash;
		$data['encoding'] = 'iso-8859-1';
		$data['payment_module'] = $this->version;
		$data['interface_version'] = $this->version;
		
		return $data;
	}
	
	function getPaymentParametersSV($userId, $projectId, $amount, $currency,
	$reason = '' , $userVariable0 = '' , $userVariable1 = '' , $userVariable2 = '' ,
	$userVariable3 = '' , $userVariable4 = '' , $userVariable5 = '', 
	$senderHolder = '', $senderAccountNumber = '', $senderBankCode = '', $senderCountryId = '', $sender_bank_bic = '', $sender_iban = '') {
	$tmparray = array(
		$userId,
		$projectId,
		$senderHolder,
		$senderAccountNumber,
		$senderBankCode,
		$sender_bank_bic,
		$sender_iban,
		$senderCountryId,
		$amount,
		$currency,
		$reason,
		$userVariable0,
		$userVariable1,
		$userVariable2,
		$userVariable3,
		$userVariable4,
		$userVariable5,
		$this->password);

		$hash = $this->generateHash(implode("|", $tmparray));

		$data['user_id'] = $userId;
		$data['project_id'] = $projectId;
		$data['amount'] = $amount;
		$data['currency_id'] = $currency;
		$data['reason'] = $reason;
		$data['user_variable_0'] = $userVariable0;
		$data['user_variable_1'] = $userVariable1;
		$data['user_variable_2'] = $userVariable2;
		$data['user_variable_3'] = $userVariable3;
		$data['user_variable_4'] = $userVariable4;
		$data['user_variable_5'] = $userVariable5;
		$data['hash'] = $hash;
		$data['encoding'] = 'iso-8859-1';
		$data['payment_module'] = $this->version;
		$data['interface_version'] = $this->version;
		
		return $data;		
	}
	
	
	/**
	 * 	checks server response and gets parameters  
	 *  @return $data array|string response parameters or ERROR_WRONG_HASH|ERROR_NO_ORDER_DETAILS if error
	 * 
	 */
	function getNotification(){
		

		$fields = array(
		'transaction', 'user_id', 'project_id', 
		'sender_holder', 'sender_account_number', 'sender_bank_code', 'sender_bank_name', 'sender_bank_bic', 'sender_iban', 'sender_country_id',	
		'recipient_holder',	'recipient_account_number', 'recipient_bank_code', 'recipient_bank_name', 'recipient_bank_bic',	'recipient_iban', 'recipient_country_id',
		'international_transaction', 'amount', 'currency_id', 
		'reason_1',	'reason_2',
		'security_criteria', 
		'user_variable_0',	'user_variable_1', 'user_variable_2', 'user_variable_3', 'user_variable_4',	'user_variable_5',
		'created'
		);

		//http-notification with status
		if(array_key_exists('status', $_POST) && !empty($_POST['status'])) {
			array_push($fields, 'status', 'status_modified');
		}
		
		$data = array();
		foreach($fields as $key) {
			$data[$key] = $_POST[$key];
		}

		//sanitize input
		$data['amount'] = number_format($data['amount'], 2, '.', '');
		$data['transaction'] = preg_replace('#[^A-Za-z0-9_-]+#', '', $data['transaction']);
		$data['user_id'] = preg_replace('#[^0-9]+#', '', $data['user_id']);
		$data['project_id'] = preg_replace('#[^0-9]+#', '', $data['project_id']);
		
		if (empty($data['user_id']) || empty($data['project_id']) || empty($data['amount']) || empty($_POST['hash'])) {
			return 'ERROR_NOTIFICATION_INCOMPLETE';
		}
		
		if(empty($this->password)) {
			return 'ERROR_NO_PASSWORD';
		}
		$data['project_password'] = $this->password;

		$validationhash = $this->generateHash(implode('|', $data));
		$messagehash = $_POST['hash'];

		if ($validationhash != $messagehash) {
			return 'ERROR_WRONG_HASH';
		}

		return $data;
	}	
	
	/**
	 * 	checks server response and gets parameters  
	 *  @return $data array|string response parameters or ERROR_WRONG_HASH|ERROR_NO_ORDER_DETAILS if error
	 * 
	 */
	function getNotificationSV(){
		$fields = array(
		'transaction', 'user_id', 'project_id', 
		'sender_holder', 'sender_account_number', 'sender_bank_code', 'sender_bank_name', 'sender_bank_bic', 'sender_iban', 'sender_country_id',	
		'recipient_holder',	'recipient_account_number', 'recipient_bank_code', 'recipient_bank_name', 'recipient_bank_bic',	'recipient_iban', 'recipient_country_id',
		'amount', 'currency_id', 
		'reason', 
		'user_variable_0',	'user_variable_1', 'user_variable_2', 'user_variable_3', 'user_variable_4',	'user_variable_5',
		'created'
		);

		//http-notification with status
		if(array_key_exists('status', $_POST) && !empty($_POST['status'])) {
			array_push($fields, 'status', 'status_modified');
		}
		
		$data = array();
		foreach($fields as $key) {
			$data[$key] = $_POST[$key];
		}

		//sanitize input
		$data['amount'] = number_format($data['amount'], 2, '.', '');
		$data['transaction'] = preg_replace('#[^A-Za-z0-9_-]+#', '', $data['transaction']);
		$data['user_id'] = preg_replace('#[^0-9]+#', '', $data['user_id']);
		$data['project_id'] = preg_replace('#[^0-9]+#', '', $data['project_id']);
		
		if (empty($data['user_id']) || empty($data['project_id']) || empty($data['amount']) || empty($_POST['hash'])) {
			return 'ERROR_NOTIFICATION_INCOMPLETE';
		}
		
		if(empty($this->password)) {
			return 'ERROR_NO_PASSWORD';
		}
		$data['project_password'] = $this->password;

		$validationhash = $this->generateHash(implode('|', $data));
		$messagehash = $_POST['hash'];

		if ($validationhash != $messagehash) {
			return 'ERROR_WRONG_HASH';
		}

		return $data;
	}		
	
	/**
	 * checks wich hash algorithms are supported by the server
	 * and returns the best one
	 *
	 * @return sha512|sha256|sha1|md5|empty string
	 */
	function getSupportedHashAlgorithm() {

		$algorithms = $this->getSupportedHashAlgorithms();
		
		if(is_array($algorithms))
			return $algorithms[0];
		else
			return ''; //no hash function found
	}

	/**
	 * checks wich hash algorithms are supported by the server
	 *
	 * @return array with all supported algorithms, preferred as first one (index 0)
	 */
	function getSupportedHashAlgorithms() {
		
		$algorithms = array();

		if(function_exists('hash') && in_array('sha512', hash_algos()))
			$algorithms[] = 'sha512';
		
		if(function_exists('hash') && in_array('sha256', hash_algos()))
			$algorithms[] =  'sha256';
		
		if(function_exists('sha1'))	//deprecated
			$algorithms[] =  'sha1';
		
		if(function_exists('md5')) //deprecated
			$algorithms[] =  'md5';
			
		return $algorithms;
	}


	/**
	 * generates a html-page that sets post-parameters and redirects to the SU-autoinstaller
	 * sets password, password2 and hashfunction
	 *
	 * @param unknown_type $projectName
	 * @param unknown_type $projectHomepage
	 * @param unknown_type $projectEmail
	 * @param unknown_type $projectLanguage
	 * @param unknown_type $currency
	 * @param unknown_type $cancelLink
	 * @param unknown_type $successLink
	 * @param unknown_type $notificationLink
	 * @param unknown_type $backLink
	 * @return string
	 */
	function getAutoInstallPage($projectName, $projectHomepage, $projectEmail, $projectLanguage, $currency,
	$cancelLink, $successLink, $notificationLink, $backLink, $shopSystemId='208'){

		$this->password = $this->generateRandomValue();
		$this->password2 = $this->generateRandomValue();
		$this->hashfunction = $this->getSupportedHashAlgorithm();

		$html = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
	<title>Schnellregistrierung | sofortueberweisung.de</title>
</head>
<body onload="document.getElementById(\'form\').submit()">
	<form method="post" action="https://www.sofortueberweisung.de/payment/createNew" id="form">
		<input type="hidden" name="project_name" value="'.$projectName.'">
		<input type="hidden" name="project_homepage" value="'.$projectHomepage.'">
		<input type="hidden" name="projectsnotification_email_email" value="'.$projectEmail.'">
		<input type="hidden" name="projectsnotification_email_activated" value="1">
		<input type="hidden" name="projectsnotification_email_language_id" value="'.$projectLanguage.'">
		<input type="hidden" name="projectsnotification_email_with_status_email" value="'.$projectEmail.'">
		<input type="hidden" name="projectsnotification_email_with_status_activated" value="1">
		<input type="hidden" name="projectsnotification_email_with_status_language_id" value="'.$projectLanguage.'">
		<input type="hidden" name="projectssetting_interface_cancel_link" value="'.$cancelLink.'">
		<input type="hidden" name="projectssetting_interface_success_link_redirect" value="1">
		<input type="hidden" name="projectssetting_interface_success_link" value="'.$successLink.'">
		<input type="hidden" name="projectssetting_currency_id" value="'.$currency.'">
		<input type="hidden" name="projectssetting_locked_amount" value="1">
		<input type="hidden" name="projectssetting_locked_reason_1" value="1">
		<input type="hidden" name="projectssetting_locked_reason_2" value="1">
		<input type="hidden" name="projectssetting_interface_input_hash_check_enabled" value="1">
		<input type="hidden" name="projectssetting_project_password" value="'.$this->password.'">
		<input type="hidden" name="project_notification_password" value="'.$this->password2.'">
		<input type="hidden" name="project_shop_system_id" value="'.$shopSystemId.'">
		<input type="hidden" name="project_hash_algorithm" value="'.$this->hashfunction.'">
		<input type="hidden" name="user_shop_system_id" value="'.$shopSystemId.'">
		<input type="hidden" name="projectsnotification_http_activated" value="1">
		<input type="hidden" name="projectsnotification_http_url" value="'.$notificationLink.'">
		<input type="hidden" name="projectsnotification_http_method" value="1">
		<input type="hidden" name="projectsnotification_http_with_status_activated" value="1">
		<input type="hidden" name="projectsnotification_http_with_status_url" value="'.$notificationLink.'">
		<input type="hidden" name="projectsnotification_http_with_status_method" value="1">
		<input type="hidden" name="backlink" value="'.$backLink.'">
		<input type="hidden" name="debug" value="0">
		<noscript><input type="submit"></noscript>
	</form>
</body>
</html>
';
		return $html;
	}

	/**
	 * @param string $data string to be hashed
	 * @return string the hash
	 */
	function generateHash($data){

		if($this->hashfunction == 'sha1')
			return sha1($data);

		//mcrypt installed?
		if(function_exists('hash') && in_array($this->hashfunction, hash_algos()))
			return hash($this->hashfunction, $data);
			
		return md5($data); //fallback to md5
	}

	/**
	 * @param int [optional] $length length of return value, default 24
	 * @return string
	 */
	function generateRandomValue($length = 24) {
		$randomValue = '';
	
		//if php >= 5.3 and openssl installed we will use its more secure random generator
		if(function_exists('openssl_random_pseudo_bytes')) {
			$p = base64_encode(openssl_random_pseudo_bytes($length, $strong)); //output is base64: a-zA-Z0-9/+
			if($strong === TRUE)
			{
				$randomValue = preg_replace('#[^A-Za-z0-9]#', '', $p); //remove all special chars
				$randomValue = substr($randomValue, 0, $length); //base64 is about 33% longer, so needs to get truncated
			}
		}
	
		//fallback to mt_rand for php < 5.3
		if(strlen($randomValue) < $length)
		{
			$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; //62 chars 0-9A-Za-z
			$charactersLength = strlen($characters)-1;
	
			//select some random characters from all characters
			for ($i = 0; $i < $length; $i++) {
				$randomValue .= $characters[mt_rand(0, $charactersLength)];
			}
		}
	
		return $randomValue;
	}
}

?>
