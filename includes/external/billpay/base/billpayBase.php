<?php

/** @noinspection PhpIncludeInspection */
require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/BillpayDB.php');
/** @noinspection PhpIncludeInspection */
require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/BillpayOrder.php');

if (!class_exists('billpayBase')) {

    // billpayBase constants - php4 way
    define('billpayBase_VERSION', '1.6.1'); // replaced by grunt build script

    define('billpayBase_PAYMENT_METHOD_INVOICE', 'BILLPAY');
    define('billpayBase_PAYMENT_METHOD_DEBIT', 'BILLPAYDEBIT');
    define('billpayBase_PAYMENT_METHOD_TRANSACTION_CREDIT', 'BILLPAYTRANSACTIONCREDIT');
    define('billpayBase_PAYMENT_METHOD_PAY_LATER', 'BILLPAYPAYLATER');

    define('billpayBase_STATE_PENDING', 'PENDING');
    define('billpayBase_STATE_APPROVED', 'APPROVED');
    define('billpayBase_STATE_COMPLETED', 'ACTIVATED');
    define('billpayBase_STATE_ERROR', 'ERROR');
    define('billpayBase_STATE_CANCELLED', 'CANCELLED');

    define('billpayBase_MODE_TEST', 'Testmodus');

    /**
     * Class billpayBase
     */
    class billpayBase {

        var $billpayStates;

        var $code, $title, $description, $enabled, $order;
        var $eula_url, $testmode, $api_url, $_formDob, $_formGender, $_log;
        var $_logPath, $enableLog, $debugLog, $_mode;
        var $bp_merchant, $bp_portal, $bp_secure, $bp_public_api_key;
        var $error;
        var $token;

        var $requiredModules 		= array('ot_total', 'ot_subtotal');
        var $billpayShippingModules = array(
            'ot_billpay_fee', 'ot_billpaydebit_fee', 'ot_billpaybusiness_fee',
            'ot_cod_fee', 'ot_loworderfee', 'ot_ps_fee', 'ot_shipping',
        );
        var $billpayExcludeModules  = array('ot_subtotal', 'ot_subtotal_no_tax', 'ot_tax', 'ot_total');

        /**
         * used by modified-shop for temporary orders
         * @var string
         */
        var $form_action_url = '';

        /**
         * status which is used for temporary orders
         * @var int
         */
        var $tmpStatus = 101;

        /**
         * flag which indicates if a temporary order should be created
         * @var bool
         */
        var $tmpOrders = false;

        /** @var bool $isTestMode Indicates if payment method works in test or production mode. */
        var $isTestMode = false;

        /** @var array $_defaultConfig Default payment method configuration used in installation */
        var $_defaultConfig = array();

        /**
         * php 4 constructor
         *
         * @param null|string $identifier
         */
        function billpayBase($identifier = null) {
            if (empty($identifier) === false) {
                $this->_paymentIdentifier = $identifier;
            }
            $this->billpayStates = array(
                constant('billpayBase_STATE_PENDING'),
                constant('billpayBase_STATE_APPROVED'),
                constant('billpayBase_STATE_COMPLETED'),
                constant('billpayBase_STATE_ERROR'),
                constant('billpayBase_STATE_CANCELLED'),
            );

            $this->code = strtolower($this->_paymentIdentifier);
            $this->title = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_TEXT_TITLE') ? constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_TEXT_TITLE') : '';
            $this->description = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_TEXT_DESCRIPTION') ? constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_TEXT_DESCRIPTION') : '';
            $this->sort_order = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_SORT_ORDER') ? constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_SORT_ORDER') : '';
            $this->min_order = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_MIN_AMOUNT') ? constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_MIN_AMOUNT') : '';
            $this->_logPath = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_LOGGING') ? constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_LOGGING') : '';
            $this->order_status = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_ORDER_STATUS') ? constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_ORDER_STATUS') : '';
            $this->gp_status = 101;

            $this->error_status = defined('MODULE_PAYMENT_BILLPAY_STATUS_ERROR') ? MODULE_PAYMENT_BILLPAY_STATUS_ERROR : '';

            $this->b2b_active = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_B2BCONFIG') ? constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_B2BCONFIG') : '';

            //$this->_testapi_url = 'https://test-api.billpay.de/xml/offline';
            $this->_testapi_url = defined('MODULE_PAYMENT_BILLPAY_GS_TESTAPI_URL_BASE') ? constant('MODULE_PAYMENT_BILLPAY_GS_TESTAPI_URL_BASE') : '';
            $this->_merchant_info = 'http://www.billpay.de/haendler/integration-plugin';

            if (empty($this->_logPath)) {
                $this->_logPath = DIR_FS_CATALOG . 'includes/external/billpay/log/billpay.log';
            }
            else {
                $this->_logPath .= '/billpay.log';
            }
            $this->enableLog = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_LOGGING_ENABLE') ? constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_LOGGING_ENABLE') : false;

            $this->testmode 	= defined('MODULE_PAYMENT_BILLPAY_GS_TESTMODE') ? constant('MODULE_PAYMENT_BILLPAY_GS_TESTMODE') : false;
            if ($this->testmode == 'Testmodus') {
                $this->isTestMode = true;
                $this->api_url = defined('MODULE_PAYMENT_BILLPAY_GS_TESTAPI_URL_BASE') ? constant('MODULE_PAYMENT_BILLPAY_GS_TESTAPI_URL_BASE') : '';
            }
            else {
                $this->api_url = defined('MODULE_PAYMENT_BILLPAY_GS_API_URL_BASE') ? constant('MODULE_PAYMENT_BILLPAY_GS_API_URL_BASE') : '';
            }

            // deactivate module on missing but needed settings
            $_bpMerchant	= defined('MODULE_PAYMENT_BILLPAY_GS_MERCHANT_ID') ? constant('MODULE_PAYMENT_BILLPAY_GS_MERCHANT_ID') : null;
            $_bpPortal	= defined('MODULE_PAYMENT_BILLPAY_GS_PORTAL_ID') ? constant('MODULE_PAYMENT_BILLPAY_GS_PORTAL_ID') : null;
            $_bpSecure	= defined('MODULE_PAYMENT_BILLPAY_GS_SECURE') ? constant('MODULE_PAYMENT_BILLPAY_GS_SECURE') : null;
            $_bpPublicApiKey = defined('MODULE_PAYMENT_BILLPAY_GS_PUBLIC_API_KEY') ? MODULE_PAYMENT_BILLPAY_GS_PUBLIC_API_KEY : null;
            if ((empty($_bpMerchant)) || (empty($_bpPortal)) || (empty($_bpSecure))) {
                $this->_mode = 'sandbox';
            } else {
                if ($this->api_url == $this->_testapi_url) {
                    $this->_mode = 'check';
                }
                $_SESSION['billpay_deactivated'] = $this->enabled;
                $this->bp_merchant = (int)$_bpMerchant;
                $this->bp_portal = (int)$_bpPortal;
                $this->bp_secure = md5($_bpSecure);
                $this->bp_public_api_key = $_bpPublicApiKey;
            }
            $this->enabled = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_STATUS') && constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_STATUS') == 'True' ? true : false;
            $this->sessionID	= xtc_session_id();

            // we just use the default checkout process url here
            $this->form_action_url = xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
        }

        ##### STATIC

        /**
         * Returns instance of payment method
         * @param string $paymentMethod
         * @return mixed
         * @static
         */
        function PaymentInstance($paymentMethod)
        {
            $lowerPaymentMethod = strtoupper($paymentMethod);
            switch ($lowerPaymentMethod)
            {
                case constant('billpayBase_PAYMENT_METHOD_INVOICE'):
                    require_once(DIR_FS_CATALOG.'includes/modules/payment/billpay.php');
                    return new billpay($paymentMethod);
                    break;
                case constant('billpayBase_PAYMENT_METHOD_DEBIT'):
                    require_once(DIR_FS_CATALOG.'includes/modules/payment/billpaydebit.php');
                    return new billpaydebit($paymentMethod);
                case constant('billpayBase_PAYMENT_METHOD_TRANSACTION_CREDIT'):
                    require_once(DIR_FS_CATALOG.'includes/modules/payment/billpaytransactioncredit.php');
                    return new billpaytransactioncredit($paymentMethod);
                case constant('billpayBase_PAYMENT_METHOD_PAY_LATER'):
                    require_once(DIR_FS_CATALOG.'includes/modules/payment/billpaypaylater.php');
                    return new BillpayPayLater($paymentMethod);
            }
            return null;
        }

        /**
         * Function returns Billpay payment methods.
         * @return array
         * @static
         */
        function GetPaymentMethods() {
            return array(
                'billpay', 'billpaydebit', 'billpaytransactioncredit', 'billpaypaylater'
            );
        }

        /**
         * Parses Billpay callback and returns data as array
         * @return array|bool
         * @static
         */
        function ParseCallback()
        {
            /*
            $example = <<<HEREDOC
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<data bptip="2e482026-4fde-4a13-a2bd-07d254494762" customer_message="" error_code="0" merchant_message="" reference="33" status="APPROVED">
    <default_params bpsecure="" mid="" pid=""/>
    <corrected_address city="" country="" street="" streetNo="" zip=""/>
    <invoice_bank_account account_holder="" account_number="" activation_performed=""
        bank_code="" bank_name="" invoice_duedate="" invoice_reference=""/>
    <hire_purchase>
        <instl_plan num_inst="12">
            <calc>
                <duration>12</duration>
                <fee_percent>12.00</fee_percent>
                <fee_total>1740</fee_total>
                <pre_payment>500</pre_payment>
                <total_amount>16736</total_amount>
                <eff_anual>27.54</eff_anual>
                <nominal>22.16</nominal>
            </calc>
            <instl_list>
                <instl date="20140318" type="immediate">2240</instl>
                <instl date="" type="date">1208</instl>
                <instl date="" type="date">1208</instl>
                <instl date="" type="date">1208</instl>
                <instl date="" type="date">1208</instl>
                <instl date="" type="date">1208</instl>
                <instl date="" type="date">1208</instl>
                <instl date="" type="date">1208</instl>
                <instl date="" type="date">1208</instl>
                <instl date="" type="date">1208</instl>
                <instl date="" type="date">1208</instl>
                <instl date="" type="date">1208</instl>
                <instl date="" type="date">1208</instl>
            </instl_list>
        </instl_plan>
    </hire_purchase>
</data>
HEREDOC;
            */

            require_once(DIR_FS_CATALOG.'includes/external/billpay/api/ipl_xml_ws.php');
            $data = parse_async_capture();
            //$data = parse_async_capture($example);
            return $data;
        }



        /**
         * Convert floating-point number to int of cents
         * i.e. fun(1.33333) = 133
         * @param float $price_float
         * @return int
         * @static
         */
        function CurrencyToSmallerUnit($price_float) {
            if ($price_float === NULL) {
                return 0;
            }
            $_price = $price_float * 100;
            return (int)round($_price);
        }

        /**
         * Returns utf-8 string
         * @param string $value
         * @return string
         * @static
         */
        function EnsureUTF8($value) {
            $trimmedValue = trim($value);
            if(defined('MODULE_PAYMENT_BILLPAY_GS_UTF8_ENCODE') && constant('MODULE_PAYMENT_BILLPAY_GS_UTF8_ENCODE') == 'local') {
                return utf8_encode($trimmedValue);
            }
            else {
                return $trimmedValue;
            }
        }

        /**
         * Returns string ready to be displayed on webpage.
         * @param $value
         * @return string
         * @static
         */
        function EnsureString($value) {
            if(defined('MODULE_PAYMENT_BILLPAY_GS_UTF8_ENCODE') && constant('MODULE_PAYMENT_BILLPAY_GS_UTF8_ENCODE') == 'local') {
                return utf8_decode($value);
            } else {
                return $value;
            }
        }

        /**
         * Returns array with products in order. Similar to new order($orderId)->products
         * Since shops cannot use order class everywhere, we have to get those ourselved
         * @param int $orderId
         * @return array
         * @static
         */
        function GetOrderProducts($orderId)
        {
            $ret = array();
            $query = xtc_db_query("SELECT products_price, products_quantity, orders_products_id, products_name FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_id='".(int)$orderId."'");
            while ($row = xtc_db_fetch_array($query)) {
                array_push($ret, array(
                        'price' =>  $row['products_price'],
                        'qty'   =>  $row['products_quantity'],
                        'opid'  =>  $row['orders_products_id'],
                        'name'  =>  $row['products_name']
                    ));
            }
            return $ret;
        }

        /**
         * Function renders and displays HTML UTF8 error page and exits.
         * Error page looks very different from what merchant usually sees.
         * @param $errorString string UTF8 encoded error
         * @static
         */
        function DisplayErrorAndExit($errorString)
        {
            include(DIR_FS_CATALOG . 'includes/external/billpay/templates/error_utf8.php');
            die($errorString);
        }


        /**
         * Function adds message to session and redirects to selected url.
         * Message is hardly visible on page.
         * @param $errorString
         * @param $redirectUrl
         */
        function QueueErrorAndRedirect($errorString, $redirectUrl)
        {
            global $messageStack;
            $messageStack->add_session($errorString, 'error');
            xtc_redirect($redirectUrl);
        }

        #### /STATIC

        /**
         * BillPay callback function handling an order confirmation and Giropay confirmation
         */
        function onBillpayCallback($data)
        {
            $dataCopy = $data;
            unset($dataCopy['xml']);
            $this->_logDebug($dataCopy);
            if ($data['xmlStatus'] !== true) {
                $this->_logError(
                    'ERROR wrong data format in async capture order request' . "\n" . $data['postdata'],
                    'Async capture: ERROR'
                );
                return false;
            }
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/Bankdata.php');
            $oBankData = new Billpay_Base_Bankdata();
            $orderId = (int)$data['reference'];
            $token   = $_GET['token'];
            $oBankData->loadByOrdersId($orderId);
            if (!$oBankData->isValidToken($token)) {
                $this->_logError('Invalid token ('.$token.') for reference ('.$orderId.').');
                return false;
            }
            $this->_logDebug('Callback token is valid.');

            if ( !in_array($data['status'], array('APPROVED', 'DENIED'))) {
                $this->_logError(
                    $data['status'].' code returned when receiving async capture order request' . "\n"
                    . print_r($data, true), 'Async capture: '.$data['status']
                );
                return false;
            }

            if ($data['status'] == 'DENIED') {
                $orderId = (int)$data['reference'];
                $this->_logDebug("Order ".$orderId." denied.");
                $this->setOrderBillpayState(constant('billpayBase_STATE_CANCELLED'), $orderId);
                return false;
            }

            // approved!
            $orderId = (int)$data['reference'];
            $this->setOrderBillpayState(constant('billpayBase_STATE_APPROVED'), $orderId);
            $this->onOrderApproved($orderId, $data);
            // send mail?

            return true;
        }

        /**
         * Event fired when admin changes order's status
         * @param $orderId
         * @param $newStatus
         * @return bool
         */
        function onOrderStatusChange($orderId, $newStatus)
        {
            $this->_logDebug("Changing order's (".$orderId.") status to ".$newStatus);
            $result = true;
            if ($newStatus == $this->getOrderStatusFromBillpayState(constant('billpayBase_STATE_COMPLETED')))
            {
                $result = $this->reqInvoiceCreated($orderId);
            }
            if ($newStatus == $this->getOrderStatusFromBillpayState(constant('billpayBase_STATE_CANCELLED')))
            {
                $result = $this->reqCancel($orderId);
            }
            if (!$result) {
                // billpayBase::DisplayErrorAndExit($this->error);
                $this->addHistoryEntry($orderId, $this->error);
                billpayBase::QueueErrorAndRedirect($this->error, DIR_WS_ADMIN.'orders.php?action=edit&oID='.$orderId);
            }
            return true;
        }

        /**
         * Event fired when Billpay calls shop back with prepayment callback.
         * @param $orderId
         * @param $data
         * @abstract
         */
        function onOrderApproved($orderId, $data)
        {

        }

        /**
         * Event fired after getting success response for editCartContent method
         * @param $orderId
         * @param ipl_edit_cart_content_request $req
         * @abstract
         */
        function onOrderChanged($orderId, $req)
        {

        }

        /**
         * Event fired when admin is looking at user's invoice.
         * Should display additional payment method's info.
         * @param int $orderId
         * @return string
         * @abstract
         */
        function onDisplayInvoice($orderId)
        {
           return '';
        }

        /**
         * Event fired when admin prints a PDF.
         * Warning: this is not a standard shop function.
         * @abstract
         * @param $pdf
         * @param $orderId
         * @param $bankDataQuery
         * @return bool
         */
        function onDisplayPdf($pdf, $orderId, $bankDataQuery)
        {
            return true;
        }

        /**
         * Returns payment information strings added to the customer's e-mail.
         *
         * @param null $orderId
         * @return array
         * @abstract
         */
        function getPaymentInfo($orderId = null)
        {
            return array(
                'html'  =>  '',
                'text'  =>  '',
            );
        }

        /**
         * Requires correct language files
         */
        function requireLang()
        {
            $language = empty($_SESSION['language']) ? 'german' : $_SESSION['language'];
            $file = DIR_FS_CATALOG .'lang/'. $language . '/modules/payment/' . strtolower($this->_paymentIdentifier) . '.php';
            if (file_exists($file))
            {
                require_once($file);
            }
        }

        function getCurrentLangIso2()
        {
            $language = empty($_SESSION['language']) ? 'german' : $_SESSION['language'];
            switch ($language) {
                case 'german':
                    return 'de';
                    break;
                case 'english':
                    return 'en';
                    break;
                case 'dutch':
                case 'netherlands':
                    return 'nl';
                    break;
                default:
                    return 'de';
            }
        }

        /**
         * Checks if it should display current payment method and displays it.
         * @return array|false
         */
        function selection() {
            unset($_SESSION['gm_error_message']); // Gambio specific
            // STEP 1: Check if customer has been denied previously
            if (isset($_SESSION['billpay_hide_payment_method']) && $_SESSION['billpay_hide_payment_method']) {
                return false;
            }

            // STEP 2: Check if minimum order value is deceeded
            if (BillpayOrder::getTotal() < (float)$this->min_order) {
                return false;
            }

            // STEP 3: Check if all required default modules are installed (need not be activated)
            foreach ($this->requiredModules as $moduleName) {
                if ($this->isModuleInstalled($moduleName) === FALSE) {
                    $this->_logError("Required module $moduleName is not installed. Hide BillPay payment method.", "FATAL ERROR");
                    return false;
                }
            }


            $config = $this->getModuleConfig();
            if (!$config) {
                $this->_logError("Cannot load moduleConfig!");
                return false;
            }

            // STEP 4: Check, if static limit is exceeded
            $staticLimit 	= $this->_getStaticLimit($config);
            $minValue 		= $this->_getMinValue($config);
            $orderTotal 	= $this->CurrencyToSmallerUnit(BillpayOrder::getTotal());
            if ($orderTotal > $staticLimit) {
                $this->_logError($this->_paymentIdentifier.' static limit exceeded (' . $orderTotal . ' > ' . $staticLimit . ')');
                return false;
            }
            if ($orderTotal < $minValue) {
                $this->_logError($this->_paymentIdentifier.' min value deceeded (' . $orderTotal . ' < ' . $minValue . ')');
                return false;
            }

            // STEP 5: Check, if all customer groups are denied
            if ($this->_is_b2b_allowed($config) == false && $this->_is_b2c_allowed($config) == false) {
                $this->_logError('No customer groups allowed for ' . $this->_paymentIdentifier);
                return false;
            }

            return $this->_buildPaymentHtml();
        }

        /**
         * Adds to order's status history, without changing status. Used by partial cancel.
         * @param int $oID
         * @param string $infoText
         * @param int|null $status  New status. If not set, it preserves current status.
         */
        function addHistoryEntry($oID, $infoText, $status = null) {
            if ($status === null) {
                // get last status
                $handle = xtc_db_query("SELECT orders_status FROM ".TABLE_ORDERS." WHERE orders_id='".(int)$oID."'");
                $data = xtc_db_fetch_array($handle);
                $status = $data['orders_status'];
            }

            $modification_version = $this->getShopModification();
            if ($modification_version['modification'] == 'gambio'
                && version_compare($modification_version['version'], '2.1.0', '>=')) {
                // Gambio 2.1 displays admin page using UTF-8
                $infoText = html_entity_decode($infoText, null, 'UTF-8');
            } else {
                // since xtc shows admin page with ISO-8859-15 charset and strips html entities while displaying statuses...
                $infoText = html_entity_decode($infoText, null, 'ISO-8859-15');
            }

            xtc_db_query("INSERT INTO ".TABLE_ORDERS_STATUS_HISTORY." (orders_id, orders_status_id, date_added, comments) VALUES (".(int)$oID.", ".(int)$status.", now(), '".$infoText."')");
        }

        /**
         * Returns selection "object" with full payment form
         * @return array
         */
        function _buildPaymentHtml() {
            unset($_SESSION['gm_error_message']);

            $input_fields = '';

            // use span for one page checkout in order to avoid gui being displayed initially after payment selection
            $holder_element = class_exists("xajax") ? 'span' : 'div';
            $holder_element_height = class_exists("xajax") ? 'height:200px;' : '';

            $config = $this->getModuleConfig();


            $b2bselection = '';
            if ($this->_is_b2b_allowed($config)) {
                $customerCompany = BillpayOrder::getCustomerCompany();
                if(isset($_SESSION['billpay_preselect']) && $_SESSION['billpay_preselect'] == 'b2c') {
                    $preselect_b2b = 'none';
                    $preselect_b2c = 'block';

                } elseif (isset($_SESSION['billpay_preselect']) && $_SESSION['billpay_preselect'] == 'b2b'
                          || (isset($_SESSION['customer_vat_id']) === true && $_SESSION['customer_vat_id'] != '')
                          || (!empty($customerCompany))
                ) {
                    $preselect_b2b = 'block';
                    $preselect_b2c = 'none';

                } else {
                    $preselect_b2b = 'none';
                    $preselect_b2c = 'block';
                }

                if ($this->b2b_active == 'BOTH'
                    && $this->_is_b2b_allowed($config)
                    && $this->_is_b2c_allowed($config)
                ) {
                    $b2bselection = $this->_addB2BSelection();
                    $input_fields .= '<' . $holder_element . ' id="b2b" style="' . $holder_element_height . 'display:' . $preselect_b2b . '">'
                                  . $this->_addB2BInputFields() . '</' . $holder_element . '>'
                                  . '<' . $holder_element . ' id="b2c" style="' . $holder_element_height . 'display:' . $preselect_b2c . '">'
                                  . $this->_addB2CInputFields() . '</' . $holder_element . '>';

                } elseif (in_array($this->b2b_active, array('B2B', 'BOTH')) && $this->_is_b2b_allowed($config)) {
                    $input_fields .= '<div id="b2b" style="display:block" >' . $this->_addB2BInputFields();
                    $input_fields .= '<input type="hidden" name="b2bflag" value="1" /></div>';
                }
                else if(in_array($this->b2b_active, array('B2C', 'BOTH')) && $this->_is_b2c_allowed($config)) {
                    $input_fields .= '<div id="b2c" style="display:block">' . $this->_addB2CInputFields();
                    $input_fields .= '<input type="hidden" name="b2bflag" value="0" /></div>';
                }
            }
            else {
                $input_fields .= '<div id="b2c" style="display:block">' . $this->_addB2CInputFields() . '</div>';
            }

            $billpay_js = $this->_displayPaymentJs();

            $executeScript = '';
            $onClickAction = 'onclick="show_billpay_details(\''.$this->_paymentIdentifier.'\');"';
            if (isset($_GET['error_message']) && $_SESSION['payment'] == strtolower($this->_paymentIdentifier)) {
                $executeScript = 'show_billpay_details(\''.$this->_paymentIdentifier.'\');';
            }
            // Check if OneStepCheckout is installed and activated
            elseif (defined('FILENAME_CHECKOUT') && strstr($_SERVER['PHP_SELF'], FILENAME_CHECKOUT)) {
                $executeScript = 'show_billpay_details(\''.$this->_paymentIdentifier.'\');';
            }

            $billpay_input = "";
            if($this->_mode == 'sandbox') {
                $billpay_input .= '<div style="margin-top:3px; margin-bottom:10px; border-style: solid;border-color:red; text-align:center; background-color:#ffd9b3;"><font color="red"><strong>'. MODULE_PAYMENT_BILLPAY_TEXT_SANDBOX .'</font> <br /> <a href="'. $this->_merchant_info .'" target="_blank">'.MODULE_PAYMENT_BILLPAY_UNLOCK_INFO.'</a></strong> </div>';
            }
            else if($this->_mode == 'check') {
                $billpay_input .= '<div style="margin-top:3px; margin-bottom:10px; border-style: solid;border-color:red; text-align:center; background-color:#ffd9b3;"><font color="red"><strong>'. MODULE_PAYMENT_BILLPAY_TEXT_CHECK .'</font> <br /> <a href="'. $this->_merchant_info .'" target="_blank">'.MODULE_PAYMENT_BILLPAY_UNLOCK_INFO.'</a></strong> </div>';
            }

            $title_ext = $this->_buildFeeTitleExtension($this->_paymentIdentifier);

            $selection = array('id' => $this->code,
                'module' => $this->title . ($title_ext ? (' ' . $title_ext): ''));

            if(isset($GLOBALS['ot_payment']) && method_exists($GLOBALS['ot_payment'], 'get_percent')) {
                $selection['module_cost'] = $GLOBALS['ot_payment']->get_percent(strtolower($this->_paymentIdentifier));
            }

            $is_fee = false;
            if(isset($fee) && $fee > 0) {
                $is_fee = true;
            }

            $billpay_input .= $b2bselection;
            $billpay_input .= $this->getPaymentForm($input_fields, $is_fee);

            if (!empty($executeScript)) {
                $billpay_js .= '<script>'.$executeScript.'</script>';
            }
            //$selection['fields'][] = array('title' => $billpay_input.$billpay_js);
            $billpay_js .= $this->_injectJavascript();
            $billpay_js .= $this->_injectCss();
            $selection = $this->_extendSeoLayout($selection, $billpay_input.$billpay_js);

            $sepaText = $this->getSepaText();

            if (!isset($_SESSION['bp_fraud_tags_rendered'])) {
                $hash = $this->getCustomerIdentifier();
                $sepaText .= '<p style="margin: 0px; background:url(https://cdntm.billpay.de/fp/clear.png?org_id=ulk99l7b&session_id='.$hash.'&m=1)"></p><img src="https://cdntm.billpay.de/fp/clear.png?org_id=ulk99l7b&session_id='.$hash.'&m=2" alt="" ><script src="https://cdntm.billpay.de/fp/check.js?org_id=ulk99l7b&session_id='.$hash.'" type="text/javascript"></script><object type="application/x-shockwave-flash" data="https://cdntm.billpay.de/fp/fp.swf?org_id=ulk99l7b&session_id='.$hash.'" width="1" height="1" id="obj_id"><param name="movie" value="https://cdntm.billpay.de/fp/fp.swf?org_id=ulk99l7b&session_id='.$hash.'" /><div></div></object>';
                $_SESSION['bp_fraud_tags_rendered'] = true;
            }

            // Attach html for Billpay logo
            if ($this->_paymentIdentifier != constant('billpayBase_PAYMENT_METHOD_TRANSACTION_CREDIT')) {
                $sepaText .= MODULE_PAYMENT_BILLPAY_TEXT_INFO;
            }

            $selection = $this->_extendSeoEula($selection, $sepaText, $onClickAction);
            $selection = $this->rebuildSelection($selection);

            return $selection;
        }

        /**
         * Rebuilds selection array so it is rendered in divs.
         * @param $selection
         * @return mixed
         */
        function rebuildSelection($selection)
        {
            $selectionString = '';
            foreach ($selection['fields'] as $field)
            {
                $selectionString .= '<div class="bpyField">'.$field['title'].'</div>';
            }
            $selection['fields'] = array(
                array(
                    'title' =>  '',
                    'field' =>  '<div class="bpySelection">'.$selectionString.'</div>',
                ),
            );
            return $selection;
        }


        /**
         * Checks if merchant setted up additional fee for using selected payment
         * @param $paymentIdentifier
         * @return bool|string
         */
        function _buildFeeTitleExtension($paymentIdentifier) {

            $fee_string = '';
            if (class_exists('ot_'.$this->_getDataIdentifier('fee'))
                && defined('MODULE_ORDER_TOTAL_'.$paymentIdentifier.'_FEE_STATUS')
                && constant('MODULE_ORDER_TOTAL_'.$paymentIdentifier.'_FEE_STATUS') == 'true')
            {
                // Warning: TC and PayLater don't use this type of OT
                //$class_name = 'ot_'.$this->_getDataIdentifier('fee');
                $class_name = 'ot_'.strtolower($paymentIdentifier).'_fee';
                /** @var BillpayOT $billpay_fee */
                $billpay_fee = new $class_name;
                $fee = $billpay_fee->display();
                if (isset($fee) && $fee > 0) {
                    $fee_string .= MODULE_PAYMENT_BILLPAY_TEXT_ADD. $billpay_fee->display_formated();
                }
            }
            if ($paymentIdentifier == billpayBase_PAYMENT_METHOD_INVOICE) {
                if (!empty($fee_string)) {
                    $fee_string = 'B2C: '.$fee_string;
                }
                $class_name = 'ot_billpaybusiness_fee';
                if (class_exists($class_name)
                    && defined('MODULE_ORDER_TOTAL_BILLPAYBUSINESS_FEE_STATUS')
                    && constant('MODULE_ORDER_TOTAL_BILLPAYBUSINESS_FEE_STATUS') == 'true')
                {
                    /** @var BillpayOT $billpay_fee */
                    $billpay_fee = new $class_name;
                    $fee = $billpay_fee->display();
                    if (isset($fee) && $fee > 0) {
                        $fee_string .= ' B2B: '.MODULE_PAYMENT_BILLPAY_TEXT_ADD. $billpay_fee->display_formated();
                    }
                }
            }

            if (!empty($fee_string)) {
                return $fee_string;
            }
            return false;
        }

        /**
         * Admin backend checks if payment module is enabled.
         * @return bool|int
         */
        function check() {
            $check_query = xtc_db_query('SELECT configuration_value FROM ' . TABLE_CONFIGURATION . ' WHERE configuration_key =' . "'MODULE_PAYMENT_".$this->_paymentIdentifier."_STATUS'");
            return xtc_db_num_rows($check_query);
        }

        /**
         * Sets customer data into the preauth request.
         * @param ipl_preauthorize_request $req
         * @param string $customerGroup
         * @return ipl_preauthorize_request
         */
        function _set_customer_details($req, $customerGroup='p') {
            //added get customer phone for tc
            $phone = $this->getPhone();

            $billing = BillpayOrder::getCustomerBilling();
            $req->set_customer_details(
                billpayBase::EnsureUTF8($this->_getCustomerId()),
                billpayBase::EnsureUTF8($this->_getCustomerGroup()),
                billpayBase::EnsureUTF8($this->_getCustomerSalutation($this->_formGender)),
                '', // title
                billpayBase::EnsureUTF8($billing['firstName']),
                billpayBase::EnsureUTF8($billing['lastName']),
                billpayBase::EnsureUTF8($billing['address']),
                '', // streetno
                '', // address extra
                $billing['postCode'],
                billpayBase::EnsureUTF8($billing['city']),
                $billing['country3'],
                billpayBase::EnsureUTF8(BillpayOrder::getCustomerEmail()),
                billpayBase::EnsureUTF8($phone),
                '', // cellphone
                billpayBase::EnsureUTF8(date('Ymd', $this->getDateOfBirth())),
                billpayBase::EnsureUTF8($this->_getLanguage()),
                billpayBase::EnsureUTF8($this->_getCustomerIp()),
                billpayBase::EnsureUTF8($customerGroup)
            );

            return $req;
        }

        /**
         * Sets shipping data into the preauth request.
         * @param ipl_preauthorize_request $req
         * @return ipl_preauthorize_request
         */
        function _set_shipping_details($req) {
            $delivery = BillpayOrder::getCustomerBilling();
            $phone = BillpayOrder::getCustomerPhone();
            if (empty($phone)) {
                $phone = $_POST[strtolower($this->_paymentIdentifier) . "_phone"];
            }
            $req->set_shipping_details(FALSE,
                billpayBase::EnsureUTF8($this->_getCustomerSalutation($this->_getDataIdentifier('gender', $_POST))),
                '', // title
                billpayBase::EnsureUTF8($delivery['firstName']),
                billpayBase::EnsureUTF8($delivery['lastName']),
                billpayBase::EnsureUTF8($delivery['address']),
                '', // streetno
                '', // address extra
                $delivery['postCode'],
                billpayBase::EnsureUTF8($delivery['city']),
                $delivery['country3'],
                $phone,
                '' // cellphone
            );
            return $req;
        }

        /**
         * Adds ordered articles to the preauth request
         * @param ipl_preauthorize_request $req
         * @return ipl_preauthorize_request mixed
         */
        function _add_articles($req) {
            $products = BillpayOrder::getProducts();
            foreach ($products as $p) {
                $req->add_article($p['id'], $p['qty'], $p['name'], '',
                    $this->_getPrice($p['price'], $p['tax'], $_SESSION['customers_status']['customers_status_show_price_tax']),
                    $this->CurrencyToSmallerUnit($p['price'])
                );
            }
            return $req;
        }

        /**
         * @param ipl_preauthorize_request $req
         * @return ipl_preauthorize_request
         */
        function _add_order_totals($req) {
            $billpayTotals = $this->_getDataValue('order_totals');

            $req->set_total(
                $this->CurrencyToSmallerUnit($billpayTotals['billpayRebateNet']),	// rebate
                $this->CurrencyToSmallerUnit($billpayTotals['billpayRebateGross']),	// rebategross
                'n/a',
                $this->CurrencyToSmallerUnit($billpayTotals['billpayShippingNet']),
                $this->CurrencyToSmallerUnit($billpayTotals['billpayShippingGross']),
                $this->CurrencyToSmallerUnit($billpayTotals['orderTotalNet']),
                $this->CurrencyToSmallerUnit($billpayTotals['orderTotalGross']),
                $this->_getCurrency(), // currency
                '' 	// reference
            );

            return $req;
        }

        /**
         * Sets statistical data to the request, so Billpay can see which shops are in use.
         * @param ipl_preauthorize_request $req
         * @return ipl_preauthorize_request
         */
        function _setTrace($req)
        {
            $req->setTracePluginVersion(constant('billpayBase_VERSION'));
            $shop = $this->getShopModification();
            $req->setTraceShopType($shop['modification']);
            $req->setTraceShopVersion($shop['version']);
            return $req;
        }

        /**
         * @param $order_total_modules
         * @param $order
         * @param $isNetShippingPrice
         * @return array
         */
        function _calculate_billpay_totals($order_total_modules, $order, $isNetShippingPrice) {
            # TODO: check this function
            // Calculate and add totals
            $order_totals = $order_total_modules->modules;

            $orderTotalGross = 0;
            $orderSubTotalGross = 0;
            $orderTax = 0;
            $billpayShippingNet = 0;
            $billpayShippingGross = 0;
            $billpayRebateGross = 0;

            if (is_array($order_totals)) {
                reset($order_totals);

                while(list(, $value) = each($order_totals)) {
                    $classname = substr($value, 0, strrpos($value, '.'));

                    if (!class_exists($classname) || ! $GLOBALS[$classname]->enabled) {
                        continue;
                    }

                    if (substr($classname, 0, 5) === "ot_z_")
                    {
                        continue; // after totals should not be included
                    }

                    for($i = 0; $i < sizeof($GLOBALS[$classname]->output); $i ++) {
                        // Handling shipping module differently
                        if ($classname == 'ot_shipping') {
                            $totalValue = $GLOBALS[$classname]->output [$i]['value'];
                            $shippingId = $_SESSION['shipping']['id'];
                            $parts = explode('_', $shippingId);
                            $shippingCode = strtoupper($parts[0]);

                            if (defined('MODULE_SHIPPING_'.$shippingCode.'_TAX_CLASS')) {
                                $taxClass = constant('MODULE_SHIPPING_'.$shippingCode.'_TAX_CLASS');
                                $taxRate = xtc_get_tax_rate($taxClass, $order->delivery['country']['id'], $order->delivery['zone_id']);
                                if($taxRate > 0) {
                                    if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0
                                        && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0) { /* Tax not calculated for customer group */
                                        $totalNetAmount		= $totalValue;
                                        $totalGrossValue 	= $totalValue;
                                    }
                                    else if ($isNetShippingPrice) { /* Shipping prices are excl. tax */
                                        $taxAmount = round(($totalValue / 100 * $taxRate), 2);
                                        $totalNetAmount		= $totalValue;

                                        // We want to be consistent with the shop and send net shipping amount
                                        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0
                                            && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
                                                $totalGrossValue 	= $totalValue;
                                        }
                                        else {
                                            $totalGrossValue 	= $totalValue + $taxAmount;
                                        }

                                        // Increase order total gross amount by tax amount
                                        $orderTotalGross += $taxAmount;
                                    }
                                    else if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0
                                        && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
                                            $taxAmount = round(($totalValue / 100 * $taxRate), 2);

                                            $totalNetAmount		= $totalValue;
                                            $totalGrossValue 	= $totalValue;

                                            // Subtract shipping tax from rebate because we send net shipping amount
                                            $billpayRebateGross -= $taxAmount;
                                    }
                                    else {	/* Shipping prices are incl. tax */
                                        $taxAmount = round($totalValue / (100 + $taxRate) * $taxRate, 2);

                                        $totalNetAmount		= $totalValue - $taxAmount;
                                        $totalGrossValue 	= $totalValue;
                                    }
                                }
                                else {
                                    $totalNetAmount 	= $totalValue;
                                    $totalGrossValue 	= $totalValue;
                                }
                                $billpayShippingNet 	+= $totalNetAmount;
                                $billpayShippingGross	+= $totalGrossValue;
                            }
                        }
                        else {
                            $totalGrossValue = $GLOBALS[$classname]->output [$i]['value'];
                            $codename = strtoupper(str_replace('ot_', '', $classname));

                            $status = false;
                            if(defined('MODULE_ORDER_TOTAL_' . $codename . '_STATUS')) {
                                $status = constant('MODULE_ORDER_TOTAL_' . $codename . '_STATUS');
                            }

                            if($status == 'true') {
                                if (in_array($classname, $this->billpayShippingModules)) {
                                    $tax_amount = 0;
                                    if(defined('MODULE_ORDER_TOTAL_' . $codename . '_TAX_CLASS') && $this->currentCustomerGroupUsesTax()) {
                                        $tax_class = constant('MODULE_ORDER_TOTAL_' . $codename . '_TAX_CLASS');
                                        $tax_rate = xtc_get_tax_rate($tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);

                                        if($tax_rate > 0) {
                                            $tax_amount = round($totalGrossValue / (100 + $tax_rate) * $tax_rate, 2);
                                        }
                                    }

                                    $billpayShippingNet += ($totalGrossValue - $tax_amount);
                                    $billpayShippingGross += $totalGrossValue;
                                }
                                else {
                                    switch ($classname) {
                                        case 'ot_total':
                                            $orderTotalGross += $totalGrossValue;
                                            break;
                                        case 'ot_subtotal':
                                            if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0
                                                && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
                                                    $orderSubTotalGross += $totalGrossValue;
                                            }
                                            else {
                                                $orderSubTotalGross = $_SESSION['cart']->total;
                                            }
                                            break;
                                        case 'ot_tax':
                                            $orderTax += $totalGrossValue;
                                            break;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $billpayRebateGross = -($orderTotalGross - $orderSubTotalGross - $billpayShippingGross);
            $billpayRebateNet = $billpayRebateGross;
            $orderTotalNet = $orderTotalGross - $orderTax;
            $ret = array(
                'billpayRebateNet' => $billpayRebateNet,
                'billpayRebateGross' => $billpayRebateGross,
                'billpayShippingNet' => $billpayShippingNet,
                'billpayShippingGross' => $billpayShippingGross,
                'orderTotalNet' => $orderTotalNet,
                'orderTotalGross' => $orderTotalGross
            );
            return $ret;
        }


        /**
         * Adds user ordering history to the preauth request
         * @param ipl_preauthorize_request $req
         * @return ipl_preauthorize_request mixed
         */
        function _add_order_history($req) {
            $_OrderHistory = $this->_getOrderHistory($this->_getCustomerId());
            foreach ($_OrderHistory as $historyPart) {
                $history_amount = 0;
                if (isset($historyPart['hamount']) && $historyPart['hamount'] >= 0) {
                    $history_amount = $historyPart['hamount'];
                }

                $req->add_order_history($historyPart['hid'],
                    $historyPart['hdate'],
                    $history_amount,
                    isset($historyPart['hcurrency']) ? $historyPart['hcurrency'] : 'EUR',
                    $historyPart['hpaymenttype'],
                    $historyPart['hstatus']
                );
            }
            return $req;
        }

        /**
         * Compares order's billing and delivery addresses. If the delivery address is different, sets it in request.
         * @param ipl_preauthorize_request $req
         * @return mixed
         */
        function _addressCompare($req) {
            $billing = BillpayOrder::getCustomerBilling();
            $delivery= BillpayOrder::getCustomerDelivery();


            $addressCompare = (int) count(array_intersect_assoc($billing, $delivery));
            $billingCount   = (int)count($billing);
            if ($addressCompare < $billingCount ) {
                // if addresses don't match set shipping address
                $this->_set_shipping_details($req);
            }
            else {
                $req->set_shipping_details(TRUE);
            }

            return $req;
        }

        /**
         * Redirects to a page with an error. If ajax, sets SESSION var.
         * @param string $err_msg
         */
        function _error_redirect($err_msg) {
            $err_msg = billpayBase::EnsureString($err_msg);
            $_SESSION['gm_error_message'] = $err_msg;
            $this->_logDebug($err_msg);

            if ($_POST['xajax']) {
                /** ajax one page checkout  */
                $_SESSION['checkout_payment_error'] = 'payment_error=' . $this->code . '&error=' . $err_msg;
            } else {
                xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message='.urlencode($err_msg), 'SSL'));
            }
        }

        /**
         * Checks if B2B form was filled correctly.
         * @param array $data_arr
         * @return bool
         */
        function _validateB2BValues($data_arr) {
            $companyName = $this->_getDataValue('company_name', $data_arr);
            if (!$companyName) {
                $companyName = BillpayOrder::getCustomerCompany();
            }

            $taxNumber =  $this->_getDataValue('tax_number', $data_arr);
            if (!$taxNumber) {
                $taxNumber = $_SESSION['customer_vat_id'];
            }

            $legalForm 		= $this->_getDataValue('legal_form', $data_arr);
            $registerNumber = $this->_getDataValue('register_number', $data_arr);
            $holderName		= $this->_getDataValue('holder_name', $data_arr);
            $genderB2B		= $this->getGender();

            $this->_setDataValue('company_name', $companyName);
            $this->_setDataValue('tax_number', $taxNumber);
            $this->_setDataValue('legal_form', $legalForm);
            $this->_setDataValue('register_number', $registerNumber);
            $this->_setDataValue('holder_name', $holderName);
            $this->_setDataValue('gender', $genderB2B);

            if (!isset($companyName) || $companyName == '') {
                $this->_error_redirect(MODULE_PAYMENT_BILLPAY_B2B_COMPANY_FIELD_EMPTY);
                return false;
            }
            if (!isset($legalForm) || $legalForm == '') {
                $this->_error_redirect(MODULE_PAYMENT_BILLPAY_B2B_LEGAL_FORM_FIELD_EMPTY);
                return false;
            }

            $customerGender = $this->getGender();
            if(!$customerGender) {
                if(!$this->_formGender || $this->_formGender == '') {
                    $this->_error_redirect(MODULE_PAYMENT_BILLPAY_TEXT_ENTER_TITLE);
                    return false;
                }
            }
            return true;
        }

        /**
         * Checks if B2c form was filled correctly.
         * @param array $data_arr
         * @return bool
         */
        function _validateB2CValues($data_arr) {
            if ($this->getDateOfBirth() === null) {
                $this->_error_redirect(MODULE_PAYMENT_BILLPAY_TEXT_ENTER_BIRTHDATE);
                return false;
            }

            if ($this->getGender() === null) {
                $this->_error_redirect(MODULE_PAYMENT_BILLPAY_TEXT_ENTER_GENDER);
                return false;
            }

            if (isset($data_arr['payment']) && $data_arr['payment'] == strtolower($this->_paymentIdentifier)) {
                $this->_checkBankValues($data_arr);
            }

            return true;
        }

        /**
         * Validates gender, b2b/b2c fields and EULA
         * @param array $vars
         */
        function pre_confirmation_check($vars = null) {
            if (empty($vars))
            {
                $vars = $_POST;
            }
            $success = $this->onMethodInput($vars);
            if (!$success) {
                $this->_error_redirect($this->error);
                return;
            }

            if (!$this->getEula()) {
                $this->_error_redirect(MODULE_PAYMENT_BILLPAY_TEXT_ERROR_EULA);
                return;
            }

            if ($this->getDateOfBirth() === null && !$this->isB2B($vars)) {
                $this->_error_redirect(MODULE_PAYMENT_BILLPAY_TEXT_ENTER_BIRTHDATE);
                return;
            }

            if ($this->getGender() === null) {
                $this->_error_redirect(MODULE_PAYMENT_BILLPAY_TEXT_ENTER_GENDER);
                return;
            }

        }

        /**
         * It executes on Checkout Confirmation page.
         * If it returns array, it will show it's contents in "Payment information" box.
         * @return array
         */
        function confirmation() {
            #$confirmation = array(
            #    'title'     =>  'BillPay',
            #    'fields'    =>  array(
            #        array('title' => 'a', 'field' => 'b'),
            #    ),
            #);
            #return $confirmation;
            return false;
        }

        /**
         * Prepares preauth request (with autocapture, one page checkout) and saves it in $_SESSION[billpay_preauth_req]
         * It executes on Checkout Confirmation page, before order confirmation.
         */
        function process_button() {
            $order = $GLOBALS['order'];
            $order_total_modules = $GLOBALS['order_total_modules'];

            // Gambio 2.1 no longer have order_total_modules global
            if (empty($order_total_modules)) {
                $order_total_modules = new stdClass();
                $order_total_modules->modules = explode(';', constant('MODULE_ORDER_TOTAL_INSTALLED'));
            }
            // In Gambio 2.1 PL, shipping already contain VAT
            $orderTotals = $this->_calculate_billpay_totals($order_total_modules, $order, false);
            $this->_setDataValue('order_totals', $orderTotals);

            return;
        }


        /**
         * Executes preauth
         * It executes after confirming an order.
         */
        function before_process() {
            $this->_logError('START beforeProcess');
            $data = array();
            $success = $this->reqPreauthorizeCapture($data);
            if (!$success) {
                $error = billpayBase::EnsureString($this->error);
                $_SESSION['gm_error_message'] = $error; // Gambio specific
                xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message='.urlencode($error), 'SSL'));
            }
        }

        /**
         *
         * It executes after before_process(). If BillPay denied, it won't be executed.
         */
        function after_process() {
            global $insert_id; # newOrderId

            // persist reference for payment information
            $invoiceReference = $this->generateInvoiceReference($insert_id);

            $qry = 'UPDATE billpay_bankdata
                    SET orders_id = ' . $insert_id . ',
                        invoice_reference = "' . $invoiceReference . '"
                    WHERE tx_id= "' . $this->_getTransactionId().'"
                    LIMIT 1';
            xtc_db_query($qry);

            $error = false;

            if (!$this->_getTransactionId()) {
                $error = 'Transaction ID not found in session';
            }

            if ($error) {
                $this->setOrderBillpayState(constant('billpayBase_STATE_ERROR'), $insert_id);
                $this->_logError('Transaction ID not found in session', 'ERROR in after_process');
                xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message='.urlencode(MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DEFAULT), 'SSL'));
                return false; # xtc_redirect exits
            }

            $this->setOrderBillpayState($_SESSION['billpay_onAfterProcess']['orderState'], $insert_id);

            $productIds = $this->_prepareProductMapping($insert_id);
            $this->reqUpdateOrder($this->_getTransactionId(), $insert_id, $productIds);

            unset($_SESSION['billpay_transaction_id']);
            unset($_SESSION['billpay_total_amount']);
            unset($_SESSION['billpay_preselect']);
            unset($_SESSION['bp_rate_result']);
            unset($_SESSION['rr_data']);

            return true;
        }


        function _prepareProductMapping($insert_id)
        {
            // create mapping for id update list
            $productIds = array();
            $query = xtc_db_query("SELECT orders_products_id FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_id='".(int)$insert_id."' ORDER BY orders_products_id ASC");
            $idMapping = array();
            foreach($_SESSION['cart']->contents as $tmpID => $data) {
                if (isset($data['qty'])) {
                    $idMapping[] = array($tmpID, -1);
                }
            }
            $count = 0;
            while ($res = xtc_db_fetch_array($query)) {
                $targetId = $res['orders_products_id'];
                $idMapping[$count][1] = $targetId;
                ++$count;
            }
            foreach ($idMapping as $entry) {
                $productIds[$entry[0]] = $entry[1];
            }
            return $productIds;
        }


        /**
         * Returns array with last 10 customer's orders
         * @param int $_customerId
         * @return array
         */
        function _getOrderHistory($_customerId)
        {
            $_return = array();

            if ($_customerId === null) {
                return array();
            }
            $qry = 'SELECT o.`orders_id`, o.`date_purchased`, o.`payment_method`, o.`orders_status`, o.`currency`, ot.`value`
                    FROM ' . TABLE_ORDERS . ' o
                    JOIN ' . TABLE_ORDERS_TOTAL . ' ot ON (o.orders_id = ot.orders_id)
                    WHERE ot.sort_order = 99
                      AND o.`customers_id` = ' . (int)$_customerId . '
                    ORDER BY date_purchased DESC
                    LIMIT 10';
            $_queryOrder = xtc_db_query($qry);
            while (($_resultOrder = xtc_db_fetch_array($_queryOrder)) !== false) {
                $_return[] = array(
                    'hid'          => utf8_encode($_resultOrder['orders_id']),
                    'hdate'        => utf8_encode($this->_formatDate('Ymd H:i:s', $_resultOrder['date_purchased'])),
                    'hamount'      => $this->CurrencyToSmallerUnit($_resultOrder['value']),
                    'hcurrency'    => utf8_encode($_resultOrder['currency']),
                    'hpaymenttype' => utf8_encode($this->_getPaymentMethod($_resultOrder['payment_method'])),
                    'hstatus'      => 0,
                );
            }
            return $_return;
        }


        /**
         * Invokes and returns selected ot_ module (order_total)
         * @param string $classname
         * @return mixed
         */
        function _createTotals($classname) {
            global $xtPrice;
            $ot = new $classname($xtPrice);
            return $ot;
        }


        /**
         * Saves log message to the log file (/includes/external/billpay/logs)
         * @param string $logMessage
         * @param string $logType
         * @return bool
         */
        function _logError($logMessage, $logType = 'default') {
            $_write = FALSE;
            if (is_array($logMessage)) {
                $logMessage = print_r($logMessage, true);
            }
            if ((!empty($this->_logPath)) ) {
                $_data  = '------------------< '. strtoupper($logType) . ' ('.date('r').')' . ' >------------------';
                $_data .= "\n\n" . $logMessage;
                $_data .= "\n\n";

                if ((function_exists('version_compare')) && (version_compare(PHP_VERSION, '5.0.0', '>='))) {
                    $_write = file_put_contents($this->_logPath, $_data, FILE_APPEND);
                    $_write = ($_write !== FALSE ? TRUE : FALSE);
                }
                else { // PHP4 workaround
                    $handle = fopen($this->_logPath, 'a');

                    if (fwrite($handle, $_data) != FALSE) {
                        $_write = TRUE;
                    }

                    fclose($handle);
                }
            }
            return $_write;
        }

        /**
         * Logs debug information. It may be filtered from billpay.log.
         * @param string $message
         */
        function _logDebug($message)
        {
            $this->_logError($message, 'debug');
        }

        /**
         * Checks if selected OT (order total) module is installed
         * @param $moduleName
         * @return bool
         */
        function isModuleInstalled($moduleName) {
            if(defined('MODULE_ORDER_TOTAL_INSTALLED')) {
                $totalModules = explode(';', MODULE_ORDER_TOTAL_INSTALLED);

                foreach ($totalModules as $installedModule) {
                    $splitted = explode('.', $installedModule);
                    if (trim($splitted[0]) == $moduleName) {
                        return TRUE;
                    }
                }

                return in_array(strtolower(trim($moduleName)), $totalModules);
            }
            else {
                return FALSE;
            }
        }


        /**
         * installs the payment method
         */
        function install()
        {
            $this->_logDebug('Starting payment method installation: '.$this->_paymentIdentifier);
            $state = 'install';
            // make sure we get a clean state
            $this->remove($state);

            // fetch next sort order
            switch ($this->_paymentIdentifier) {
                case constant('billpayBase_PAYMENT_METHOD_INVOICE'):
                    $sortOrder = 3;
                    break;
                case constant('billpayBase_PAYMENT_METHOD_DEBIT'):
                    $sortOrder = 4;
                    break;
                case constant('billpayBase_PAYMENT_METHOD_TRANSACTION_CREDIT');
                    $sortOrder = 5;
                    break;
                case constant('billpayBase_PAYMENT_METHOD_PAY_LATER'):
                    $sortOrder = 6;
                    break;
                default:
                    $sortOrder = 7;
                    break;
            }

            $language = $_SESSION['language'];
            $langFile = DIR_FS_LANGUAGES . $language . '/modules/payment/' . strtolower($this->_paymentIdentifier) . '.php';
            if (!file_exists($langFile)) {
                $langFile = DIR_FS_LANGUAGES . 'german/modules/payment/' . strtolower($this->_paymentIdentifier) . '.php';
            }
            $this->_logDebug('Including lang file: '.$langFile);
            require_once $langFile;

            // install new configuration
            $results = array();
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_STATUS";
            $results[$configuration_key] = xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('".$configuration_key."', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_LOGGING";
            $results[$configuration_key] = xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('".$configuration_key."', '', '6', '0', now())");
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_LOGGING_ENABLE";
            $results[$configuration_key] = xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('".$configuration_key."', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_ID";
            $results[$configuration_key] = xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('".$configuration_key."', 'ShopID', '6', '0', now())");
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_SHIPPING_TAX";
            $results[$configuration_key] = xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('".$configuration_key."', '',  '6', '0', now())");
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_SORT_ORDER";
            $results[$configuration_key] = xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('".$configuration_key."', '".$sortOrder."', '6', '0', now())");
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_ALLOWED";
            $results[$configuration_key] = xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('".$configuration_key."', 'DE',   '6', '0', now())");
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_ORDER_STATUS";
            $results[$configuration_key] = xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('".$configuration_key."', '0', '6', '0', 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(', now())");
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_TABLE";
            $results[$configuration_key] = xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('".$configuration_key."', 'payment_billpay', '6', '0', now())");
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_MIN_AMOUNT";
            $results[$configuration_key] = xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('".$configuration_key."', '".$this->_getDefaultInstallConfig('MIN_AMOUNT')."', '6', '0', now())");
            $this->_logDebug("Setting local configuration keys:\n".print_r($results, true));

            //check if UTF8 setting is set globally = GS
            $configuration_key = 'MODULE_PAYMENT_BILLPAY_GS_UTF8_ENCODE';
            $check_status = xtc_db_query('SELECT count(*) AS number FROM ' . TABLE_CONFIGURATION . ' where configuration_key LIKE "'.$configuration_key.'"');
            $rs_check_status = xtc_db_fetch_array($check_status);
            if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
                $this->_logDebug("Setting global key: $configuration_key");
                xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('".$configuration_key."', 'local', '6',  '0',\"xtc_cfg_select_option(array('local', 'UTF-8'), \", now())");
            }

            //check if login data is already set globally = GS
            $configuration_key = "MODULE_PAYMENT_BILLPAY_GS_MERCHANT_ID";
            $check_status = xtc_db_query('SELECT count(*) AS number FROM ' . TABLE_CONFIGURATION . ' where configuration_key LIKE "'.$configuration_key.'"');
            $rs_check_status = xtc_db_fetch_array($check_status);
            if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
                $this->_logDebug("Setting global key: $configuration_key");
                xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('".$configuration_key."', '0', '6', '0', now())");
            }

            $configuration_key = "MODULE_PAYMENT_BILLPAY_GS_PORTAL_ID";
            $check_status = xtc_db_query('SELECT count(*) AS number FROM ' . TABLE_CONFIGURATION . ' where configuration_key LIKE "'.$configuration_key.'"');
            $rs_check_status = xtc_db_fetch_array($check_status);
            if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
                $this->_logDebug("Setting global key: $configuration_key");
                xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('".$configuration_key."', '0', '6', '0', now())");
            }

            $configuration_key = "MODULE_PAYMENT_BILLPAY_GS_SECURE";
            $check_status = xtc_db_query('SELECT count(*) AS number FROM ' . TABLE_CONFIGURATION . ' where configuration_key LIKE "'.$configuration_key.'"');
            $rs_check_status = xtc_db_fetch_array($check_status);
            if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
                $this->_logDebug("Setting global key: $configuration_key");
                xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('".$configuration_key."', '0', '6', '0', now())");
            }

            $configuration_key = "MODULE_PAYMENT_BILLPAY_GS_PUBLIC_API_KEY";
            $check_status = xtc_db_query('SELECT count(*) AS number FROM ' . TABLE_CONFIGURATION . ' where configuration_key LIKE "'.$configuration_key.'"');
            $rs_check_status = xtc_db_fetch_array($check_status);
            if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
                $this->_logDebug("Setting global key: $configuration_key");
                xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('".$configuration_key."', '0', '6', '0', now())");
            }

            //check if TEST API / API URl is already set
            $configuration_key = "MODULE_PAYMENT_BILLPAY_GS_TESTAPI_URL_BASE";
            $check_status = xtc_db_query('SELECT count(*) AS number FROM ' . TABLE_CONFIGURATION . ' where configuration_key LIKE "'.$configuration_key.'"');
            $rs_check_status = xtc_db_fetch_array($check_status);
            if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
                $this->_logDebug("Setting global key: $configuration_key");
                xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('".$configuration_key."', 'https://test-api.billpay.de/xml/offline', '6', '0', now())");
            }

            $configuration_key = "MODULE_PAYMENT_BILLPAY_GS_API_URL_BASE";
            $check_status = xtc_db_query('SELECT count(*) AS number FROM ' . TABLE_CONFIGURATION . ' where configuration_key LIKE "'.$configuration_key.'"');
            $rs_check_status = xtc_db_fetch_array($check_status);
            if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
                $this->_logDebug("Setting global key: $configuration_key");
                xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('".$configuration_key."', 'https://api.billpay.de/xml', '6', '0', now())");
            }

            //check if mode is already set
            $configuration_key = "MODULE_PAYMENT_BILLPAY_GS_TESTMODE";
            $check_status = xtc_db_query('SELECT count(*) AS number FROM ' . TABLE_CONFIGURATION . ' where configuration_key LIKE "'.$configuration_key.'"');
            $rs_check_status = xtc_db_fetch_array($check_status);
            if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
                # You don't translate configuration values!
                $this->_logDebug("Setting global key: $configuration_key");
                xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('".$configuration_key."', 'Testmodus', '6', '0', 'xtc_cfg_select_option(array(\'Testmodus\', \'Livemodus\'), ', now())");
            }

            //check if HTTP_X_FORWARDED FOR is already installed
            $configuration_key = "MODULE_PAYMENT_BILLPAY_GS_HTTP_X";
            $check_status = xtc_db_query('SELECT count(*) AS number FROM ' . TABLE_CONFIGURATION . ' where configuration_key LIKE "'.$configuration_key.'"');
            $rs_check_status = xtc_db_fetch_array($check_status);
            if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
                $this->_logDebug("Setting global key: $configuration_key");
                xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('".$configuration_key."', 'False', '6', '0', 'xtc_cfg_select_option(array(\'False\', \'True\'), ', now())");
            }

            $this->_logDebug("Executing payment specific installation code.");
            $this->onInstall();

            // checking if all BillPay statuses exists
            $this->_logDebug("Checking BillPay order states\n".print_r($this->billpayStates, true));
            foreach ($this->billpayStates as $stateId) {
                $configuration_key = "MODULE_PAYMENT_BILLPAY_STATUS_".$stateId;
                $configuration_value = BillpayDB::DBFetchValue("SELECT configuration_value FROM ".TABLE_CONFIGURATION." WHERE configuration_key = '".$configuration_key."' LIMIT 1");
                if (!empty($configuration_value)) {
                    continue;
                }

                // creating non existing order status
                $this->_logDebug("Creating state #$stateId");
                $nextId = BillpayDB::DBFetchValue('SELECT max(orders_status_id) + 1 AS nextId FROM ' . TABLE_ORDERS_STATUS);
                $textEn = 'BillPay '.$stateId;
                $textDe = 'BillPay '.$stateId;
                if (defined($configuration_key.'_TITLE_EN')) {
                    $textEn = constant($configuration_key.'_TITLE_EN');
                }
                if (defined($configuration_key.'_TITLE_DE')) {
                    $textDe = constant($configuration_key.'_TITLE_DE');
                }
                $this->_logDebug("New state: $nextId, \nen:$textEn \nde:$textDe");
                xtc_db_query('INSERT INTO ' . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) VALUES ('" . $nextId . "', '1', '" . $textEn. "')");
                xtc_db_query('INSERT INTO ' . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) VALUES ('" . $nextId . "', '2', '" . $textDe . "')");
                xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('".$configuration_key."', '" . $nextId . "', '6', '0', now())");
            }

            // billpay_bankdata table
            $this->_logDebug("Checking BankData");
            $check_query = xtc_db_query("SHOW TABLES LIKE 'billpay_bankdata'");
            if (xtc_db_num_rows($check_query) == 0) {
                // create new table if it does not exist yet
                $this->_logDebug("New table billpay_bankdata");
                xtc_db_query(
                    "CREATE TABLE IF NOT EXISTS `billpay_bankdata` (
                        `api_reference_id` varchar(64) NOT NULL,
                        `account_holder` varchar(100) NOT NULL,
                        `account_number` varchar(50) NOT NULL,
                        `bank_code` varchar(50) NOT NULL,
                        `bank_name` varchar(100) NOT NULL,
                        `invoice_reference` varchar(250) NOT NULL,
                        `invoice_due_date` varchar(9) default NULL,
                        `tx_id` varchar(64) NOT NULL,
                        `orders_id` int(11) unsigned default NULL,
                        `rate_surcharge` decimal(12,4) DEFAULT NULL,
                        `rate_total_amount` decimal(12,4) DEFAULT NULL,
                        `rate_count` int(10) unsigned DEFAULT NULL,
                        `rate_dues` text,
                        `rate_interest_rate` decimal(12,4) DEFAULT NULL,
                        `rate_anual_rate` decimal(12,4) DEFAULT NULL,
                        `rate_base_amount` decimal(12,4) DEFAULT NULL,
                        `rate_fee` decimal(12,4) DEFAULT NULL,
                        `rate_fee_tax` decimal(12,4) DEFAULT NULL,
                        `prepayment_amount` decimal(12,4) DEFAULT NULL,
                        `customer_cache` text,

                        `instalment_count` int(10) unsigned DEFAULT NULL,
                        `duration` int(10) unsigned DEFAULT NULL,
                        `fee_percent` decimal(12,4) DEFAULT NULL,
                        `fee_total` decimal(12,4) DEFAULT NULL,
                        `pre_payment` decimal(12,4) DEFAULT NULL,
                        `total_amount` decimal(12,4) DEFAULT NULL,
                        `effective_annual` decimal(12,4) DEFAULT NULL,
                        `nominal_annual` decimal(12,4) DEFAULT NULL
                    )"
                );
            } else {
                // Example data 20110305#8415:20110405#6211:20110505#6211:20110605#6211:20110705#6211:20110805#6211
                // Date is empty before activation: #8415:#6211:#6211:#6211:#6211:#6211
                // if table exists already, check if tc columns exist and add them if necessary
                $this->_logDebug("Extending billpay_bankdata");
                $columns = array(
                    "rate_surcharge"     => "decimal(12,4) DEFAULT NULL",
                    "rate_total_amount"  => "decimal(12,4) DEFAULT NULL",
                    "rate_count"         => "int(10) unsigned DEFAULT NULL",
                    "rate_dues"          => "text",
                    "rate_interest_rate" => "decimal(12,4) DEFAULT NULL",
                    "rate_anual_rate"    => "decimal(12,4) DEFAULT NULL",
                    "rate_base_amount"   => "decimal(12,4) DEFAULT NULL",
                    "rate_fee"           => "decimal(12,4) DEFAULT NULL",
                    "rate_fee_tax"       => "decimal(12,4) DEFAULT NULL",
                    "prepayment_amount"  => "decimal(12,4) DEFAULT NULL",
                    "customer_cache"     => "text",

                    // PayLater specific
                    "instalment_count"   => "int(10) unsigned DEFAULT NULL",
                    "duration"           => "int(10) unsigned DEFAULT NULL",
                    "fee_percent"        => "decimal(12,4) DEFAULT NULL",
                    "fee_total"          => "decimal(12,4) DEFAULT NULL",
                    "pre_payment"        => "decimal(12,4) DEFAULT NULL",
                    "total_amount"       => "decimal(12,4) DEFAULT NULL",
                    "effective_annual"   => "decimal(12,4) DEFAULT NULL",
                    "nominal_annual"     => "decimal(12,4) DEFAULT NULL",
                );
                foreach ($columns as $columnName => $columnType) {
                    $check_query = xtc_db_query(
                        'SELECT *
                         FROM information_schema.COLUMNS
                         WHERE TABLE_SCHEMA = "' . DB_DATABASE . '"
                           AND TABLE_NAME = "billpay_bankdata"
                           AND COLUMN_NAME = "' . $columnName . '"'
                    );
                    if (xtc_db_num_rows($check_query) == 0) {
                        // create tc columns if they do not exist yet
                        xtc_db_query(
                            'ALTER TABLE `billpay_bankdata`
                               ADD `' . $columnName . '` ' . $columnType
                        );
                    }
                }
            }

            $this->_logDebug('Installation successful.');
        }

        /**
         * Removes configuration, except when $state == 'install'
         * @param string $state
         */
        function remove($state = NULL) {
            $this->_logDebug('Removing payment method config.');
            xtc_db_query('DELETE FROM ' . TABLE_CONFIGURATION . ' '.
                            'WHERE configuration_key LIKE "MODULE_PAYMENT_'.$this->_paymentIdentifier.'\_%" '.
                            'AND configuration_key NOT LIKE "MODULE_PAYMENT_BILLPAY_GS_%"'.
                            'AND configuration_key NOT LIKE "MODULE_PAYMENT_BILLPAY_STATUS_%" ');
            // complete removal (GS, statuses) is disabled
        }

        /**
         * returns all configuration constants of the payment module
         *
         * @return array
         */
        function keys()
        {
            // configuration options will be displayed
            // in the here defined order at "admin/payment methods"
            $config_array = array(
                // config per payment method
                'MODULE_PAYMENT_' . $this->_paymentIdentifier . '_STATUS',
                'MODULE_PAYMENT_' . $this->_paymentIdentifier . '_LOGGING_ENABLE',
                'MODULE_PAYMENT_' . $this->_paymentIdentifier . '_LOGGING',
                'MODULE_PAYMENT_' . $this->_paymentIdentifier . '_ORDER_STATUS',
                'MODULE_PAYMENT_' . $this->_paymentIdentifier . '_ALLOWED',
                'MODULE_PAYMENT_' . $this->_paymentIdentifier . '_SORT_ORDER',
                'MODULE_PAYMENT_' . $this->_paymentIdentifier . '_MIN_AMOUNT',

                // global config for BillPay
                'MODULE_PAYMENT_BILLPAY_GS_TESTMODE',
                'MODULE_PAYMENT_BILLPAY_GS_UTF8_ENCODE',
                'MODULE_PAYMENT_BILLPAY_GS_MERCHANT_ID',
                'MODULE_PAYMENT_BILLPAY_GS_PORTAL_ID',
                'MODULE_PAYMENT_BILLPAY_GS_SECURE',
                'MODULE_PAYMENT_BILLPAY_GS_PUBLIC_API_KEY',
                'MODULE_PAYMENT_BILLPAY_GS_API_URL_BASE',
                'MODULE_PAYMENT_BILLPAY_GS_TESTAPI_URL_BASE',
                'MODULE_PAYMENT_BILLPAY_GS_HTTP_X',
            );

            $config_array = $this->onKeys($config_array);

            return $config_array;
        }


        function getModuleConfig() {
            $country = strtoupper($this->_getCountry(3));
            $currency = strtoupper($this->_getCurrency());
            $language = strtoupper($this->_getLanguage());

            if (isset($_SESSION['billpay_module_config'][$country][$currency])) {
                $config = $_SESSION['billpay_module_config'][$country][$currency];
                if ($config == false) {
                    $this->_logError('Fetching module config failed previously. BillPay payment not available.');
                }
                return $config;
            }

            $this->_logError($this->api_url, 'module config check api url for '.$this->_paymentIdentifier);

            $data = array(
                'country'   =>  $country,
                'currency'  =>  $currency,
                'language'  =>  $language,
            );
            $config = $this->reqModuleConfig($data);
            if (!empty($config)) {
                $_SESSION['billpay_module_config'][$country][$currency] = $config;
            }
            return $config;
        }

        /**
         * Sends preauthorize request and returns the data
         * @return bool
         */
        function reqPreauthorizeCapture()
        {
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/ipl_xml_api.php');
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/php4/ipl_preauthorize_request.php');
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/Bankdata.php');

            $req = new ipl_preauthorize_request($this->api_url, $this->_getPaymentType());
            $req->set_default_params($this->bp_merchant, $this->bp_portal, $this->bp_secure);
            $req = $this->_setTrace($req);
            $group = 'p';
            $req = $this->_set_customer_details($req, $group);
            $req = $this->_add_articles($req);
            $req = $this->_addressCompare($req);

            $req = $this->_add_order_totals($req);

            /* set fraud detection parameters */
            $req->set_fraud_detection($this->getCustomerIdentifier());

            $req->set_terms_accepted(true);
            $req->set_capture_request_necessary(false);

            // fetch the order history for customers only (not guests)
            if ($this->_getCustomerGroup() != 'g') {
                // fetch & add history
                $req = $this->_add_order_history($req);
            }

            $this->token = ipl_create_random();
            $shopDomain = $this->_getShopDomain();
            $this->_logDebug($shopDomain);
            if (strpos($shopDomain, "localhost") !== false) {
                $this->_logError("Shop working on localhost, cannot receive callbacks: ".$shopDomain);
                $shopDomain = "http://billpay.de/";
                //$shopDomain = 'http://10bddc06.ngrok.com/';
            }
            $billpay_notify_url = $shopDomain . "callback/billpay/billpayWS.php?token=".$this->token;
            $billpay_redirect_url = $shopDomain ."callback/billpay/billpayRedirectUrl.php";
            $req->set_async_capture($billpay_redirect_url,$billpay_notify_url);
            $req = $this->onMethodOutput($req);

            $internalError = $req->send();
            $this->_logError($req->get_request_xml(), 'XML request preauthorize');
            $this->_logError($req->get_response_xml(), 'XML response preauthorize');
            if ($internalError) {
                $this->error = $internalError['error_message'];
                $this->_logError($this->error, 'internal error preauthorize');
                return false;
            }

            if ($req->get_status() == 'DENIED') {
                $_SESSION['billpay_hide_payment_method'] = true;
                // will return false, because has_error == true
            }

            if ($req->has_error()) {
                $this->error = $req->get_customer_error_message();
                $this->_logError($req->get_merchant_error_message(), 'Error during preauthorize');
                return false;
            }

            $this->_setTransactionId(utf8_decode((string)$req->get_bptid()));

            Billpay_Base_Bankdata::SaveRequest($req);
            $this->onPreauthResponse($req);
            $_SESSION['billpay_onAfterProcess'] = array(
                'orderState'           =>  constant('billpayBase_STATE_APPROVED'),
                'campaignText'         =>  '',
                'externalRedirect'     =>  '',
                'campaignImg'          =>  '',
            );
            if ($req->get_status() == 'PRE_APPROVED') {
                $_SESSION['billpay_onAfterProcess']['orderState'] = constant('billpayBase_STATE_PENDING');
                $_SESSION['billpay_onAfterProcess']['externalRedirect'] = $req->get_external_redirect_url();
                $_SESSION['billpay_onAfterProcess']['campaignText'] = $req->get_campaign_display_text();
                $_SESSION['billpay_onAfterProcess']['campaignImg'] = $req->get_campaign_display_image_url();
                $_SESSION['billpay_onAfterProcess']['rateLink'] = $req->get_rate_plan_url();
                $this->form_action_url = 'checkout_billpay_giropay.php';
                $this->tmpOrders = true;
                $this->tmpStatus = $this->getOrderStatusFromBillpayState(constant('billpayBase_STATE_PENDING'));
            }
            unset($_SESSION['billpay_data_arr']);
            unset($_SESSION['billpay_fee_cost']);
            unset($_SESSION['billpay_fee_tax']);
            unset($_SESSION['billpay_preauth_req']);
            return true;
        }

        /**
         * Sends moduleConfig request and returns the data.
         * @param array $data
         * @return array|bool|mixed
         */
        function reqModuleConfig($data)
        {
            /**
             * $data = array(
             *      'country'   =>  'deu',
             *      'currency'  =>  'EUR',
             *      'language'  =>  'de',
             * )
             */
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/ipl_xml_api.php');
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/php4/ipl_module_config_request.php');

            $req = new ipl_module_config_request($this->api_url);
            $req->set_default_params($this->bp_merchant, $this->bp_portal, $this->bp_secure);
            $req->set_locale($data['country'], $data['currency'], $data['language']);

            $internalError = $req->send();
            $this->_logError($req->get_request_xml(), 'XML request ModuleConfig');
            $this->_logError($req->get_response_xml(), 'XML response ModuleConfig');
            if ($internalError) {
                $this->_logError($internalError['error_message'], 'internal error module config');
                return false;
            }
            if ($req->has_error()) {
                $this->_logError($req->get_merchant_error_message(), 'Error fetching module config');
                return false;
            }
            $config = array();
            $config = $this->_getPaymentStatus($req, $config);
            return $config;
        }

        /**
         * Sends invoiceCreated request for selected order.
         * @param int $orderId
         * @return bool
         */
        function reqInvoiceCreated($orderId)
        {
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/ipl_xml_api.php');
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/php4/ipl_invoice_created_request.php');
            $this->_logDebug("Activating order");
            $this->requireLang();
            $req = new ipl_invoice_created_request($this->api_url);
            $req->set_default_params($this->bp_merchant, $this->bp_portal, $this->bp_secure);

            $total = BillpayDB::DBFetchValue('SELECT value FROM '. TABLE_ORDERS_TOTAL .' WHERE class = "ot_total" AND orders_id = '.(int)$orderId);
            $total = $this->CurrencyToSmallerUnit($total);
            $currency = BillpayDB::DBFetchValue('SELECT currency FROM '.TABLE_ORDERS.' WHERE orders_id = '.(int)$orderId);
            $req->set_invoice_params($total, $currency, $orderId);
            $internalError = $req->send();
            $_xmlReq 	= (string)utf8_decode($req->get_request_xml());
            $_xmlResp 	= (string)utf8_decode($req->get_response_xml());
            $this->_logError($_xmlReq, 'XML request (invoiceCreated)');
            $this->_logError($_xmlResp, 'XML response (invoiceCreated)');
            if ($internalError) {
                $this->error = $internalError['error_message'];
                $this->_logError($this->error, 'Internal error occurred (invoiceCreated)');
                return false;
            }
            if ($req->has_error()) {
                $this->error = $req->get_customer_error_message();
                $this->_logError($req->get_merchant_error_message(), 'Merchant error occurred (invoiceCreated)');
                return false;
            }
            $dueDate = $req->get_invoice_duedate();
            if (empty($dueDate)) {
                $this->error = 'Invoice Due Date is empty.';
                $this->_logError($this->error, 'Invoice error occurred (invoiceCreated)');
                return false;
            }
            xtc_db_query('UPDATE billpay_bankdata SET invoice_due_date = "'.$dueDate.'" '.'WHERE orders_id = '.(int)$orderId);
            $newStatus = $this->getOrderStatusFromBillpayState(constant('billpayBase_STATE_COMPLETED'));
            $this->addHistoryEntry($orderId, constant('MODULE_PAYMENT_'.strtoupper($this->_paymentIdentifier).'_TEXT_INVOICE_CREATED_COMMENT'), $newStatus);
            $this->onAfterInvoiceCreated($req, $orderId);
            return true;
        }

        /**
         * Sends updateOrder request
         * @param $transactionId
         * @param $orderId
         * @param $productIds
         * @return bool
         */
        function reqUpdateOrder($transactionId, $orderId, $productIds)
        {
            # we update Billpay DB with orderId
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/ipl_xml_api.php');
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/php4/ipl_update_order_request.php');

            $req = new ipl_update_order_request($this->api_url);
            $req->set_default_params($this->bp_merchant, $this->bp_portal, $this->bp_secure);
            $req->set_update_params($transactionId, $orderId);

            foreach ($productIds as $key => $val) {
                $req->add_id_update($key, $val);
            }

            $internalError = $req->send();
            if ($internalError) {
                $this->_logError($internalError['error_message'], 'WARNING: Error sending update order request. Must use tx_id as api reference');
                return false;
            }

            $this->_logError($req->get_request_xml(), 'update order request XML');
            $this->_logError($req->get_response_xml(), 'update order response XML');

            if ($req->has_error()) {
                $this->_logError($req->get_merchant_error_message(), 'WARNING: Error sending update order request. Must use tx_id as api reference');
                return false;
            }
            xtc_db_query("UPDATE billpay_bankdata SET api_reference_id='" . $orderId . "' WHERE tx_id='".$transactionId."'");
            return true;
        }

        /**
         * Cancels accepted or completed order.
         * @param $orderId
         * @return bool
         */
        function reqCancel($orderId)
        {
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/ipl_xml_api.php');
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/php4/ipl_cancel_request.php');
            $this->requireLang();
            $this->_logDebug("Cancelling order.");
            $req = new ipl_cancel_request($this->api_url);
            $req->set_default_params($this->bp_merchant, $this->bp_portal, $this->bp_secure);
            $orderCurrency = BillpayDB::DBFetchValue("SELECT currency FROM ".TABLE_ORDERS." WHERE orders_id = '".(int)$orderId."'");
            $orderTotal = BillpayDB::DBFetchValue("SELECT value FROM ".TABLE_ORDERS_TOTAL." WHERE orders_id = '".(int)$orderId."' AND class='ot_total'");
            $orderTotal = billpayBase::CurrencyToSmallerUnit($orderTotal);
            $req->set_cancel_params($orderId, $orderTotal, $orderCurrency);

            $internalError = $req->send();
            if ($internalError) {
                $this->error = $internalError['error_message'];
                $this->_logError($this->error, 'WARNING: Error sending cancel order request.');
                return false;
            }

            $this->_logError($req->get_request_xml(), 'cancel request XML');
            $this->_logError($req->get_response_xml(), 'cancel response XML');

            if ($req->has_error()) {
                $this->error = $req->get_customer_error_message();
                $this->_logError($req->get_merchant_error_message(), 'WARNING: Error sending cancel order request.');
                return false;
            }
            $newStatus = $this->getOrderStatusFromBillpayState(constant('billpayBase_STATE_CANCELLED'));
            $this->addHistoryEntry($orderId, constant('MODULE_PAYMENT_BILLPAY_TEXT_CANCEL_COMMENT'), $newStatus);
            return true;
        }


        /**
         * Sends current order contents to Billpay
         * @param int $orderId
         * @return boolean
         */
        function reqEditCartContent($orderId)
        {
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/ipl_xml_api.php');
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/php4/ipl_edit_cart_content_request.php');

            $req = new ipl_edit_cart_content_request($this->api_url);
            $req->set_default_params($this->bp_merchant, $this->bp_portal, $this->bp_secure);

            $order_products = billpayBase::GetOrderProducts($orderId);
            $subtotal = 0;
            foreach ($order_products as $product) {
                $price = billpayBase::CurrencyToSmallerUnit($product['price']);
                $subtotal += $price * $product['qty'];
                if ($product['qty'] < 1) {
                    continue;
                }
                $req->add_article(
                    $product['opid'], $product['qty'],
                    $product['name'], '',
                    $price,
                    $price
                );
            }

            $rebate = BillpayDB::DBFetchValue("SELECT value FROM ".TABLE_ORDERS_TOTAL." WHERE orders_id = '".(int)$orderId."'  AND class = 'ot_discount'");
            $rebate = billpayBase::CurrencyToSmallerUnit($rebate) * -1;
            $total = BillpayDB::DBFetchValue("SELECT value FROM ".TABLE_ORDERS_TOTAL." WHERE orders_id = '".(int)$orderId."'  AND class = 'ot_total'");
            $total = billpayBase::CurrencyToSmallerUnit($total);
            $shipping = $total - $subtotal + $rebate;
            $order = BillpayDB::DBFetchRow("SELECT shipping_method, currency FROM ".TABLE_ORDERS." WHERE orders_id = '".(int)$orderId."'");
            $shipping_method = $order['shipping_method'];
            $currency = $order['currency'];
            $req->set_total($rebate, $rebate, $shipping_method, $shipping, $shipping, $total, $total, $currency, $orderId);

            $internalError = $req->send();
            if ($internalError) {
                $this->error = $internalError['error_message'];
                $this->_logError($this->error, 'WARNING: Error sending editCartContent request.');
                return false;
            }

            $this->_logError($req->get_request_xml(), 'editCartContent request XML');
            $this->_logError($req->get_response_xml(), 'editCartContent response XML');

            if ($req->has_error()) {
                $this->error = $req->get_customer_error_message();
                $this->_logError($req->get_merchant_error_message(), 'WARNING: Error sending editCartContent request.');
                return false;
            }

            $this->onOrderChanged($orderId, $req);

            return true;
        }

        /**
         * Changes selected order's status using Billpay states.
         * @param int       $billpayStateId (self::STATE_*)
         * @param int       $orderId
         * @param string    $message        (optional) Comment for order status change
         * @return bool
         */
        function setOrderBillpayState($billpayStateId, $orderId, $message = '')
        {
            $this->requireLang();
            $messages = array(
                constant('billpayBase_STATE_PENDING')     =>  constant('MODULE_PAYMENT_BILLPAY_STATUS_PENDING_DESC'),
                constant('billpayBase_STATE_APPROVED')    =>  constant('MODULE_PAYMENT_BILLPAY_STATUS_APPROVED_DESC'),
                constant('billpayBase_STATE_COMPLETED')   =>  constant('MODULE_PAYMENT_BILLPAY_STATUS_ACTIVATED_DESC'),
                constant('billpayBase_STATE_CANCELLED')   =>  constant('MODULE_PAYMENT_BILLPAY_STATUS_CANCELLED_DESC'),
                constant('billpayBase_STATE_ERROR')       =>  constant('MODULE_PAYMENT_BILLPAY_STATUS_ERROR_DESC')
            );
            $orderStatusId = $this->getOrderStatusFromBillpayState($billpayStateId);
            if (empty($message)) {
                $message = $messages[$billpayStateId];
            }
            $this->setOrderStatus($orderStatusId, $orderId, $message);
        }

        /**
         * Changes selected order's status using shop statuses
         * @param $orderStatusId
         * @param $orderId
         * @param $message
         * @return bool
         */
        function setOrderStatus($orderStatusId, $orderId, $message)
        {
            $qry = 'UPDATE ' . TABLE_ORDERS . '
                        SET orders_status = '.(int)$orderStatusId.'
                        WHERE orders_id = ' . (int)$orderId . '
                        LIMIT 1';
            xtc_db_query($qry);
            $this->addHistoryEntry($orderId, $message, $orderStatusId);
            return true;
        }


        ##### Abstracts
        /**
         * Returns maximum value of payment; BillPay won't handle transactions higher that it from this merchant.
         * @abstract
         * @param $config
         * @return int
         */
        function _getStaticLimit($config) {
            return 0;
        }

        /**
         * Returns minimum value of payment; BillPay won't handle transactions lower than it from this merchant.
         * @abstract
         * @param $config
         * @return int
         */
        function _getMinValue($config) {
            return 0;
        }

        /**
         * Checks if current method allows customers to use it.
         * @abstract
         * @param $config
         * @return bool
         */
        function _is_b2c_allowed($config) {
            return true;
        }

        /**
         * Checks if current method allows businesses to use it.
         * @abstract
         * @param $config
         * @return bool
         */
        function _is_b2b_allowed($config) {
            return false;
        }

        /**
         * Add customers bank data to the preautho request. only for direct debit
         * @abstract
         */
        function _addBankData($req, $vars) {
            return $req;
        }

        /**
         * Add tc-specific details to preauth request
         * @abstract
         */
        function _addPreauthTcDetails($req, $numberRates, $ratePlanTotal) {
            return $req;
        }


        /**
         * Display input fields for customers bank data. Only for direct debit
         * @abstract
         * @return string
         */
        function _displaySepaBankData() {
            return '';
        }

        /**
         * Type of the payment, defined in IPL_CORE_PAYMENT_TYPE_*
         * @abstract
         * @return int
         */
        function _getPaymentType()
        {
            return 0;
        }

        /**
         * Event function that allows you to modify data before standard validation
         * @abstract
         * @param array $data
         * @return array
         */
        function beforeValidate($data)
        {
            return $data;
        }

        /**
         * Event executed during payment method installation.
         * @abstract
         */
        function onInstall()
        {

        }

        /**
         * Event executed while checking for plugin configuration keys.
         * @param $config_array
         * @return array
         * @abstract
         */
        function onKeys($config_array)
        {
            return $config_array;
        }


        /**
         * step for temporary order
         * @return void
         */
        function payment_action()
        {
            $orderId = $_SESSION['tmp_oID'];

            // persist reference for payment information
            $invoiceReference = $this->generateInvoiceReference($orderId);

            $qry = 'UPDATE billpay_bankdata
                    SET orders_id = ' . $orderId . ',
                        invoice_reference = "' . $invoiceReference . '"
                    WHERE tx_id= "' . $this->_getTransactionId().'"
                    LIMIT 1';
            xtc_db_query($qry);

            $productIds = $this->_prepareProductMapping($orderId);
            $this->reqUpdateOrder($this->_getTransactionId(), $orderId, $productIds);
            xtc_redirect(xtc_href_link($this->form_action_url, '', 'SSL'));
        }

        /**
         * check if bank data values are not empty. only for direct debit and transaction credit
         * @param array $vars
         *
         * @return void
         */
        function _checkBankValues($vars=array()) {}


        /**
         * Process payment method input data (form), before validation
         * @param array $data
         * @return bool
         * @abstract
         */
        function onMethodInput($data)
        {
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
            return $req;
        }

        /**
         * Event fired after creating invoice.
         * @param $req
         * @param int $orderId
         * @abstract
         */
        function onAfterInvoiceCreated($req, $orderId) {

        }

        /**
         * Fired before saving edited order in admin/order_edit
         * @param $orderId
         * @abstract
         */
        function onSaveEditOrderBefore($orderId)
        {

        }

        /**
         * Event fired after receiving preauthorize response
         * @param ipl_preauthorize_request $req
         */
        function onPreauthResponse($req) {

        }

        /**
         * Event fired when admin deletes order in backend.
         * @param $orderId
         * @return bool
         */
        function onOrderDelete($orderId)
        {
            return $this->reqCancel($orderId);
        }


        /**
         * Gambio specific error messages
         * @abstract
         * @param $error
         *
         */
        function _displayGMerror($error) {}

        ##### GETTERS / SETTERS

        /**
         * Function returns Order Status of selected Billpay State
         * @param $billpayStateId
         * @return int
         */
        function getOrderStatusFromBillpayState($billpayStateId)
        {
            // support for ORDER_STATUS setting from old plugin versions
            if ($billpayStateId === constant('billpayBase_STATE_APPROVED')) {
                $approvedStatus = BillpayDB::DBFetchValue("SELECT configuration_value FROM ".TABLE_CONFIGURATION." WHERE configuration_key = 'MODULE_PAYMENT_".$this->_paymentIdentifier."_ORDER_STATUS' LIMIT 1");
                if ($approvedStatus) {
                    return $approvedStatus;
                }
            }
            $configuration_key = "MODULE_PAYMENT_BILLPAY_STATUS_".$billpayStateId;
            $configuration_value = BillpayDB::DBFetchValue("SELECT configuration_value FROM ".TABLE_CONFIGURATION." WHERE configuration_key = '".$configuration_key."' LIMIT 1");
            if (!empty($configuration_value)) {
                return $configuration_value;
            }
            $this->_logError('BillPay state '.$billpayStateId.' not found, plugin was not installed?');

            // Fallback
            if ($billpayStateId === constant('billpayBase_STATE_APPROVED')) {
                $this->_logError("ORDER_STATE not set, fallback to 1 (pending)");
                return 1;
            }

            return 0;
        }

        /**
         * Gets default install config value.
         *
         * @param $key
         * @return string
         */
        function _getDefaultInstallConfig($key)
        {
            if (empty($this->_defaultConfig[$key])) return '';
            return $this->_defaultConfig[$key];
        }

        /**
         * Returns type of connection to BillPay API
         * @return false|'Testmodus'
         */
        function getMode() {
            return $this->testmode;
        }

        /**
         * Returns shop's URL i.e. "https://example.shopdomain.com/"
         * @return string
         */
        function _getShopDomain() {
            return (ENABLE_SSL ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG;
        }


        function currentCustomerGroupUsesTax() {
            return $_SESSION['customers_status']['customers_status_show_price_tax'] == 1
            || $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1;
        }


        /**
         * Getter for requestTransactionId
         * @return string
         */
        function _getTransactionId() {
            return $_SESSION['billpay_transaction_id'];
        }

        /**
         * Setter for requestTransactionId
         * @param string $transId
         */
        function _setTransactionId($transId) {
            $_SESSION['billpay_transaction_id'] = $transId;
        }

        /**
         * Returns lower case code of order's billing country
         * @param int $len
         * @return string
         */
        function _getCountry($len) {
            $billing = BillpayOrder::getCustomerBilling();
            if ($len == 3) {
                return $billing['country3'];
            }
            if ($len == 2) {
                return $billing['country2'];
            }
            return 'DEU';
        }

        /**
         * Returns current order's currency. Or current session.
         * @return string
         */
        function _getCurrency() {
            return BillpayOrder::getCurrency();
        }

        /**
         * reads some payment configuration from a module config request object and writes them into the given
         * config array
         *
         * @param ipl_module_config_request $req
         * @param array $config
         *
         * @return mixed
         */
        function _getPaymentStatus($req, $config = array())
        {
            if ($req->is_invoice_allowed() == true) {
                $config['static_limit_invoice'] = $req->get_static_limit_invoice();
            }

            if ($req->is_invoicebusiness_allowed() == true) {
                $config['static_limit_invoicebusiness'] = $req->get_static_limit_invoicebusiness();
            }

            if ($req->is_direct_debit_allowed() == true) {
                $config['static_limit_directdebit'] = $req->get_static_limit_direct_debit();
            }
            if ($req->is_hire_purchase_allowed() == true) {
                $config['static_limit_transactioncredit'] = $req->get_static_limit_hire_purchase();
                $config['min_value_transactioncredit']    = $req->get_hire_purchase_min_value();
                $config['terms']                          = $req->get_terms();
            }
            if (defined('MODULE_PAYMENT_'. billpayBase_PAYMENT_METHOD_PAY_LATER . '_STATUS')
                && constant('MODULE_PAYMENT_' . billpayBase_PAYMENT_METHOD_PAY_LATER . '_STATUS'))
            {
                $config['static_limit_paylater'] = 10000000000;
            }

            return $config;
        }


        function _getLanguage() {
            if (empty($_SESSION['language_code'])) {
                return 'de';
            }
            return $_SESSION['language_code'];
        }

        function getTermsOfServiceText() {
            return MODULE_PAYMENT_BILLPAY_TEXT_EULA_CHECK;
        }

        /**
         * Returns unique ID of the client using hash, server url and session_id
         * @return string
         */
        function getCustomerIdentifier() {
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/ipl_xml_api.php');
            return ipl_create_hash(session_id());
        }

        /**
         * @return int
         */
        function _getPaymentBlockWidth() {
            return 500;
        }

        /**
         * @return int
         */
        function _getPaymentBlockHeight() {
            return 148;
        }




        /**
         * Identifies payment method of a historic order.
         * @param array $_paymentMethod     Array containing historic order data
         * @return int
         */
        function _getPaymentMethod($_paymentMethod = NULL) {
            /*
             0: Lastschrift					|		0: Bezahlt
             1: Kreditkarte					|		1: Offen
             2: Vorkasse	    			|		2: Mahnwesen
             3: Nachnahme					|		3: Inkasso
             4: Paypal						|		4: Ueberbezahlt
             5: Sofortueberweisung/Giropay	|		5: Unterbezahlt
             6: Rechnung		    		|		6: Geplatzt
             7: Billpay (Rechnung)
             100: Other
             */
            switch($_paymentMethod) {
                case 'moneybookers_elv':
                case 'micropayment_debit':
                    return 0;
                    break;
                case 'cc':
                case 'moneybookers_cc':
                case 'micropayment_cc':
                case 'worldpay':
                    return 1;
                    break;
                case 'banktransfer':
                case 'eustandardtransfer':
                    return 2;
                    break;
                case 'cod':
                    return 3;
                    break;
                case 'paypal':
                case 'paypalexpress':
                    return 4;
                    break;
                case 'pn_sofortueberweisung':
                case 'moneybookers_sft':
                case 'moneybookers_giropay':
                case 'giropay':
                    return 5;
                    break;
                case 'invoice':
                    return 6;
                    break;
                case 'billpay':
                    return 7;
                    break;
            }
            return 100; # other
        }

        /**
         * Returns net or gross price in cents
         *
         * @param float $valuePrice     the base price value
         * @param int   $valueTax       the tax amount as integer
         * @param bool  $calculateTax   convert price from net to gross or from gross to net
         * @param bool  $isGrossPrice   true if the supplied price includes tax (gross price)
         *
         * @return int
         */
        function _getPrice($valuePrice, $valueTax, $calculateTax = true, $isGrossPrice = true)
        {
            if ($valuePrice === null) {
                return 0;
            }
            if ($valueTax === null || !$calculateTax) {
                return $this->CurrencyToSmallerUnit($valuePrice);
            }
            if ($isGrossPrice) {
                $taxAmount = (float)($valuePrice * $valueTax / (100 + $valueTax));
            } else {
                $taxAmount = (float)($valuePrice * $valueTax / 100);
            }
            $taxUnits = (int)$this->CurrencyToSmallerUnit($taxAmount);
            $priceNetUnits = (int)$this->CurrencyToSmallerUnit($valuePrice);

            if ($isGrossPrice == true) {
                return $priceNetUnits - $taxUnits;    // gross price. convert to net price
            } else {
                return $priceNetUnits + $taxUnits;    // net price. convert to gross price
            }
        }


        /**
         * Returns customer IP
         * @return string
         */
        function _getCustomerIp()
        {
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $forwardedForArray = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                return trim(array_shift($forwardedForArray));
            }
            if (!empty($_SESSION['tracking']['ip'])) {
                return $_SESSION['tracking']['ip'];
            }
            if (!empty($_SERVER['REMOTE_ADDR'])) {
                return $_SERVER['REMOTE_ADDR'];
            }
            return '';
        }

        /**
         * Returns customer's salutation
         * @param null|string $customerGender
         * @return string|null
         */
        function _getCustomerSalutation($customerGender = null)
        {
            if (!$customerGender) {
                $customerGender = $this->getGender();
            }
            if (!$customerGender) {
                return null;
            }
            switch ($customerGender) {
                case 'm':
                    return constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_SALUTATION_MALE');
                    break;
                case 'f':
                    return constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_SALUTATION_FEMALE');
                    break;
            }
            return null;
        }

        /**
         * Returns customer id stored in session.
         * @return int|null
         */
        function _getCustomerId()
        {
            if (empty($_SESSION['customer_id']) === false) {
                return (int)$_SESSION['customer_id'];
            }
            return null;
        }

        /**
         * Returns current customer's group to populate preauth request
         * @return string
         */
        function _getCustomerGroup()
        {
            if (isset($_SESSION['customers_status']['customers_status_id'])) {
                // default values
                // 0 = admin, 1 = guest, 2 = new customer, 3 = merchant
                switch ($_SESSION['customers_status']['customers_status_id']) {
                    case '0':
                    case '3':
                        return 'e';
                        break;
                    case '2':
                        return 'n';
                        break;
                    case '1':
                    default:
                        return 'g';
                        break;
                }
            }
            return 'n';
        }

        /**
         * Reformats supplied date to another format
         * @param string $dateFormat
         * @param string $dateString
         *
         * @return null|string
         */
        function _formatDate($dateFormat, $dateString)
        {
            $_checkStamp = strtotime($dateString);
            if ($_checkStamp !== false && $_checkStamp != -1) {
                return date($dateFormat, $_checkStamp);
            }
            return null;
        }

        /**
         * Returns HTML <select> for picking day
         * @return string
         */
        function _getSelectDobDay() {
            return $this->_genSelectDob('day', 1, 31, 'asc');
        }

        /**
         * Returns HTML <select> for picking month
         * @return string
         */
        function _getSelectDobMonth() {
            return $this->_genSelectDob('month', 1, 12, 'asc');
        }

        /**
         * Returns HTML <select> for picking year between _getMinYear and _getMaxYear
         * @return string
         */
        function _getSelectDobYear() {
            return $this->_genSelectDob('year', $this->_getMinYear(), $this->_getMaxYear(), 'asc');
        }

        /**
         * Returns HTML <select> for picking gender
         * @param string $clientType
         * @return string
         */
        function _genSelectGender($clientType = 'b2c') {
            $varSelectedMale = $varSelectedFemale = '';
            $constTextMale = MODULE_PAYMENT_BILLPAY_TEXT_MR;
            $constTextFemale = MODULE_PAYMENT_BILLPAY_TEXT_MRS;
            $gender = $this->_getDataValue('gender');
            $identGender = $this->_getDataIdentifier('gender');
            switch ($gender) {
                case 'm':
                    $varSelectedMale = 'selected';
                    break;
                case 'f':
                    $varSelectedFemale = 'selected';
                    break;
            }
            $genderSelectHTML = <<<HEREDOC
            <select name="$identGender">
                <option value="">---</option>
                <option value="m" $varSelectedMale>$constTextMale</option>
                <option value="f" $varSelectedFemale>$constTextFemale</option>
            </select>
            <span class="inputRequirement">&nbsp;*&nbsp;</span>
HEREDOC;
            return $genderSelectHTML;
        }


        /**
         * Returns HTML <select> for specified data
         * @param $genName
         * @param $genFrom
         * @param $genTo
         * @param $sortDirection
         * @return string
         */
        function _genSelectDob($genName, $genFrom, $genTo, $sortDirection) {
            $identifier = $this->_getDataIdentifier('dob_'.strtolower($genName));
            $dobSelectHTML = '<select name="'.$identifier.'" style="width:60px">';

            $value = $this->_getDataValue('dob_'.$genName);
            if(isset($value) && $value > 0) {
                $dobSelectHTML .= '<option value="'.$value.'">'.$value.'</option>';
            }
            $dobSelectHTML .= '<option value="00">---</option>';

            if ($sortDirection == 'desc') {
                for ($i = $genTo; $i >= $genFrom;) {
                    $iMod = sprintf('%02d', (int)$i);
                    $dobSelectHTML .= '<option value="' . $iMod . '">&nbsp;&nbsp;' . $iMod . '&nbsp;&nbsp;</option>';
                    $i--;
                }
            }
            else {
                for ($i = $genFrom; $i <= $genTo;) {
                    $iMod = sprintf('%02d', (int)$i);
                    $dobSelectHTML .= '<option value="' . $iMod . '">&nbsp;&nbsp;' . $iMod . '&nbsp;&nbsp;</option>';
                    $i++;
                }
            }

            $dobSelectHTML .= '</select>';

            return $dobSelectHTML;
        }

        function _getMinYear() {
            return (int)date('Y') - 100;
        }

        function _getMaxYear() {
            return (int)date('Y') - 15;
        }

        /**
         * Returns formatted error message
         * @param string $_code
         * @param string $_msgMerchant
         * @param string $_msgCustomer
         * @return string
         */
        function _errorMessage($_code, $_msgMerchant, $_msgCustomer) {
            $_errorTpl  =	'Code: ' 			. "\t\t" . '%s' . "\n";
            $_errorTpl .=	'Merchant MSG: ' 	. "\t\t" . '%s' . "\n";
            $_errorTpl .=	'Customer MSG: ' 	. "\t\t" . '%s'	. "\n";

            $_errorMsg = sprintf(
                $_errorTpl,
                (string)utf8_decode($_code),
                (string)utf8_decode($_msgMerchant),
                (string)utf8_decode($_msgCustomer)
            );

            return $_errorMsg;
        }

        /**
         * Returns formatted invoice reference
         * @param $orderID
         * @return string
         */
        function generateInvoiceReference($orderID) {
            return 'BP' . $orderID . '/' . $this->bp_merchant;
        }




        /**
         * Trims and (if option is set) utf8-encodes
         * @param string $value
         * @return string
         * @deprecated
         */
        function _encodeValue($value) {
            return billpayBase::EnsureUTF8($value);
        }


        /**
         * Recoveres variable from data or session
         * @param string $key Variable name
         * @param array|null $data
         * @return null | string
         */
        function _getDataValue($key, $data = null) {
            if (is_null($data)) {
                $data =& $_SESSION;
            }

            $prefixedKey = $this->_getDataIdentifier($key);
            if (array_key_exists($prefixedKey, $data)) {
                return $data[$prefixedKey];
            }

            if (array_key_exists($key, $data)) {
                return $data[$key];
            }
            return null;
        }

        /**
         * Sets variable in session
         * @param string $key
         * @param mixed $value
         */
        function _setDataValue($key, $value) {
            $dataIdentifier = $this->_getDataIdentifier($key);
            $_SESSION[$dataIdentifier] = $value;
        }

        /**
         * Gets identifier for current payment method i.e "field" in "payment" is "payment_field"
         * @param string $key
         * @param bool $upper
         * @return string
         */
        function _getDataIdentifier($key = '', $upper = false) {
            if ($key == '') {
                $dataIdentifier = $this->_paymentIdentifier;
            }
            else {
                $dataIdentifier = $this->_paymentIdentifier.'_'.$key;
            }

            return $upper ? strtoupper($dataIdentifier) : strtolower($dataIdentifier);
        }




        /**
         * Returns JS used to validate fields.
         * @return string
         */
        function javascript_validation() {
            // check values
            $js = '   if (payment_value == "' . $this->code . '") {' . "\n" .
                '   if (document.getElementById("checkout_payment").elements["'.strtolower($this->_paymentIdentifier).'[dob][day]"].value == "00") {' . "\n" .
                '   error_message = error_message + unescape("' . JS_BILLPAY_DOBDAY . '");' . "\n" .
                '   error = 1;'."\n".'    }' . "\n" .
                '   if (document.getElementById("checkout_payment").elements["'.strtolower($this->_paymentIdentifier).'[dob][month]"].value == "00") {' . "\n" .
                '   error_message = error_message + unescape("' . JS_BILLPAY_DOBMONTH . '");' . "\n" .
                '   error = 1;'."\n".'    }' . "\n" .
                '   if (document.getElementById("checkout_payment").elements["'.strtolower($this->_paymentIdentifier).'[dob][year]"].value == "00") {' . "\n" .
                '   error_message = error_message + unescape("' . JS_BILLPAY_DOBYEAR . '");' . "\n" .
                '   error = 1;'."\n".'    }' . "\n" .
                '   if (document.getElementById("checkout_payment").elements["'.strtolower($this->_paymentIdentifier).'_gender"].value == "") {' . "\n" .
                '   error_message = error_message + unescape("' . JS_BILLPAY_GENDER . '");' . "\n" .
                '   error = 1;'."\n".'    }' . "\n";

            $js .= 	 '	if (!document.getElementById("checkout_payment").'.strtolower($this->_paymentIdentifier).'_eula.checked) {' . "\n" .
                '	error_message = error_message + unescape("' . JS_BILLPAY_EULA . '");' . "\n" .
                '	error = 1;' . "\n" .
                '	}'  . "\n" .
                '}' . "\n";
            return $js;
        }

        /**
         * Returns JS defined in payment method
         * @return string
         */
        function _displayPaymentJs() {
            $ret = '
            <script type="text/javascript">
            function show_billpay_details(method) {
                var elem = document.getElementById(method);
                if (elem) {
                    elem.style.display = "block";
                    if (elem.dataset.bpyLoad) {
                        eval(elem.dataset.bpyLoad);
                    }
                }
            }
            </script>
            ';
            return $ret;
        }

        /**
         * Returns <script> tag for billpay/templates/js/billpay.js
         * Executes only once
         * @return string
         */
        function _injectJavascript()
        {
            // CLARIFICATION:
            //     There are two ways xtc3 will call selection()
            //     1. (if more than 1 PM are available) Once per every payment method - we need to return our JS once.
            //     2. (if only 1 PM is available)       Twice a single payment method - we need to return our JS both times.
            //
            if (defined('billpayBase_injectJavascript') && billpayBase_injectJavascript != $this->_paymentIdentifier)
            {
                return '';
            }
            define('billpayBase_injectJavascript', $this->_paymentIdentifier);
            $billpayScript = <<<HEREDOC
<script type="text/javascript">
var loadPayLater = function() {
    (function(d, script) {
        script = d.createElement('script');
        script.type = 'text/javascript';
        script.async = true;
        script.src = '//paylatercdn.billpay.de/js/require.min.js';
        script.setAttribute('data-main', '//paylatercdn.billpay.de/js/release/1.x.x.js');
        //script.setAttribute('data-main', '//paylatercdn.billpay.de/js/release/latest.js');
        d.getElementsByTagName('head')[0].appendChild(script);
    }(document));
};
loadPayLater();
</script>
HEREDOC;
            return $billpayScript;
        }

        /**
         * Returns <link type="text/css"> tag for billpay/templates/css/billpay.css
         * Executes only once
         * @return string
         */
        function _injectCss()
        {
            if (defined('billpayBase_injectCss') && billpayBase_injectJavascript != $this->_paymentIdentifier)
            {
                return '';
            }
            define('billpayBase_injectCss', $this->_paymentIdentifier);
            return '<link type="text/css" rel="stylesheet" href="' . $this->_getShopDomain() . 'includes/external/billpay/templates/css/billpay.css"/>';
        }

        /**
         * Returns HTML with B2B fields
         * @return string
         */
        function _addB2BInputFields() {
            $companyName 	= $this->_getDataValue('company_name') ? $this->_getDataValue('company_name') : BillpayOrder::getCustomerCompany();
            $legalForm		= $this->_getDataValue('legal_form');
            $registerNumber	= $this->_getDataValue('register_number');
            $taxNumber		= $this->_getDataValue('tax_number') ? $this->_getDataValue('tax_number') : $_SESSION['customer_vat_id'];
            $holderName		= $this->_getDataValue('holder_name');
            $billing = BillpayOrder::getCustomerBilling();
            $contactPerson	= $billing['firstName'] . ' ' . $billing['lastName'];

            $salutation		= $this->_getCustomerSalutation($this->_getDataValue('gender'));
            if ($salutation) {
                $contactPerson = $salutation . ' ' . $contactPerson;
            }

            $incOptionList = '';
            $legalFormList = explode("|", MODULE_PAYMENT_BILLPAY_B2B_LEGALFORM_VALUES);
            foreach ($legalFormList as $l) {
                $parts = explode(":", $l);
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                $incOptionList .= '<option value="'.$key.'" '.($key == $legalForm ? 'selected': '').'>'.$value.'</option>';
            }
            $incPhoneField = '';
            if ($this->isPhoneRequired()) {
                $_customerPhone = $this->getPermanentPhone();
                if (empty($_customerPhone)) {
                    $incPhoneField = '<tr><td>'.MODULE_PAYMENT_BILLPAY_TEXT_PHONE.'</td><td><input type="text" name="'.$this->_getDataIdentifier('phone').'" value="'.$this->getPhone().'" /><span class="inputRequirement">&nbsp;*&nbsp;</span></td></tr>';
                }
            }
            $constCompanyNameText = constant('MODULE_PAYMENT_BILLPAY_B2B_COMPANY_NAME_TEXT');
            $constCompanyLegalFormText = constant('MODULE_PAYMENT_BILLPAY_B2B_COMPANY_LEGAL_FORM_TEXT');
            $constRegisterNumber = constant('MODULE_PAYMENT_BILLPAY_B2B_REGISTER_NUMBER_TEXT');
            $constTaxNumber = constant('MODULE_PAYMENT_BILLPAY_B2B_TAX_NUMBER_TEXT');
            $constHolderName = constant('MODULE_PAYMENT_BILLPAY_B2B_HOLDER_NAME_TEXT');
            $constContactPerson = constant('MODULE_PAYMENT_BILLPAY_B2B_CONTACT_PERSON_TEXT');
            $identCompanyName = $this->_getDataIdentifier('company_name');
            $identLegalForm   = $this->_getDataIdentifier('legal_form');
            $identRegisterNumber = $this->_getDataIdentifier('register_number');
            $identTaxNumber      = $this->_getDataIdentifier('tax_number');
            $identHolderName     = $this->_getDataIdentifier('holder_name');
            $incGenderField = $this->_add_gender_input_field('b2b');
            $b2bdata = <<<HEREDOC
                <table style="margin-bottom:15px">
                    <tr>
                        <td style="width: 180px">$constCompanyNameText</td>
                        <td><input type="text" id="$identCompanyName" name="$identCompanyName" value="$companyName" style="width:170px" /><span class="inputRequirement">&nbsp;*&nbsp;</span></td>
                    </tr>
                    <tr>
                        <td>$constCompanyLegalFormText</td>
                        <td>
                            <select id="$identLegalForm" name="$identLegalForm" style="width:250px">
                                <option value="">---</option>
                                $incOptionList
                            </select>
                            <span class="inputRequirement">&nbsp;*&nbsp;</span>
                        </td>
                    </tr>
                    $incGenderField
                    $incPhoneField
                    <tr><td>$constRegisterNumber</td><td><input type="text" id="$identRegisterNumber" name="$identRegisterNumber" value="$registerNumber" style="width:110px" /></td></tr>
                    <tr><td>$constTaxNumber</td><td><input type="text" id="$identTaxNumber" name="$identTaxNumber" value="$taxNumber" style="width:110px" /></td></tr>
                    <tr><td>$constHolderName</td><td><input type="text" id="$identHolderName" name="$identHolderName" value="$holderName" style="width:170px" /></td></tr>
                    <tr><td colspan="2" style="padding-top:10px; font-style:italic">$constContactPerson:&nbsp;$contactPerson</td></tr>
                </table>
HEREDOC;
            return $b2bdata;
        }

        /**
         * Returns HTML with B2C fields
         * @return string
         */
        function _addB2CInputFields() {
            $_customerDob = $this->getDateOfBirth();
            $_customerGender = $this->getGender();


            $guiVisible = false;
            if (empty($_customerGender)) {
                $guiVisible = true;
            }

            $genderSelectHTML = $this->_add_gender_input_field('b2c');

            if ($this->isDobValid($_customerDob)) {
                $birthdaySelectHTML = '<input type="hidden" maxlength="10" size="10" name="'.$this->_getDataIdentifier('dob').'" value="'.$_customerDob.'"/>';
            } else {
                $birthdaySelectHTML = '<tr><td>' . MODULE_PAYMENT_BILLPAY_TEXT_BIRTHDATE .'</td><td>'.$this->_getSelectDobDay() .'&nbsp;'.$this->_getSelectDobMonth() .'&nbsp;'.$this->_getSelectDobYear() . '<span class="inputRequirement">&nbsp;*&nbsp;</span></td>';
                $guiVisible = true;
            }

            $phoneHTML = '';
            if ($this->isPhoneRequired()) {
                $_customerPhone = $this->getPermanentPhone();
                if (empty($_customerPhone)) {
                    $phoneHTML = '<tr><td style="width: 180px">'.MODULE_PAYMENT_BILLPAY_TEXT_PHONE.'</td><td><input type="text" name="'.$this->_getDataIdentifier('phone').'" value="'.$this->getPhone().'" /></td></tr>';
                }
            }

            $margin = $guiVisible ? '10' : '0';
            return '<table style="margin-bottom:'.$margin.'px">'.$genderSelectHTML.$birthdaySelectHTML.$phoneHTML.'</table>';
        }

        /**
         * Returns HTML used to choose B2B or B2C during checkout
         * @return string
         */
        function _addB2BSelection() {
            $config = $this->getModuleConfig();
            if (!$this->_is_b2b_allowed($config)) {
                return '';
            }
            $company = BillpayOrder::getCustomerCompany();
            if($_SESSION['billpay_preselect'] == 'b2c') {
                $b2b_checked = '';
                $b2c_checked = 'selected';
            } else if(!empty($_SESSION['customer_vat_id']) ||
                $_SESSION['customers_status']['customers_status_id'] == 3 ||
                $_SESSION['billpay_preselect'] == 'b2b'	|| !empty($company)) {
                $b2b_checked = 'selected';
                $b2c_checked = '';
            }
            else {
                $b2b_checked = '';
                $b2c_checked = 'selected';
            }

            $titleChoose   = MODULE_PAYMENT_BILLPAY_B2B_CHOOSE_CLIENT_TEXT;
            $titlePrivate  = MODULE_PAYMENT_BILLPAY_B2B_PRIVATE_CLIENT_TEXT;
            $titleBusiness = MODULE_PAYMENT_BILLPAY_B2B_BUSINESS_CLIENT_TEXT;

            $html = <<<HEREDOC
            <script type="application/javascript">
            var bpyChangeInvoiceCustomerType = function(el) {
                var value = el.value;
                if (value == "0") {
                    document.getElementById('b2c').style.display='block';
                    document.getElementById('b2b').style.display='none';
                }
                if (value == "1") {
                    document.getElementById('b2c').style.display='none';
                    document.getElementById('b2b').style.display='block';
                }
            }
            </script>
            <table>
                <tr>
                    <td style="width: 180px">
                        <label for="bpyInvoiceCustomerType">$titleChoose:</label>
                    </td>
                    <td>
                        <select id="bpyInvoiceCustomerType" name="b2bflag" onChange="bpyChangeInvoiceCustomerType(this)">
                            <option value="0" $b2c_checked>$titlePrivate</option>
                            <option value="1" $b2b_checked>$titleBusiness</option>
                        </select>
                    </td>
                </tr>
            </table>
HEREDOC;
            return $html;
        }

        /**
         * Returns HTML with gender field
         * @param string $customerMode Either 'b2c' or 'b2b'
         * @return string
         */
        function _add_gender_input_field($customerMode = 'b2c') {
            $_customerGender = $this->getGender();
            if ($_customerGender) {
                return '<tr><td colspan="2"><input type="hidden" maxlength="10" size="10" name="'.$this->_getDataIdentifier('gender').'" value="'.$_customerGender.'" /></td><td>';
            }
            return '<tr><td>'.MODULE_PAYMENT_BILLPAY_TEXT_SALUTATION .'</td><td>' . $this->_genSelectGender($customerMode) . '</td></tr>';
        }

        /**
         * Appends text to the selection "object"
         * @param array $selection
         * @param string $input
         * @return array
         */
        function _extendSeoLayout($selection, $input) {
            $selection['fields'][] = array('title' => $input);
            return $selection;
        }

        /**
         * Appends EULA checkbox to the selection "object"
         * @param array $selection
         * @param string $eulaText
         * @param string $onClickAction
         * @return array
         */
        function _extendSeoEula($selection, $eulaText, $onClickAction='') {
            $idName = $this->_getDataIdentifier('eula');
            $selection['fields'][] = array(
                'title' => '<label for="'.$idName.'" class="bpy-terms-box-text"><input id="'.$idName.'" type="checkbox" name="'.$idName.'" '.$onClickAction.' style="display:block;float:left;">' . $eulaText.'</label>');
            return $selection;
        }

        /**
         * Returns SEPA and EULA text.
         * @return string
         */
        function getSepaText()
        {
            return $this->_getSepaEulaText() . $this->getSepaAdditionalInformation();
        }

        /**
         * Returns EULA text.
         * @return string
         */
        function _getEulaText()
        {
            $eulaText = constant('MODULE_PAYMENT_' . $this->_paymentIdentifier . '_TEXT_EULA_CHECK');
            $eulaText = sprintf($eulaText, $this->_buildTermsOfServiceUrl());
            return $this->_buildEulaHTML($eulaText);
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

            return $this->_buildEulaHTML($eulaText);
        }

        function getSepaAdditionalInformation()
        {
            $informationText = $this->_getSepaAdditionalInformationText();

            if (strlen($informationText) == 0) {
                return '';
            }

            return $this->_buildSepaAdditionalInformationHtml($informationText);
        }

        function _getSepaAdditionalInformationText()
        {
            $infoTextIdentifier = 'MODULE_PAYMENT_' . $this->_paymentIdentifier . '_TEXT_SEPA_INFORMATION';
            $infoTextIdentifier = $this->_getCountrySpecificIdentifier($infoTextIdentifier);

            if (defined($infoTextIdentifier) === false) {
                return '';
            }
            $infoText = constant($infoTextIdentifier);

            return $infoText;
        }

        function _buildSepaAdditionalInformationHtml($informationText)
        {
            $smarty = $GLOBALS['smarty'];
            if (empty($smarty)) {
                $smarty = new Smarty;
                $smarty->caching = 0;
            }
            $smarty->assign('sepa_info_text', $informationText);

            return $smarty->fetch('../includes/external/billpay/templates/additional_sepa_information.tpl');
        }

        function _buildEulaHTML($eulaText)
        {
            return $eulaText;
        }

        function _getCountrySpecificIdentifier($baseIdentifier)
        {
            $countryIso2Code = $this->_getCountryIso2Code();

            if ($countryIso2Code != 'DE'
                && defined($baseIdentifier . '_' . $countryIso2Code)
            ) {
                return $baseIdentifier . '_' . $countryIso2Code;
            } else {
                return $baseIdentifier;
            }
        }

        /**
         * Returns billing country code for current order.
         * @return string i.e. "DE"
         */
        function _getCountryIso2Code()
        {
            $billing = BillpayOrder::getCustomerBilling();
            return strtoupper($billing['country2']);
        }

        /**
         * Returns Payment Terms URL based on order's billing country
         * @return string
         */
        function _buildTermsOfServiceUrl() {
            $country = strtolower($this->_getCountryIso2Code());
            $termsUrl = 'https://www.billpay.'.$country.'/kunden/agb';
            return $termsUrl;
        }

        /**
         * Displays HTML form for bank data.
         * @return string
         */
        function displayBankData()
        {
            return $this->_displaySepaBankData();
        }


        /**
         * Sets customer date of birth in form and database.
         * @param string $dob
         */
        function setDateOfBirth($dob)
        {
            if (preg_match('/(^|-)00-?/', $dob)) {
                return;
            }
            if ($dob === null) {
                return;
            }
            $dobTimestamp = strtotime($dob);
            if ($dobTimestamp !== false && $dobTimestamp !== -1) {
                $this->_formDob = $dobTimestamp;
                $customerId = $this->_getCustomerId();
                if ($customerId) {
                    // not updating customer's db
                    // xtc_db_query("UPDATE ".TABLE_CUSTOMERS." SET customers_dob ='".date('Y-m-d', $dobTimestamp)."' WHERE customers_id = '".(int)$customerId."'");
                }
            }
        }

        /**
         * Returns customer date of birth from form or from database.
         * @return int|null Timestamp of date of birth
         */
        function getDateOfBirth()
        {
            if (!empty($this->_formDob)) {
                return $this->_formDob;
            }
            if (!empty($_SESSION['customer_id'])) {
                $dob = BillpayDB::DBFetchValue("SELECT customers_dob FROM ".TABLE_CUSTOMERS." WHERE customers_id = '".(int)$_SESSION['customer_id']."'");
                if ($dob === "0000-00-00 00:00:00")
                {
                    return null;
                }
                $dobTimestamp = strtotime($dob);
                if ($dobTimestamp !== false && $dobTimestamp !== -1) {
                    $this->_formDob = $dobTimestamp;
                    return $dobTimestamp;
                }
            }
            return null;
        }

        /**
         * Checks if $vars (usually $_POST) contains B2B flag.
         * @param $vars
         * @return bool
         */
        function isB2B($vars)
        {
            return $vars['b2bflag'] == 1;
        }

        /**
         * Checks if customer's dob is valid for ordering.
         * Does not have to be precise. We use it to check if we should allow customer to change it.
         * @param   int     $dobTimestamp
         * @return  bool
         */
        function isDobValid($dobTimestamp)
        {
            if (empty($dobTimestamp)) {
                return false;
            }
            if ($dobTimestamp > strtotime('18 years ago')) {
                return false;
            }
            return true;
        }

        /**
         * Sets customer gender in form and database
         * @param string $gender either 'm' or 'f'
         */
        function setGender($gender)
        {
            if (!empty($gender)) {
                $this->_formGender = $gender;
                $customerId = $this->_getCustomerId();
                if ($customerId) {
                    xtc_db_query("UPDATE ".TABLE_CUSTOMERS." SET customers_gender ='".substr($gender, 0, 1)."' WHERE customers_id = '".(int)$customerId."'");
                }
            }
        }

        /**
         * Returns customer gender from form or database.
         * @return string|null either 'm' of 'f'
         */
        function getGender()
        {
            if (!empty($this->_formGender)) {
                return $this->_formGender;
            }
            if (!empty($_SESSION['customer_gender'])) {
                return $_SESSION['customer_gender'];
            }
            $customerId = $this->_getCustomerId();
            if (!empty($customerId)) {
                $gender = BillpayDB::DBFetchValue("SELECT customers_gender FROM ".TABLE_CUSTOMERS." WHERE customers_id = '".(int)$customerId."'");
                return $gender;
            }
            return null;
        }

        /**
         * Sets EULA confirmation status
         * @param $isChecked
         */
        function setEula($isChecked)
        {
            $isChecked = (bool)$isChecked;
            $this->_setDataValue('eula', $isChecked);
        }

        /**
         * Reads eula confirmation status
         * @return bool
         */
        function getEula()
        {
            return $this->_getDataValue('eula') ? true : false;
        }

        /**
         * Sets customer's phone number.
         * @param string $phone
         */
        function setPhone($phone)
        {
            if (strlen($phone) > 5)
            {
                $this->_setDataValue('phone', $phone);
            }
        }

        /**
         * Returns customer's phone number
         * @return null|string
         */
        function getPhone()
        {
            $phone = $this->_getDataValue('phone');
            if (empty($phone)) {
                $phone = $this->getPermanentPhone();
            }
            return $phone;
        }

        /**
         * Returns only phone saved in database.
         * @return null
         */
        function getPermanentPhone()
        {
            $phone = null;
            $customerId = $this->_getCustomerId();
            if (empty($customerId) === false) {
                $qry = 'SELECT customers_telephone AS phone
                        FROM ' . TABLE_CUSTOMERS . '
                        WHERE customers_id = ' . (int)$customerId . ' LIMIT 1';
                $phone = BillpayDB::DBFetchValue($qry);
                if (empty($phone)) {
                    $phone = null;
                }
            }
            return $phone;
        }

        /**
         * Checks if current customer can use automatic SEPA debit.
         *      Usually true, unless in Swiss
         * @param string $country2 ISO 2 letter code ie "DE", "CH"
         * @return bool
         */
        function canPayWithAutoSEPA($country2 = "")
        {
            if (!$country2) {
                $country2 = $this->_getCountryIso2Code();
            }
            if ($country2 == "CH") {
                return false;
            }
            return true;
        }

        /**
         * Saves manual SEPA payment information in order status.
         *      Used by invoice and swiss TC.
         * @param ipl_preauthorize_request $req
         * @param int $orderId
         */
        function setManualSEPAPaymentInStatus($req, $orderId)
        {
            $dueDate 			= $req->get_invoice_duedate();
            $dueDateFormatted 	= substr($dueDate,6,2).".".substr($dueDate,4,-2).".".substr($dueDate,0,-4);

            $infoText  = MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER . ": " . $req->get_account_holder() . "\n";
            $infoText .= MODULE_PAYMENT_BILLPAY_TEXT_IBAN . ": " . $req->get_account_number() . "\n";
            $infoText .= MODULE_PAYMENT_BILLPAY_TEXT_BIC . ": " . $req->get_bank_code() . "\n";
            $infoText .= MODULE_PAYMENT_BILLPAY_TEXT_BANK_NAME . ": " . $req->get_bank_name() . "\n";
            $infoText .= MODULE_PAYMENT_BILLPAY_TEXT_PURPOSE . ": " . $this->generateInvoiceReference($orderId) . "\n";
            $infoText .= MODULE_PAYMENT_BILLPAY_DUEDATE_TITLE . ": " . $dueDateFormatted;
            $newStatus = $this->getOrderStatusFromBillpayState(constant('billpayBase_STATE_COMPLETED'));
            $this->addHistoryEntry($orderId, $infoText, $newStatus);
        }

        /**
         * Returns order's billing country iso code 2
         * Ie. "DE", "AU", "CH"
         * @param $orderId
         * @return mixed
         */
        function getOrderCountry2($orderId)
        {
            return BillpayDB::DBFetchValue("SELECT billing_country_iso_code_2 FROM orders WHERE orders_id = '".(int)$orderId."'");
        }

        /**
         * @param $input_fields
         * @param $is_fee
         * @return string
         */
        function getPaymentForm($input_fields, $is_fee)
        {
            $payment_form = '<div id="' . $this->_paymentIdentifier . '" style="display: none;">';
            $payment_form .= $input_fields;

            if ($is_fee) {
                $payment_form .= '<br /><br />' . constant(
                        'MODULE_PAYMENT_' . $this->_paymentIdentifier . '_TEXT_FEE_INFO1'
                    ) . // $billpay_fee->display_formated() .
                    constant('MODULE_PAYMENT_' . $this->_paymentIdentifier . '_TEXT_FEE_INFO2');
            }
            $payment_form .= $this->displayBankData();
            $payment_form .= '</div>';
            return $payment_form; //span
        }

        function isPhoneRequired()
        {
            return false;
        }

        function getShopModification()
        {
            $ret = array(
                'modification'  =>  'xtc3',
                'version'       =>  '1.0.0',
            );
            $ret['version'] = constant('PROJECT_VERSION');

            if (defined('_GM_VALID_CALL')) {
                $ret['modification'] = 'gambio';
                $gx_version = 'unknown';
                @include(DIR_FS_CATALOG . 'release_info.php');
                if (substr($gx_version, 0, 1) == 'v') {
                    preg_match('/\d+\.\d+\.\d+/', $gx_version, $match);
                    $gx_version = $match[0];
                }
                $ret['version'] = $gx_version;
            }
            if (defined('COMMERCE_SEO_V22_INSTALLED')) {
                $ret['modification'] = 'commerceseo';
            }
            if (in_array(constant('PROJECT_VERSION'), array('modified eCommerce Shopsoftware', 'xtcModified'))) {
                $ret['modification'] = 'xtcmod';
            };
            if (constant('PROJECT_VERSION') === '3D Commerce') {
                $ret['modification'] = 'mastershop';
            }

            return $ret;
        }

    }
}
