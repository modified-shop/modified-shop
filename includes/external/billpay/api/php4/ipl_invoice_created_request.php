<?php

require_once(dirname(__FILE__).'/ipl_xml_request.php');

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial
 */
class ipl_invoice_created_request extends ipl_xml_request {

	var $_invoice_params      = array();
	var $_payment_info_params = array();
	var $_article_data        = array();

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

	function set_invoice_params($carttotalgross, $currency, $reference,
	            $delayindays = 0, $is_partial = 0, $invoice_number = 0,
	            $rebate = 0, $rebate_gross = 0, $shipping_name = "", $shipping_price = 0,
	            $shipping_price_gross = 0, $cart_total_price = 0) {

	    $this->_invoice_params['carttotalgross']        = $carttotalgross;
		$this->_invoice_params['currency']              = $currency;
		$this->_invoice_params['reference']             = $reference;
		$this->_invoice_params['delayindays']           = $delayindays;
        //Partial activation
        if($is_partial == 1)
        {
            $this->_invoice_params['is_partial']            = $is_partial;
            $this->_invoice_params['invoice_number']        = $invoice_number;
            $this->_invoice_params['shippingname'] 			= $shipping_name;
            $this->_invoice_params['shippingprice'] 		= $shipping_price;
            $this->_invoice_params['shippingpricegross'] 	= $shipping_price_gross;
            $this->_invoice_params['rebate']				= $rebate;
            $this->_invoice_params['rebategross'] 			= $rebate_gross;
            $this->_invoice_params['carttotalprice'] 		= $cart_total_price;
        }
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

	function set_payment_info_params($showhtmlinfo, $showplaininfo) {
		$this->_payment_info_params['htmlinfo'] = $showhtmlinfo ? "1" : "0";
		$this->_payment_info_params['plaininfo'] = $showplaininfo ? "1" : "0";
	}

	function _send() {
		return ipl_core_send_invoice_request(
            $this->_ipl_request_url,
            $this->getTraceData(),
            $this->_default_params,
            $this->_invoice_params,
            $this->_payment_info_params,
            $this->_article_data
        );
	}

	function _process_response_xml($data) {
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
	}
}
