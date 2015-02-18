<?php

require_once(dirname(__FILE__).'/ipl_xml_request.php');

/**
 * @author Jan Wehrs (jan.wehrs@billpay.de)
 * @copyright Copyright 2010 Billpay GmbH
 * @license commercial 
 */
class ipl_edit_cart_content_request extends ipl_xml_request
{
    var $_totals       = array();
    var $_article_data = array();
    var $_invoice_list = array();

	var $due_update;
	var $number_of_rates;

    // paylater specific
    var $duration;
    var $fee_percent;
    var $fee_total;
    var $pre_payment_amount;
    var $total_amount;
    var $effective_annual;
    var $nominal_annual;
    var $dues = array();

    // prepayment
    var $async_amount;

	function get_due_update() {
		return $this->due_update;
	}
	
	function get_number_of_rates() {
		return $this->number_of_rates;
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

    function get_dues()
    {
        return $this->dues;
    }

    // -------- pre payment specific ----- //

    function get_prepayment_amount()
    {
        return $this->async_amount;
    }

	function add_article($articleid, $articlequantity, $articlename, $articledescription,
		$article_price, $article_price_gross, $invoice_number = "") {

            if ($articlequantity < 1) {
                return; // we don't send empty records
            }
			$article = array();
			$article['articleid'] 			= $articleid;
			$article['articlequantity'] 	= $articlequantity;
			$article['articlename'] 		= $articlename;
			$article['articledescription'] 	= $articledescription;
			$article['articleprice'] 		= $article_price;
			$article['articlepricegross'] 	= $article_price_gross;
			
			$this->_article_data[] = $article;
            if($invoice_number != "")
            {
                $this->_invoice_list[$invoice_number]['article_data'][] = $article;
            }
	}

    function add_invoice($rebate, $rebate_gross, $shipping_price, $shipping_price_gross,
                                $cart_total_price, $cart_total_price_gross,
                                $currency, $invoice_number){
        $invoice = array();
        $invoice['rebate']                      = $rebate;
        $invoice['rebategross']                 = $rebate_gross;
        $invoice['shippingprice']               = $shipping_price;
        $invoice['shippingpricegross']          = $shipping_price_gross;
        $invoice['carttotalprice']              = $cart_total_price;
        $invoice['carttotalpricegross']         = $cart_total_price_gross;
        $invoice['currency']                    = $currency;
        $invoice['article_data']                = array();
        $this->_invoice_list[$invoice_number]   = $invoice;
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
		return ipl_core_send_edit_cart_content_request(
			$this->_ipl_request_url,
            $this->getTraceData(),
			$this->_default_params, 
			$this->_totals, 
			$this->_article_data,
            $this->_invoice_list
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
	}
}
