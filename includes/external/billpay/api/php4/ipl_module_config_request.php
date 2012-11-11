<?php

require_once(dirname(__FILE__).'/ipl_xml_request.php');

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_module_config_request extends ipl_xml_request {
	
	var $invoicestatic 		   = 0;
	var $directdebitstatic     = 0;
	var $hirepurchasestatic    = 0;
	var $invoicebusinessstatic = 0;
		
	var $invoicemin 		= 0;
	var $directdebitmin 	= 0;
	var $hirepurchasemin 	= 0;
	var $invoicebusinessmin = 0;
	
	var $active 				= false;
	var $invoiceallowed 		= false;
	var $directdebitallowed 	= false;
	var $hirepurchaseallowed 	= false;
	var $invoicebusinessallowed	= false;
	
	var $terms = array();

	var $_locale = array();

	function is_active() {
		return $this->active;
	}
	function is_invoice_allowed() {
		return $this->invoiceallowed;
	}
	function is_invoicebusiness_allowed() {
		return $this->invoicebusinessallowed;
	}
	function is_direct_debit_allowed() {
		return $this->directdebitallowed;	
	}
	function is_hire_purchase_allowed() {
		return $this->hirepurchaseallowed;
	}
	function get_invoice_min_value() {
		return $this->invoicemin;
	}
	function get_invoicebusiness_min_value() {
		return $this->invoicebusinessmin;
	}
	function get_direct_debit_min_value() {
		return $this->directdebitmin;
	}
	function get_hire_purchase_min_value() {
		return $this->hirepurchasemin;
	}
	function get_static_limit_invoice() {
		return $this->invoicestatic;
	}
	function get_static_limit_invoicebusiness() {
		return $this->invoicebusinessstatic;
	}
	function get_static_limit_direct_debit() {
		return $this->directdebitstatic;
	}
	function get_static_limit_hire_purchase() {
		return $this->hirepurchasestatic;
	}
	function get_terms() {
		return $this->terms;
	}
	
	function get_config_data() {
		return array(
			'is_active' => $this->is_active(),
			'is_allowed_invoice' => $this->is_invoice_allowed(),
			'is_allowed_invoicebusiness' => $this->is_invoicebusiness_allowed(),
			'is_allowed_directdebit' => $this->is_direct_debit_allowed(),
			'is_allowed_transactioncredit' => $this->is_hire_purchase_allowed(),
			'minvalue_invoice' => $this->get_invoice_min_value(),
			'minvalue_invoicebusiness' => $this->get_invoicebusiness_min_value(),
			'minvalue_directdebit' => $this->get_direct_debit_min_value(),
			'minvalue_transactioncredit' => $this->get_hire_purchase_min_value(),
			'maxvalue_invoice' => $this->get_static_limit_invoice(),
			'maxvalue_invoicebusiness' => $this->get_static_limit_invoicebusiness(),
			'maxvalue_directdebit' => $this->get_static_limit_direct_debit(),
			'maxvalue_transactioncredit' => $this->get_static_limit_hire_purchase()
		);
	}

	function _process_response_xml($data) {
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
	}

	function set_locale($country, $currency, $language) {
		$this->_locale['country'] = $country;
		$this->_locale['currency'] = $currency;
		$this->_locale['language'] = $language;
	}

	function _send() {
		return ipl_core_send_module_config_request(
			$this->_ipl_request_url,
			$this->_default_params,
			$this->_locale
		);
	}
}

?>