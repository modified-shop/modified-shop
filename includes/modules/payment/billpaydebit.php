<?php

require_once DIR_FS_CATALOG . 'includes/external/billpay/base/billpayBase.php';

class billpaydebit extends BillPayBase {

    protected $_paymentIdentifier = BillPayBase::PAYMENT_METHOD_DEBIT;
    protected $ucPaymentName = 'directDebit';

    protected function _getPaymentType() {
        return IPL_CORE_PAYMENT_TYPE_DIRECT_DEBIT;
    }

    protected function _getMinValue($config) {
        if (defined('MODULE_PAYMENT_BILLPAYDEBIT_MIN_AMOUNT')) {
            return MODULE_PAYMENT_BILLPAYDEBIT_MIN_AMOUNT;
        }
        return 0;
    }

    protected function _getStaticLimit($config) {
        return $config['static_limit_directdebit'];
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

        $this->setEula($data['billpay']['direct_debit_toc'] === "true");

        $require_result = $this->requireIBAN();
        if (!$require_result) {
            return false;
        }
        return true;
    }

    /**
     * Process payment method output data (res), before sending request
     * @param ipl_preauthorize_request $req
     * @return ipl_preauthorize_request
     */
    public function onMethodOutput($req)
    {
        $req->set_bank_account(
            utf8_encode($this->_getDataValue('account_holder')),
            utf8_encode($this->_getDataValue('account_iban')),
            utf8_encode($this->_getDataValue('account_bic'))
        );
        return $req;
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
        $pdf->SetFont($pdf->fontfamily, 'B', '9');
        $pdf->SetLineWidth(0.4);
        $pdf->ln(4);
        $pdf->MultiCell(0, 1, '', 'LRT');
        $pdf->MultiCell(0, 4, html_entity_decode(MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_INFO1), 'LR');
        $pdf->MultiCell(0, 1, '', 'LRB');
        $pdf->ln(3);
        $pdf->SetLineWidth(0.1);
    }

    /**
     * Renders thankYouText displayed on invoice and email.
     * @return string
     */
    public function getThankYouText()
    {
        return MODULE_PAYMENT_BILLPAYDEBIT_THANK_YOU_TEXT;
    }

    /**
     * @param $bank_data Billpay_Base_Bankdata
     * @param $currency
     * @return string
     */
    public function getPayUntilText($bank_data, $currency)
    {
        $amount             = $bank_data->getTotalAmount();
        $amountFormatted    = $this->renderMoney($amount);
        $return = sprintf(MODULE_PAYMENT_BILLPAYDEBIT_PAY_UNTIL_TEXT,
            $amountFormatted, // amount
            $currency         // currency
        );
        return $return;
    }

    /**
     * We don't display any payment details for Direct Debit.
     * @param Billpay_Base_Bankdata $bank_data
     * @param string $currency
     * @return string
     */
    public function getPaymentDetails($bank_data, $currency)
    {
        return '';
    }

    /**
     * Renders "additional email" text included in invoice and email.
     * @return string
     */
    public function getEmailText()
    {
        return MODULE_PAYMENT_BILLPAYDEBIT_EMAIL_TEXT;
    }

}
