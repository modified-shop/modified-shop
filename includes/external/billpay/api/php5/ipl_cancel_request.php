<?php

require_once(dirname(__FILE__) . '/ipl_xml_request.php');

/**
 * @author Unknown Artist (support@billpay.de)
 * @copyright Copyright 2010 BillPay GmbH
 * @license commercial
 */
class ipl_cancel_request extends ipl_xml_request
{

    private $_cancel_params = array();

    public function set_cancel_params($reference, $cart_total_gross, $currency)
    {
        $this->_cancel_params['reference'] = $reference;
        $this->_cancel_params['carttotalgross'] = $cart_total_gross;
        $this->_cancel_params['currency'] = $currency;
    }

    protected function _send()
    {
        return ipl_core_send_cancel_request(
            $this->_ipl_request_url,
            $this->getTraceData(),
            $this->_default_params,
            $this->_cancel_params
        );
    }

    protected function _process_response_xml($data)
    {
    }

}
