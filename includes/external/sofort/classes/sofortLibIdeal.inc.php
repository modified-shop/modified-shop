<?php
/**
 * Extends Multipay to deal with iDeal transactions
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
class SofortLibIdeal extends Sofort\SofortLib\Ideal {

  var $_parameters;
  
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
	 * Setter for user_variable_5
	 *
	 * @param string $customer_id
	 * @return SofortLibSofortueberweisungClassic $this
	 */
	public function setCallbackIdentifier($length) {
		$this->_parameters['user_variable_2'] = $this->generatePassword($length);

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