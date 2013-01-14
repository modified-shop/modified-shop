<?php
/**
 * class for refund/rueckbuchung
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Date: 2012-09-05 14:27:56 +0200 (Wed, 05 Sep 2012) $
 * @version SofortLib 1.5.0  $Id: sofortLib_refund.inc.php 3751 2012-10-10 08:36:20Z gtb-modified $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
 */
class SofortLib_Refund extends SofortLib_Abstract {
	
	protected $_parameters = array();
	
	protected $_response = array();
	
	protected $_xmlRootTag = 'refunds';
	
	
	public function __construct($configKey = '') {
		list($userId, $projectId, $apiKey) = explode(':', $configKey);
		$apiUrl = (getenv('refundApiUrl') != '') ? getenv('refundApiUrl') : 'https://www.sofort.com/payment/refunds';
		parent::__construct($userId, $apiKey, $apiUrl);
	}
	
	
	/**
	 * send this message and get response
	 *
	 * @return array transactionid=>status
	 */
	public function sendRequest() {
		parent::sendRequest();
		return $this->getStatusArray();
	}
	
	
	/**
	 * add a new refund to this message
	 *
	 * @param string $transaction transaction id of transfer you want to refund
	 * @param float $amount amount of money to refund, less or equal to amount of original transfer
	 * @param string $comment comment that will be displayed in  admin-menu later
	 * @return SofortLib_Refund $this
	 */
	public function addRefund($transaction, $amount, $comment = '') {
		$this->_parameters['refund'][] = array(
			'transaction' => $transaction,
			'amount' => $amount,
			'comment' => $comment,
		);
		return $this;
	}
	
	
	/**
	 * set data of account
	 *
	 * @param string $bank_code bank code of bank
	 * @param string $account_number account number
	 * @param string $holder Name/Holder of this account
	 * @return SofortLib_Multipay $this
	 */
	public function setSenderAccount($bankCode, $accountNumber, $holder = '') {
		$this->_parameters['sender'] = array(
			'holder' => $holder,
			'account_number' => $accountNumber,
			'bank_code' => $bankCode,
		);
		return $this;
	}
	
	
	public function setTitle($arg) {
		$this->_parameters['title'] = $arg;
		return $this;
	}
	
	
	public function getTransactionId($i = 0) {
		return $this->_response['refunds']['refund'][$i]['transaction']['@data'];
	}
	
	
	public function getAmount($i = 0) {
		return $this->_response['refunds']['refund'][$i]['amount']['@data'];
	}
	
	
	public function getStatus($i = 0) {
		return $this->_response['refunds']['refund'][$i]['status']['@data'];
	}
	
	
	public function getComment($i = 0) {
		return $this->_response['refunds']['refund'][$i]['comment']['@data'];
	}
	
	
	public function getTitle() {
		return $this->_response['refunds']['title']['@data'];
	}
	
	
	public function getRefundError($i = 0) {
		return parent::getError('all', $this->_response[$i]);
	}
	
	
	public function isRefundError($i = 0) {
		return $this->_response['refunds']['refund'][$i]['status']['@data'] == 'error';
	}
	
	
	public function getDta() {
		return $this->_response['refunds']['dta']['@data'];
	}
	
	
	public function getAsArray() {
		return $this->_response;
	}
	
	
	/*
	 * @deprecated
	 */
	public function getStatusArray() {
		$ret = array();
		
		foreach ($this->_response['refunds']['refund'] as $transaction) {
			$ret[$transaction['transaction']['@data']] = $transaction['status']['@data'];
		}
		
		return $ret;
	}
	
	
	protected function _parseXml() {}
	
	
	protected function _handleErrors() {
		if (!isset($this->_response['refunds']['refund'][0])) {
			$tmp = $this->_response['refunds']['refund'];
			unset($this->_response['refunds']['refund']);
			$this->_response['refunds']['refund'][] = $tmp;
		}
		
		foreach ($this->_response['refunds']['refund'] as $response) {
			//handle errors
			if (isset($response['errors']['error'])) {
				if (!isset($response['errors']['error'][0])) {
					$tmp = $response['errors']['error'];
					unset($response['errors']['error']);
					$response['errors']['error'][0] = $tmp;
				}
				
				foreach ($response['errors']['error'] as $error) {
					$this->errors['global'][] = $this->_getErrorBlock($error);
				}
			}
			
			//handle warnings
			if (isset($response['warnings']['warning'])) {
				if (!isset($response['warnings']['warning'][0])) {
					$tmp = $response['warnings']['warning'];
					unset($response['warnings']['warning']);
					$response['warnings']['warning'][0] = $tmp;
				}
			
				foreach ($response['warnings']['warning'] as $error) {
					$this->warnings['global'][] = $this->_getErrorBlock($error);
				}
			}
		}
	}
}
?>