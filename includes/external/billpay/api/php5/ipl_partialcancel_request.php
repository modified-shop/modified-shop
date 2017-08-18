<?php

require_once(dirname(__FILE__) . '/ipl_xml_request.php');

/**
 * @author Unknown Artist (support@billpay.de)
 * @copyright Copyright 2010 BillPay GmbH
 * @license commercial
 */
class ipl_partialcancel_request extends ipl_xml_request
{

    private $_cancel_params = array();
    private $_canceled_articles = array();

    private $due_update;
    private $number_of_rates;

    public function is_transaction_credit_order()
    {
        return $this->due_update;
    }

    public function get_due_update()
    {
        return $this->due_update;
    }

    public function get_number_of_rates()
    {
        return $this->number_of_rates;
    }

    public function set_cancel_params($reference, $rebatedecrease, $rebatedecreasegross, $shippingdecrease, $shippingdecreasegross, $currency)
    {
        $this->_cancel_params['reference'] = $reference;
        $this->_cancel_params['rebatedecrease'] = $rebatedecrease;
        $this->_cancel_params['rebatedecreasegross'] = $rebatedecreasegross;
        $this->_cancel_params['shippingdecrease'] = $shippingdecrease;
        $this->_cancel_params['shippingdecreasegross'] = $shippingdecreasegross;
        $this->_cancel_params['currency'] = $currency;
    }

    public function add_canceled_article($articleid, $articlequantity)
    {
        $article = array();
        $article['articleid'] = $articleid;
        $article['articlequantity'] = $articlequantity;

        $this->_canceled_articles[] = $article;
    }

    protected function _send()
    {
        return ipl_core_send_partialcancel_request(
            $this->_ipl_request_url,
            $this->getTraceData(),
            $this->_default_params,
            $this->_cancel_params,
            $this->_canceled_articles
        );
    }

    protected function _process_response_xml($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

}
