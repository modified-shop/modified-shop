<?php

require_once DIR_FS_CATALOG . 'includes/external/billpay/base/billpayBase.php';

class billpaydebit extends billpayBase {
    var $_paymentIdentifier;

    function billpaydebit($identifier = null)
    {
        $this->_paymentIdentifier = constant('billpayBase_PAYMENT_METHOD_DEBIT');
        parent::billpayBase($identifier);
    }

    function _getPaymentType() {
        return IPL_CORE_PAYMENT_TYPE_DIRECT_DEBIT;
    }

    function _getStaticLimit($config) {
        return $config['static_limit_directdebit'];
    }

    function _displaySepaBankData()
    {
        $smarty = $GLOBALS['smarty'];
        $order  = $GLOBALS['order'];
        if (empty($smarty)) {
            $smarty = new Smarty;
            $smarty->caching = 0;
        }

        $accountPreselect = isset($_SESSION['billpaydebit_owner'])
            ? $_SESSION['billpaydebit_owner']
            : $order->billing['firstname'] . ' ' . $order->billing['lastname'];
        $accountHolderInput = xtc_draw_input_field(
            strtolower($this->_paymentIdentifier) . '_owner',
            $accountPreselect,
            'style="width:250px"'
        );

        $accountNumberInput = xtc_draw_input_field(
            strtolower($this->_paymentIdentifier) . '_number',
            '',
            'style="width:250px"'
        );

        $bankCodeInput = xtc_draw_input_field(
            strtolower($this->_paymentIdentifier) . '_code',
            '',
            'style="width:250px"'
        );

        $smarty->assign(array(
                'headline'             => MODULE_PAYMENT_BILLPAYDEBIT_TEXT_BANKDATA,
                'account_holder_text'  => MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ACCOUNT_HOLDER,
                'account_holder_input' => $accountHolderInput,
                'account_number_text'  => MODULE_PAYMENT_BILLPAYDEBIT_TEXT_IBAN,
                'account_number_input' => $accountNumberInput,
                'bank_code_text'       => MODULE_PAYMENT_BILLPAYDEBIT_TEXT_BIC,
                'bank_code_input'      => $bankCodeInput,
            ));

        return $smarty->fetch('../includes/external/billpay/templates/bankdata_sepa_form.tpl');
    }

    function _getSepaEulaText()
    {
        $baseIdentifier = 'MODULE_PAYMENT_' . $this->_paymentIdentifier . '_TEXT_EULA_CHECK_SEPA';
        $eulaIdentifier = $this->_getCountrySpecificIdentifier($baseIdentifier);

        // fallback
        if (defined($eulaIdentifier) === false) {
            return $this->_getEulaText();
        }

        $eulaText = constant($eulaIdentifier);
        // Es gelten die <a href='%1$s'>Datenschutzbestimmungen</a> von Billpay.
        $eulaText = sprintf($eulaText, $this->_buildTermsOfServiceUrl());

        return $this->_buildEulaHTML($eulaText);
    }


    /**
     * Process payment method input data (form), before validation
     */
    function onMethodInput($data)
    {
        $dob = $data['billpaydebit_dob_year'].'-'.$data['billpaydebit_dob_month'].'-'.$data['billpaydebit_dob_day'];
        $this->setDateOfBirth($dob);
        $this->setGender($data['billpaydebit_gender']);
        $this->setPhone($data['billpaydebit_phone']);
        $this->_setDataValue('eula', (bool)$data['billpaydebit_eula']);

        $this->_setDataValue('account_holder', $data['billpaydebit_owner']);
        $this->_setDataValue('account_iban', $data['billpaydebit_number']);
        $this->_setDataValue('account_bic', $data['billpaydebit_code']);

        $required = array(
            'account_holder'    =>  MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_NAME,
            'account_iban'      =>  MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_NUMBER,
        );
        foreach ($required as $field => $error)
        {
            $field_val = $this->_getDataValue($field);
            if (empty($field_val))
            {
                $this->error = $error;
                return false;
            }
        }
        return true;
    }

    /**
     * Process payment method output data (res), before sending request
     * @param ipl_preauthorize_request $req
     * @return ipl_preauthorize_request
     */
    function onMethodOutput($req)
    {
        $req->set_bank_account(
            utf8_encode($this->_getDataValue('account_holder')),
            utf8_encode($this->_getDataValue('account_iban')),
            utf8_encode($this->_getDataValue('account_bic'))
        );
        return $req;
    }

    function addJsBankValidation() {
        // TODO: is this function used?
        $js = ' if (document.getElementById("checkout_payment").elements["billpaydebit_owner"].value == "") {
                error_message = error_message + unescape("' . JS_BILLPAYDEBIT_NAME . '");
                error = 1;
            }
            if (document.getElementById("checkout_payment").elements["billpaydebit_number"].value == "") {
                error_message = error_message + unescape("' . JS_BILLPAYDEBIT_NUMBER . '");
                error = 1;
            }';

        return $js;
    }

    /**
     * Event fired when admin is looking at user's invoice.
     * Should display additional payment method's info.
     * @param int $orderId
     * @return string
     */
    function onDisplayInvoice($orderId)
    {
        $bank_data_query = xtc_db_query('SELECT invoice_due_date FROM billpay_bankdata WHERE orders_id = '.(int)$orderId);
        $bank_data = xtc_db_fetch_array($bank_data_query);

        $infoText = '<br/><br/>'.MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_INFO1 . '<br/>'. MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_INFO2 . '<br/><br/>';

        if (!$bank_data['invoice_due_date']) {
            $infoText .= '<br/>'.MODULE_PAYMENT_BILLPAYDEBIT_ACTIVATE_ORDER_WARNING;
        }

        return $infoText;
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
        $pdf->SetFont($pdf->fontfamily, 'B', '9');
        $pdf->SetLineWidth(0.4);
        $pdf->ln(4);
        $pdf->MultiCell(0, 1, '', 'LRT');
        $pdf->MultiCell(0, 4, html_entity_decode(MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_INFO1), 'LR');
        $pdf->MultiCell(0, 1, '', 'LRB');
        $pdf->ln(3);
        $pdf->SetLineWidth(0.1);
    }

    function getPaymentInfo($orderId = null)
    {
        $billpay_info_text = '<br /><br />' . MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_INFO1;
        if(defined('EMAIL_USE_HTML') && EMAIL_USE_HTML == 'false') {
            $billpay_info_text = utf8_decode(html_entity_decode($billpay_info_text, ENT_COMPAT | ENT_HTML401, 'UTF-8'));
        }
        if(defined('MODULE_PAYMENT_BILLPAYDEBIT_UTF8_ENCODE') &&
            constant('MODULE_PAYMENT_BILLPAYDEBIT_UTF8_ENCODE') == 'True') {
            $billpay_info_text = utf8_encode($billpay_info_text);
        }
        return array(
            'html'  =>  $billpay_info_text,
            'text'  =>  str_replace("<br />", "\n", $billpay_info_text),
        );
    }

}
