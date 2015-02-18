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
    var $_order_history_attr    = array();
	var $_order_history_data 	= array();
	var $_company_details		= array();

	var $_payment_info_params	= array();
	var $_fraud_detection		= array();

	var $_preauth_params         = array();
	var $_async_capture_params   = array();

	var $_payment_type;

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
	var $activation_performed;

	var $_terms_accepted = false;
	var $_capture_request_necessary = true;
	var $_expected_days_till_shipping = 0;

	var $standard_information_pdf;
	var $email_attachment_pdf;

	var $payment_info_html;
	var $payment_info_plain;

    // rate payment specific
    var $instalment_count;
    var $duration;
    var $fee_percent;
    var $fee_total;
    var $total_amount;
    var $effective_annual;
    var $nominal_annual;
    var $base_amount;
    var $cart_amount;
    var $surcharge;
    var $interest;
    var $dues = array();

    // pre approved specific
    var $async_amount;
    var $rate_plan_url;
    var $external_redirect_url;
    var $campaign_type;
    var $campaign_display_text;
    var $campaign_display_image_url;

	// parameters needed for prescore
	var $is_prescored = 0;

	// ctr
	function ipl_preauthorize_request($ipl_request_url, $payment_type) {
		$this->_payment_type = $payment_type;
		parent::ipl_xml_request($ipl_request_url);
	}

    function setTraceShopType($sShopType)
    {
        $this->aTraceData['shop_type'] = $sShopType;
    }

    function setTraceShopVersion($sVersion)
    {
        $this->aTraceData['shop_version'] = $sVersion;
    }

    function setTraceShopDomain($sShopDomain)
    {
        $this->aTraceData['shop_domain'] = $sShopDomain;
    }

    function setTracePluginVersion($sVersion)
    {
        $this->aTraceData['plugin_version'] = $sVersion;
    }

    function getTraceData()
    {
        $aTraceData = parent::getTraceData();

        if (isset($aTraceData['shop_domain']) === false) {
            $aTraceData['shop_domain'] = $_SERVER['SERVER_NAME'];
        }

        $aTraceData['php_version'] = phpversion();
        $aTraceData['os_version']  = @php_uname('a');
        $aTraceData['api_version'] = IPL_CORE_API_VERSION;

        ksort($aTraceData);

        return $aTraceData;
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
	function get_activation_performed() {
	    return $this->activation_performed;
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
    function get_async_amount() {
        return $this->async_amount;
    }
    function get_prepayment_amount() {
        return $this->async_amount;
    }
    function get_external_redirect_url() {
        return $this->external_redirect_url;
    }
    function get_rate_plan_url() {
        return $this->rate_plan_url;
    }
    function get_campaign_type() {
        return $this->campaign_type;
    }
    function get_campaign_display_text() {
        return $this->campaign_display_text;
    }
    function get_campaign_display_image_url() {
        return $this->campaign_display_image_url;
    }

    // ------------------ paylater specific ------------------ //
    function get_instalment_count()
    {
        return $this->instalment_count;
    }

    function get_duration()
    {
        return $this->duration;
    }

    function get_fee_percent()
    {
        return $this->fee_percent;
    }

    function get_fee_total()
    {
        return $this->fee_total;
    }

    function get_total_amount()
    {
        return $this->total_amount;
    }

    function get_effective_annual()
    {
        return $this->effective_annual;
    }

    function get_nominal_annual()
    {
        return $this->nominal_annual;
    }

    /**
     * Returns base value of an order (base order + tax)
     * @return int
     */
    function get_base_amount()
    {
        return (int)$this->base_amount;
    }

    /**
     * Returns cart value (base order + shipping fee + tax)
     * @return int
     */
    function get_cart_amount()
    {
        return (int)$this->cart_amount;
    }

    /**
     * Returns interest surcharge (how much TC/PL costs)
     * @return int
     */
    function get_surcharge()
    {
        return (int)$this->surcharge;
    }

    /**
     * Returns interest rate in 0.01 of percent
     * ie. 100 means 1% interest rate
     * @return int
     */
    function get_interest()
    {
        return (int)$this->interest;
    }

    function get_dues()
    {
        return $this->dues;
    }

	function set_customer_details($customer_id, $customer_type, $salutation, $title,
		$first_name, $last_name, $street, $street_no, $address_addition, $zip,
		$city, $country, $email, $phone, $cell_phone, $birthday, $language, $ip, $customerGroup) {

			$this->_customer_details['customerid'] 		= $customer_id;
			$this->_customer_details['customertype'] 	= $customer_type;
			$this->_customer_details['salutation'] 		= $salutation;
			$this->_customer_details['title'] 			= $title;
			$this->_customer_details['firstName']       = $first_name;
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
            $this->_customer_details['customerGroup']   = $customerGroup;
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

    function add_order_history_attributes($iMerchantCustomerLimit, $iRepeatCustomer) {
        $this->_order_history_attr = array(
            'merchant_customer_limit' => (int)$iMerchantCustomerLimit,
            'repeat_customer'         => (int)$iRepeatCustomer,
        );
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
			$currency, $reference, $reference2 = "") {
		$this->_totals['shippingname'] 			= $shipping_name;
		$this->_totals['shippingprice'] 		= $shipping_price;
		$this->_totals['shippingpricegross'] 	= $shipping_price_gross;
		$this->_totals['rebate']				= $rebate;
		$this->_totals['rebategross'] 			= $rebate_gross;
		$this->_totals['carttotalprice'] 		= $cart_total_price;
		$this->_totals['carttotalpricegross'] 	= $cart_total_price_gross;
		$this->_totals['currency'] 				= $currency;
		$this->_totals['reference']				= $reference;
		$this->_totals['reference2']			= $reference2;
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

    /**
     * Sets rate info for TC and PL.
     *      Usually, term is the same as rate count, so it's not sent
     *      In case of big TC CHF order, rate count is always "four" and we need to set real term
     * @param int $rate_count
     * @param int $total_amount
     * @param int $term             (optional) Set, if different than $rate_count.
     */
    function set_rate_request($rate_count, $total_amount, $term = 0) {
        $this->_rate_request_data['ratecount']      = $rate_count;
        $this->_rate_request_data['totalamount']    = $total_amount;
        if ($term) {
            $this->_rate_request_data['term']       = $term;
        }
    }

	function set_payment_info_params($showhtmlinfo, $showplaininfo) {
		$this->_payment_info_params['htmlinfo']  = $showhtmlinfo ? "1" : "0";
		$this->_payment_info_params['plaininfo'] = $showplaininfo ? "1" : "0";
	}

	function set_fraud_detection($session_id) {
		$this->_fraud_detection['session_id'] = $session_id;
	}

	function set_prescore_enable($is_prescored, $bptid) {
        if($is_prescored == true) {
    	    $this->is_prescored = 1;
    	    $this->bptid = $bptid;
    	    $this->_preauth_params['is_prescored'] = 1;
    	    $this->_preauth_params['bptid'] = $bptid;
	    } else {
	        $this->is_prescored = 0;
	        $this->_preauth_params['is_prescored'] = 0;
	    }
	}
	
	function set_async_capture($redirect_url,$notify_url){
		$this->_async_capture_params['redirect_url']= $redirect_url;
		$this->_async_capture_params['notify_url'] 	= $notify_url;
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
            $this->getTraceData(),
			$this->_default_params,
		    $this->_preauth_params,
			$this->_customer_details,
			$this->_shippping_details,
			$this->_bank_account,
			$this->_totals,
			$this->_article_data,
            $this->_order_history_attr,
			$this->_order_history_data,
			$this->_rate_request_data,
			$this->_company_details,
			$this->_payment_info_params,
			$this->_fraud_detection,
			$this->_async_capture_params
		);
	}

	function _process_response_xml($data) {
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
	}

    function _process_error_response_xml($data) {
        if (isset($data['status'])) {
            $this->status = $data['status'];
        }
        if (isset($data['validation_errors'])) {
            $this->_validation_errors = $data['validation_errors'];
        }
    }
}
