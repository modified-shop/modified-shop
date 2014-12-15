<?php
require_once(DIR_FS_EXTERNAL.'sofort/core/sofortLibAbstract.inc.php');

/**
 * This class encapsulates retrieval of listed banks of the Netherlands
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
class SofortLibIdealBanks extends SofortLibAbstract {

	const IDEAL_BANKS_URL = 'https://www.sofort.com/payment/ideal/banks';

	/**
	 * Array for the banks and Ids returned from the API
	 *
	 * @var array
	 */
	protected $_banks = array();


	/**
	 * Constructor for SofortLibIDealBanks
	 *
	 * @param string $configKey
	 * @param string $apiUrl (optional)
	 * @return \SofortLibIdealBanks
	 */
	public function __construct($configKey, $apiUrl = '') {
		$this->_rootTag = 'ideal';

		if ($apiUrl == '') $apiUrl = (getenv('idealApiUrl') != '') ? getenv('idealApiUrl').'/banks' : self::IDEAL_BANKS_URL;

		parent::__construct($configKey, $apiUrl);
	}


	/**
	 * Getter for bank list
	 *
	 * @return array
	 */
	public function getBanks() {
		return $this->_banks;
	}


	/**
	 * Parse the xml (override)
	 *
	 * @see SofortLib_Abstract::_parse()
	 * @return void
	 */
	protected function _parse() {
		if (isset($this->_response['ideal']['banks']['bank'][0]['code']['@data'])) {
			foreach($this->_response['ideal']['banks']['bank'] as $key => $bank) {
				$this->_banks[$key]['id'] = $bank['code']['@data'];
				$this->_banks[$key]['text'] = $bank['name']['@data'];
			}
		}
	}

}