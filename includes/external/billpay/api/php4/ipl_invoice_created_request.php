<?php

require_once(dirname(__FILE__).'/ipl_xml_request.php');

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_invoice_created_request extends ipl_xml_request {
	
	var $_invoice_params = array();
	
	// bank account
	var $account_holder;
	var $account_number;
	var $bank_code;
	var $bank_name;
	var $invoice_reference;
	var $invoice_duedate;
	var $activation_performed;
	
	var $payment_info_html;
	var $payment_info_plain;
		
	var $dues;
	
	function get_account_holder() {
		return $this->account_holder;
	}
	function get_account_number() {
		return $this->account_number;
	}
	function get_bank_code() {
		return $this->bank_code;
	}
	function get_bank_name() {
		return $this->bank_name;
	}
	function get_invoice_reference() {
		return $this->invoice_reference;
	}
	function get_invoice_duedate() {
		return $this->invoice_duedate;
	}
	function get_activation_performed() {
		return $this->activation_performed;
	}
	function get_payment_info_html() {
		return $this->payment_info_html;
	}
	function get_payment_info_plain() {
		return $this->payment_info_plain;
	}
	
	function get_dues() {
		return $this->dues;
	}
	 	
	function set_invoice_params($carttotalgross, $currency, $reference, $delayindays = 0) {
		$this->_invoice_params['carttotalgross'] = $carttotalgross;
		$this->_invoice_params['currency'] = $currency;
		$this->_invoice_params['reference'] = $reference;
		$this->_invoice_params['delayindays'] = $delayindays;
	}
	
	function set_payment_info_params($showhtmlinfo, $showplaininfo) {
		$this->_payment_info_params['htmlinfo'] = $showhtmlinfo ? "1" : "0";
		$this->_payment_info_params['plaininfo'] = $showplaininfo ? "1" : "0";
	}
	
	function _send() {
		return ipl_core_send_invoice_request($this->_ipl_request_url, $this->_default_params, $this->_invoice_params, $this->_payment_info_params);
	}
	
	function _process_response_xml($data) {
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
	}
	
}

?>