<?php

require_once DIR_FS_CATALOG . 'includes/external/billpay/base/billpayBase.php';

class BillPay extends BillPayBase {

    protected $_paymentIdentifier = billpayBase::PAYMENT_METHOD_INVOICE;

    protected $ucPaymentName = 'invoice';

    protected function _getPaymentType() {
        return IPL_CORE_PAYMENT_TYPE_INVOICE;
    }

    protected function _getMinValue($config) {
        if (defined('MODULE_PAYMENT_BILLPAY_MIN_AMOUNT')) {
            return MODULE_PAYMENT_BILLPAY_MIN_AMOUNT;
        }
        return 0;
    }

    protected function _getStaticLimit($config) {
        if ($this->b2b_active == 'BOTH') {
            return max($config['static_limit_invoice'], $config['static_limit_invoicebusiness']);
        }

        if ($this->b2b_active == 'B2C') {
            return $config['static_limit_invoice'];
        }
        else {
            return $config['static_limit_invoicebusiness'];
        }
    }

    protected function _is_b2b_allowed($config) {
        return ($config['static_limit_invoicebusiness'] > 0);
    }

    protected function _is_b2c_allowed($config) {
        return ($config['static_limit_invoice'] > 0);
    }

    /**
     * Event executed during payment method installation.
     */
    public function onInstall()
    {
        $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_B2BCONFIG";
        $this->_logDebug("Setting local key: $configuration_key");
        $table_config = TABLE_CONFIGURATION;
        xtc_db_query("INSERT INTO $table_config (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
          VALUES ('$configuration_key', 'B2C', '6', '0', 'xtc_cfg_select_option(array(\'B2C\', \'B2B\', \'BOTH\'), ', now())");
    }

    /**
     * Event executed while checking for plugin configuration keys.
     * @param $config_array
     * @return array
     */
    public function onKeys($config_array)
    {
        if (defined('MODULE_PAYMENT_' . $this->_paymentIdentifier . '_B2BCONFIG')) {
            $config_array[] = 'MODULE_PAYMENT_' . $this->_paymentIdentifier . '_B2BCONFIG';
        }
        return $config_array;
    }

    private function _checkBuildFeeTitleExtension() {
        $config = $this->getModuleConfig();

        if ($this->b2b_active == 'BOTH' && $this->_is_b2b_allowed($config) && $this->_is_b2c_allowed($config)) {
            return false;
        }
        else if (in_array($this->b2b_active, array('B2C', 'BOTH'))) {
            return parent::_buildFeeTitleExtension('BILLPAY');
        }
        else if (in_array($this->b2b_active, array('B2B', 'BOTH'))) {
            return parent::_buildFeeTitleExtension('BILLPAYBUSINESS');
        }

        return false;
    }


    /**
     * Process payment method input data (form), before validation
     */
    public function onMethodInput($data)
    {
        // saving user data into session
        $parent_result = parent::onMethodInput($data);
        if (!$parent_result) {
            return false;
        }

        $this->setEula($data['billpay']['invoice_toc'] === "true");

        if ($data['billpay']['invoice_customer_group'] === "business") {
            $this->_setDataValue("b2b", true);
            $this->_setDataValue('company_name', $data['billpay']['company_name']);
            $this->_setDataValue('legal_form', $data['billpay']['company_legal_form']);
            $this->_setDataValue('register_number', $data['billpay']['company_register_number']);
            $this->_setDataValue('holder_name', $data['billpay']['company_holder']);
            $this->_setDataValue('tax_number', $data['billpay']['company_tax_number']);
        } else {
            $this->_setDataValue("b2b", false);
        }

        // B2B code
        /*
        if ($data['b2bflag'] === "1")
        {
            $this->_setDataValue("b2b", true);
            $this->_setDataValue('company_name', $data['billpay_company_name']);
            $this->_setDataValue('legal_form', $data['billpay_legal_form']);
            $this->_setDataValue('register_number', $data['billpay_register_number']);
            $this->_setDataValue('holder_name', $data['billpay_holder_name']);
            $this->_setDataValue('tax_number', $data['billpay_tax_number']);

            return $this->validateB2B();
        } else {
            $this->_setDataValue("b2b", false);
        }
        */

        return $parent_result;
    }

    /**
     * Process payment method output data (res), before sending request
     * @param ipl_preauthorize_request $req
     * @return ipl_preauthorize_request
     * @abstract
     */
    public function onMethodOutput($req)
    {
        if ($this->_getDataValue("b2b"))
        {
            $req->set_company_details(
                billpayBase::EnsureUTF8($this->_getDataValue('company_name')),
                billpayBase::EnsureUTF8($this->_getDataValue('legal_form')),
                billpayBase::EnsureUTF8($this->_getDataValue('register_number')),
                billpayBase::EnsureUTF8($this->_getDataValue('holder_name')),
                billpayBase::EnsureUTF8($this->_getDataValue('tax_number'))
            );
            $req = $this->_set_customer_details($req, 'b');
        }
        return $req;
    }

    /**
     * Event fired after creating invoice.
     * @param ipl_preauthorize_request $req
     * @param int $orderId
     */
    public function onAfterInvoiceCreated($req, $orderId) {
        $this->setManualSEPAPaymentInStatus($req, $orderId);
    }

    /**
     * Event fired when admin prints a PDF.
     * Warning: this is not a standard shop function.
     * @param $pdf
     * @param $orderId
     * @param $bankDataQuery
     * @return bool
     */
    public function onDisplayPdf($pdf, $orderId, $bankDataQuery)
    {
        // TODO: change it, it does not display Plugin 1.7 info
        $dat = $bankDataQuery['invoice_due_date'];
        $year = substr($dat,0,-4);
        $mon = substr($dat,4,-2);
        $day = substr($dat,6,2);

        $bank_data_string = sprintf(MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO, $bankDataQuery['invoice_reference'], $day, $mon, $year);

        $pdf->SetFont($pdf->fontfamily, 'B', '9');
        $pdf->SetLineWidth(0.4);
        $pdf->ln(4);
        $pdf->MultiCell(0, 1, '', 'LRT');
        //$pdf->MultiCell(0, 4, html_entity_decode(MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO1) . $day.".".$mon.".".$year.html_entity_decode(MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO2), 'LR');
        $pdf->MultiCell(0, 4, html_entity_decode($bank_data_string), 'LR');
        $pdf->MultiCell(0, 2, '', 'LR');
        $pdf->SetFont($pdf->fontfamily, '', '9');
        $pdf->MultiCell(0, 4, html_entity_decode(MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER) . ': ' . $bankDataQuery['account_holder'], 'LR');
        $pdf->ln(0);
        $pdf->MultiCell(0, 4, html_entity_decode(MODULE_PAYMENT_BILLPAY_TEXT_BANK_NAME) . ': ' . $bankDataQuery['bank_name']  , 'LR');
        $pdf->ln(0);
        $pdf->MultiCell(0, 4, html_entity_decode(MODULE_PAYMENT_BILLPAY_TEXT_BIC) . ': ' . $bankDataQuery['bank_code'], 'LR');
        $pdf->ln(0);
        $pdf->MultiCell(0, 4, html_entity_decode(MODULE_PAYMENT_BILLPAY_TEXT_IBAN) . ': ' . $bankDataQuery['account_number'], 'LR');
        $pdf->ln(0);
        $pdf->MultiCell(0, 4, html_entity_decode(MODULE_PAYMENT_BILLPAY_TEXT_PURPOSE) . ': ' . $bankDataQuery['invoice_reference'], 'LR');
        $pdf->MultiCell(0, 1, '', 'LRB');
        $pdf->ln(3);
        $pdf->SetLineWidth(0.1);
    }

    /**
     * Returns true, if current cart's country requires phone number.
     * Only NLD requires it, check IPL-11283
     * @return bool
     */
    public function isPhoneRequired()
    {
        return $this->_getCountryIso2Code() == 'NL';
    }

    public function isDobRequired($data)
    {
        if ($data['billpay']['invoice_customer_group'] === "business") {
            return false; // Invoice B2B does not require dob.
        }
        return true;
    }

    /**
     * Renders thank you text visible on invoice and email confirmation.
     * @return string
     */
    public function getThankYouText()
    {
        return MODULE_PAYMENT_BILLPAY_THANK_YOU_TEXT;
    }

    /**
     * Renders pay until text visible on invoice and email order confirmation.
     * @param $bank_data Billpay_Base_Bankdata
     * @param $currency string
     * @return string
     */
    public function getPayUntilText($bank_data, $currency)
    {
        $dueDate = $bank_data->getInvoiceDueDate();
        $amount  = $bank_data->getTotalAmount();
        $amountFormatted    = $this->renderMoney($amount);
        if (empty($dueDate)) {
            $return = sprintf(MODULE_PAYMENT_BILLPAY_PAY_UNTIL_TEXT_NO_DUE_DATE,
                $amountFormatted, // amount
                $currency         // currency
            );
        } else {
            $dueDateFormatted = substr($dueDate,6,2).".".substr($dueDate,4,-2).".".substr($dueDate,0,-4);
            $return = sprintf(MODULE_PAYMENT_BILLPAY_PAY_UNTIL_TEXT,
                $amountFormatted, // amount
                $currency,        // currency
                $dueDateFormatted // date
            );
        }
        if ($currency === 'CHF') {
            $return .= ' '.sprintf(MODULE_PAYMENT_BILLPAY_PAY_UNTIL_TEXT_ADD_CH,
                    '1,80',  // post fee - hardcoded
                    'CHF'    // currency
                );
        }
        return $return;
    }

    /**
     * Gathers order's  payment data.
     * If Swiss, gathers additional data.
     * @param $bank_data
     * @param $currency
     * @return array
     */
    public function gatherPaymentDetails($bank_data, $currency)
    {
        $data = parent::gatherPaymentDetails($bank_data, $currency);
        if ($data['currency'] === 'CHF') {
            $data['h_iban_ch'] = MODULE_PAYMENT_BILLPAY_TEXT_IBAN_CH;
            $data['h_bic_ch']  = MODULE_PAYMENT_BILLPAY_TEXT_BIC_CH;
            $data['h_bank_ch'] = MODULE_PAYMENT_BILLPAY_TEXT_BANK_NAME;
            $data['payee_ch']  = MODULE_PAYMENT_BILLPAY_TEXT_PAYEE_CH;

            // iban_ch, bic_ch is not used
        }
        return $data;
    }

    /**
     * Renders invoice payment data.
     * If Swiss, redirects to renderPaymentDetailsCHF
     * @param array $data
     * @return string
     */
    public function renderPaymentDetails($data)
    {
        if ($data['currency'] === 'CHF') {
            $details = array(
                $data['h_payee'] => $data['account_holder'],
                ''  =>  $data['payee_ch'],
                $data['h_bank_ch'] => $data['bank_name'],
                $data['h_iban_ch'] => $data['iban_ch'],
                $data['h_bic_ch'] => $data['bic_ch'],
                $data['h_iban'] => $data['account_number'],
                $data['h_bic'] => $data['bank_code'],
                $data['h_total_amount'] => $data['total_amount'],
                $data['h_reference'] => $data['invoice_reference'],
                $data['h_due_date'] => $data['due_date'],
            );
        } else {
            $details = array(
                $data['h_payee'] => $data['account_holder'],
                $data['h_iban'] => $data['account_number'],
                $data['h_bic'] => $data['bank_code'],
                $data['h_bank'] => $data['bank_name'],
                $data['h_total_amount'] => $data['total_amount'],
                $data['h_reference'] => $data['invoice_reference'],
                $data['h_due_date'] => $data['due_date'],
            );
        }
        return $details;
    }

    /**
     * Renders additional email text visible on invoice and email order confirmation.
     * @return string
     */
    public function getEmailText()
    {
        return ''; // no email text in invoice payment
    }

}

