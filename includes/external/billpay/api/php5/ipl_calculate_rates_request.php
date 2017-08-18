<?php

require_once(dirname(__FILE__) . '/ipl_xml_request.php');

/**
 * @author Unknown Artist (support@billpay.de)
 * @copyright Copyright 2010 BillPay GmbH
 * @license commercial
 */
class ipl_calculate_rates_request extends ipl_xml_request
{

    private $_rate_params = array();
    private $options;

    private $_locale = array();

    public function get_options()
    {
        return $this->options;
    }

    public function set_rate_request_params($baseamount, $carttotalgross)
    {
        $this->_rate_params['baseamount'] = $baseamount;
        $this->_rate_params['carttotalgross'] = $carttotalgross;
    }

    public function set_locale($country, $currency, $language)
    {
        $this->_locale['country'] = $country;
        $this->_locale['currency'] = $currency;
        $this->_locale['language'] = $language;
    }

    protected function _send()
    {
        return ipl_core_send_calculate_rates_request(
            $this->_ipl_request_url,
            $this->getTraceData(),
            $this->_default_params,
            $this->_rate_params,
            $this->_locale
        );
    }

    protected function _process_response_xml($data)
    {
        $this->options = $data['options'];
    }
}
