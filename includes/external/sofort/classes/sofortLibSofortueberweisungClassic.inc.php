<?php
/**
 * Extends Multipay to deal with sofortueberweisung classic transactions
 *
 * @author SOFORT AG (integration@sofort.com)
 *
 * @copyright 2010-2014 SOFORT AG
 *
 * @license Released under the GNU LESSER GENERAL PUBLIC LICENSE (Version 3)
 * @license http://www.gnu.org/licenses/lgpl.html
 *
 * @version SofortLib 2.1.1
 *
 * @link http://www.sofort.com/ official website
 */
class SofortLibSofortueberweisungClassic extends Sofort\SofortLib\Multipay {

	const SOFORT_CLASSIC_URL = 'https://www.sofortueberweisung.de/payment/start';

	/**
	 * Fields to be sent with the request
	 *
	 * @var array
	 */
	protected $_hashFields = array(
		'user_id',
		'project_id',
		'sender_holder',
		'sender_account_number',
		'sender_bank_code',
		'sender_country_id',
		'amount',
		'currency_id',
		'reason_1',
		'reason_2',
		'user_variable_0',
		'user_variable_1',
		'user_variable_2',
		'user_variable_3',
		'user_variable_4',
		'user_variable_5',
	);

	/**
	 * Container for the has function to be used
	 *
	 * @var string
	 */
	protected $_hashFunction;

	/**
	 * Ideal Password
	 *
	 * @var string
	 */
	protected $_password;

	/**
	 * Project ID from sofort.com
	 *
	 * @var string
	 */
	protected $_projectId;

	/**
	 * User ID from sofort.com
	 *
	 * @var string
	 */
	protected $_userId;

	/**
	 * API-URL
	 *
	 * @var string
	 */
	protected $_paymentUrl = self::SOFORT_CLASSIC_URL;


	/**
	 * Constructor for SofortLibSofortueberweisungClassic
	 *
	 * @param string $configKey
	 * @param string $password
	 * @param string $hashFunction (default sha1)
	 * @return \SofortLibSofortueberweisungClassic
	 */
	public function __construct($userId, $projectId, $password, $hashFunction = 'sha1') {
		$this->_password = $password;
		$this->_userId = $this->_parameters['user_id'] = $userId;
		$this->_projectId = $this->_parameters['project_id'] = $projectId;
		$this->_hashFunction = strtolower($hashFunction);
		$this->_paymentUrl = $this->_getPaymentDomain();
	}


	/**
	 * Get the hash value
	 *
	 * @param string $data string to be hashed
	 * @param string @hashFunction (default sha1)
	 * @return string the hash
	 */
	public function getHashHexValue($data, $hashFunction = 'sha1') {
		if ($hashFunction == 'sha1') return sha1($data);
		if ($hashFunction == 'md5') return md5($data);

		//mcrypt installed?
		if (function_exists('hash') && in_array($hashFunction, hash_algos())) {
			return hash($hashFunction, $data);
		}

		return false;
	}


	/**
	 * Getter for payment URL
	 *
	 * @return string Url
	 */
	public function getPaymentUrl() {
		//fields required for hash
		$hashFields = $this->_hashFields;
		//build parameter-string for hashing
		$hashString = '';

		foreach ($hashFields as $value) {
			if (array_key_exists($value, $this->_parameters)) {
				$hashString .= $this->_parameters[$value];
			}

			$hashString .= '|';
		}

		$hashString .= $this->_password;
		//calculate hash
		$hash = $this->getHashHexValue($hashString, $this->_hashFunction);
		$this->_parameters['hash'] = $hash;
		//create parameter string
		$paramString = '';

		foreach ($this->_parameters as $key => $value) {
			$paramString .= $key.'='.urlencode($value).'&';
		}

		$paramString = substr($paramString, 0, -1); //remove last "&"

		return $this->_paymentUrl.'?'.$paramString;
	}


	/**
	 * Setter for Amount
	 *
	 * @param float $amount
	 * @return SofortLibSofortueberweisungClassic $this
	 */
	public function setAmount($amount = 0.00) {
		$this->_setAmount($amount);

		return $this;
	}


	/**
	 * Setter for currency
	 *
	 * @param float $currency
	 * @return SofortLibSofortueberweisungClassic $this
	 */
	public function setCurrencyCode($currency = 0.00) {
		$this->_parameters['currency_id'] = $currency;

		return $this;
	}


	/**
	 * Setter for user_variable_0
	 *
	 * @param string $order_id
	 * @return SofortLibSofortueberweisungClassic $this
	 */
	public function setOrderID($order_id) {
		$this->_parameters['user_variable_0'] = $order_id;

		return $this;
	}


