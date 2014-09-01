<?php

define('VERSION_CLASSIC','1.2.0');

require_once 'sofortLib_http.inc.php';
require_once 'sofortLib_sofortueberweisung_classic.php';
require_once 'sofortLib_Logger.inc.php';
require_once 'sofortLib_ideal_banks.inc.php';
/**
 * iDeal_Classic extends Sofortueberweisung_Classic, implementing payment via iDeal
 * Setup a session within iDeal using the classic api
 * You get the so called payment-url after successful configuration
 * Payment is enabled with this url being sent to iDeal
 *
 * eg:
 * $sofort = $sofortLib_iDealClassic = new SofortLib_iDealClassic ($configurationKey, $password, $hashfunction = 'sha1');
 * $sofort->getRelatedBanks(); //get all iDEAL-Banks
 * $sofort->getPaymentUrl(); //returns paymentUrl including (including ...&hash=1234567890&...)
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Date: 2012-09-05 14:27:56 +0200 (Wed, 05 Sep 2012) $
 * @version SofortLib 1.5.0  $Id: sofortLib_ideal_classic.php 3751 2012-10-10 08:36:20Z gtb-modified $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
 */
class SofortLib_iDealClassic extends SofortLib_SofortueberweisungClassic {
	
	private $_apiUrl = '';
	
	private $_apiKey = '';
	
	private $_relatedBanks = array();
	
	private $_SofortLib_iDeal_Banks = null;
	
	protected $_password;
	
	protected $_userId;
	
	protected $_projectId;
	
	protected $_paymentUrl = 'https://www.sofort.com/payment/ideal';
	
	protected $_hashFields = array(
		'user_id',
		'project_id',
		'sender_holder',
		'sender_account_number',
		'sender_bank_code',
		'sender_country_id',
		'amount',
		'reason_1',
		'reason_2',
		'user_variable_0',
		'user_variable_1',
		'user_variable_2',
		'user_variable_3',
		'user_variable_4',
		'user_variable_5',
	);
	
	
	public function __construct($configKey, $password, $hashFunction = 'sha1') {
		list($userId, $projectId, $apiKey) = explode(':', $configKey);
		$this->_password = $password;
		$this->_userId = $this->params['user_id'] = $userId;
		$this->_projectId = $this->params['project_id'] = $projectId;
		$this->_hashFunction = strtolower($hashFunction);
		$this->_paymentUrl = $this->_getPaymentDomain();
		$this->_SofortLib_iDeal_Banks = new SofortLib_iDeal_Banks($configKey, $this->_paymentUrl);
	}
	
	
	/**
	 *
	 * Set sender's country id
	 * @param string $senderCountryId
	 * @return instance
	 */
	public function setSenderCountryId($senderCountryId = 'NL') {
		$this->params['sender_country_id'] = $senderCountryId;
	}
	
	
	/**
	 *
	 * Set sender's bank code
	 * @param string $senderBankCode
	 * @return instance
	 */
	public function setSenderBankCode($senderBankCode) {
		$this->params['sender_bank_code'] = $senderBankCode;
		return $this;
	}
	
	
	public function getError(){
		return $this->error;
	}
	
	
	/**
	 * Get related banks of iDeal
	 * @return array
	 */
	public function getRelatedBanks() {
		$this->_SofortLib_iDeal_Banks->sendRequest();
		return $this->_SofortLib_iDeal_Banks->getBanks();
	}
	
	
	protected function _getPaymentDomain() {
		return (getenv('idealApiUrl') != '') ? getenv('idealApiUrl') : $this->_paymentUrl;
	}
}
?>