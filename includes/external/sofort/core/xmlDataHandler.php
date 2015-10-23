<?php
require_once(dirname(__FILE__).'/abstractDataHandler.php');
require_once(dirname(__FILE__).'/lib/xmlToArray.php');
require_once(dirname(__FILE__).'/lib/arrayToXml.php');

/**
 * Handler for XML Data
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
class XmlDataHandler extends AbstractDataHandler {
	
	/**
	 * Should be moved to somewhere else (where it fits better)
	 *
	 * @param string $configKey
	 * @return \XmlDataHandler
	 */
	public function __construct($configKey) {
		parent::__construct($configKey);
	}
	
	
	/**
	 * Preparing data and parsing result received
	 * 
	 * @param array $data
	 * @return void
	 */
	public function handle($data) {
		$this->_request = ArrayToXml::render($data);
		$this->_rawRequest = $this->_request;
		$xmlResponse = self::sendMessage($this->_request);
		
 		if (!in_array($this->getConnection()->getHttpStatusCode(), array('200', '301', '302'))) {
			$this->_response = array(
				'errors' => array(
					'error' => array(
						'code' => array('@data' => $this->getConnection()->getHttpStatusCode()),
						'message' => array('@data' => $this->getConnection()->error)
					)
				)
			);
 		} else {
			try {
				$this->_response = XmlToArray::render($xmlResponse);
			} catch (Exception $e) {
				$this->_response = array(
					'errors' => array(
						'error' => array(
							'code' => array('@data' => '0999'),
							'message' => array('@data' => $e->getMessage())
						)
					)
				);
			}
 		}
		$this->_rawResponse = $xmlResponse;
 	}
	
	
	/**
	 * Sending Data to connection and returning results
	 * 
	 * @param string $data
	 * @return string
	 */
	public function sendMessage($data) {
		return $this->getConnection()->post($data);
	}
}