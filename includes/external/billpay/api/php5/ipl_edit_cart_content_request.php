<?php

require_once(dirname(__FILE__) . '/ipl_xml_request.php');

/**
 * @author Unknown Artist (support@billpay.de)
 * @copyright Copyright 2010 BillPay GmbH
 * @license commercial
 */
class ipl_edit_cart_content_request extends ipl_xml_request
{
    private $_totals = array();
    private $_article_data = array();
    private $_invoice_list = array();

    private $due_update;
    private $number_of_rates;

    // paylater specific
    private $instalment_count;
    private $duration;
    private $fee_percent;
    private $fee_total;
    private $total_amount;
    private $effective_annual;
    private $nominal_annual;
    private $dues = array();

    // prepayment
    private $async_amount;

    public function get_due_update()
    {
        return $this->due_update;
    }

    public function get_number_of_rates()
    {
        return $this->number_of_rates;
    }

    // ------------------ paylater specific ------------------ //

    public function get_instalment_count()
    {
        return $this->instalment_count;
    }

    public function get_duration()
    {
        return $this->duration;
    }

    public function get_fee_percent()
    {
        return $this->fee_percent;
    }

    public function get_fee_total()
    {
        return $this->fee_total;
    }

    public function get_total_amount()
    {
        return $this->total_amount;
    }

    public function get_effective_annual()
    {
        return $this->effective_annual;
    }

    public function get_nominal_annual()
    {
        return $this->nominal_annual;
    }

    public function get_dues()
    {
        return $this->dues;
    }

    // -------- pre payment specific ----- //

    public function get_prepayment_amount()
    {
        return $this->async_amount;
    }

    public function add_article($articleid, $articlequantity, $articlename, $articledescription,
                                $article_price, $article_price_gross, $invoice_number = "")
    {

        if ($articlequantity < 1) {
            return; // we don't send empty records
        }
        $article = array();
        $article['articleid'] = $articleid;
        $article['articlequantity'] = $articlequantity;
        $article['articlename'] = $articlename;
        $article['articledescription'] = $articledescription;
        $article['articleprice'] = $article_price;
        $article['articlepricegross'] = $article_price_gross;

        $this->_article_data[] = $article;
        if ($invoice_number != "") {
            $this->_invoice_list[$invoice_number]['article_data'][] = $article;
        }
    }

    public function add_invoice($rebate, $rebate_gross, $shipping_price, $shipping_price_gross,
                                $cart_total_price, $cart_total_price_gross,
                                $currency, $invoice_number)
    {
        $invoice = array();
        $invoice['rebate'] = $rebate;
        $invoice['rebategross'] = $rebate_gross;
        $invoice['shippingprice'] = $shipping_price;
        $invoice['shippingpricegross'] = $shipping_price_gross;
        $invoice['carttotalprice'] = $cart_total_price;
        $invoice['carttotalpricegross'] = $cart_total_price_gross;
        $invoice['currency'] = $currency;
        $invoice['article_data'] = array();
        $this->_invoice_list[$invoice_number] = $invoice;
    }

    public function set_total($rebate, $rebate_gross, $shipping_name, $shipping_price,
                              $shipping_price_gross, $cart_total_price, $cart_total_price_gross,
                              $currency, $reference)
    {
        $this->_totals['shippingname'] = $shipping_name;
        $this->_totals['shippingprice'] = $shipping_price;
        $this->_totals['shippingpricegross'] = $shipping_price_gross;
        $this->_totals['rebate'] = $rebate;
        $this->_totals['rebategross'] = $rebate_gross;
        $this->_totals['carttotalprice'] = $cart_total_price;
        $this->_totals['carttotalpricegross'] = $cart_total_price_gross;
        $this->_totals['currency'] = $currency;
        $this->_totals['reference'] = $reference;
    }


    protected function _send()
    {
        return ipl_core_send_edit_cart_content_request(
            $this->_ipl_request_url,
            $this->getTraceData(),
            $this->_default_params,
            $this->_totals,
            $this->_article_data,
            $this->_invoice_list
        );
    }

    protected function _process_response_xml($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    protected function _process_error_response_xml($data)
    {
        if (isset($data['status'])) {
            $this->status = $data['status'];
        }
    }
}
