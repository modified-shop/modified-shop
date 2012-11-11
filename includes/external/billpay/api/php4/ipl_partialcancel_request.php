<?php

require_once(dirname(__FILE__).'/ipl_xml_request.php');

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_partialcancel_request extends ipl_xml_request {
	
	var $_cancel_params = array();
	var $_canceled_articles = array();
	
	var $due_update;
	var $number_of_rates;

	function is_transaction_credit_order() {
		return $this->due_update;
	}
	
	function get_due_update() {
		return $this->due_update;
	}
	
	function get_number_of_rates() {
		return $this->number_of_rates;
	}
	
	function set_cancel_params($reference, $rebatedecrease, $rebatedecreasegross, $shippingdecrease, $shippingdecreasegross, $currency) {
		$this->_cancel_params['reference'] = $reference;
		$this->_cancel_params['rebatedecrease'] = $rebatedecrease;
		$this->_cancel_params['rebatedecreasegross'] = $rebatedecreasegross;
		$this->_cancel_params['shippingdecrease'] = $shippingdecrease;
		$this->_cancel_params['shippingdecreasegross'] = $shippingdecreasegross;
		$this->_cancel_params['currency'] = $currency;
	}
	
	function add_canceled_article($articleid, $articlequantity) {
		$article = array();
		$article['articleid'] = $articleid;
		$article['articlequantity'] = $articlequantity;
		
		$this->_canceled_articles[] = $article;
	}

	function _send() {
		return ipl_core_send_partialcancel_request(
			$this->_ipl_request_url,
			$this->_default_params,
			$this->_cancel_params,
			$this->_canceled_articles
		);
	}
	
	function _process_response_xml($data) {
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
	}
	
}

?>