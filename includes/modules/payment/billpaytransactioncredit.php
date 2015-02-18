<?php

require_once DIR_FS_CATALOG . 'includes/external/billpay/base/billpayBase.php';

define('BillPayTransactionCredit_BIG_CHF', 50000);

class BillPayTransactionCredit extends BillPayBase
{
    var $_paymentIdentifier;
    var $otModules = array(
        'ot_billpaytc_surcharge'
    );

    function BillpayTransactionCredit($identifier = null)
    {
        $this->_paymentIdentifier = constant('billpayBase_PAYMENT_METHOD_TRANSACTION_CREDIT');
        $this->_defaultConfig['MIN_AMOUNT'] = '100'; // TC is enabled from 150EUR by default. Merchant can change it.
        parent::billpayBase($identifier);
    }

    function _getPaymentType() {
        return IPL_CORE_PAYMENT_TYPE_RATE_PAYMENT;
    }

    function _getStaticLimit($config) {
        return $config['static_limit_transactioncredit'];
    }

    function getPaymentForm($input_fields)
    {
        $order = $GLOBALS['order'];

        if (empty($order)) {
            if (!class_exists('order')) {
                require (DIR_WS_CLASSES . 'order.php');
            }
            // TODO: this should use orderId
            $order = new order();
        }

        $config = $this->getModuleConfig();
        $country = $order->billing['country']['iso_code_3'];
        $currency = $order->info['currency'];
        $total = $order->info['total'];
        $allowed_rates = $config['terms'];
        if ($this->isBigCHFOrder($country, $currency, $total)) {
            $old_allowed_rates = $allowed_rates;
            $allowed_rates = array();
            $allowed_che_rates = array(6, 9, 12);
            foreach ($old_allowed_rates as $val) {
                if (in_array($val, $allowed_che_rates)) {
                    $allowed_rates[] = $val;
                }
            }
        }
        $txt_input_rates = MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ENTER_NUMBER_RATES;
        $rate_options = '';
        foreach ($allowed_rates as $val) {
            $rate_options .= '<option value="'.$val.'" '.($_SESSION['billpay_selected_rate'] == $val ? 'selected' : '').'>'.$val.'</option>';
        }
        $ws_catalog = DIR_WS_CATALOG;
        $payment_form = <<<HEREDOC
<style type="text/css">
        .bpy-cf {
            /* for IE 6/7 */
            *zoom: expression(this.runtimeStyle.zoom="1", this.appendChild(document.createElement("br")).style.cssText="clear:both;font:0/0 serif");
            /* non-JS fallback */
            *zoom: 1;
        }
        .bpy-cf:before,
        .bpy-cf:after {
            content: ".";
            display: block;
            height: 0;
            overflow: hidden;
            visibility: hidden;
        }
        .bpy-cf:after {
            clear: both;
        }
        /* rateplan start */
        .bpy-rateplan-block {
            width: 450px;
            padding: 10px;
            color: #38414b;
            font-size: 13px;
        }
        .bpy-rateplan-block .bpy-rate-selection {
            background: #eff3f6;
            padding: 5px 10px;
            text-align: left;
            color: #414141;
            line-height: 24px;
        }
        .bpy-rateplan-block .bpy-rate-selection .outer-select {
            margin: 0 0 0 5px !important;
            float: right;
        }
        .bpy-rateplan-block .bpy-rate-details-block {
            background: #eff3f6;
            margin: 5px 0;
            padding: 5px;
            color: #777777;
            border-bottom: 2px solid #003366;
        }
        .bpy-rateplan-block .bpy-rate-details-block .bpy-row {
            margin: 5px 0;
        }
        .bpy-rateplan-block .bpy-rate-details-block .bpy-row > span {
            color: #003366;
            white-space: nowrap;
        }
        .bpy-rateplan .bpy-financial-details-block {
            margin-top: 10px;
        }
        .bpy-rateplan .bpy-financial-details-block a {
            display: block;
            padding: 5px;
            text-align: center;
            width: 120px;
            background-color: #777777;
            color: white;
            font-size: 14px;
        }
        /* rateplan end */
</style>
<div class="bpy-rateplan-block" id="$this->_paymentIdentifier" style="display: none;" data-bpy-load="bpyTCReload()">
        <div class="bpy-container">
            <div class="bpy-rate-selection">
                $txt_input_rates:
                <div class="outer-select" style="width: 53px;">
                    <select name="billpay_selected_rate" id="billpay_selected_rate" onchange="bpyTCReload()" style="width: 53px; margin: 0;">
                        $rate_options
                    </select>
                </div>
                <script type="text/javascript">
                    changeRatePlan = function(creditLength) {
                        bpyQry.ajax({
                            url: "billpay_rate_requests.php?duration="+creditLength,
                            success: function(data) {
                                bpyQry("#billpay_rateplan_container").html(data);
                                bpyQry("#billpay_calculate_rate_button").hide();
                            }
                        });
                    };
                    var bpyTCReload = function() {
                        if (bpyQry) {
                            changeRatePlan(bpyQry('#billpay_selected_rate').val());
                        }
                    };
                    var bpyExternalPopup = function(el) {
                        var element = bpyQry.bpy.externalPopup(bpyQry(el).attr('href'));
                        var iTop = bpyQry(el).position().top;
                        element.find('iframe').css({height: '365px', width: '600px'});
                        element.css({height: '365px', width:  '600px'});
                        element.css('margin-left', (element.width() / 2) * -1);
                        element.css('margin-top', iTop - (element.height() / 2));
                        element.css('background-color', '#f0f3f5');
                        return element;
                    };
                </script>
            </div>
            <div id="billpay_rateplan_container">
            </div>
        </div>
HEREDOC;
        $payment_form .= $input_fields;

        $payment_form .= $this->displayBankData();
        $payment_form .= '</div>';
        return $payment_form; //span
    }

