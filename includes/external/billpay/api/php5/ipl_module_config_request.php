<?php

require_once(dirname(__FILE__) . '/ipl_xml_request.php');

/**
 * @author Unknown Artist (support@billpay.de)
 * @copyright Copyright 2010 BillPay GmbH
 * @license commercial
 */
class ipl_module_config_request extends ipl_xml_request
{

    private $invoicestatic = 0;
    private $invoicebusinessstatic = 0;
    private $directdebitstatic = 0;
    private $hirepurchasestatic = 0;

    private $invoicemin = 0;
    private $invoicebusinessmin = 0;
    private $directdebitmin = 0;
    private $hirepurchasemin = 0;

    private $active = false;
    private $invoiceallowed = false;
    private $invoicebusinessallowed = false;
    private $directdebitallowed = false;
    private $hirepurchaseallowed = false;

    private $terms = array();

    private $_locale = array();

    public function is_active()
    {
        return $this->active;
    }

    public function is_invoice_allowed()
    {
        return $this->invoiceallowed;
    }

    public function is_invoicebusiness_allowed()
    {
        return $this->invoicebusinessallowed;
    }

    public function is_direct_debit_allowed()
    {
        return $this->directdebitallowed;
    }

    public function is_hire_purchase_allowed()
    {
        return $this->hirepurchaseallowed;
    }

    public function get_invoice_min_value()
    {
        return $this->invoicemin;
    }

    public function get_invoicebusiness_min_value()
    {
        return $this->invoicebusinessmin;
    }

    public function get_direct_debit_min_value()
    {
        return $this->directdebitmin;
    }

    public function get_hire_purchase_min_value()
    {
        return $this->hirepurchasemin;
    }

    public function get_static_limit_invoice()
    {
        return $this->invoicestatic;
    }

    public function get_static_limit_invoicebusiness()
    {
        return $this->invoicebusinessstatic;
    }

    public function get_static_limit_direct_debit()
    {
        return $this->directdebitstatic;
    }

    public function get_static_limit_hire_purchase()
    {
        return $this->hirepurchasestatic;
    }

    public function get_terms()
    {
        return $this->terms;
    }

    // --------------------------------- //
    // ---- PAYLATER STATIC OPTIONS ---- //
    public function is_paylater_allowed()
    {
        return true;
    }

    public function is_paylaterbusiness_allowed()
    {
        return false;
    }

    public function get_paylater_min_value()
    {
        return 0;
    }

    public function get_paylaterbusiness_min_value()
    {
        return 0;
    }

    public function get_static_limit_paylater()
    {
        return PHP_INT_MAX;
    }

    public function get_static_limit_paylaterbusiness()
    {
        return 0;
    }

    public function get_config_data()
    {
        return array(
            'is_active' => $this->is_active(),
            'is_allowed_invoice' => $this->is_invoice_allowed(),
            'is_allowed_invoicebusiness' => $this->is_invoicebusiness_allowed(),
            'is_allowed_directdebit' => $this->is_direct_debit_allowed(),
            'is_allowed_transactioncredit' => $this->is_hire_purchase_allowed(),
            'is_allowed_paylater' => $this->is_paylater_allowed(),
            'is_allowed_paylaterbusiness' => $this->is_paylaterbusiness_allowed(),

            'minvalue_invoice' => $this->get_invoice_min_value(),
            'minvalue_invoicebusiness' => $this->get_invoicebusiness_min_value(),
            'minvalue_directdebit' => $this->get_direct_debit_min_value(),
            'minvalue_transactioncredit' => $this->get_hire_purchase_min_value(),
            'minvalue_paylater' => $this->get_paylater_min_value(),
            'minvalue_paylaterbusiness' => $this->get_paylaterbusiness_min_value(),

            'maxvalue_invoice' => $this->get_static_limit_invoice(),
            'maxvalue_invoicebusiness' => $this->get_static_limit_invoicebusiness(),
            'maxvalue_directdebit' => $this->get_static_limit_direct_debit(),
            'maxvalue_transactioncredit' => $this->get_static_limit_hire_purchase(),
            'maxvalue_paylater' => $this->get_static_limit_hire_purchase(),
            'maxvalue_paylaterbusiness' => $this->get_static_limit_hire_purchase(),
        );
    }

    protected function _process_response_xml($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function set_locale($country, $currency, $language)
    {
        $this->_locale['country'] = $country;
        $this->_locale['currency'] = $currency;
        $this->_locale['language'] = $language;
    }

    protected function _send()
    {
        return ipl_core_send_module_config_request(
            $this->_ipl_request_url,
            $this->getTraceData(),
            $this->_default_params,
            $this->_locale
        );
    }
}
