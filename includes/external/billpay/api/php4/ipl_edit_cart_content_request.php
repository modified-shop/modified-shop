<?php

require_once(dirname(__FILE__).'/ipl_xml_request.php');

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_edit_cart_content_request extends ipl_xml_request {
	var $_totals 				= array();
	var $_article_data 			= array();
	
	var $due_update;
	var $number_of_rates;
	
	function get_due_update() {
		return $this->due_update;
	}
	
	function get_number_of_rates() {
		return $this->number_of_rates;
	}
	
	// ctr
	function ipl_edit_cart_content_request($ipl_request_url) {
		parent::ipl_xml_request($ipl_request_url);
	}
	
	function add_article($articleid, $articlequantity, $articlename, $articledescription,
		$article_price, $article_price_gross) {
			$article = array();
			$article['articleid'] 			= $articleid;
			$article['articlequantity'] 	= $articlequantity;
			$article['articlename'] 		= $articlename;
			$article['articledescription'] 	= $articledescription;
			$article['articleprice'] 		= $article_price;
			$article['articlepricegross'] 	= $article_price_gross;
			
			$this->_article_data[] = $article;
	}
	
		
	function set_total($rebate, $rebate_gross, $shipping_name, $shipping_price, 
			$shipping_price_gross, $cart_total_price, $cart_total_price_gross, 
			$currency, $reference) {
		$this->_totals['shippingname'] 			= $shipping_name;
		$this->_totals['shippingprice']			= $shipping_price;
		$this->_totals['shippingpricegross'] 	= $shipping_price_gross;
		$this->_totals['rebate']				= $rebate;
		$this->_totals['rebategross'] 			= $rebate_gross;
		$this->_totals['carttotalprice'] 		= $cart_total_price;
		$this->_totals['carttotalpricegross'] 	= $cart_total_price_gross;
		$this->_totals['currency'] 				= $currency;
		$this->_totals['reference'] 			= $reference;
	}
	

	function _send() {
		$attributes = array();
		return ipl_core_send_edit_cart_content_request(
			$this->_ipl_request_url, 
			$this->_default_params, 
			$this->_totals, 
			$this->_article_data 
		);
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