    function _displaySepaBankData()
    {
        $smarty = $GLOBALS['smarty'];
        $order  = $GLOBALS['order'];
        if (empty($smarty)) {
            $smarty = new Smarty;
            $smarty->caching = 0;
        }

        if (!$this->canPayWithAutoSEPA()) {
            return '';
        }

        $accountPreselect = isset($_SESSION['billpaydebit_owner'])
                                ? $_SESSION['billpaydebit_owner']
                                : $order->billing['firstname'] . ' ' . $order->billing['lastname'];
        $accountHolderInput = xtc_draw_input_field(
            strtolower($this->_paymentIdentifier) . '_owner',
            $accountPreselect,
            'style="width:200px"'
        );

        $accountNumberInput = xtc_draw_input_field(
            strtolower($this->_paymentIdentifier) . '_number',
            '',
            'style="width:200px"'
        );

        $bankCodeInput = xtc_draw_input_field(
            strtolower($this->_paymentIdentifier) . '_code',
            '',
            'style="width:200px"'
        );

        $smarty->assign(array(
            'headline'             => MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_BANKDATA,
            'account_holder_text'  => MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ACCOUNT_HOLDER,
            'account_holder_input' => $accountHolderInput,
            'account_number_text'  => MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_IBAN,
            'account_number_input' => $accountNumberInput,
            'bank_code_text'       => MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_BIC,
            'bank_code_input'      => $bankCodeInput,
        ));

        $bankForm = $smarty->fetch('../includes/external/billpay/templates/bankdata_sepa_form.tpl');
        return $bankForm;
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
        // Es gelten die <a href='%1$s'>AGB</a>, <a href='%2$s'>Zahlungsbedingungen</a> und <a href='%3$s'>Datenschutzbestimmungen</a>"
        $eulaText = sprintf($eulaText, $this->_buildTcTermsUrl(), $this->_buildPaymentConditionUrl(), $this->_buildTcPrivacyUrl());

        return $this->_buildEulaHTML($eulaText);
    }

    function _getEulaText()
    {
        $eulaText = constant('MODULE_PAYMENT_' . $this->_paymentIdentifier . '_TEXT_EULA_CHECK');
        $eulaText = sprintf($eulaText, $this->_buildTcTermsUrl(),$this->_buildPaymentConditionUrl(), $this->_buildTcPrivacyUrl());

        return $this->_buildEulaHTML($eulaText);
    }

