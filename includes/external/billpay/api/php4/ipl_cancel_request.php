<?php

require_once(dirname(__FILE__).'/ipl_xml_request.php');

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_cancel_request extends ipl_xml_request {
	
	var $_cancel_params = array();
	
	function set_cancel_params($reference, $cart_total_gross, $currency) {
		$this->_cancel_params['reference'] = $reference;
		$this->_cancel_params['carttotalgross'] = $cart_total_gross;
		$this->_cancel_params['currency'] = $currency;
	}
	
	function _send() {
		return ipl_core_send_cancel_request($this->_ipl_request_url, $this->_default_params, $this->_cancel_params);
	}
	
	function _process_response_xml($data) {
	}
	
}

?>