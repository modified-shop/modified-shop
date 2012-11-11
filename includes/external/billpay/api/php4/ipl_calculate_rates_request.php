<?php

require_once(dirname(__FILE__).'/ipl_xml_request.php');

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_calculate_rates_request extends ipl_xml_request {
	
	var $_rate_params = array();
	var $options;

	var $_locale = array();

	function get_options() {
		return $this->options;
	}
	
	function set_rate_request_params($baseamount, $carttotalgross) {
		$this->_rate_params['baseamount'] 		= $baseamount;
		$this->_rate_params['carttotalgross'] 	= $carttotalgross;
	}
	
	function set_locale($country, $currency, $language) {
		$this->_locale['country'] = $country;
		$this->_locale['currency'] = $currency;
		$this->_locale['language'] = $language;
	}

	function _send() {
		return ipl_core_send_calculate_rates_request(
			$this->_ipl_request_url,
			$this->_default_params,
			$this->_rate_params,
			$this->_locale
		);
	}
	
	function _process_response_xml($data) {
		$this->options = $data['options'];
	}
}

?>