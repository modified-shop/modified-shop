<?php

require_once DIR_FS_CATALOG . 'includes/external/billpay/base/billpayBase.php';

if (!class_exists('BillpayPayLater'))
{
    class BillpayPayLater extends billpayBase {
        var $VISUAL_MODE_PAY_LATER      = 1;
        var $VISUAL_MODE_RECHNUNG_PLUS  = 2;
        var $visualMode = 1;
        var $_paymentIdentifier;
        var $otModules = array(
            'ot_z_paylater_fee',
            'ot_z_paylater_total'
        );

        function BillpayPayLater($identifier = null)
        {
            $this->_paymentIdentifier = constant('billpayBase_PAYMENT_METHOD_PAY_LATER');
            $this->_defaultConfig['MIN_AMOUNT'] = '150'; // PL is enabled from 150EUR by default. Merchant can change it.
            parent::billpayBase($identifier);
            $this->requireLang();

            if (defined("MODULE_PAYMENT_".$this->_paymentIdentifier."_VISUAL_MODE") && constant("MODULE_PAYMENT_".$this->_paymentIdentifier."_VISUAL_MODE") === "RechnungPlus")
            {
                $this->visualMode = $this->VISUAL_MODE_RECHNUNG_PLUS;
                $this->title = constant("MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_TITLE_RECHNUNG_PLUS");
            }
        }

        function _getPaymentType() {
            return IPL_CORE_PAYMENT_TYPE_PAY_LATER;
        }

        function _getStaticLimit($config) {
            return 10000000000; // moduleConfig no longer applies
        }

        function _getMinValue($config) {
            return 0;
        }

        /**
         * Returns configured visual mode of the method.
         * @return int VISUAL_MODE_PAY_LATER | VISUAL_MODE_RECHNUNG_PLUS
         */
        function getVisualMode()
        {
            return $this->visualMode;
        }

        /**
         * Displays payment method @ checkout_payment.php
         * @return array $selection
         */
        function _buildPaymentHtml()
        {
            $title_ext = '';

            // PayLater does not allow to add additional surcharges
            // $title_ext = $this->_buildFeeTitleExtension($this->_paymentIdentifier);
            $selection = array(
                'id'        => $this->code,
                'module'    => $this->title . ($title_ext ? (' ' . $title_ext): '')
            );

            $selection = $this->_extendSeoLayout($selection, $this->renderPaymentMethod());
            $selection = $this->rebuildSelection($selection);
            return $selection;
        }

        /**
         * Returns cart total and shipping.
         * @return array
         */
        function _getCartBaseAndShipping()
        {
            $baseAmount = 0;
            $cart = $_SESSION['cart'];
            if ($cart)
            {
                $baseAmount = (float)$cart->total;
            }
            $shippingAmount = $this->_getTrueShipping();
            $rebateAmount = $this->_getRebateAmount();
            $ret = array(
                'baseAmount'        => (string)$baseAmount - $rebateAmount,
                'shippingAmount'    => (string)$shippingAmount,
                'orderAmount'       => (string)($baseAmount + $shippingAmount - $rebateAmount),
            );
            return $ret;
        }

        /**
         * While choosing payment, shipping is not calculated with tax. We are recalculating it now.
         * @return float
         */
        function _getTrueShipping()
        {
            $order = $GLOBALS['order'];
            list($shippingBase, ) = explode('_', $_SESSION['shipping']['id']);
            $nettoShipping = $_SESSION['shipping']['cost'];
            $constTaxClass = 'MODULE_SHIPPING_'.strtoupper($shippingBase).'_TAX_CLASS';
            if (!defined($constTaxClass)) {
                return $nettoShipping;
            }
            $taxClass = constant($constTaxClass);
            if (empty($taxClass))
            {
                return $nettoShipping;
            }
            $taxRate = xtc_get_tax_rate($taxClass, $order->delivery['country']['id'], $order->delivery['zone_id']);
            if ($taxRate == 0)
            {
                return $nettoShipping;
            }
            $taxAmount = round(($nettoShipping / 100 * $taxRate), 2);
            return $nettoShipping + $taxAmount;
        }


        /**
         * Calculates RebateGross of current order
         * @return mixed
         */
        function _getRebateAmount()
        {
            global $order_total_modules;
            $order = new stdClass();
            $order->delivery = array();
            $ots = $this->_calculate_billpay_totals($order_total_modules, $order, true);
            return $ots['billpayRebateGross'];
        }




        /**
         * Renders payment method's form
         * @return string
         */
        function renderPaymentMethod()
        {
            $apiKey = $this->bp_public_api_key;
            $userIdentifier = $this->getCustomerIdentifier();
            $country3 = $this->_getCountry(3);
            $country2 = $this->_getCountry(2);
            $currency = $this->_getCurrency();
            $lang     = $this->_getLanguage();
            $amount = $this->_getCartBaseAndShipping();
            $baseAmount = $amount['baseAmount'];
            $orderAmount= $amount['orderAmount'];
            $customerName   =   $_SESSION['customer_first_name']. " " . $_SESSION['customer_last_name'];
            $customerDob    =   date('Y-m-d', $this->getDateOfBirth());
            if ($customerDob === "1970-01-01") {
                $customerDob = '';
            }
            $customerGender =   $this->getGender();
            $customerPhone = $this->getPhone();
            $isLive = ($this->isTestMode ? '0' : '1');
            $visualMode = $this->getVisualMode();
            $afterLoad = <<<HEREDOC
                function(jQ, loadForm) {
                    window.jQ = jQ;
                    onClick = function() {
                        var el = jQ(this),
                            plt = jQ(window.bpyPayLater.selector),
                            initialized = plt.is(':bpy-paylater');
                        if (el.attr('value') != 'billpaypaylater') {
                            if (initialized) {
                                plt.data('bpy-paylater').destroy();
                            }
                        } else if (el.prop('checked') || !initialized) {
                            loadForm();
                        }
                    };
                    jQ('input[name=payment]').on('click', onClick);
                    jQ('div.payment_item').on('click', function() {
                        var el = jQ(this).find('input[name=payment]');
                        onClick.call(el[0]);
                    });
                    if (jQ('input[value=billpaypaylater]').prop('checked')) {
                        loadForm();
                    }
                }
HEREDOC;
            $afterLoad = str_replace("\n", "", $afterLoad);
            $payLaterString = <<<HEREDOC
            {
                "selector": "#paylater_container",
                "options": {
                    "apiKey":       "$apiKey",
                    "form":         "#checkout_payment",
                    "country":      "$country3",
                    "countryIso2":  "$country2",
                    "currency":     "$currency",
                    "lang":         "$lang",
                    "baseAmount":   "$baseAmount",
                    "orderAmount":  "$orderAmount",
                    "live": $isLive,
                    "mode": $visualMode,
                    "userIdentifier": "$userIdentifier",
                    "customer": {
                        "name":     "$customerName",
                        "dob":      "$customerDob",
                        "gender":   "$customerGender",
                        "phone":    "$customerPhone",
                    },
                },
                "waitingFor": "$afterLoad"
            }
HEREDOC;
            $constPayLaterWelcome = constant('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_PROMO');
            $js = <<<HEREDOC
            <div id="paylater_container">$constPayLaterWelcome</div>
            <script type="text/javascript">
                window.bpyPayLater = $payLaterString;
                try { initPayLater(); } catch(e) {  }
            </script>
HEREDOC;
            $js .= $this->_injectJavascript();
            return $js;
        }

        /**
         * Process payment method input data (form), before validation
         */
        function onMethodInput($data)
        {
            $this->setDateOfBirth($data['billpay']['dob']);
            $gender = '';
            switch ($data['billpay']['gender']) {
                case 'Herr':
                    $gender = 'm';
                    break;
                case 'Frau':
                    $gender = 'f';
                    break;
            }
            $this->setGender($gender);
            $this->setEula($data['billpay']['toc']);
            $this->setPhone($data['billpay']['phone']);
            $this->_setDataValue('totalAmount', $data['billpay']['total_amount'] * 0.01);
            $this->_setDataValue('feeAmount', $data['billpay']['fee_total'] * 0.01);

            $this->_setDataValue('account_holder', $data['billpay']['account_holder']);
            $this->_setDataValue('account_iban', $data['billpay']['account_iban']);
            $this->_setDataValue('account_bic', $data['billpay']['account_bic']);



            $this->_setDataValue('instalments', $data['billpay']['instalments']);
            $this->_setDataValue('total_amount', $data['billpay']['total_amount']);

            if (!$this->getPhone()) {
                $this->error = constant('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_PHONE');
                return false;
            }

            $required = array(
                'account_holder'    =>  MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_BANKDATA,
                'account_iban'      =>  MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_BANKDATA,
                'totalAmount'       =>  MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ERROR_NO_RATEPLAN,
                'feeAmount'         =>  MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ERROR_NO_RATEPLAN,
                'instalments'       =>  MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ERROR_NO_RATEPLAN,
                'total_amount'      =>  MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ERROR_NO_RATEPLAN,
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
            $req->set_rate_request(
                $this->_getDataValue('instalments'),
                $this->_getDataValue('total_amount')
            );
            return $req;
        }



        /**
         * Event fired after receiving preauthorize response
         * @param ipl_preauthorize_request $req
         */
        function onPreauthResponse($req) {
            // TODO: this should use BankData class
            $customerData = array(
                'token'     =>  $this->token,
            );
            $data = array(
                'customer_cache'    =>  mysql_real_escape_string(serialize($customerData)),
            );
            Billpay_Base_Bankdata::UpdateByTxId($this->_getTransactionId(), $data);
        }

        /**
         * Event fired when Billpay calls shop back with Giropay prepayment.
         * @param $orderId
         * @param $data
         * @abstract
         */
        function onOrderApproved($orderId, $data) {
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
        function onAfterInvoiceCreated($req, $orderId) {
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
                'duration'      =>  $req->duration,
                'fee_percent'   =>  $req->fee_percent,
                'fee_total'     =>  $req->fee_total,
                'total_amount'  =>  $req->total_amount,
                'nominal_annual'=>  $req->nominal_annual,
                'dues'          =>  $req->dues,
                'pre_payment_amount'    =>  $req->pre_payment_amount,
                'effective_annual'      =>  $req->effective_annual,
            );
            $this->_processRates($data);
        }

        /**
         * Fired before saving edited order in admin/order_edit
         * @param $orderId
         * @abstract
         */
        function onSaveEditOrderBefore($orderId)
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
        function onOrderChanged($orderId, $req)
        {
            global $xtPrice;

            // set OT values and installment plan
            $data = array(
                'instalment_count'=>$req->instalment_count,
                'orderId'       =>  $orderId,
                'duration'      =>  $req->duration,
                'fee_percent'   =>  $req->fee_percent,
                'fee_total'     =>  $req->fee_total,
                'total_amount'  =>  $req->total_amount,
                'nominal_annual'=>  $req->nominal_annual,
                'dues'          =>  $req->dues,
                'pre_payment_amount'    =>  $req->pre_payment_amount,
                'effective_annual'      =>  $req->effective_annual,
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
        function _processRates($data)
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
         * Event fired when admin is looking at user's invoice.
         * Should display additional payment method's info.
         * @param int $orderId
         * @return string
         */
        function onDisplayInvoice($orderId)
        {
            $currency = BillpayDB::DBFetchValue("SELECT currency FROM ".TABLE_ORDERS." WHERE orders_id = '".$orderId."'");
            $rateDuesSerialized = BillpayDB::DBFetchValue("SELECT rate_dues FROM billpay_bankdata WHERE orders_id = '".(int)$orderId."'");
            $rateDues = unserialize($rateDuesSerialized);
            $paymentArr = array();
            if (false)
            {
                if (!empty($rateDues['pre_payment_amount'])) {
                    $paymentArr['Prepayment']    =  $rateDues['pre_payment_amount'];
                }
            }
            $paymentString = '';
            foreach ($paymentArr as $key => $val) {
                $paymentString .= '<br /><strong>'.$key.':</strong>&nbsp;'.$val;
            }
            $paymentString .= '<br />'.constant('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_INVOICE_INFO1');
            if (false) // not displaying installment plan anymore
            {
                $paymentString .= '<br /><strong>Installment plan:</strong><ul>';
                foreach ($rateDues['dues'] as $rate) {
                    $paymentString .= '<li>';
                    $paymentString .= date('Y-m-d', strtotime($rate['date']));
                    $paymentString .= ' - ';
                    $paymentString .= sprintf("%.2f", $rate['value'] * 0.01). ' '.$currency;
                    $paymentString .= '</li>';
                }
                $paymentString .= '</ul>';
            }
            return $paymentString;
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
         * Event executed during payment method installation.
         */
        function onInstall()
        {
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_VISUAL_MODE";
            $this->_logDebug("Setting local key: $configuration_key");
            xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_".$this->_paymentIdentifier."_VISUAL_MODE', '*PayLater', '6', '0', 'xtc_cfg_select_option(array(\'*PayLater\', \'RechnungPlus\'), ', now())");
        }

        /**
         * Event executed while checking for plugin configuration keys.
         * @param $config_array
         * @return array
         */
        function onKeys($config_array)
        {
            $config_array[] = "MODULE_PAYMENT_".$this->_paymentIdentifier."_VISUAL_MODE";
            return $config_array;
        }

        function getPaymentInfo($orderId = null)
        {
            $infoText = constant('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_INVOICE_INFO1');
            return array(
                'html'  =>  $infoText,
                'text'  =>  $infoText,
            );
        }

    }
}
