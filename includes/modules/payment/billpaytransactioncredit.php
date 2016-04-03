<?php

require_once DIR_FS_CATALOG . 'includes/external/billpay/base/billpayBase.php';

if (!class_exists('BillPayTransactionCredit')) {
    class BillPayTransactionCredit extends BillPayBase
    {
        /**
         * If TC CHE order amount is higher than this, we change amount of rates to 4.
         * https://wiki.billpay.wonga.com/display/itdev/PM3+-+Transaction+Credit+-+implementation+checklist
         */
        const BIG_CHF_ORDER_AMOUNT = 50000;

        const OT_FEE = 'ot_z_bpytc_fee';
        const OT_TOTAL = 'ot_z_bpytc_total';

        protected $_paymentIdentifier = BillPayBase::PAYMENT_METHOD_TRANSACTION_CREDIT;
        protected $ucPaymentName = 'transactionCredit';
        protected $otModules = array(
            self::OT_FEE, self::OT_TOTAL
        );

        public function __construct($identifier = null)
        {
            $this->_defaultConfig['MIN_AMOUNT'] = '100'; // TC is enabled from 150EUR by default. Merchant can change it.
            parent::__construct($identifier);
        }

        protected function _getPaymentType()
        {
            return IPL_CORE_PAYMENT_TYPE_RATE_PAYMENT;
        }

        protected function _getMinValue($config)
        {
            if (defined('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_MIN_AMOUNT')) {
                return max(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_MIN_AMOUNT, $config['min_value_transactioncredit']);
            }
            return $config['min_value_transactioncredit'];
        }

        protected function _getStaticLimit($config)
        {
            return $config['static_limit_transactioncredit'];
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

            $this->setEula($data['billpay']['transaction_credit_toc'] === "true");

            $creditLength = $data['billpay']['transaction_credit_duration'];
            $rateCount = $data['billpay']['transaction_credit_instalments_count'];
            $totalAmount = $data['billpay']['transaction_credit_total_amount'];

            $this->_setDataValue('creditLength', $creditLength);
            $this->_setDataValue('rateCount', $rateCount);
            $this->_setDataValue('totalAmount', $totalAmount);
            $this->_setDataValue('feeAmount', ($data['billpay']['transaction_credit_fee_absolute']
                + $data['billpay']['transaction_credit_processing_fee_absolute']));

            if ($this->canPayWithAutoSEPACountry()) {
                $require_result = $this->requireIBAN();
                if (!$require_result) {
                    return false;
                }
            }

            $required = array(
                'creditLength' => MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_NO_RATEPLAN,
                'totalAmount' => MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_NO_RATEPLAN,
            );

            foreach ($required as $field => $error) {
                $field_val = $this->_getDataValue($field);
                if (empty($field_val)) {
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
        public function onMethodOutput($req)
        {
            $req->set_bank_account(
                utf8_encode($this->_getDataValue('account_holder')),
                utf8_encode($this->_getDataValue('account_iban')),
                utf8_encode($this->_getDataValue('account_bic'))
            );
            $totalAmount = $this->_getDataValue('totalAmount');
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
        public function onPreauthResponse($req)
        {
            $data = array(
                'rate_surcharge' => $req->get_surcharge(),
                'rate_total_amount' => $req->get_total_amount(),
                'rate_dues' => '',
                'rate_interest_rate' => $req->get_interest(),
                'rate_anual_rate' => $req->get_nominal_annual(),    // spelling mistake in DB
                'rate_base_amount' => $req->get_base_amount(),
                'rate_fee' => $req->get_fee_total(),

                'duration' => $req->get_duration(),
                'instalment_count' => $req->get_instalment_count(),

                'customer_cache' => mysql_real_escape_string(serialize(''))
            );

            Billpay_Base_Bankdata::UpdateByTxId(self::GetTransactionId(), $data);
        }

        /**
         * Event fired after creating invoice.
         * @param ipl_invoice_created_request $req
         * @param int $orderId
         */
        public function onAfterInvoiceCreated($req, $orderId)
        {
            $dueDateList = $req->get_dues();
            $serializedDueDateList = Billpay_Base_Bankdata::serializeDueDateArray($dueDateList);

            $data = array(
                'rate_dues' => $serializedDueDateList,
            );
            Billpay_Base_Bankdata::UpdateByTxId(
                Billpay_Base_Bankdata::GetTxIdFromApiReference($orderId),
                $data
            );

            $country2 = $this->getOrderCountry2($orderId);
            if (!$this->canPayWithAutoSEPACountry($country2)) {
                $this->setManualSEPAPaymentInStatus($req, $orderId);
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
        public function onDisplayPdf($pdf, $orderId, $bankDataQuery)
        {
            // TODO: change it, it does not display Plugin 1.7 info
            $pdf->SetFont($pdf->fontfamily, 'B', '9');
            $pdf->SetLineWidth(0.4);
            $pdf->ln(4);
            $pdf->MultiCell(0, 1, '', 'LRT');
            $pdf->MultiCell(0, 4, html_entity_decode(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_INVOICEPDF_INFO), 'LR');
            $pdf->MultiCell(0, 1, '', 'LRB');
            $pdf->ln(3);
            $pdf->SetLineWidth(0.1);
        }

        public function isPhoneRequired()
        {
            return true;
        }

        /**
         * Renders thankYouText visible on invoice and email confirmation.
         * @return string
         */
        public function getThankYouText()
        {
            return MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_THANK_YOU_TEXT;
        }

        /**
         * Renders payUntilText visible on invoice and email confirmation.
         * Switzerland should have different text here.
         * @param $bank_data Billpay_Base_Bankdata
         * @param $currency string
         * @return string
         */
        public function getPayUntilText($bank_data, $currency)
        {
            if (!$this->canPayWithAutoSEPACurrency($currency)) {
                $return = MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_PAY_UNTIL_TEXT_CH;
                $return .= ' ' . sprintf(MODULE_PAYMENT_BILLPAY_PAY_UNTIL_TEXT_ADD_CH,
                        '1,80',  // post fee - hardcoded
                        'CHF'    // currency
                    );
                return $return;
            }
            return MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_PAY_UNTIL_TEXT;
        }

        /**
         * Renders "additional email" text included in invoice and email.
         * @return string
         */
        public function getEmailText()
        {
            return MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_EMAIL_TEXT;
        }

        /**
         * Unless TC CHE, we don't display any payment details for TC.
         * @param Billpay_Base_Bankdata $bank_data
         * @param string $currency
         * @return string
         */
        public function getPaymentDetails($bank_data, $currency)
        {
            if ($this->canPayWithAutoSEPACurrency($currency)) {
                return '';
            }
            return parent::getPaymentDetails($bank_data, $currency);
        }

        public function gatherPaymentDetails($bank_data, $currency)
        {
            $data = parent::gatherPaymentDetails($bank_data, $currency);
            if ($this->canPayWithAutoSEPACurrency($currency)) {
                $data = $this->removeAutoSEPADetails($data);
            } else {
                // TC CHE does not display total amount nor due date there
                unset($data['h_total_amount']);
                unset($data['total_amount']);
                unset($data['h_due_date']);
                unset($data['due_date']);
            }
            return $data;
        }

        /**
         * Fired before saving edited order in admin/order_edit
         * @param $orderId
         * @abstract
         */
        public function onSaveEditOrderBefore($orderId)
        {
            // since saving new cart sums all OT options, we have to temporarily clear them
            xtc_db_query("UPDATE " . TABLE_ORDERS_TOTAL . " SET value=0 WHERE "
                . "(class='" . self::OT_FEE . "' OR class='" . self::OT_TOTAL . "') AND orders_id = '" . (int)$orderId . "' ");
        }

        /**
         * Event fired after getting success response for editCartContent method
         * @param $orderId
         * @param ipl_edit_cart_content_request $req
         */
        public function onOrderChanged($orderId, $req)
        {
            /** @var $xtPrice object */
            global $xtPrice;

            // special TC way of doing things
            $due_update = $req->get_due_update();
            $data = array(
                'fee_total' => $due_update['calculation']['surcharge'] + $due_update['calculation']['fee'],
                'total_amount' => $due_update['calculation']['total'],
            );

            $transactionCreditFee = $data['fee_total'] * 0.01;
            $transactionCreditTotal = $data['total_amount'] * 0.01;
            $transactionCreditFeeText = $xtPrice->xtcFormat($transactionCreditFee, true);
            $transactionCreditTotalText = '<strong>' . $xtPrice->xtcFormat($transactionCreditTotal, true) . '</strong>';
            xtc_db_query("UPDATE " . TABLE_ORDERS_TOTAL . " SET value='" . $transactionCreditFee . "', text='" . $transactionCreditFeeText . "' WHERE class='" . self::OT_FEE . "' AND orders_id = '" . $orderId . "'");
            xtc_db_query("UPDATE " . TABLE_ORDERS_TOTAL . " SET value='" . $transactionCreditTotal . "', text='" . $transactionCreditTotalText . "' WHERE class='" . self::OT_TOTAL . "' AND orders_id = '" . $orderId . "'");
        }

        /**
         * Checks if current customer can use automatic SEPA debit.
         *      Usually true, unless in Swiss
         * @param string $country2 ISO 2 letter code ie "DE", "CH"
         * @return bool
         */
        private function canPayWithAutoSEPACountry($country2 = "")
        {
            if (!$country2) {
                $country2 = $this->_getCountryIso2Code();
            }
            if ($country2 == "CH") {
                return false;
            }
            return true;
        }

        private function canPayWithAutoSEPACurrency($currency)
        {
            return $currency !== "CHF";
        }
    }
}