    /**
     * Process payment method input data (form), before validation
     */
    function onMethodInput($data)
    {
        $this->_logDebug($data);
        $this->setDateOfBirth(
            $data['billpaytransactioncredit_dob_day']
            .'-'.$data['billpaytransactioncredit_dob_month']
            .'-'.$data['billpaytransactioncredit_dob_year']
        );
        $gender = '';
        switch ($data['billpaytransactioncredit_gender']) {
            case 'Herr':
                $gender = 'm';
                break;
            case 'Frau':
                $gender = 'f';
                break;
        }
        $this->setGender($gender);
        $this->setPhone($data['billpaytransactioncredit_phone']);
        $this->setEula($data['billpaytransactioncredit_eula']);

        $this->_setDataValue('totalAmount', $data['billpay']['total_amount'] * 0.01);
        $this->_setDataValue('feeAmount', $data['billpay']['fee_total'] * 0.01);

        $this->_setDataValue('account_holder', $data['billpaytransactioncredit_owner']);
        $this->_setDataValue('account_iban', $data['billpaytransactioncredit_number']);
        $this->_setDataValue('account_bic', $data['billpaytransactioncredit_code']);



        $creditLength = $_SESSION['bp_rate_result']['duration'];
        $rateCount = $_SESSION['bp_rate_result']['rateplan'][$creditLength]['rateCount'];
        $this->_logDebug(print_r($_SESSION['bp_rate_result'], true));
        $this->_setDataValue('creditLength', $creditLength);
        $this->_setDataValue('rateCount', $rateCount);
        $this->_setDataValue('total_amount', $_SESSION['bp_rate_result']['rateplan'][$creditLength]['calculation']['total']);

        $required = array(
            'creditLength'      =>  MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_NO_RATEPLAN,
            'total_amount'      =>  MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_NO_RATEPLAN,
        );
        if ($this->canPayWithAutoSEPA()) {
            $required['account_holder'] = MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_BANKDATA;
            $required['account_iban']   = MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_BANKDATA;
        }

        foreach ($required as $field => $error)
        {
            $field_val = $this->_getDataValue($field);
            if (empty($field_val))
            {
                $this->error = $error;
                return false;
            }
        }
        if (!$this->getPhone()) {
            $this->error = MODULE_PAYMENT_BILLPAY_TEXT_ENTER_PHONE;
            return false;
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
        $totalAmount = $this->_getDataValue('total_amount');
        $creditLength = $this->_getDataValue('creditLength');
        $rateCount = $this->_getDataValue('rateCount');
        if ($rateCount == $creditLength) {
            $req->set_rate_request(
                $rateCount,
                $totalAmount
            );
        } else {
            $req->set_rate_request(
                $rateCount,
                $totalAmount,
                $creditLength
            );
        }

        return $req;
    }

    /**
     * Event fired after receiving preauthorize response
     * @param ipl_preauthorize_request $req
     */
    function onPreauthResponse($req) {
        require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/Bankdata.php');

        // since TC preauth is always done in customer's context, we can use session
        $duration = $_SESSION['bp_rate_result']['duration'];
        $ratePlan = $_SESSION['bp_rate_result']['rateplan'][$duration];
        $rateCount = $ratePlan['rateCount'];
        $rateDues = $this->serializeDueDateArray($ratePlan['dues']);
        $calculation = $ratePlan['calculation'];
        $rateSurcharge = (float) (float)$calculation['surcharge'] / 100;
        $rateTotalAmount = (float)$calculation['total'] / 100;
        $rateInterest = (float)$calculation['interest'] / 100;
        $rateAnnual = (float)$calculation['anual'] / 100;
        $rateBase = (float)$calculation['base'] / 100;
        $rateFee = (float)$calculation['fee'] / 100;

        $data = array(
            'rate_surcharge'    =>  $rateSurcharge,
            'rate_total_amount' =>  $rateTotalAmount,
            'rate_dues'         =>  $rateDues,
            'rate_interest_rate'=>  $rateInterest,
            'rate_anual_rate'   =>  $rateAnnual,    // spelling mistake in DB
            'rate_base_amount'  =>  $rateBase,
            'rate_fee'          =>  $rateFee,

            'duration'          =>  $duration,
            'instalment_count'  =>  $rateCount,

            'customer_cache'    =>  mysql_real_escape_string(serialize(''))
        );

        Billpay_Base_Bankdata::UpdateByTxId($this->_getTransactionId(), $data);
    }

    function addJsBankValidation() {

        return '';
    }

    function showFeeInTitle() {
        return true;
    }

    /**
     * Returns Transaction Credit Terms URL based on country
     * @return string
     */
    function _buildTcTermsUrl() {
        $fileName = 'tc/'.$this->bp_public_api_key . '.html';

        $country = strtolower($this->_getCountry('3'));

        if ($this->testmode == constant('billpayBase_MODE_TEST')) {
            $termsUrl = 'https://www.billpay.de/s/agb-beta/';
        }
        else {
            $termsUrl = 'https://www.billpay.de/s/agb/';
        }

        if($country != 'deu') {
            $termsUrl.= $country.'/'.$fileName;
        } else {
            $termsUrl.= $fileName;
        }
        // using lang parameter will break the address due to BillPay's htaccess for billpay.de
        //$termsUrl .= '?lang='.$this->getCurrentLangIso2();
        return $termsUrl;
    }

    /**
     * build tc specific privacy url
     */
    function _buildTcPrivacyUrl() {
        $country = $this->_getCountry(2);
        $privacyUrl = 'https://www.billpay.de/'.$country.'/api-'.$country.'/ratenkauf-'.$country.'/datenschutz/';
        $privacyUrl .= '?lang='.$this->getCurrentLangIso2();
        return $privacyUrl;
    }

    /**
     * build tc specific url for payment conditions
     */
    function _buildPaymentConditionUrl() {
        return 'https://www.billpay.de/api/ratenkauf/zahlungsbedingungen/'.'?lang='.$this->getCurrentLangIso2();
    }


    /**
     * Event fired after creating invoice.
     * @param ipl_invoice_created_request $req
     * @param int $orderId
     */
    function onAfterInvoiceCreated($req, $orderId) {
        require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/Bankdata.php');

        $dueDateList = $req->get_dues();
        $serializedDueDateList = $this->serializeDueDateArray($dueDateList);

        $data = array(
            'rate_dues' =>  $serializedDueDateList,
        );
        Billpay_Base_Bankdata::UpdateByTxId(
            Billpay_Base_Bankdata::GetTxIdFromApiReference($orderId),
            $data
        );

        $country2 = $this->getOrderCountry2($orderId);
        if (!$this->canPayWithAutoSEPA($country2)) {
            $this->setManualSEPAPaymentInStatus($req, $orderId);
        }
    }

    /**
     * Event fired when admin is looking at user's invoice.
     * Should display additional payment method's info.
     * @param int $orderId
     * @return string
     */
    function onDisplayInvoice($orderId)
    {
        require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/Bankdata.php');
        global $order;

        //$rateDetails = $this->buildTCPaymentInfo($orderId, $order, true);

        $bankData = new Billpay_Base_Bankdata();
        $bankData->loadByOrdersId($orderId);

        $rateDetails = '';

        $rateDues = $bankData->getRateDues();
        $isActivated = false;
        if (!empty($rateDues[0]['date'])) {
            $isActivated = true;
        }

        if (!$isActivated) {
            return '<p style="text-weight: bold; color: red;">'.MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ACTIVATE_ORDER.'</p>';
        }

        $country2 = BillpayDB::DBFetchValue("SELECT billing_country_iso_code_2 FROM orders WHERE orders_id = '".(int)$orderId."'");
        $canPayWithAutoSEPA = $this->canPayWithAutoSEPA($country2);

        if ($canPayWithAutoSEPA) {
            $rateDetails = MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_INVOICE_INFO1.'<br>';
        } else {
            $rateDetails = MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_MANUAL_TRANSFER.'<br>';
            $labelPayee = MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_PAYEE;
            $labelIBAN  = MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_IBAN.':';
            $labelBIC   = MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_BIC.':';
            $labelBankName  = MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_BANK_NAME.':';
            $labelPurpose   = MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_PURPOSE.':';

            $valueIBAN = $bankData->getAccountNumber();
            $valueBIC  = $bankData->getBankCode();
            $valueBankName  = $bankData->getBankName();
            $valuePurpose   = $bankData->getInvoiceReference();

            $rateDetails .= <<<HEREDOC
<table style="font-size: 10px">
    <tr class="small">
        <th style="text-align: left;">$labelPayee</th>
        <th style="text-align: left;">$labelIBAN</th>
        <td>$valueIBAN</td>
    </tr>
    <tr class="small">
        <td rowspan="3" style="padding-right: 5px;">
            BillPay GmbH<br>
            Zweigniederlassung Schweiz (Regensdorf)<br>
            DE-10115 Berlin
        </td>
        <th style="text-align: left;">$labelBIC</th>
        <td>$valueBIC</td>
    </tr>
    <tr class="small">
        <th style="text-align: left;">$labelBankName</th>
        <td>$valueBankName</td>
    </tr>
    <tr class="small">
        <th style="text-align: left;">$labelPurpose</th>
        <td>$valuePurpose</td>
    </tr>
</table>
HEREDOC;
            $rateDetails .= '<br><strong>'.MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_MANUAL_RATE_PLAN.'</strong><br>';
        }

        $dues = array();
        foreach ((array)$rateDues as $due) {
            $sValue = xtc_format_price_order($due['value'] / 100, 1, $order->info['currency']);
            if (!isset($dues[$sValue])) {
                $dues[$sValue] = array();
            }
            $dues[$sValue][] = substr($due['date'], 6, 2) . '.' . substr($due['date'], 4, 2) . '.' . substr($due['date'], 0, 4);
        }
        $rateDetails .= '<table cellpadding="0" cellspacing="0" style="font-size: 10px"><tr class="small"><th style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px">'.MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_AMOUNT.'</th><th style="border-bottom: 1px solid black; padding: 5px">'.MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_DATES.'</th></tr>';
        foreach ($dues as $rate_value => $rate_dates) {
            $rateDetails .= '<tr class="small"><td style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 5px; width: 100px;">'.$rate_value.'</td><td style="border-bottom: 1px solid black; padding: 5px">';
            $rateDetails .= join('; ', $rate_dates);
            $rateDetails .= '</td></tr>';
        }
        $rateDetails .= '</table>';

        $infoText = '<br/><br/>'.$rateDetails;
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
        $pdf->MultiCell(0, 4, html_entity_decode(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_INVOICEPDF_INFO), 'LR');
        $pdf->MultiCell(0, 1, '', 'LRB');
        $pdf->ln(3);
        $pdf->SetLineWidth(0.1);
    }

    /**
     * Event used while installing the plugin
     */
    function install() {
        parent::install();

        foreach ($this->otModules as $ot) {
            require_once(DIR_FS_CATALOG . 'includes/modules/order_total/'.$ot.'.php');
            $otModule = new $ot();
            $otModule->install();
        }

    }

    /**
     * Event used while removing the plugin
     * @param null $state - ???
     */
    function remove($state = null) {
        parent::remove($state);

        foreach ($this->otModules as $ot) {
            require_once(DIR_FS_CATALOG . 'includes/modules/order_total/'.$ot.'.php');
            $otModule = new $ot();
            $otModule->remove();
        }
    }

    /**
     * Due to legal factors, big orders in CHE using Transaction Credit needs to be processed differently.
     * Is order big enough?
     * @param string $country
     * @param string $currency
     * @param int $total
     * @return bool
     */
    function isBigCHFOrder($country, $currency, $total)
    {
        $total = $this->CurrencyToSmallerUnit($total);
        if (strtoupper($country) == "CHE"
            && strtoupper($currency) == "CHF"
            && (int) $total >= (int) constant('BillPayTransactionCredit_BIG_CHF')) {
            return true;
        }
        return false;
    }

    /**
     * @param $apiReference
     * @param order $order
     *
     * @return array
     */
    function getRatePlanInformation($apiReference, $order)
    {
        require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/Bankdata.php');
        require_once(DIR_FS_INC . 'xtc_format_price_order.inc.php');

        $ratePlanValues = array(
            'misc'  => array(
                'currency' => $order->info['currency'],
                'font_size' => array(
                    'small'  => 8,
                    'medium' => 9,
                    'big'    => 10,
                ),
            ),
            'texts' => array(
                'top_info'         => $this->EnsureString(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_INVOICE_INFO1),
                'top_calculation'  => $this->EnsureString(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TOTAL_PRICE_CALC_TEXT),
                'pre_payment'      => $this->EnsureString(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_PREPAYMENT_TEXT),
                'rate'             => $this->EnsureString(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_RATE),
                'rate_due'         => $this->EnsureString(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_RATEDUE_TEXT),
                'cart_amount'      => $this->EnsureString(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_CART_AMOUNT_TEXT),
                'cart_amount_without_pre_payment' => $this->EnsureString(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_CART_AMOUNT_AFTER_PREPAYMENT_TEXT),
                'surcharge'        => $this->EnsureString(MODULE_PAYMENT_BILLPAYTC_SURCHARGE_TEXT),
                'fee'              => $this->EnsureString(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TRANSACTION_FEE_TEXT),
                'fee_tax'          => '('
                    . $this->EnsureString(MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_TRANSACTION_FEE_TAX1)
                    . ' %s '
                    . $this->EnsureString(MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_TRANSACTION_FEE_TAX2)
                    . ')',
                'additional_costs' => $this->EnsureString(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_OTHER_COSTS_TEXT),
                'total_amount'     => $this->EnsureString(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TOTAL_AMOUNT_TEXT),
                'annual_rate'      => $this->EnsureString(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ANUAL_RATE_TEXT),
                'button_calculation' => $this->EnsureString(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_EXAMPLE_TEXT),

                // !canPayWithAutoSEPA
                'account_holder'    =>  $this->EnsureString(MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER),
                'account_iban'      =>  $this->EnsureString(MODULE_PAYMENT_BILLPAY_TEXT_IBAN),
                'account_bic'       =>  $this->EnsureString(MODULE_PAYMENT_BILLPAY_TEXT_BIC),
                'bank_name'         =>  $this->EnsureString(MODULE_PAYMENT_BILLPAY_TEXT_BANK_NAME),
                'invoice_purpose'   =>  $this->EnsureString(MODULE_PAYMENT_BILLPAY_TEXT_PURPOSE),
                'invoice_due_date'  =>  $this->EnsureString(MODULE_PAYMENT_BILLPAY_DUEDATE_TITLE)
            ),
        );

        $oBankdata = new Billpay_Base_Bankdata();
        $oBankdata->loadByApiReference($apiReference);

        // did we found any data?
        if ($oBankdata->hasAttributes()) {
            $this->_logDebug(print_r($order->info, true));
            $ratePlanValues['values'] = array(
                'pre_payment'      => xtc_format_price_order(
                    $oBankdata->getPrePayment(), 1, $order->info['currency']
                ),
                'cart_amount'      => xtc_format_price_order(
                    $oBankdata->getRateBaseAmount() + $oBankdata->getPrePayment(), 1, $order->info['currency']),
                'rate_base_amount' => xtc_format_price_order(
                    $oBankdata->getRateBaseAmount(), 1, $order->info['currency']
                ),
                'interest'         => $oBankdata->getInterestRate(),
                'rate_count'       => $oBankdata->getRateCount(),
                'surcharge'        => xtc_format_price_order($oBankdata->getRateSurcharge(), 1, $order->info['currency']),
                'fee'              => xtc_format_price_order($oBankdata->getFee(), 1, $order->info['currency']),
                'fee_tax'          => xtc_format_price_order($oBankdata->getFeeTax(), 1, $order->info['currency']),
                'additional_costs' => xtc_format_price_order($oBankdata->getAdditionalCosts(), 1, $order->info['currency']),
                'total_amount'     => xtc_format_price_order($oBankdata->getRateTotalAmount(), 1, $order->info['currency']),
                'annual_rate'      => $oBankdata->getAnnualRate(),

                // !canPayWithAutoSEPA
                'account_holder'    =>  $oBankdata->getAccountHolder(),
                'account_number'    =>  $oBankdata->getAccountNumber(),
                'bank_code'         =>  $oBankdata->getBankCode(),
                'bank_name'         =>  $oBankdata->getBankName(),
                'invoice_reference' =>  $oBankdata->getInvoiceReference(),
                'invoice_due_data'  =>  $oBankdata->getInvoiceDueData(),

            );

            if ($oBankdata->getAccountNumber()) {
                $ratePlanValues['texts']['top_info'] = $this->EnsureString(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_INVOICE_INFO_MANUAL);
            }

            $dueData = array();
            $dues = $oBankdata->getRateDues();
            if (is_array($dues) === true) {
                foreach($dues as $due) {
                    $date = 0;
                    if (isset($due['date']) === true && strlen($due['date']) == 8) {
                        $date = mktime(null, null, null,
                            substr(trim($due['date']), 4, 2), // month
                            substr(trim($due['date']), 6, 2), // day
                            substr(trim($due['date']), 0, 4)); // year
                    }

                    $dueData[] = array(
                        'amount' => xtc_format_price_order($due['value'] / 100, 1, $order->info['currency']),
                        'date' => $date,
                    );
                }
            }
            $ratePlanValues['values']['dues'] = $dueData;
        }

        return $ratePlanValues;
    }

    function getRatePlanHtml($ratePlanValues)
    {
        $smarty = $GLOBALS['smarty'];
        if (empty($smarty)) {
            $smarty = new Smarty();
            $smarty->caching = 0;
        }

        $smarty->assign($ratePlanValues);

        $tempDir = getcwd();
        chdir(DIR_FS_CATALOG);

        $html = $smarty->fetch('../includes/external/billpay/templates/rateplan_details.tpl');
        chdir($tempDir);

        return $html;
    }

    /**
     * Build rate plan and calculation details that will be displayed on invoice and email confirmation
     *
     * @param string $apiReference
     * @param order  $order
     * @param bool   $isHTML
     * @param bool   $isEMail
     *
     * @return string
     */
    function buildTCPaymentInfo($apiReference, $order, $isHTML = true, $isEMail = false)
    {
        $ratePlanDetails = $this->getRatePlanInformation($apiReference, $order);
        if (isset($ratePlanDetails['values'])) {

            if ($isEMail === true) {
                $ratePlanDetails['misc']['font_size'] = array(
                    'small'  => 8,
                    'medium' => 10,
                    'big'    => 10,
                );
            }

            $infoText = $this->getRatePlanHtml($ratePlanDetails);

            if ($isHTML === false) {
                $infoText = strip_tags($infoText);
            }

            return $infoText;
        }

        return '';
    }

    /**
     * Create a string representation from special formatted array that can be stored in the database
     *
     * Result:
     * Example data (incl. date): 20110305#8415:20110405#6211:20110505#6211:20110605#6211:20110705#6211:20110805#6211
     * Example data (before activation): #8415:#6211:#6211:#6211:#6211:#6211
     *
     * @param array $dueDateArray
     *
     * @return string
     */
    function serializeDueDateArray($dueDateArray)
    {
        require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/Bankdata.php');
        $oBankdata = new Billpay_Base_Bankdata();

        return $oBankdata->serializeDueDateArray($dueDateArray);
    }

    /**
     * Create array representation out of serialized due date string (Format specification input param see 'serializeDueDateArray')
     *
     * @param $serializedDueDates
     *
     * @return array
     */
    function unserializeDueDates($serializedDueDates)
    {
        require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/Bankdata.php');
        $oBankdata = new Billpay_Base_Bankdata();

        return $oBankdata->unserializeDueDates($serializedDueDates);
    }

    function getPaymentInfo($orderId = null)
    {
        $sThankYou = MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_THANK_YOU . ' ' . MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_RATE_PLAN_EMAIL;

        return array(
            'html'  =>  $sThankYou,
            'text'  =>  html_entity_decode($sThankYou, ENT_COMPAT | ENT_HTML401, 'UTF-8'),
        );
    }

    function isPhoneRequired()
    {
        return true;
    }
}
