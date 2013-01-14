<?php
/**
 * Instance of this class handles the callback of SOFORT to notify about a status change, the classic way to do so
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Date: 2012-09-05 14:28:13 +0200 (Wed, 05 Sep 2012) $
 * @version SofortLib 1.5.0  $Id: sofortLib_classic_notification.inc.php 3751 2012-10-10 08:36:20Z gtb-modified $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
 */
class SofortLib_ClassicNotification {
	
	public $params = array();
	
	private $_password;
	
	private $_userId;
	
	private $_projectId;
	
	private $_hashFunction;

	private $_hashCheck = false;
	
	
	public function __construct($userId, $projectId, $password, $hashFunction = 'sha1') {
		$this->_password = $password;
		$this->_userId = $userId;
		$this->_projectId = $projectId;
		$this->_hashFunction = strtolower($hashFunction);
	}
	
	
	public function getNotification($request = '') {
		if ($request == '') {
			$request = $_POST;
		}
		
		if (array_key_exists('international_transaction', $request)) {
			//standard notification
			$fields = array(
				'transaction', 'user_id', 'project_id',
				'sender_holder', 'sender_account_number', 'sender_bank_code', 'sender_bank_name', 'sender_bank_bic', 'sender_iban', 'sender_country_id',
				'recipient_holder', 'recipient_account_number', 'recipient_bank_code', 'recipient_bank_name', 'recipient_bank_bic', 'recipient_iban', 'recipient_country_id',
				'international_transaction', 'amount', 'currency_id', 'reason_1', 'reason_2', 'security_criteria',
				'user_variable_0', 'user_variable_1', 'user_variable_2', 'user_variable_3', 'user_variable_4', 'user_variable_5',
				'created',
			);
		} else {
			//ideal
			$fields = array(
				'transaction', 'user_id', 'project_id',
				'sender_holder', 'sender_account_number', 'sender_bank_name', 'sender_bank_bic', 'sender_iban', 'sender_country_id',
				'recipient_holder', 'recipient_account_number', 'recipient_bank_code', 'recipient_bank_name', 'recipient_bank_bic',	'recipient_iban', 'recipient_country_id',
				'amount', 'currency_id', 'reason_1', 'reason_2',
				'user_variable_0', 'user_variable_1', 'user_variable_2', 'user_variable_3', 'user_variable_4', 'user_variable_5',
				'created',
			);
		}
		
		// http-notification with status
		if (array_key_exists('status', $request) && !empty($request['status'])) {
			array_push($fields, 'status', 'status_modified');
		}
		
		$this->params = array();
		
		foreach ($fields as $key) {
			$this->params[$key] = $request[$key];
		}
		
		$this->params['project_password'] = $this->_password;
		$validationHash = $this->_getHashHexValue(implode('|', $this->params), $this->_hashFunction);
		$messageHash = $request['hash'];
		$this->_hashCheck = ($validationHash === $messageHash);
		return $this;
	}
	
	
	public function isError() {
		if (!$this->_hashCheck) {
			return true;
		}
		
		return false;
	}
	
	
	public function getError() {
		if (!$this->_hashCheck) {
			return 'hash-check failed';
		}
		
		return false;
	}
	
	
	public function getTransaction() {
		return $this->params['transaction'];
	}
	
	
	public function getAmount() {
		return $this->params['amount'];
	}
	
	
	public function getUserVariable($i = 0) {
		return $this->params['user_variable_'.$i];
	}
	
	
	public function getCurrency() {
		return $this->params['currency_id'];
	}
	
	
	public function getTime() {
		return $this->params['created'];
	}
	
	
	public function getStatus() {
		return $this->params['status'];
	}
	
	
	public function getStatusReason() {
		switch ($this->getStatus()) {
			case 'received':
				return 'credited';
			case 'pending':
				return 'not_credited_yet';
			case 'loss':
				return 'loss';
		}
		
		return false;
	}
	
	
	/**
	 * @param string $data string to be hashed
	 * @return string the hash
	 */
	protected function _getHashHexValue($data, $hashFunction = 'sha1') {
		if ($hashFunction == 'sha1') {
			return sha1($data);
		}
		
		if ($hashFunction == 'md5') {
			return md5($data);
		}
		
		//mcrypt installed?
		if (function_exists('hash') && in_array($hashFunction, hash_algos())) {
			return hash($hashFunction, $data);
		}
		
		return false;
	}
}
?>