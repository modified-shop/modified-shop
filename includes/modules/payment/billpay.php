<?php

require_once DIR_FS_CATALOG . 'includes/external/billpay/base/billpayBase.php';

class BillPay extends BillPayBase {
    var $_paymentIdentifier;

    function billpay($identifier = null)
    {
        $this->_paymentIdentifier = constant('billpayBase_PAYMENT_METHOD_INVOICE');
        parent::billpayBase($identifier);
    }

    function _getPaymentType() {
        return IPL_CORE_PAYMENT_TYPE_INVOICE;
    }

    function _getStaticLimit($config) {
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

    function _is_b2b_allowed($config) {
        return ($config['static_limit_invoicebusiness'] > 0);
    }

    function _is_b2c_allowed($config) {
        return ($config['static_limit_invoice'] > 0);
    }

    /**
     * Event executed during payment method installation.
     */
    function onInstall()
    {
        $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_B2BCONFIG";
        $this->_logDebug("Setting local key: $configuration_key");
        xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('".$configuration_key."', 'B2C', '6', '0', 'xtc_cfg_select_option(array(\'B2C\', \'B2B\', \'BOTH\'), ', now())");
    }

    /**
     * Event executed while checking for plugin configuration keys.
     * @param $config_array
     * @return array
     */
    function onKeys($config_array)
    {
        if (defined('MODULE_PAYMENT_' . $this->_paymentIdentifier . '_B2BCONFIG')) {
            $config_array[] = 'MODULE_PAYMENT_' . $this->_paymentIdentifier . '_B2BCONFIG';
        }
        return $config_array;
    }

    function _checkBuildFeeTitleExtension() {
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

    function getSepaText()
    {
        return $this->_getEulaText();
    }

    /**
     * Process payment method input data (form), before validation
     */
    function onMethodInput($data)
    {
        $dob = $data['billpay_dob_year'].'-'.$data['billpay_dob_month'].'-'.$data['billpay_dob_day'];
        $this->setDateOfBirth($dob);
        $this->setGender($data['billpay_gender']);
        $this->setPhone($data['billpay_phone']);
        $this->_setDataValue('eula', (bool)$data['billpay_eula']);

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

        if ($this->isPhoneRequired()) {
            if (!$this->getPhone()) {
                $this->error = MODULE_PAYMENT_BILLPAY_TEXT_ENTER_PHONE;
                return false;
            }
        }

        return true;
    }

    /**
     * Validates if B2B values are correct
     * @return bool
     */
    function validateB2B()
    {
        $company_name = (string) $this->_getDataValue('company_name');
        if (strlen($company_name) < 5)
        {
            $this->error = constant('MODULE_PAYMENT_BILLPAY_B2B_COMPANY_FIELD_EMPTY');
            return false;
        }

        $legal_form = (string) $this->_getDataValue('legal_form');
        if (strlen($legal_form) < 2)
        {
            $this->error = constant('MODULE_PAYMENT_BILLPAY_B2B_LEGAL_FORM_FIELD_EMPTY');
            return false;
        }

        /* as mentioned in billpay-api-documentation_v2_0_en.pdf, page 40, point 13.3
         * depending on legal form and country, different fields are required.
         * Also, "The client-side validation of the company data entered by the customer
         * is not necessary before sending the preauthorize request"
         */
        /*
        $holder_name = (string) $this->_getDataValue('holder_name');
        if (strlen($holder_name) < 5)
        {
            $this->error = constant('MODULE_PAYMENT_BILLPAY_B2B_HOLDER_NAME_EMPTY');
            return false;
        }

        $register_number = $this->_getDataValue('register_number');
        if (empty($register_number))
        {
            $this->error = constant('MODULE_PAYMENT_BILLPAY_B2B_REGISTER_NUMBER_EMPTY');
            return false;
        }
        */

        $tax_number = $this->_getDataValue('tax_number');
        if (empty($tax_number))
        {
            $this->error = constant('MODULE_PAYMENT_BILLPAY_B2B_TAX_NUMBER_EMPTY');
            return false;
        }

        return true;
    }


    /**
     * Process payment method output data (res), before sending request
     * @param ipl_preauthorize_request $req
     * @return ipl_preauthorize_request
     * @abstract
     */
    function onMethodOutput($req)
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
    function onAfterInvoiceCreated($req, $orderId) {
        $this->setManualSEPAPaymentInStatus($req, $orderId);
    }

    /**
     * Event fired when admin is looking at user's invoice.
     * Should display additional payment method's info.
     * @param int $orderId
     * @return string
     */
    function onDisplayInvoice($orderId)
    {
        $bank_data_query = xtc_db_query(' SELECT account_holder, account_number, bank_code, bank_name, invoice_reference, invoice_due_date '.
            ' FROM billpay_bankdata WHERE orders_id = '.(int)$orderId);
        if (!xtc_db_num_rows($bank_data_query)) {
            return '';
        }
        else {
            $bank_data = xtc_db_fetch_array($bank_data_query);
            $dueDate 			= $bank_data['invoice_due_date'];
            $dueDateFormatted 	= substr($dueDate,6,2).".".substr($dueDate,4,-2).".".substr($dueDate,0,-4);

            $bank_data_string = sprintf(MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO, $bank_data['invoice_reference'], substr($dueDate,6,2), substr($dueDate,4,-2), substr($dueDate,0,-4));
            $bank_data_string = '<br/><br/>'.$bank_data_string.'<br/>';

            $bank_data_string .= '<br/>';
            $bank_data_string .= '<strong>'.MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER .':</strong>&nbsp;' . $bank_data['account_holder'].'<br/>';

            $bank_data_string .= '<strong>'.MODULE_PAYMENT_BILLPAY_TEXT_IBAN .':</strong>&nbsp;' . $bank_data['account_number'].'<br/>';
            $bank_data_string .= '<strong>'.MODULE_PAYMENT_BILLPAY_TEXT_BIC .':</strong>&nbsp;' . $bank_data['bank_code'].'<br/>';
            $bank_data_string .= '<strong>'.MODULE_PAYMENT_BILLPAY_TEXT_BANK_NAME .':</strong>&nbsp;' . $bank_data['bank_name'].'<br/>';
            $bank_data_string .= '<strong>'.MODULE_PAYMENT_BILLPAY_TEXT_PURPOSE .':</strong>&nbsp;' . $bank_data['invoice_reference'].'<br/>';

            if ($dueDate) {
                $bank_data_string .= '<strong>'.MODULE_PAYMENT_BILLPAY_DUEDATE_TITLE .':</strong>&nbsp;' . $dueDateFormatted . '<br/>';
            }
            else {
                $bank_data_string .= MODULE_PAYMENT_BILLPAY_ACTIVATE_ORDER_WARNING;
            }

            return $bank_data_string;
        }

    }

    /**
     * Event fired when admin prints a PDF.
     * Warning: this is not a standard shop function.
     * @param $pdf
     * @param $orderId
     * @param $bankDataQuery
     * @return bool
     */
    function onDisplayPdf($pdf, $orderId, $bankDataQuery)
    {
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

    function getPaymentInfo($orderId = null)
    {
        // TODO: this looks ugly
        if(isset($_SESSION['billpay_transaction_id'])) {
            $billpay_bank_data_query = "SELECT account_holder, account_number, bank_code, bank_name, invoice_reference ".
                "FROM billpay_bankdata ".
                "WHERE tx_id = '".$_SESSION['billpay_transaction_id']."'";
        }else {
            $billpay_bank_data_query = "SELECT account_holder, account_number, bank_code, bank_name, invoice_reference ".
                "FROM billpay_bankdata ".
                "WHERE api_reference_id = '".(int)$orderId."'";
        }

        $billpay_bank_data_result = xtc_db_query($billpay_bank_data_query);
        $billpay_bank_data = xtc_db_fetch_array($billpay_bank_data_result);
        //$billpay_info_text = MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO_MAIL . '<br /><br />';

        if(!$billpay_bank_data['api_reference']){
            $invoiceReference = $this->generateInvoiceReference($orderId);
        } else {
            $invoiceReference = $billpay_bank_data['invoice_reference'];
        }

        //$invoiceReference = $billpay->generateInvoiceReference($insert_id);

        $billpay_info_text = sprintf(MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO_MAIL, $invoiceReference) . '<br /><br />';
        $billpay_info_text .= MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER .': '. $billpay_bank_data['account_holder'].'<br />';
        $billpay_info_text .= MODULE_PAYMENT_BILLPAY_TEXT_IBAN .': '. $billpay_bank_data['account_number'].'<br />';
        $billpay_info_text .= MODULE_PAYMENT_BILLPAY_TEXT_BIC .': '. $billpay_bank_data['bank_code'].'<br />';
        $billpay_info_text .= MODULE_PAYMENT_BILLPAY_TEXT_BANK_NAME .': '. $billpay_bank_data['bank_name'].'<br />';
        $billpay_info_text .= MODULE_PAYMENT_BILLPAY_TEXT_PURPOSE .': ' . $invoiceReference . '<br />';
        if(defined('EMAIL_USE_HTML') && EMAIL_USE_HTML == 'false') {
            $billpay_info_text = utf8_decode(html_entity_decode($billpay_info_text, ENT_COMPAT | ENT_HTML401, 'UTF-8'));
        }
        if(defined('MODULE_PAYMENT_BILLPAY_UTF8_ENCODE') &&
            constant('MODULE_PAYMENT_BILLPAY_UTF8_ENCODE') == 'True') {
            $billpay_info_text = utf8_encode($billpay_info_text);
        }
        return array(
            'html'  =>  $billpay_info_text,
            'text'  =>  str_replace("<br />", "\n", $billpay_info_text),
        );
    }

    /**
     * Returns true, if current cart's country requires phone number.
     * Only NLD requires it, check IPL-11283
     * @return bool
     */
    function isPhoneRequired()
    {
        $billing = BillpayOrder::getCustomerBilling();
        $country2 = strtoupper($billing['country2']);
        return $country2 == 'NL';
    }

}

