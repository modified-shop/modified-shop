<?php
/**
 * class for handling debit/lastschrift
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Date: 2012-09-05 14:27:56 +0200 (Wed, 05 Sep 2012) $
 * @version SofortLib 1.5.0  $Id: sofortLib_debit.inc.php 3751 2012-10-10 08:36:20Z gtb-modified $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
 */
class SofortLib_Debit extends SofortLib_Abstract {
	
	protected $_response = array();
	
	protected $_parameters = array();
	
	protected $_xmlRootTag = 'debitpay';
	
	
	public function __construct($configKey = '') {
		list($userId, $projectId, $apiKey) = explode(':', $configKey);
		$apiUrl = (getenv('debitApiUrl') != '') ? getenv('debitApiUrl') : 'https://www.sofort.com/payment/debitpay/xml';
		parent::__construct($userId, $apiKey, $apiUrl);
		$this->setProjectId($projectId);
		$this->setDate(); //set date to today
	}
	
	
	/**
	 * send this debitpay and get response
	 * @return boolean true if transaction was accepted, false otherwise
	 */
	public function sendRequest() {
		parent::sendRequest();
		return $this->isError() === false;
	}
	
	
	/**
	 * Project id
	 * Id of your Sofortlastschrift project
	 *
	 * @param int $id project id
	 * @return SofortLib_Debit $this
	 */
	public function setProjectId($id) {
		$this->_parameters['project_id'] = $id;
		return $this;
	}
	
	
	/**
	 * sets date of this debitpay
	 * automatically called in constructor and set to today
	 *
	 * @param String $date date in Format Y-m-d (eg: 2011-01-20), default: today
	 * @return SofortLib_Debit $this
	 */
	public function setDate($date = '') {
		if (empty($date)) {
			$date = date('Y-m-d');
		}
		
		$this->_parameters['date'] = $date;
		return $this;
	}
	
	
	/**
	 * set data of account
	 *
	 * @param String $bankCode bank code of bank
	 * @param String $accountNumber account number
	 * @param String $holder Name/Holder of this account
	 * @return SofortLib_Debit $this
	 */
	public function setSenderAccount($bankCode, $accountNumber, $holder) {
		$this->_parameters['sl']['sender'] = array(
			'holder' => $holder,
			'account_number' => $accountNumber,
			'bank_code' => $bankCode,
		);
		return $this;
	}
	
	
	/**
	 * set data of account
	 *
	 * @param String $accountNumber account number
	 * @return SofortLib_Debit $this
	 */
	public function setSenderAccountNumber($accountNumber) {
		$this->_parameters['sl']['sender']['account_number'] = $accountNumber;
		return $this;
	}
	
	
	/**
	 * set data of account
	 *
	 * @param String $bankCode bank code of bank
	 * @return SofortLib_Debit $this
	 */
	public function setSenderBankCode($bankCode) {
		$this->_parameters['sl']['sender']['bank_code'] = $bankCode;
		return $this;
	}
	
	
	/**
	 * set data of account
	 *
	 * @param String $name Name/Holder of this account
	 * @return SofortLib_Debit $this
	 */
	public function setSenderHolder($name) {
		$this->_parameters['sl']['sender']['holder'] = $name;
		return $this;
	}
	
	
	/**
	 * set amount of this transfer
	 * needs to be a decimal e.g. 2.24
	 *
	 * @param float $amount amount of this transfer
	 */
	public function setAmount($amount) {
		$this->_parameters['sl']['amount'] = $amount;
		return $this;
	}
	
	
	/**
	 * add another user-variable to this transfer
	 * this variable could be a customer-number or similar and will
	 * help you identify this transfer later
	 *
	 * @param String $userVariable max 255 characters
	 * @return SofortLib_Debit $this
	 */
	public function addUserVariable($userVariable) {
		$this->_parameters['sl']['user_variables']['user_variable'][] = $userVariable;
		return $this;
	}
	
	
	/**
	 * adds another reason (verwendungszweck)
	 * only first two can be used, 27 characters each
	 *
	 * @param $reason string
	 * @return SofortLib_Debit $this
	 */
	public function addReason($reason) {
		$this->_parameters['sl']['reasons']['reason'][] = $reason;
		return $this;
	}
	
	
	/**
	 * set reason (verwendugszweck) of this transfer
	 * two lines possible, 27 characters each
	 *
	 * @param $reason1
	 * @param $reason2
	 * @return SofortLib_Debit $this
	 */
	public function setReason($reason1, $reason2 = '') {
		$this->_parameters['sl']['reasons']['reason'][0] = $reason1;
		$this->_parameters['sl']['reasons']['reason'][1] = $reason2;
		return $this;
	}
	
	
	/**
	 * get Transaction-Id of this Transfer
	 * @return String transaction-id
	 */
	public function getTransactionId() {
		return $this->_response['transaction'];
	}
	
	
	public function getReason($i = 0) {
		return $this->_response['reasons'][$i];
	}
	
	
	public function getAmount() {
		return $this->_response['amount'];
	}
	
	
	public function getUserVariable($i = 0) {
		return $this->_response['user_variables'][$i];
	}
	
	
	public function getDate() {
		return $this->_response['date'];
	}
	
	
	public function isError($paymentMethod = 'all', $message = ''){
		return parent::isError($paymentMethod, $message);
	}
	
	
	public function getError($paymentMethod = 'all', $message = '') {
		return parent::getError($paymentMethod, $message);
	}
	
	
	public function getResponse() {
		return $this->_response;
	}
}
?>