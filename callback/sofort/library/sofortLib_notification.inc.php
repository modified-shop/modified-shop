<?php
/**
 * Instance of this class handles the callback of Payment Network to notify about a status change
 *
 * In rare cases notifications might be doubled or even wrong alltogether (if
 * send by a malicious user). So don't use this to change your status but instead
 * use the transaction id to query the webservice for detailed data (SofortLib_TransactionData)
 *
 * eg: $notificationObj = new SofortLib_Notification();
 *
 * $transactionId = $notificationObj->getNotification();
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Date: 2012-09-05 14:27:56 +0200 (Wed, 05 Sep 2012) $
 * @version SofortLib 1.5.0  $Id: sofortLib_notification.inc.php 3751 2012-10-10 08:36:20Z gtb-modified $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
 */
class SofortLib_Notification extends SofortLib_Abstract {
	
	protected $_parameters = array();
	
	protected $_response = array();
	
	private $_transactionId = '';
	
	private $_time;
	
	
	/**
	 * creates a new notification object for receiving notifications
	 */
	public function __construct() {
		parent::__construct('', '', '');
	}
	
	
	/**
	 * reads the input and tries to read the transaction id
	 *
	 * @return array transactionid=>status
	 */
	public function getNotification($source = 'php://input') {
		$data = file_get_contents($source);
		
		//we don't really need a huge parser, simply extract the transaction-id
		if (!preg_match('#<transaction>([0-9a-z-]+)</transaction>#i', $data, $matches)) {
			$this->log(__CLASS__.' <- '.$data);
			$this->errors['error']['message'] = 'could not parse message';
			return false;
		}
		
		$this->_transactionId = $matches[1];
		$this->log(__CLASS__.' <- '.$data);
		preg_match('#<time>(.+)</time>#i', $data, $matches);
		
		if (isset($matches[1])) {
			$this->_time = $matches[1];
		}
		
		return $this->_transactionId;
	}
	
	
	public function sendRequest() {
		trigger_error('sendRequest() not possible in this case', E_USER_NOTICE);
	}
	
	
	public function getTime() {
		return $this->_time;
	}
	
	
	public function getTransactionId() {
		return $this->_transactionId;
	}
}
?>