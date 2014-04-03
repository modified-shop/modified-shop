<?php

require_once(dirname(__FILE__).'/ipl_xml_request.php');

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_get_billpay_bank_data extends ipl_xml_request {
	
	private $_get_billpay_bank_data_params = array();

	// response parameters
	private $account_holder;
	private $account_number;
	private $bank_code;
	private $bank_name;
	private $invoice_reference;
	private $invoice_duedate;
	private $reference;
	
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
	
	function set_order_reference($reference) {
		$this->_get_billpay_bank_data_params['reference'] = $reference;
	}

	function _send() {
		return ipl_core_send_get_billpay_bank_data_request($this->_ipl_request_url, $this->_default_params, $this->_get_billpay_bank_data_params);
	}
	
	function _process_response_xml($data) {
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
	}
	
	function _process_error_response_xml($data) {
		if (key_exists('status', $data)) {
			$this->status = $data['status'];
		}
	}
	
}

?>