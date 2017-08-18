<?php

require_once(dirname(__FILE__) . '/ipl_xml_request.php');

/**
 * @author Unknown Artist (support@billpay.de)
 * @copyright Copyright 2010 BillPay GmbH
 * @license commercial
 */
class ipl_get_billpay_bank_data extends ipl_xml_request
{

    private $_get_billpay_bank_data_params = array();

    // response parameters
    private $account_holder;
    private $account_number;
    private $bank_code;
    private $bank_name;
    private $invoice_reference;
    private $invoice_duedate;
    private $reference;

    public function get_account_holder()
    {
        return $this->account_holder;
    }

    public function get_account_number()
    {
        return $this->account_number;
    }

    public function get_bank_code()
    {
        return $this->bank_code;
    }

    public function get_bank_name()
    {
        return $this->bank_name;
    }

    public function get_invoice_reference()
    {
        return $this->invoice_reference;
    }

    public function get_invoice_duedate()
    {
        return $this->invoice_duedate;
    }

    public function set_order_reference($reference)
    {
        $this->_get_billpay_bank_data_params['reference'] = $reference;
    }

    protected function _send()
    {
        return ipl_core_send_get_billpay_bank_data_request(
            $this->_ipl_request_url,
            $this->getTraceData(),
            $this->_default_params,
            $this->_get_billpay_bank_data_params
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