	/**
	 * Setter for user_variable_1
	 *
	 * @param string $customer_id
	 * @return SofortLibSofortueberweisungClassic $this
	 */
	public function setCustomerID($customer_id) {
		$this->_parameters['user_variable_1'] = $customer_id;

		return $this;
	}


	/**
	 * Set the url where you want redirect on success
	 * being sent to. Use SofortLibTransactionData
	 * to further process that notification
	 *
	 * @param string $successUrl url
	 * @return SofortLibSofortueberweisungClassic $this
	 */
	public function setSuccessUrl($successUrl, $redirect = true) {
		$this->_parameters['user_variable_2'] = $successUrl;

		return $this;
	}


	/**
	 * Set the url where you want redirect on abort
	 * being sent to. Use SofortLibTransactionData
	 * to further process that notification
	 *
	 * @param string $successUrl url
	 * @return SofortLibSofortueberweisungClassic $this
	 */
	public function setAbortUrl($abortUrl) {
		$this->_parameters['user_variable_3'] = $abortUrl;

		return $this;
	}


	/**
	 * Set the url where you want notification about status changes
	 * being sent to. Use SofortLibTransactionData
	 * to further process that notification
	 *
	 * @param string $notificationUrl url
	 * @return SofortLibSofortueberweisungClassic $this
	 */
	public function setNotificationUrl($notificationUrl, $notifyOn = '') {
		$this->_parameters['user_variable_4'] = $notificationUrl;

		return $this;
	}


	/**
	 * Setter for user_variable_5
	 *
	 * @param string $customer_id
	 * @return SofortLibSofortueberweisungClassic $this
	 */
	public function setCallbackIdentifier($length) {
		$this->_parameters['user_variable_5'] = $this->generatePassword($length);

		return $this;
	}


	/**
	 * Set the reason (Verwendungszweck) for sending money
	 *
	 * @param string $reason1
	 * @param string $reason2 (optional)
	 * @return SofortLibSofortueberweisungClassic $this
	 */
	public function setReason($reason1, $reason2 = '', $productCode = NULL) {
		$this->_parameters['reason_1'] = $reason1;
		$this->_parameters['reason_2'] = $reason2;

		return $this;
	}


	/**
	 * Setter for encoding
	 *
	 * @param string $encoding
	 * @return string
	 */
	public function setEncoding($encoding) {
		$this->_parameters['encoding'] = $encoding;
	}


	/**
	 * Setter for sender's account holder
	 *
	 * @param string $senderAccountHolder
	 * @return string
	 */
	public function setSenderAccountHolder($senderAccountHolder) {
		$this->_parameters['sender_holder'] = $senderAccountHolder;
	}


	/**
	 * Set sender's bank code
	 *
	 * @param string $senderBankCode
	 * @return SofortLibSofortueberweisungClassic $this
	 */
	public function setSenderBankCode($senderBankCode) {
		$this->_parameters['sender_bank_code'] = $senderBankCode;

		return $this;
	}


	/**
	 * Set sender's country id
	 *
	 * @param string $senderCountryId (default NL)
	 * @return SofortLibSofortueberweisungClassic $this
	 */
	public function setSenderCountryId($senderCountryId) {
		$this->_parameters['sender_country_id'] = $senderCountryId;
	}


	/**
	 * Setter for sender and holder
	 *
	 * @param string $senderHolder
	 * @return string
	 */
	public function setSenderHolder($senderHolder) {
		$this->_parameters['sender_holder'] = $senderHolder;
	}


	/**
	 * Getter for the payment domain
	 *
	 * @return string
	 */
	protected function _getPaymentDomain() {
		return (getenv('sofortueberweisungClassicApiUrl') != '') ? getenv('sofortueberweisungClassicApiUrl') : $this->_paymentUrl;
	}


	/**
	 * @param int [optional] $length length of return value, default 24
	 * @return string
	 */
	public static function generatePassword($length = 24) {
		$password = '';

		//we generate about 5-34 random characters [A-Za-z0-9] in every loop
		do {
			$randomBytes = '';
			$strong = false;

			if (function_exists('openssl_random_pseudo_bytes')) { //php >= 5.3
				$randomBytes = openssl_random_pseudo_bytes(32, $strong);//get 256bit
			}

			if (!$strong) { //fallback
				$randomBytes = pack('I*', mt_rand()); //get 32bit (pseudo-random)
			}

			//convert bytes to base64 and remove special chars
			$password .= preg_replace('#[^A-Za-z0-9]#', '', base64_encode($randomBytes));
		} while (strlen($password) < $length);

		return substr($password, 0, $length);
	}


	/**
	 * Getter for user variables
	 *
	 * @param int $i (default 0)
	 * @return string
	 */
	public function getUserVariable($i = 0) {
		return $this->_parameters['user_variable_'.$i];
	}

}