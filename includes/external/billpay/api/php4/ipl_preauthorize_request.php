<?php

require_once(dirname(__FILE__).'/ipl_xml_request.php');

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_preauthorize_request extends ipl_xml_request {
	var $_customer_details 		= array();
	var $_shippping_details 	= array();
	var $_totals 				= array();
	var $_bank_account 			= array();
	var $_rate_request_data		= array();
	
	var $_article_data 			= array();
	var $_order_history_data 	= array();
	var $_company_details		= array();
	
	var $_fraud_detection		= array();
	
	var $_payment_type;
	
	var $status;
	var $bptid;
	
	var $corrected_street;
	var $corrected_street_no;
	var $corrected_zip;
	var $corrected_city;
	var $corrected_country;
	
	// parameters needed for auto-capture
	var $account_holder;
	var $account_number;
	var $bank_code;
	var $bank_name;
	var $invoice_reference;
	var $invoice_duedate;
	
	var $_terms_accepted = false;
	var $_capture_request_necessary = true;
	var $_expected_days_till_shipping = 0;
	
	var $standard_information_pdf;
	var $email_attachment_pdf;
	
	var $payment_info_html;
	var $payment_info_plain;
	
	// ctr
	function ipl_preauthorize_request($ipl_request_url, $payment_type) {
		$this->_payment_type = $payment_type;
		parent::ipl_xml_request($ipl_request_url);
	}
	
	function get_terms_accepted() {
		return $this->_terms_accepted;
	}
	function set_terms_accepted($val) {
		$this->_terms_accepted = $val;
	}
	function set_expected_days_till_shipping($val) {
		$this->_expected_days_till_shipping = $val;
	}
	function set_capture_request_necessary($val) {
		$this->_capture_request_necessary = $val;
	}
	
	function get_expected_days_till_shipping() {
		return $this->_expected_days_till_shipping;
	}
	function get_capture_request_nesessary() {
		return $this->_capture_request_necessary;
	}
	function get_payment_type() {
		return $this->_payment_type;
	}
	
	function get_status() {
		return $this->status;
	}
	function get_bptid() {
		return $this->bptid;
	}
	function get_corrected_street() {
		return $this->corrected_street;
	}
	function get_corrected_street_no() {
		return $this->corrected_street_no;
	}
	function get_corrected_zip() {
		return $this->corrected_zip;
	}
	function get_corrected_city() {
		return $this->corrected_city;
	}
	function get_corrected_country() {
		return $this->corrected_country;
	}
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
	function get_standard_information_pdf() {
		return $this->standard_information_pdf;
	}
	function get_email_attachment_pdf() {
		return $this->email_attachment_pdf;
	}
	function get_payment_info_html() {
		return $this->payment_info_html;
	}
	function get_payment_info_plain() {
		return $this->payment_info_plain;
	}
	
	function set_customer_details($customer_id, $customer_type, $salutation, $title, 
		$first_name, $last_name, $street, $street_no, $address_addition, $zip,
		$city, $country, $email, $phone, $cell_phone, $birthday, $language, $ip, $customerGroup) {

			$this->_customer_details['customerid'] 		= $customer_id;
			$this->_customer_details['customertype'] 	= $customer_type;
			$this->_customer_details['salutation'] 		= $salutation;
			$this->_customer_details['title'] 			= $title;
			$this->_customer_details['firstName'] 		= $first_name;
			$this->_customer_details['lastName'] 		= $last_name;
			$this->_customer_details['street'] 			= $street;
			$this->_customer_details['streetNo'] 		= $street_no;
			$this->_customer_details['addressAddition'] = $address_addition;
			$this->_customer_details['zip'] 			= $zip;
			$this->_customer_details['city'] 			= $city;
			$this->_customer_details['country'] 		= $country;
			$this->_customer_details['email'] 			= $email;
			$this->_customer_details['phone'] 			= $phone;
			$this->_customer_details['cellPhone'] 		= $cell_phone;
			$this->_customer_details['birthday'] 		= $birthday;
			$this->_customer_details['language'] 		= $language;
			$this->_customer_details['ip'] 				= $ip;
			$this->_customer_details['customerGroup'] 	= $customerGroup;
	}
	
	
	function set_shipping_details($use_billing_address, $salutation=null, $title=null, $first_name=null, $last_name=null, 
		$street=null, $street_no=null, $address_addition=null, $zip=null, $city=null, $country=null, $phone=null, $cell_phone=null) {
			
			$this->_shippping_details['useBillingAddress'] 	= $use_billing_address ? '1' : '0';
			$this->_shippping_details['salutation'] 		= $salutation;
			$this->_shippping_details['title'] 				= $title;
			$this->_shippping_details['firstName'] 			= $first_name;
			$this->_shippping_details['lastName'] 			= $last_name;
			$this->_shippping_details['street'] 			= $street;
			$this->_shippping_details['streetNo'] 			= $street_no;
			$this->_shippping_details['addressAddition'] 	= $address_addition;
			$this->_shippping_details['zip'] 				= $zip;
			$this->_shippping_details['city'] 				= $city;
			$this->_shippping_details['country'] 			= $country;
			$this->_shippping_details['phone'] 				= $phone;
			$this->_shippping_details['cellPhone'] 			= $cell_phone;
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
	
	
	function add_order_history($horderid, $hdate, $hamount, $hcurrency, $hpaymenttype, $hstatus) {
		$histOrder = array();
		$histOrder['horderid'] 		= $horderid;
		$histOrder['hdate'] 		= $hdate;
		$histOrder['hamount'] 		= $hamount;
		$histOrder['hcurrency'] 	= $hcurrency;
		$histOrder['hpaymenttype'] 	= $hpaymenttype;
		$histOrder['hstatus'] 		= $hstatus;
		
		$this->_order_history_data[] = $histOrder;
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
	
	function set_bank_account($account_holder, $account_number, $sort_code) {
		$this->_bank_account['accountholder'] 	= $account_holder;
		$this->_bank_account['accountnumber'] 	= $account_number;
		$this->_bank_account['sortcode'] 		= $sort_code;
	}
	
	function set_company_details($name, $legalForm, $registerNumber, $holderName, $taxNumber) {
		$this->_company_details['name'] 			= $name;
		$this->_company_details['legalForm'] 		= $legalForm;
		$this->_company_details['registerNumber'] 	= $registerNumber;
		$this->_company_details['holderName'] 		= $holderName;
		$this->_company_details['taxNumber'] 		= $taxNumber;
	}
	
	function set_rate_request($rate_count, $total_amount) {
		$this->_rate_request_data['ratecount'] 		= $rate_count;
		$this->_rate_request_data['totalamount'] 	= $total_amount;
	}
	
	function set_payment_info_params($showhtmlinfo, $showplaininfo) {
		$this->_payment_info_params['htmlinfo'] 	= $showhtmlinfo ? "1" : "0";
		$this->_payment_info_params['plaininfo'] 	= $showplaininfo ? "1" : "0";
	}
	
	function set_fraud_detection($session_id) {
		$this->_fraud_detection['session_id'] = $session_id;
	}

	function _send() {
		$attributes = array();
		$attributes['tcaccepted'] 					= $this->_terms_accepted;
		$attributes['expecteddaystillshipping'] 	= $this->_expected_days_till_shipping;
		$attributes['capturerequestnecessary']		= $this->_capture_request_necessary;
		$attributes['paymenttype']					= $this->_payment_type;
		
		return ipl_core_send_preauthorize_request(
			$this->_ipl_request_url, 
			$attributes, 
			$this->_default_params, 
			$this->_customer_details, 
			$this->_shippping_details, 
			$this->_bank_account, 
			$this->_totals, 
			$this->_article_data, 
			$this->_order_history_data,
			$this->_rate_request_data,
			$this->_company_details,
			$this->_payment_info_params,
			$this->_fraud_detection
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