<?php

require_once DIR_FS_CATALOG . 'includes/external/billpay/base/billpayBase.php';

if (!class_exists('BillpayPayLater'))
{
    class BillpayPayLater extends BillPayBase {

        const DEFAULT_MIN_AMOUNT = 50; // PL is enabled from 50EUR by default. Merchant can change it.

        private $visualMode = 1;
        protected $_paymentIdentifier = BillPayBase::PAYMENT_METHOD_PAY_LATER;
        protected $ucPaymentName = 'paylater';
        protected $otModules = array(
            'ot_z_paylater_fee',
            'ot_z_paylater_total'
        );

        public function __construct($identifier = null)
        {
            $this->_defaultConfig['MIN_AMOUNT'] = self::DEFAULT_MIN_AMOUNT;
            parent::__construct($identifier);
            $this->requireLang();
        }

        protected function _getPaymentType() {
            return IPL_CORE_PAYMENT_TYPE_PAY_LATER;
        }

        protected function _getStaticLimit($config) {
            return 10000000000; // moduleConfig no longer applies
        }

        protected function _getMinValue($config) {
            return 0;
        }

         /**
         * Process payment method input data (form), before validation
         */
        public function onMethodInput($data)
        {
            $parent_result = parent::onMethodInput($data);
            if (!$parent_result) {
                return false;
            }

            $this->setEula($data['billpay']['paylater_toc'] === "true");

            $this->_setDataValue('instalments', $data['billpay']['paylater_instalments_count']);
            $this->_setDataValue('totalAmount', $data['billpay']['paylater_total_amount']);
            $this->_setDataValue('feeAmount', $data['billpay']['paylater_fee_absolute']);

            $required = array(
                'account_holder'    =>  MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_BANKDATA,
                'account_iban'      =>  MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_BANKDATA,
                'totalAmount'       =>  MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ERROR_NO_RATEPLAN,
                'feeAmount'         =>  MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ERROR_NO_RATEPLAN,
                'instalments'       =>  MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ERROR_NO_RATEPLAN,
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
        public function onMethodOutput($req)
        {
            $req->set_bank_account(
                utf8_encode($this->_getDataValue('account_holder')),
                utf8_encode($this->_getDataValue('account_iban')),
                utf8_encode($this->_getDataValue('account_bic'))
            );
            $req->set_rate_request(
                $this->_getDataValue('instalments'),
                $this->_getDataValue('totalAmount')
            );
            return $req;
        }



        /**
         * Event fired after receiving preauthorize response
         * @param ipl_preauthorize_request $req
         */
        public function onPreauthResponse($req) {
            // TODO: this should use BankData class
            $customerData = array(
                'token'     =>  $this->token,
            );

            //mysql_real_escape_string do not work - it everytime returned "false" and therefore we use the "str_replace"
            $data = array(
                'customer_cache'    =>  str_replace('"', '\"', serialize($customerData)),
            );

            Billpay_Base_Bankdata::UpdateByTxId(self::GetTransactionId(), $data);
        }

        /**
         * Event fired when Billpay calls shop back with Giropay prepayment.
         * @param $orderId
         * @param $data
         * @abstract
         */
        public function onOrderApproved($orderId, $data) {
            unset($data['xml']);
            unset($data['postdata']);
            $data['orderId'] = $orderId;
            $this->_processRates($data);
        }

        /**
         * Event fired after creating invoice.
         * @param ipl_invoice_created_request $req
         * @param int $orderId
         */
        public function onAfterInvoiceCreated($req, $orderId) {
            /* // example
            $data = array(
                'reference'         =>  33,
                'installment_count' =>  12,
                'duration'          =>  12,
                'fee_percent'       =>  12,
                'fee_total'         =>  1740,
                'pre_payment_amount'=>  500,
                'total_amount'      =>  16736,
                'effective_annual'  =>  27.54,
                'nominal_annual'    =>  22.16,
                'dues'              =>  array(
                    array(
                        'type'  =>  'immediate',
                        'date'  =>  '20140318',
                        'value' =>  2240
                    ),
                    array(
                        'type'  =>  'date',
                        'date'  =>  '',
                        'value' =>  1208
                    ),
                ),
            );
            */
            $data = array(
                'orderId'       =>  $orderId,
                'duration'      =>  $req->get_duration(),
                'fee_percent'   =>  $req->get_fee_percent(),
                'fee_total'     =>  $req->get_fee_total(),
                'total_amount'  =>  $req->get_total_amount(),
                'nominal_annual'=>  $req->get_nominal_annual(),
                'dues'          =>  $req->get_dues(),
                // 'pre_payment_amount'    =>  $req->pre_payment_amount,
                'effective_annual'      =>  $req->get_effective_annual(),
            );
            $this->_processRates($data);
        }

        /**
         * Fired before saving edited order in admin/order_edit
         * @param $orderId
         * @abstract
         */
        public function onSaveEditOrderBefore($orderId)
        {
            // since saving new cart sums all OT options, we have to temporarily clear them
            xtc_db_query("UPDATE ".TABLE_ORDERS_TOTAL." SET value=0 WHERE "
                ."(class='ot_z_paylater_fee' OR class='ot_z_paylater_total') AND orders_id = '".$orderId."' ");
        }

        /**
         * Event fired after getting success response for editCartContent method
         * @param $orderId
         * @param ipl_edit_cart_content_request $req
         */
        public function onOrderChanged($orderId, $req)
        {
            global $xtPrice;

            // set OT values and installment plan
            $data = array(
                'instalment_count'      =>  $req->get_instalment_count(),
                'orderId'       =>  $orderId,
                'duration'      =>  $req->get_duration(),
                'fee_percent'   =>  $req->get_fee_percent(),
                'fee_total'     =>  $req->get_fee_total(),
                'total_amount'  =>  $req->get_total_amount(),
                'nominal_annual'=>  $req->get_nominal_annual(),
                'dues'          =>  $req->get_dues(),
                // 'pre_payment_amount'    =>  $req->pre_payment_amount,
                'effective_annual'      =>  $req->get_effective_annual(),
            );
            $payLaterFee = $data['fee_total'] / 100;
            $payLaterTotal = $data['total_amount'] / 100;
            $payLaterFeeText = $xtPrice->xtcFormat($payLaterFee, true);
            $payLaterTotalText = $xtPrice->xtcFormat($payLaterTotal, true);
            xtc_db_query("UPDATE ".TABLE_ORDERS_TOTAL." SET value='".$payLaterFee."', text='".$payLaterFeeText."' WHERE class='ot_z_paylater_fee' AND orders_id = '".$orderId."'");
            xtc_db_query("UPDATE ".TABLE_ORDERS_TOTAL." SET value='".$payLaterTotal."', text='".$payLaterTotalText."' WHERE class='ot_z_paylater_total' AND orders_id = '".$orderId."'");
            $this->_processRates($data);
        }

        /**
         * Saves new PayLater data to DB.
         * @param $data
         */
        private function _processRates($data)
        {
            /* Fields for PayLater:
                    instalment_count
                    duration
                    fee_percent
                    fee_total
                    pre_payment
                    total_amount
                    effective_annual
                    nominal_annual
             */
            $qry = 'UPDATE billpay_bankdata
                        SET
                            instalment_count = "'.(int)$data['instalment_count'].'",
                            duration = "'.(int)$data['duration'].'",
                            fee_percent = "'.(float)$data['fee_percent'].'",
                            fee_total = "'.(float)$data['fee_total'].'",
                            pre_payment = "'.(float)$data['pre_payment_amount'].'",
                            total_amount = "'.(float)$data['total_amount'].'",
                            effective_annual = "'.(float)$data['effective_annual'].'",
                            nominal_annual = "'.(float)$data['nominal_annual'].'",
                            rate_dues = "' . mysql_real_escape_string(serialize($data)) . '"
                        WHERE orders_id = "' . (int)$data['orderId'] . '"
                        LIMIT 1';
            xtc_db_query($qry);
        }

        /**
         * Renders thankYouText visible on the invoice and email order confirmation.
         * @return string
         */
        public function getThankYouText()
        {
            return MODULE_PAYMENT_BILLPAYPAYLATER_THANK_YOU_TEXT;
        }

        /**
         * Renders payUntilText visible on invoice and email order confirmation.
         * @return string
         */
        function getPayUntilText($bank_data, $currency)
        {
            return MODULE_PAYMENT_BILLPAYPAYLATER_PAY_UNTIL_TEXT;
        }

        /**
         * We don't display any payment details for PayLater
         * @param Billpay_Base_Bankdata $bank_data
         * @param string $currency
         * @return string
         */
        public function getPaymentDetails($bank_data, $currency)
        {
            return '';
        }

        /**
         * Renders "additional email" text visible on invoice and email order confirmation.
         * @return string
         */
        public function getEmailText()
        {
            return MODULE_PAYMENT_BILLPAYPAYLATER_EMAIL_TEXT;
        }

    }
}
