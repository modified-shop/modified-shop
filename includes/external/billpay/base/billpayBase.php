<?php

/** @noinspection PhpIncludeInspection */
require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/BillpayDB.php');
/** @noinspection PhpIncludeInspection */
require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/BillpayOrder.php');
/** @noinspection PhpIncludeInspection */
require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/Bankdata.php');

if (!class_exists('billpayBase')) {
    /**
     * Some hints:
     * Parameter which are "public" can be used by PSS (see checkout_process.php with debugging)
     *
     *
     * Class billpayBase
     */
    class billpayBase {

        const VERSION = '1.7.11'; // replaced by grunt build script
        const PAYMENT_METHOD_INVOICE = 'BILLPAY';
        const PAYMENT_METHOD_DEBIT = 'BILLPAYDEBIT';
        const PAYMENT_METHOD_TRANSACTION_CREDIT = 'BILLPAYTRANSACTIONCREDIT';
        const PAYMENT_METHOD_PAY_LATER = 'BILLPAYPAYLATER';

        const SESSION_TRANSACTION_ID = 'billpay_transaction_id';

        const STATE_PENDING   = 'PENDING';
        const STATE_APPROVED  = 'APPROVED';
        const STATE_COMPLETED = 'ACTIVATED';
        const STATE_ERROR     = 'ERROR';
        const STATE_CANCELLED = 'CANCELLED';

        const MODE_TEST = 'Testmodus';
        const MODE_LIVE = 'Livemodus';

        private $billpayStates;

        protected $_paymentIdentifier;
        protected $ucPaymentName;

        /** @var bool $enabled Needs to be public, because it is checked by shop. */
        public $enabled;

        /** @var string $title Defines name of a payment method. */
        public $title;

        /** @var string $code Internal name of the plugin. */
        public $code;

        // used by PSS
        public $bp_merchant, $bp_portal, $bp_secure, $bp_public_api_key;

        public  $description, $order;
        private $testmode, $api_url, $_formDob, $_formGender;
        private $_logPath, $enableLog, $_mode;
        protected $token;
        public $error;

        private $requiredModules 		= array('ot_total', 'ot_subtotal');
        private $billpayShippingModules = array(
            'ot_billpay_fee', 'ot_billpaydebit_fee', 'ot_billpaybusiness_fee',
            'ot_cod_fee', 'ot_loworderfee', 'ot_ps_fee', 'ot_shipping',
        );

        /**
         * used by modified-shop for temporary orders
         * @var string
         */
        public $form_action_url = '';

        /**
         * status which is used for temporary orders
         * @var int
         */
        public $tmpStatus = 101;

        /**
         * flag which indicates if a temporary order should be created
         * @var bool
         */
        public $tmpOrders = false;

        /** @var bool $isTestMode Indicates if payment method works in test or production mode. */
        public $isTestMode = false;

        /** @var array $_defaultConfig Default payment method configuration used in installation */
        protected $_defaultConfig = array();

        /** @var string $orderPrefix is used in CI to prepend prefix to order to ensure unique order ids */
        private $orderPrefix = '';

        /** @var bool $isUpdateChecking enables checking for new version of plugin on plugins page */
        private $isUpdateChecking = true;

        /** @var array $otModules contains list of PM specific OT modules */
        protected $otModules = array();

        /**
         * @param null|string $identifier
         */
        public function __construct($identifier = null) {
            if (empty($identifier) === false) {
                $this->_paymentIdentifier = $identifier;
            }
            $this->billpayStates = array(
                billpayBase::STATE_PENDING,
                billpayBase::STATE_APPROVED,
                billpayBase::STATE_COMPLETED,
                billpayBase::STATE_ERROR,
                billpayBase::STATE_CANCELLED,
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
            if ($this->testmode == billpayBase::MODE_TEST) {
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

            if ($this->isUpdateChecking) {
                $this->injectUpdateChecker();
            }

            // used for debug purposes
            if (file_exists(DIR_FS_CATALOG . 'includes/external/billpay/base/debug.php')) {
                /** @noinspection PhpIncludeInspection */
                include(DIR_FS_CATALOG . 'includes/external/billpay/base/debug.php');
            }
        }

        ##### STATIC

        /**
         * Returns instance of payment method
         * @param string $paymentMethod
         * @return mixed
         * @static
         */
        public static function PaymentInstance($paymentMethod)
        {
            $lowerPaymentMethod = strtoupper($paymentMethod);
            switch ($lowerPaymentMethod)
            {
                case billpayBase::PAYMENT_METHOD_INVOICE:
                    /** @noinspection PhpIncludeInspection */
                    require_once(DIR_FS_CATALOG.'includes/modules/payment/billpay.php');
                    return new billpay($paymentMethod);
                    break;
                case billpayBase::PAYMENT_METHOD_DEBIT:
                    /** @noinspection PhpIncludeInspection */
                    require_once(DIR_FS_CATALOG.'includes/modules/payment/billpaydebit.php');
                    return new billpaydebit($paymentMethod);
                case billpayBase::PAYMENT_METHOD_TRANSACTION_CREDIT:
                    /** @noinspection PhpIncludeInspection */
                    require_once(DIR_FS_CATALOG.'includes/modules/payment/billpaytransactioncredit.php');
                    return new billpaytransactioncredit($paymentMethod);
                case billpayBase::PAYMENT_METHOD_PAY_LATER:
                    /** @noinspection PhpIncludeInspection */
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
        public static function GetPaymentMethods() {
            return array(
                'billpay', 'billpaydebit', 'billpaytransactioncredit', 'billpaypaylater'
            );
        }

        /**
         * Parses Billpay callback and returns data as array
         * @return array|bool
         * @static
         */
        public static function ParseCallback()
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

            /** @noinspection PhpIncludeInspection */
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
        public static function CurrencyToSmallerUnit($price_float) {
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
        public static function EnsureUTF8($value) {
            $trimmedValue = trim($value);
            if(defined('MODULE_PAYMENT_BILLPAY_GS_UTF8_ENCODE') && constant('MODULE_PAYMENT_BILLPAY_GS_UTF8_ENCODE') == 'local') {
                return mb_convert_encoding($trimmedValue, "UTF-8", mb_detect_encoding($trimmedValue, "UTF-8, ISO-8859-1, ISO-8859-15", true));
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
        public static function EnsureString($value) {
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
        public static function GetOrderProducts($orderId)
        {
            $ret = array();
            $table = TABLE_ORDERS_PRODUCTS;
            $orders_id = (int)$orderId;
            $rows = BillpayDB::DBFetchArray("SELECT products_price, products_quantity, orders_products_id, products_name FROM $table WHERE orders_id='$orders_id'");
            foreach ($rows as $row) {
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
        public static function DisplayErrorAndExit($errorString)
        {
            /** @noinspection PhpIncludeInspection */
            include(DIR_FS_CATALOG . 'includes/external/billpay/templates/error_utf8.php');
            die($errorString);
        }


        /**
         * Function adds message to session and redirects to selected url.
         * Message is hardly visible on page.
         * @param $errorString
         * @param $redirectUrl
         * @static
         */
        public static function QueueErrorAndRedirect($errorString, $redirectUrl)
        {
            /** @var $messageStack object */
            global $messageStack;
            $messageStack->add_session($errorString, 'error');
            xtc_redirect($redirectUrl);
        }

        #### /STATIC

        /**
         * BillPay callback function handling an order confirmation and Giropay confirmation
         * @param $data Array
         * @return bool
         */
        public function onBillpayCallback($data)
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

            $orderId = (int)$data['reference'];
            $token   = $_GET['token'];
            $oBankData = Billpay_Base_Bankdata::LoadByOrdersId($orderId);
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
                $this->setOrderBillpayState(billpayBase::STATE_CANCELLED, $orderId);
                return true; // deny is valid status
            }

            // approved!
            $orderId = (int)$data['reference'];
            $this->setOrderBillpayState(billpayBase::STATE_APPROVED, $orderId);
            $this->onOrderApproved($orderId, $data);

            // send confirmation mail
            if($this->isGambio()) {
                $coo_send_order_process = MainFactory::create_object('SendOrderProcess');
                $coo_send_order_process->set_('order_id', $orderId);
                $coo_send_order_process->proceed();
            } elseif($this->isModified()) {
                if (file_exists('../../local/configure.php')) {
                    include('../../local/configure.php');
                } else {
                    include('../../configure.php');
                }

                $smarty = new Smarty;
                $insert_id = $orderId;
                $send_by_admin = true;

                define('SEND_BY_ADMIN_PATH', DIR_FS_CATALOG);

                require(DIR_WS_CLASSES . 'order.php');
                include(DIR_FS_CATALOG . 'send_order.php');
            }

            return true;
        }

        /**
         * Event fired when admin changes order's status
         * @param $orderId
         * @param $newStatus
         * @return bool
         */
        public function onOrderStatusChange($orderId, $newStatus)
        {
            $this->_logDebug("Changing order's (".$orderId.") status to ".$newStatus);
            $result = true;
            if ($newStatus == $this->getOrderStatusFromBillpayState(billpayBase::STATE_COMPLETED))
            {
                $result = $this->reqInvoiceCreated($orderId);
            }
            if ($newStatus == $this->getOrderStatusFromBillpayState(billpayBase::STATE_CANCELLED))
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
        public function onOrderApproved($orderId, $data)
        {

        }

        /**
         * Event fired after getting success response for editCartContent method
         * @param $orderId
         * @param ipl_edit_cart_content_request $req
         * @abstract
         */
        public function onOrderChanged($orderId, $req)
        {

        }

        public function getInvoiceTextData($bank_data, $currency)
        {
            $return = array();
            $return[] = $this->getThankYouText();
            $return[] = $this->getPayUntilText($bank_data, $currency);
            $return[] = $this->getPaymentDetails($bank_data, $currency);
            $return[] = $this->getEmailText();
            $return = array_filter($return);
            return $return;
        }


        public function onDisplayInvoiceHtml($invoice_data)
        {
            $html = '';
            foreach ($invoice_data as $row) {
                if (!is_array($row)) {
                    $html .= $row . "<br>";
                    continue;
                }
                $html .= $this->renderPaymentDetailsHTML($row);
            }
            return $html;
        }

        private function onDisplayInvoiceText($invoice_data)
        {
            $text = '';
            foreach ($invoice_data as $row) {
                if (!is_array($row)) {
                    $text .= $row . "\n";
                    continue;
                }
                foreach ($row as $header => $value) {
                    if (empty($value)) continue;
                    if (empty($header)) {
                        $text .= $value . "\n";
                    }
                    $text .= $header . ': ' . $value . "\n";
                }
            }
            return $text;
        }

        /**
         * Event fired when admin prints a PDF.
         * Warning: this is not a standard shop function.
         * Used by: Mastershop
         * @abstract
         * @return bool
         */
        public function onDisplayPdf($pdf, $orderId, $bankDataQuery)
        //public function onDisplayPdf()
        {
            return true;
        }

        /**
         * Returns payment information strings added to the customer's e-mail.
         *
         * @param int $orderId
         * @return array
         * @abstract
         */
        public function getPaymentInfo($orderId)
        {
            $currency = BillpayOrder::getCurrencyById($orderId);
            $bank_data = Billpay_Base_Bankdata::LoadByOrdersId($orderId);
            $data = $this->getInvoiceTextData($bank_data, $currency);
            $html = $this->onDisplayInvoiceHtml($data);
            $text = $this->onDisplayInvoiceText($data);
            return array(
                'html'  =>  $html,
                'text'  =>  $text,
            );
        }

        /**
         * Requires correct language files
         */
        public function requireLang()
        {
            $language = empty($_SESSION['language']) ? 'german' : $_SESSION['language'];
            $file = DIR_FS_CATALOG .'lang/'. $language . '/modules/payment/' . strtolower($this->_paymentIdentifier) . '.php';
            if (file_exists($file))
            {
                /** @noinspection PhpIncludeInspection */
                require_once($file);
            }
        }

        /**
         * Checks if it should display current payment method and displays it.
         * @return array|false
         */
        public function selection() {
            unset($_SESSION['billpay']);
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
            // $this->_logDebug($minValue.' < '.$orderTotal.' < '.$staticLimit);
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

            return $this->renderUnifiedCheckout();
        }

        /**
         * Adds to order's status history, without changing status. Used by partial cancel.
         * @param int $oID
         * @param string $infoText
         * @param int|null $status  New status. If not set, it preserves current status.
         */
        private function addHistoryEntry($oID, $infoText, $status = null) {
            if ($status === null) {
                // get last status
                $table = TABLE_ORDERS;
                $orders_id = (int)$oID;
                $status = BillpayDB::DBFetchValue("SELECT orders_status FROM $table WHERE orders_id='$orders_id'");
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

            $table = TABLE_ORDERS_STATUS_HISTORY;
            $orders_id = (int)$oID;
            $orders_status_id = (int)$status;
            $comments = $infoText;
            BillpayDB::DBQuery("INSERT INTO $table (orders_id, orders_status_id, date_added, comments) VALUES ($orders_id, $orders_status_id, now(), '$comments')");
        }


        private function injectUnifiedCheckout()
        {
            if (defined('billpayBase_injectUnifiedCheckout') && billpayBase_injectUnifiedCheckout != $this->_paymentIdentifier)
            {
                return '';
            }
            define('billpayBase_injectUnifiedCheckout', $this->_paymentIdentifier);

            $apiKey = $this->bp_public_api_key;
            $userIdentifier = $this->getCustomerIdentifier();
            $country3 = $this->_getCountryIso3Code();
            $country2 = $this->_getCountryIso2Code();
            $currency = BillpayOrder::GetCurrentCurrency();
            $lang     = $this->_getLanguage();
            $amount = $this->_getCartBaseAndShipping();
            $baseAmount = $amount['baseAmount'];
            $orderAmount= $amount['orderAmount'];
            $customerFirstName = $_SESSION['customer_first_name'];
            $customerLastName  = $_SESSION['customer_last_name'];
            $customerDob    =   date('Y-m-d', $this->getDateOfBirth());
            if ($customerDob === "1970-01-01") {
                $customerDob = '';
            }
            $customerGender =   $this->getGender();
            $customerPhone = $this->getPhone();
            $isLive = ($this->isTestMode ? 'false' : 'true');

            $billpayScript = <<<JAVASCRIPT
<script type="text/javascript">
// live
var bpyReq={},appPath="//widgetcdn.billpay.de/checkout/1.x.x/";!function(e,a,t,n){bpyReq={deps:["main"],baseUrl:t,skipDataMain:!0,callback:function(){}},e.BillPayCheckout=n,e[n]=e[n]||function(){(e[n].queue=e[n].queue||[]).push(arguments)};var c=a.createElement("script");c.src=t+"require.js",a.getElementsByTagName("head")[0].appendChild(c)}(window,document,appPath,"billpayCheckout");

billpayCheckout('options', {
    "checkout": {"form": "#checkout_payment"},
    "validateOn": ".continue_button",
    "shop": {
        "apiKey": "$apiKey",
        "live": $isLive
    },
    "order": {
        "cartAmount": $baseAmount,
        "orderAmount": $orderAmount,
        "currency": "$currency"
    },
    "customer": {
        "billing": {
            "salutation": "$customerGender",
            "firstName": "$customerFirstName",
            "lastName": "$customerLastName",
            "street": "",
            "streetNo": "",
            "zip": "",
            "city": "",
            "phoneNumber": "$customerPhone",
            "countryIso2": "$country2",
            "countryIso3": "$country3",
            "dayOfBirth": "$customerDob"
        },
        "customerType": "",
        "language": "$lang",
        "identifier": "$userIdentifier"
    },
    "request": []
});
billpayCheckout('exec', function($) {
    var current_payment = null;
    var payment_methods = {
        billpay: 'invoice',
        billpaydebit: 'directDebit',
        billpaytransactioncredit: 'transactionCredit',
        billpaypaylater: 'paylater'
    };
    var show_payment = function(payment_name) {
        if ($.inArray(payment_name, Object.keys(payment_methods)) === -1) {
            return;
        }
        if (current_payment == payment_name) {
            return;
        }
        $('.bpy-checkout-container').hide();
        $('.bpy-checkout-container[bpy-pm="' + payment_methods[payment_name] + '"]').show();
        billpayCheckout('run', {"container": '.bpy-checkout-container'});
        current_payment = payment_name;
    };
    var selected_payment_name = $('input[name="payment"]:checked').val();
    show_payment(selected_payment_name);
    /**
     * modified eCommerce Shopsoftware
     */
    $("[id*=\"rd\"]").click(function(e) {
      var payment_name = $('input[name="payment"]:checked', '#checkout_payment').val();
      show_payment(payment_name);
    });
});
</script>
JAVASCRIPT;
            return $billpayScript;
        }

        /**
         * Renders Unified Checkout script injection, payment method configuration and instantiates PL.
         */
        private function renderUnifiedCheckout()
        {
            $html = '';
            $html .= $this->injectUnifiedCheckout();
            $ucPaymentName = $this->ucPaymentName;
            $html .= '<div class="bpy bpy-checkout-container" bpy-pm="'.$ucPaymentName.'"></div>';
            $title_ext = $this->_buildFeeTitleExtension($this->_paymentIdentifier);
            return array(
                'id'        =>  $this->code,
                'module'    =>  $this->title.($title_ext ? (' '.$title_ext) : ''),
                'fields'    =>  array(
                    array(
                        'title'     =>  '',
                        'field'     =>  $html,
                    ),
                )
            );
        }

        /**
         * Checks if merchant set up additional fee for using selected payment
         * @param $paymentIdentifier
         * @return bool|string
         */
        protected function _buildFeeTitleExtension($paymentIdentifier) {

            $fee_string = '';

            // TODO: xtc3 does not include them for some reason
            /** @noinspection PhpIncludeInspection */
            require_once(DIR_FS_CATALOG . 'includes/modules/order_total/ot_billpay_fee.php');
            /** @noinspection PhpIncludeInspection */
            require_once(DIR_FS_CATALOG . 'includes/modules/order_total/ot_billpaybusiness_fee.php');

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
            if ($paymentIdentifier == billpayBase::PAYMENT_METHOD_INVOICE) {
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
        public function check() {
            if (!isset($this->_check)) {
              if (defined('MODULE_PAYMENT_' . $this->_paymentIdentifier . '_STATUS')) {
                $this->_check = true;
              } else {
                $table = TABLE_CONFIGURATION;
                $config_key = 'MODULE_PAYMENT_' . $this->_paymentIdentifier . '_STATUS';
                $query = "SELECT configuration_value from $table where configuration_key = '$config_key'";
                $this->_check = BillpayDB::DBCount($query);
              }
            }
            return $this->_check;
        }

        /**
         * Sets customer data into the preauth request.
         * @param ipl_preauthorize_request $req
         * @param string $customerGroup
         * @return ipl_preauthorize_request
         */
        protected function _set_customer_details($req, $customerGroup='p') {
            //added get customer phone for tc
            $phone = $this->getPhone();

            $billing = BillpayOrder::getCustomerBilling();
            $req->set_customer_details(
                billpayBase::EnsureUTF8($this->_getCustomerId()),
                billpayBase::EnsureUTF8($this->_getCustomerGroup()),
                billpayBase::EnsureUTF8($this->_getCustomerSalutation()),
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
        private function _set_shipping_details($req) {
            $delivery = BillpayOrder::getCustomerShipping();
            $phone = BillpayOrder::getCustomerPhone();
            $req->set_shipping_details(FALSE,
                billpayBase::EnsureUTF8($this->_getCustomerSalutation($this->_getDataIdentifier('gender', $_POST))), // TODO: change into standard
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
        private function _add_articles($req) {
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
        private function _add_order_totals($req) {
            $billpayTotals = $this->_getDataValue('order_totals');

            $req->set_total(
                $this->CurrencyToSmallerUnit($billpayTotals['billpayRebateNet']),	// rebate
                $this->CurrencyToSmallerUnit($billpayTotals['billpayRebateGross']),	// rebategross
                'n/a',
                $this->CurrencyToSmallerUnit($billpayTotals['billpayShippingNet']),
                $this->CurrencyToSmallerUnit($billpayTotals['billpayShippingGross']),
                $this->CurrencyToSmallerUnit($billpayTotals['orderTotalNet']),
                $this->CurrencyToSmallerUnit($billpayTotals['orderTotalGross']),
                BillpayOrder::GetCurrentCurrency(), // currency
                '' 	// reference
            );

            return $req;
        }

        /**
         * Sets statistical data to the request, so Billpay can see which shops are in use.
         * @param ipl_preauthorize_request $req
         * @return ipl_preauthorize_request
         */
        private function _setTrace($req)
        {
            $req->setTracePluginVersion(billpayBase::VERSION);
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
        private function _calculate_billpay_totals($order_total_modules, $order, $isNetShippingPrice) {
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

                foreach ($order_totals as $value) {
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
                                                $orderSubTotalGross = $_SESSION['cart']->show_total();
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
         * Compares order's billing and delivery addresses. If the delivery address is different, sets it in request.
         * @param ipl_preauthorize_request $req
         * @return mixed
         */
        private function _addressCompare($req) {
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
        private function _error_redirect($err_msg) {
            global $messageStack;
            
            $err_msg = billpayBase::EnsureString($err_msg);
            $_SESSION['gm_error_message'] = $err_msg;
            $this->_logDebug($err_msg);
            unset($_SESSION['customer_gender']);
            unset($_SESSION['customer_dob']);

            if ($_POST['xajax']) {
                /** ajax one page checkout  */
                $_SESSION['checkout_payment_error'] = 'payment_error=' . $this->code . '&error=' . $err_msg;
            } else {
                $messageStack->add_session('checkout_payment', $err_msg);
                xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
            }
        }

         /**
         * Validates gender, b2b/b2c fields and EULA
         * @param array $vars
         */
        public function pre_confirmation_check($vars = null) {
            if (empty($vars))
            {
                $vars = $_POST;
                if ($_SERVER['REQUEST_METHOD'] == 'POST' 
                    && isset($_POST['billpay'])
                    )
                {
                  $_SESSION['billpay'] = $_POST['billpay'];
                } elseif ($_SERVER['REQUEST_METHOD'] == 'GET'
                          && isset($_SESSION['billpay'])
                          )
                {
                  $_POST['billpay'] = $_SESSION['billpay'];
                  $vars = $_POST;
                }
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

            if (!$this->isDobValid($this->getDateOfBirth()) && !$this->isB2B($vars)) {
                if ($this->getDateOfBirth() === null) {
                    $this->_error_redirect(MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DOB);
                } else {
                    $this->_error_redirect(MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DOB_UNDER);
                }
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
        public function confirmation() {
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
        public function process_button() {
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
        public function before_process() {
            global $messageStack;
            
            $this->_logError('START beforeProcess');
            $data = array();
            $success = $this->reqPreauthorizeCapture($data);
            if (!$success) {
                $error = billpayBase::EnsureString($this->error);
                $_SESSION['gm_error_message'] = $error; // Gambio specific
                $messageStack->add_session('checkout_payment', $error);
                xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
            }
        }

        /**
         *
         * It executes after before_process(). If BillPay denied, it won't be executed.
         */
        public function after_process() {
            global $insert_id, $messageStack; # newOrderId

            $transaction_id = self::GetTransactionId();
            $this->saveOrderId($transaction_id, $insert_id);

            $error = false;

            if (!$transaction_id) {
                $error = 'Transaction ID not found in session';
            }

            if ($error) {
                $this->setOrderBillpayState(billpayBase::STATE_ERROR, $insert_id);
                $this->_logError('Transaction ID not found in session', 'ERROR in after_process');
                $messageStack->add_session('checkout_payment', MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DEFAULT);
                xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
                return false; # xtc_redirect exits
            }

            $this->setOrderBillpayState($_SESSION['billpay_onAfterProcess']['orderState'], $insert_id);

            $productIds = $this->_prepareProductMapping($insert_id);
            $this->reqUpdateOrder(self::GetTransactionId(), $insert_id, $productIds);

            $this->onDisplayThankYou($insert_id);

            unset($_SESSION[self::SESSION_TRANSACTION_ID]);
            unset($_SESSION['billpay_total_amount']);
            unset($_SESSION['billpay_preselect']);
            unset($_SESSION['bp_rate_result']);
            unset($_SESSION['rr_data']);
            unset($_SESSION['billpay']);

            return true;
        }


        private function _prepareProductMapping($insert_id)
        {
            // create mapping for id update list
            $productIds = array();
            $table = TABLE_ORDERS_PRODUCTS;
            $orders_id = (int)$insert_id;
            $query = xtc_db_query("SELECT orders_products_id FROM $table WHERE orders_id='$orders_id' ORDER BY orders_products_id ASC");
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
         * Saves log message to the log file (/includes/external/billpay/log)
         * @param string $logMessage
         * @param string $logType
         * @return bool
         */
        public function _logError($logMessage, $logType = 'default') {
            $_write = FALSE;
            if (is_array($logMessage)) {
                $logMessage = print_r($logMessage, true);
            }
            $logMessage = $this->filterLogMessage($logMessage);
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
        public function _logDebug($message)
        {
            $this->_logError($message, 'debug');
        }

        /**
         * Filters long / sensitive fields in log messages
         *
         * @param  string $message
         * @return string
         */
        private function filterLogMessage($message)
        {
            $filtered_tags = array('email_attachment', 'standard_information');
            foreach ($filtered_tags as $tag) {
                $message = preg_replace('@(<' . $tag . '><!\[CDATA\[).*(\]\]>)</' . $tag . '>@s', '$1--filtered--$2', $message);
            }
            $filtered_attributes = array(
                'mid'   =>  '([0-9]+)',
                'pid'   =>  '([0-9]+)',
                'bpsecure'=>'([0-9a-zA-Z]+)',
            );
            foreach ($filtered_attributes as $attr => $regex) {
                $message = preg_replace('@' . $attr . '="' . $regex . '"@', '' . $attr . '="--filtered--"', $message);
            }

            return $message;
        }

        /**
         * Checks if selected OT (order total) module is installed
         * @param $moduleName
         * @return bool
         */
        private function isModuleInstalled($moduleName) {
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
         * @return string
         */
        public function getBillPayCurrentShopVersion()
        {
            $table = TABLE_CONFIGURATION;

            $paymentIdentifier = $this->_paymentIdentifier;
            $key = "MODULE_PAYMENT_".$paymentIdentifier."_CURRENT_SHOP_VERSION";
            $queryResult = xtc_db_query("SELECT configuration_value AS shopVersion FROM $table where configuration_key = '$key'");
            $fetchResult = xtc_db_fetch_array($queryResult);

            return $fetchResult['shopVersion'];
        }

        /**
         * @param string $version
         */
        public function updateBillPayShopVersion($version)
        {
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_CURRENT_SHOP_VERSION";

            $table = TABLE_CONFIGURATION;
            xtc_db_query("UPDATE $table SET configuration_value='$version' WHERE configuration_key = '$configuration_key'");
        }

        /**
         * We need the shop version to check for shop update and show a popup in the admin page
         *
         * @return bool|mysqli_result
         */
        public function insertBillPayShopVersion()
        {
            $table = TABLE_CONFIGURATION;
            $shopModification = $this->getShopModification();
            $version = $shopModification['version'];
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_CURRENT_SHOP_VERSION";

            $result = array();
            $result[$configuration_key] = xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('$configuration_key', '$version', '6', '0', now())");

            return $result;
        }

        /**
         * installs the payment method
         */
        public function install()
        {
            $this->_logDebug('Starting payment method installation: '.$this->_paymentIdentifier);
            $state = 'install';
            // make sure we get a clean state
            $this->remove($state);

            // fetch next sort order
            switch ($this->_paymentIdentifier) {
                case billpayBase::PAYMENT_METHOD_INVOICE:
                    $sortOrder = 3;
                    break;
                case billpayBase::PAYMENT_METHOD_DEBIT:
                    $sortOrder = 4;
                    break;
                case billpayBase::PAYMENT_METHOD_TRANSACTION_CREDIT;
                    $sortOrder = 5;
                    break;
                case billpayBase::PAYMENT_METHOD_PAY_LATER:
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
            /** @noinspection PhpIncludeInspection */
            require_once $langFile;

            // install new configuration
            $results = array();
            $table = TABLE_CONFIGURATION;
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_STATUS";
            $results[$configuration_key] = xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('$configuration_key', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_LOGGING";
            $results[$configuration_key] = xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('$configuration_key', '', '6', '0', now())");
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_LOGGING_ENABLE";
            $results[$configuration_key] = xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('$configuration_key', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_ID";
            $results[$configuration_key] = xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('$configuration_key', 'ShopID', '6', '0', now())");
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_SHIPPING_TAX";
            $results[$configuration_key] = xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('$configuration_key', '',  '6', '0', now())");
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_SORT_ORDER";
            $results[$configuration_key] = xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('$configuration_key', '$sortOrder', '6', '0', now())");
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_ALLOWED";
            $results[$configuration_key] = xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('$configuration_key', 'DE',   '6', '0', now())");
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_ORDER_STATUS";
            $results[$configuration_key] = xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('$configuration_key', '0', '6', '0', 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(', now())");
            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_TABLE";
            $results[$configuration_key] = xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('$configuration_key', 'payment_billpay', '6', '0', now())");

            $result[$configuration_key] = $this->insertBillPayShopVersion();
            $results = $result + $results;

            $configuration_key = "MODULE_PAYMENT_".$this->_paymentIdentifier."_MIN_AMOUNT";
            $min_amount = $this->_getDefaultInstallConfig('MIN_AMOUNT');
            $results[$configuration_key] = xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('$configuration_key', '$min_amount', '6', '0', now())");
            $this->_logDebug("Setting local configuration keys:\n".print_r($results, true));

            //check if UTF8 setting is set globally = GS
            $configuration_key = 'MODULE_PAYMENT_BILLPAY_GS_UTF8_ENCODE';
            $check_status = xtc_db_query("SELECT count(*) AS number FROM $table where configuration_key LIKE '$configuration_key'");
            $rs_check_status = xtc_db_fetch_array($check_status);
            if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
                $this->_logDebug("Setting global key: $configuration_key");
                xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('$configuration_key', 'local', '6',  '0',\"xtc_cfg_select_option(array('local', 'UTF-8'), \", now())");
            }

            //check if login data is already set globally = GS
            $configuration_key = "MODULE_PAYMENT_BILLPAY_GS_MERCHANT_ID";
            $check_status = xtc_db_query("SELECT count(*) AS number FROM $table where configuration_key LIKE '$configuration_key'");
            $rs_check_status = xtc_db_fetch_array($check_status);
            if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
                $this->_logDebug("Setting global key: $configuration_key");
                xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('$configuration_key', '0', '6', '0', now())");
            }

            $configuration_key = "MODULE_PAYMENT_BILLPAY_GS_PORTAL_ID";
            $check_status = xtc_db_query("SELECT count(*) AS number FROM $table where configuration_key LIKE '$configuration_key'");
            $rs_check_status = xtc_db_fetch_array($check_status);
            if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
                $this->_logDebug("Setting global key: $configuration_key");
                xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('$configuration_key', '0', '6', '0', now())");
            }

            $configuration_key = "MODULE_PAYMENT_BILLPAY_GS_SECURE";
            $check_status = xtc_db_query("SELECT count(*) AS number FROM $table where configuration_key LIKE '$configuration_key'");
            $rs_check_status = xtc_db_fetch_array($check_status);
            if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
                $this->_logDebug("Setting global key: $configuration_key");
                xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('$configuration_key', '0', '6', '0', now())");
            }

            $configuration_key = "MODULE_PAYMENT_BILLPAY_GS_PUBLIC_API_KEY";
            $check_status = xtc_db_query("SELECT count(*) AS number FROM $table where configuration_key LIKE '$configuration_key'");
            $rs_check_status = xtc_db_fetch_array($check_status);
            if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
                $this->_logDebug("Setting global key: $configuration_key");
                xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('$configuration_key', '0', '6', '0', now())");
            }

            //check if TEST API / API URl is already set
            $configuration_key = "MODULE_PAYMENT_BILLPAY_GS_TESTAPI_URL_BASE";
            $check_status = xtc_db_query("SELECT count(*) AS number FROM $table where configuration_key LIKE '$configuration_key'");
            $rs_check_status = xtc_db_fetch_array($check_status);
            if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
                $this->_logDebug("Setting global key: $configuration_key");
                xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('$configuration_key', 'https://test-api.billpay.de/xml/offline', '6', '0', now())");
            }

            $configuration_key = "MODULE_PAYMENT_BILLPAY_GS_API_URL_BASE";
            $check_status = xtc_db_query("SELECT count(*) AS number FROM $table where configuration_key LIKE '$configuration_key'");
            $rs_check_status = xtc_db_fetch_array($check_status);
            if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
                $this->_logDebug("Setting global key: $configuration_key");
                xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('$configuration_key', 'https://api.billpay.de/xml', '6', '0', now())");
            }

            //check if mode is already set
            $configuration_key = "MODULE_PAYMENT_BILLPAY_GS_TESTMODE";
            $check_status = xtc_db_query("SELECT count(*) AS number FROM $table where configuration_key LIKE '$configuration_key'");
            $rs_check_status = xtc_db_fetch_array($check_status);
            if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
                # You don't translate configuration values!
                $this->_logDebug("Setting global key: $configuration_key");
                xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('$configuration_key', '".BillpayBase::MODE_TEST."', '6', '0', 'xtc_cfg_select_option(array(\'".BillpayBase::MODE_TEST."\', \'".BillpayBase::MODE_LIVE."\'), ', now())");
            }

            //check if HTTP_X_FORWARDED FOR is already installed
            $configuration_key = "MODULE_PAYMENT_BILLPAY_GS_HTTP_X";
            $check_status = xtc_db_query("SELECT count(*) AS number FROM $table where configuration_key LIKE '$configuration_key'");
            $rs_check_status = xtc_db_fetch_array($check_status);
            if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
                $this->_logDebug("Setting global key: $configuration_key");
                xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('$configuration_key', 'False', '6', '0', 'xtc_cfg_select_option(array(\'False\', \'True\'), ', now())");
            }

            $this->_logDebug("Executing payment specific installation code.");
            $this->onInstall();

            // checking if all BillPay statuses exists
            $this->_logDebug("Checking BillPay order states\n".print_r($this->billpayStates, true));
            foreach ($this->billpayStates as $stateId) {
                $configuration_key = "MODULE_PAYMENT_BILLPAY_STATUS_".$stateId;
                $configuration_value = BillpayBase::GetConfig($configuration_key);
                if (!empty($configuration_value)) {
                    continue;
                }

                // creating non existing order status
                $this->_logDebug("Creating state #$stateId");
                $table = TABLE_ORDERS_STATUS;
                $nextId = BillpayDB::DBFetchValue("SELECT max(orders_status_id) + 1 AS nextId FROM $table");
                $textEn = 'BillPay '.$stateId;
                $textDe = 'BillPay '.$stateId;
                if (defined($configuration_key.'_TITLE_EN')) {
                    $textEn = constant($configuration_key.'_TITLE_EN');
                }
                if (defined($configuration_key.'_TITLE_DE')) {
                    $textDe = constant($configuration_key.'_TITLE_DE');
                }
                $this->_logDebug("New state: $nextId, \nen:$textEn \nde:$textDe");
                $table = TABLE_ORDERS_STATUS;
                xtc_db_query("INSERT INTO $table (orders_status_id, language_id, orders_status_name) VALUES ('$nextId', '1', '$textEn')");
                xtc_db_query("INSERT INTO $table (orders_status_id, language_id, orders_status_name) VALUES ('$nextId', '2', '$textDe')");

                $table = TABLE_CONFIGURATION;
                xtc_db_query("INSERT INTO $table (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('$configuration_key', '$nextId', '6', '0', now())");
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
                    $db = DB_DATABASE;
                    $check_query = xtc_db_query("SELECT *
                         FROM information_schema.COLUMNS
                         WHERE TABLE_SCHEMA = '$db'
                           AND TABLE_NAME = 'billpay_bankdata'
                           AND COLUMN_NAME = '$columnName' "
                    );
                    if (xtc_db_num_rows($check_query) == 0) {
                        // create tc columns if they do not exist yet
                        xtc_db_query("ALTER TABLE `billpay_bankdata` ADD `$columnName` $columnType");
                    }
                }
            }

            // Payment Method specific OTs
            foreach ($this->otModules as $ot) {
                /** @noinspection PhpIncludeInspection */
                require_once(DIR_FS_CATALOG . 'includes/modules/order_total/'.$ot.'.php');
                /** @var BillpayOT $otModule */
                $otModule = new $ot();
                $otModule->install();
            }

            $this->_logDebug('Installation successful.');
        }

        /**
         * Removes configuration, except when $state == 'install'
         */
        public function remove() {
            $this->_logDebug('Removing payment method config.');
            $table = TABLE_CONFIGURATION;
            $config_key = 'MODULE_PAYMENT_'.$this->_paymentIdentifier.'_%';
            xtc_db_query("DELETE FROM $table
                            WHERE configuration_key LIKE '$config_key'
                            AND configuration_key NOT LIKE 'MODULE_PAYMENT_BILLPAY_GS_%'
                            AND configuration_key NOT LIKE 'MODULE_PAYMENT_BILLPAY_STATUS_%' ");
            // complete removal (GS, statuses) is disabled

            // Payment Method specific OTs
            foreach ($this->otModules as $ot) {
                /** @noinspection PhpIncludeInspection */
                require_once(DIR_FS_CATALOG . 'includes/modules/order_total/'.$ot.'.php');
                /** @var BillpayOT $otModule */
                $otModule = new $ot();
                $otModule->remove();
            }
        }

        /**
         * returns all configuration constants of the payment module
         *
         * @return array
         */
        public function keys()
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


        protected function getModuleConfig() {
            $country = strtoupper($this->_getCountryIso3Code());
            $currency = strtoupper(BillpayOrder::GetCurrentCurrency());
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
        private function reqPreauthorizeCapture()
        {
            /** @noinspection PhpIncludeInspection */
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/ipl_xml_api.php');
            /** @noinspection PhpIncludeInspection */
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/php5/ipl_preauthorize_request.php');

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

            $this->token = ipl_create_random();
            $shopDomain = $this->_getShopDomain();
            if (strpos($shopDomain, "localhost") !== false) {
                $this->_logError("Shop working on localhost, cannot receive callbacks: ".$shopDomain);
                $shopDomain = "http://billpay.de/";
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

            $transaction_id = utf8_decode((string)$req->get_bptid());
            $this->_setTransactionId($transaction_id);

            $billpayTotals = $this->_getDataValue('order_totals');
            $orderTotalGross = $billpayTotals['orderTotalGross'];
            Billpay_Base_Bankdata::SaveRequest($req, $orderTotalGross, $transaction_id);
            $this->onPreauthResponse($req);
            $_SESSION['billpay_onAfterProcess'] = array(
                'orderState'           =>  billpayBase::STATE_APPROVED,
                'campaignText'         =>  '',
                'externalRedirect'     =>  '',
                'campaignImg'          =>  '',
            );
            if ($req->get_status() == 'PRE_APPROVED') {
                $_SESSION['billpay_onAfterProcess']['orderState'] = billpayBase::STATE_PENDING;
                $_SESSION['billpay_onAfterProcess']['externalRedirect'] = $req->get_external_redirect_url();
                $_SESSION['billpay_onAfterProcess']['campaignText'] = $req->get_campaign_display_text();
                $_SESSION['billpay_onAfterProcess']['campaignImg'] = $req->get_campaign_display_image_url();
                $_SESSION['billpay_onAfterProcess']['rateLink'] = $req->get_rate_plan_url();
                $this->form_action_url = 'checkout_billpay_giropay.php';
                $this->tmpOrders = true;
                $this->tmpStatus = $this->getOrderStatusFromBillpayState(billpayBase::STATE_PENDING);
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
        private function reqModuleConfig($data)
        {
            /**
             * $data = array(
             *      'country'   =>  'deu',
             *      'currency'  =>  'EUR',
             *      'language'  =>  'de',
             * )
             */
            /** @noinspection PhpIncludeInspection */
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/ipl_xml_api.php');
            /** @noinspection PhpIncludeInspection */
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/php5/ipl_module_config_request.php');

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
        private function reqInvoiceCreated($orderId)
        {
            /** @noinspection PhpIncludeInspection */
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/ipl_xml_api.php');
            /** @noinspection PhpIncludeInspection */
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/php5/ipl_invoice_created_request.php');
            $this->_logDebug("Activating order");
            $this->requireLang();
            $req = new ipl_invoice_created_request($this->api_url);
            $req->set_default_params($this->bp_merchant, $this->bp_portal, $this->bp_secure);
            $total = BillpayOrder::getOTById($orderId, 'ot_total');
            $total = $this->CurrencyToSmallerUnit($total);
            $currency = BillpayOrder::getCurrencyById($orderId);
            $req->set_invoice_params($total, $currency, $this->getRemoteOrderId($orderId));
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
            BillpayDB::DBQuery('UPDATE billpay_bankdata SET invoice_due_date = "'.$dueDate.'" '.'WHERE orders_id = '.(int)$orderId);
            $newStatus = $this->getOrderStatusFromBillpayState(billpayBase::STATE_COMPLETED);
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
        private function reqUpdateOrder($transactionId, $orderId, $productIds)
        {
            # we update Billpay DB with orderId
            /** @noinspection PhpIncludeInspection */
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/ipl_xml_api.php');
            /** @noinspection PhpIncludeInspection */
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/php5/ipl_update_order_request.php');

            $req = new ipl_update_order_request($this->api_url);
            $req->set_default_params($this->bp_merchant, $this->bp_portal, $this->bp_secure);
            $req->set_update_params($transactionId, $this->getRemoteOrderId($orderId));

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
            BillpayDB::DBQuery("UPDATE billpay_bankdata SET api_reference_id='" . $orderId . "' WHERE tx_id='".$transactionId."'");
            return true;
        }

        /**
         * Cancels accepted or completed order.
         * @param $orderId
         * @return bool
         */
        public function reqCancel($orderId)
        {
            /** @noinspection PhpIncludeInspection */
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/ipl_xml_api.php');
            /** @noinspection PhpIncludeInspection */
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/php5/ipl_cancel_request.php');
            $this->requireLang();
            $this->_logDebug("Cancelling order.");
            $req = new ipl_cancel_request($this->api_url);
            $req->set_default_params($this->bp_merchant, $this->bp_portal, $this->bp_secure);

            $orderCurrency = BillpayOrder::getCurrencyById($orderId);
            $orderTotal = BillpayOrder::getOTById($orderId, 'ot_total');
            $orderTotal = billpayBase::CurrencyToSmallerUnit($orderTotal);
            $req->set_cancel_params($this->getRemoteOrderId($orderId), $orderTotal, $orderCurrency);

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
            $newStatus = $this->getOrderStatusFromBillpayState(billpayBase::STATE_CANCELLED);
            $this->addHistoryEntry($orderId, constant('MODULE_PAYMENT_BILLPAY_TEXT_CANCEL_COMMENT'), $newStatus);
            return true;
        }


        /**
         * Sends current order contents to Billpay
         * @param int $orderId
         * @return boolean
         */
        public function reqEditCartContent($orderId)
        {
            /** @noinspection PhpIncludeInspection */
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/ipl_xml_api.php');
            /** @noinspection PhpIncludeInspection */
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/php5/ipl_edit_cart_content_request.php');

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
                    $product['opid'], round($product['qty'], 0),
                    billpayBase::EnsureUTF8($product['name']), '',
                    $price,
                    $price
                );
            }

            $rebate = BillpayOrder::getOTById($orderId, 'ot_discount');
            $rebate = billpayBase::CurrencyToSmallerUnit($rebate) * -1;
            $total = BillpayOrder::getOTById($orderId, 'ot_total');
            $total = billpayBase::CurrencyToSmallerUnit($total);
            $shipping = BillpayOrder::getOTById($orderId, 'ot_shipping');
            $shipping = billpayBase::CurrencyToSmallerUnit($shipping);
            $table = TABLE_ORDERS;
            $orders_id = (int)$orderId;
            $order = BillpayDB::DBFetchRow("SELECT shipping_method, currency FROM $table WHERE orders_id = '$orders_id'");
            $shipping_method = $order['shipping_method'];
            $currency = $order['currency'];
            $req->set_total($rebate, $rebate, $shipping_method, $shipping, $shipping, $total, $total, $currency, $this->getRemoteOrderId($orderId));

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
        public function setOrderBillpayState($billpayStateId, $orderId, $message = '')
        {
            $this->requireLang();
            $messages = array(
                billpayBase::STATE_PENDING     =>  constant('MODULE_PAYMENT_BILLPAY_STATUS_PENDING_DESC'),
                billpayBase::STATE_APPROVED    =>  constant('MODULE_PAYMENT_BILLPAY_STATUS_APPROVED_DESC'),
                billpayBase::STATE_COMPLETED   =>  constant('MODULE_PAYMENT_BILLPAY_STATUS_ACTIVATED_DESC'),
                billpayBase::STATE_CANCELLED   =>  constant('MODULE_PAYMENT_BILLPAY_STATUS_CANCELLED_DESC'),
                billpayBase::STATE_ERROR       =>  constant('MODULE_PAYMENT_BILLPAY_STATUS_ERROR_DESC')
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
        public function setOrderStatus($orderStatusId, $orderId, $message)
        {
            $qry = 'UPDATE ' . TABLE_ORDERS . '
                        SET orders_status = '.(int)$orderStatusId.'
                        WHERE orders_id = ' . (int)$orderId . '
                        LIMIT 1';
            BillpayDB::DBQuery($qry);
            $this->addHistoryEntry($orderId, $message, $orderStatusId);
            return true;
        }


        ##### Abstracts
        /**
         * Returns maximum value of payment; BillPay won't handle transactions higher that it from this merchant.
         * @abstract
         * @return int
         */
        protected function _getStaticLimit($config) {
        //protected function _getStaticLimit() {
            return 0;
        }

        /**
         * Returns minimum value of payment; BillPay won't handle transactions lower than it from this merchant.
         * @abstract
         * @return int
         */
        protected function _getMinValue($config) {
        //protected function _getMinValue() {
            return 0;
        }

        /**
         * Checks if current method allows customers to use it.
         * @abstract
         * @return bool
         */
        protected function _is_b2c_allowed($config) {
        //protected function _is_b2c_allowed() {
            return true;
        }

        /**
         * Checks if current method allows businesses to use it.
         * @abstract
         * @return bool
         */
        protected function _is_b2b_allowed($config) {
        //protected function _is_b2b_allowed() {
            return false;
        }

        /**
         * Type of the payment, defined in IPL_CORE_PAYMENT_TYPE_*
         * @abstract
         * @return int
         */
        protected function _getPaymentType()
        {
            return 0;
        }

        /**
         * Event function that allows you to modify data before standard validation
         * @abstract
         * @param array $data
         * @return array
         */
        public function beforeValidate($data)
        {
            // TODO: it's not used?
            return $data;
        }

        /**
         * Event executed during payment method installation.
         * @abstract
         */
        public function onInstall()
        {

        }

        /**
         * Event executed while checking for plugin configuration keys.
         * @param $config_array
         * @return array
         * @abstract
         */
        public function onKeys($config_array)
        {
            return $config_array;
        }


        /**
         * step for temporary order
         * @return void
         */
        public function payment_action()
        {
            // TODO: it's not used?
            $orderId = $_SESSION['tmp_oID'];

            // persist reference for payment information
            $invoiceReference = $this->generateInvoiceReference($orderId);

            $qry = 'UPDATE billpay_bankdata
                    SET orders_id = ' . $orderId . ',
                        invoice_reference = "' . $invoiceReference . '"
                    WHERE tx_id= "' . self::GetTransactionId().'"
                    LIMIT 1';
            BillpayDB::DBQuery($qry);

            $productIds = $this->_prepareProductMapping($orderId);
            $this->reqUpdateOrder(self::GetTransactionId(), $orderId, $productIds);
            xtc_redirect(xtc_href_link($this->form_action_url, '', 'SSL'));
        }

        /**
         * Process payment method input data (form), before validation
         * @param array $data
         * @return bool
         * @abstract
         */
        public function onMethodInput($data)
        {
            $this->setDateOfBirth(str_replace('.', '-', $data['billpay']['customer_day_of_birth']));
            $this->setGender($data['billpay']['customer_salutation']);
            $this->setPhone($data['billpay']['customer_phone_number']);

            $this->_setDataValue('account_holder', $data['billpay']['account_holder']);
            $this->_setDataValue('account_iban', $data['billpay']['customer_iban']);
            $this->_setDataValue('account_bic', $data['billpay']['customer_bic']);

            if ($this->isPhoneRequired()) {
                if (!$this->getPhone()) {
                    $this->error = MODULE_PAYMENT_BILLPAY_TEXT_ENTER_PHONE;
                    return false;
                }
            }

            if ($this->isDobRequired($data)) {
                if (!$this->isDobValid($this->getDateOfBirth())) {
                    if ($this->getDateOfBirth() === null) {
                        $this->error = MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DOB;
                    } else {
                        $this->error = MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DOB_UNDER;
                    }
                    return false;
                }
            }

            return true;
        }

        /**
         * Process payment method output data (res), before sending request
         * @param ipl_preauthorize_request $req
         * @return ipl_preauthorize_request
         * @abstract
         */
        public function onMethodOutput($req)
        {
            return $req;
        }

        /**
         * Event fired after creating invoice.
         * @param $req
         * @param int $orderId
         * @abstract
         */
        public function onAfterInvoiceCreated($req, $orderId) {

        }

        /**
         * Fired before saving edited order in admin/order_edit
         * @param $orderId
         * @abstract
         */
        public function onSaveEditOrderBefore($orderId)
        {

        }

        /**
         * Event fired after receiving preauthorize response
         * @param ipl_preauthorize_request $req
         */
        public function onPreauthResponse($req) {

        }

        /**
         * Event fired when admin deletes order in backend.
         * @param $orderId
         * @return bool
         */
        public function onOrderDelete($orderId)
        {
            return $this->reqCancel($orderId);
        }


        /**
         * Gambio specific error messages
         * @abstract
         * @param $error
         *
         */
        public function _displayGMerror($error) {}
        // TODO: it's not used?

        ##### GETTERS / SETTERS

        /**
         * Function returns Order Status of selected Billpay State
         * @param $billpayStateId
         * @return int
         */
        public function getOrderStatusFromBillpayState($billpayStateId)
        {
            // support for ORDER_STATUS setting from old plugin versions
            if ($billpayStateId === billpayBase::STATE_APPROVED) {
                $approvedStatus = BillpayBase::GetConfig('MODULE_PAYMENT_' . $this->_paymentIdentifier . '_ORDER_STATUS');
                if ($approvedStatus) {
                    return $approvedStatus;
                }
            }
            $configuration_value = BillpayBase::GetConfig("MODULE_PAYMENT_BILLPAY_STATUS_".$billpayStateId);
            if (!empty($configuration_value)) {
                return $configuration_value;
            }
            $this->_logError('BillPay state '.$billpayStateId.' not found, plugin was not installed?');

            // Fallback
            if ($billpayStateId === billpayBase::STATE_APPROVED) {
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
        private function _getDefaultInstallConfig($key)
        {
            if (empty($this->_defaultConfig[$key])) return '';
            return $this->_defaultConfig[$key];
        }

        /**
         * Returns type of connection to BillPay API
         * @return false|'Testmodus'
         */
        public function getMode() {
            return $this->testmode;
        }

        /**
         * Returns shop's URL i.e. "https://example.shopdomain.com/"
         * @return string
         */
        private function _getShopDomain() {
            return (ENABLE_SSL ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG;
        }


        private function currentCustomerGroupUsesTax() {
            return $_SESSION['customers_status']['customers_status_show_price_tax'] == 1
            || $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1;
        }


        /**
         * Getter for requestTransactionId
         * @return string
         */
        public static function GetTransactionId() {
            return $_SESSION[self::SESSION_TRANSACTION_ID];
        }

        /**
         * Setter for requestTransactionId
         * @param string $transId
         */
        private function _setTransactionId($transId) {
            $_SESSION[self::SESSION_TRANSACTION_ID] = $transId;
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
        private function _getPaymentStatus($req, $config = array())
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
            if (defined('MODULE_PAYMENT_'. billpayBase::PAYMENT_METHOD_PAY_LATER . '_STATUS')
                && constant('MODULE_PAYMENT_' . billpayBase::PAYMENT_METHOD_PAY_LATER . '_STATUS'))
            {
                $config['static_limit_paylater'] = 10000000000;
            }

            return $config;
        }


        private function _getLanguage() {
            if (empty($_SESSION['language_code'])) {
                return 'de';
            }
            return $_SESSION['language_code'];
        }

        /**
         * Returns unique ID of the client using hash, server url and session_id
         * @return string
         */
        public function getCustomerIdentifier() {
            /** @noinspection PhpIncludeInspection */
            require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/ipl_xml_api.php');
            return ipl_create_hash(session_id());
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
        private function _getPrice($valuePrice, $valueTax, $calculateTax = true, $isGrossPrice = true)
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
        private function _getCustomerIp()
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
         * @return string|null
         */
        private function _getCustomerSalutation()
        {
            $customerGender = $this->getGender();
            switch ($customerGender) {
                case 'm':
                    return constant('MODULE_PAYMENT_BILLPAY_SALUTATION_MALE');
                    break;
                case 'f':
                    return constant('MODULE_PAYMENT_BILLPAY_SALUTATION_FEMALE');
                    break;
            }
            return null;
        }

        /**
         * Returns customer id stored in session.
         * @return int|null
         */
        private function _getCustomerId()
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
        private function _getCustomerGroup()
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
         * Returns formatted invoice reference
         * @param $orderID
         * @return string
         */
        public function generateInvoiceReference($orderID) {
            return 'BP' . $orderID . '/' . $this->bp_merchant;
        }

        /**
         * Recoveres variable from data or session
         * @param string $key Variable name
         * @param array|null $data
         * @return null | string
         */
        protected function _getDataValue($key, $data = null) {
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
        protected function _setDataValue($key, $value) {
            $dataIdentifier = $this->_getDataIdentifier($key);
            $_SESSION[$dataIdentifier] = $value;
        }

        /**
         * Gets identifier for current payment method i.e "field" in "payment" is "payment_field"
         * @param string $key
         * @param bool $upper
         * @return string
         */
        private function _getDataIdentifier($key = '', $upper = false) {
            if ($key == '') {
                $dataIdentifier = $this->_paymentIdentifier;
            }
            else {
                $dataIdentifier = $this->_paymentIdentifier.'_'.$key;
            }

            return $upper ? strtoupper($dataIdentifier) : strtolower($dataIdentifier);
        }

        /**
         * Returns billing country code for current order.
         * @return string i.e. "DE"
         */
        protected function _getCountryIso2Code()
        {
            $billing = BillpayOrder::getCustomerBilling();
            return strtoupper($billing['country2']);
        }

        protected function _getCountryIso3Code()
        {
            $billing = BillpayOrder::getCustomerBilling();
            return strtoupper($billing['country3']);
        }


        /**
         * Sets customer date of birth in form and database.
         * @param string $dob
         */
        private function setDateOfBirth($dob)
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
                $_SESSION['customer_dob'] = $dobTimestamp;
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
        private function getDateOfBirth()
        {
            if (!empty($this->_formDob)) {
                return $this->_formDob;
            }
            if (!empty($_SESSION['customer_dob'])) {
                return $_SESSION['customer_dob'];
            }
            if (!empty($_SESSION['customer_id'])) {
                $table = TABLE_CUSTOMERS;
                $customers_id = (int)$_SESSION['customer_id'];
                $dob = BillpayDB::DBFetchValue("SELECT customers_dob FROM $table WHERE customers_id = '$customers_id'");
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
         * Checks if session contains B2B flag.
         * @return bool
         */
        private function isB2B()
        {
            return $this->_getDataValue("b2b");
        }

        /**
         * Checks if customer's dob is valid for ordering.
         * Does not have to be precise. We use it to check if we should allow customer to change it.
         * @param   int     $dobTimestamp
         * @return  bool
         */
        private function isDobValid($dobTimestamp)
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
        private function setGender($gender)
        {
            // $gender = 'herr' | 'frau'
            if (!empty($gender)) {
                $mapping = array(
                    'herr'  =>  'm',
                    'frau'  =>  'f',
                    'm'     =>  'm',
                    'f'     =>  'f',
                );
                if (!isset($mapping[$gender])) {
                    $this->_logDebug('Gender mapping not found for "'.$gender.'".');
                    return;
                }
                $gender = $mapping[$gender];
                $this->_formGender = $gender;
                $_SESSION['customer_gender'] = $gender;
                $customerId = $this->_getCustomerId();
                if ($customerId) {
                    //xtc_db_query("UPDATE ".TABLE_CUSTOMERS." SET customers_gender ='".substr($gender, 0, 1)."' WHERE customers_id = '".(int)$customerId."'");
                }
            }
        }

        /**
         * Returns customer gender from form or database.
         * @return string|null either 'm' of 'f'
         */
        private function getGender()
        {
            if (!empty($this->_formGender)) {
                return $this->_formGender;
            }
            if (!empty($_SESSION['customer_gender'])) {
                return $_SESSION['customer_gender'];
            }
            $customerId = $this->_getCustomerId();
            if (!empty($customerId)) {
                $table = TABLE_CUSTOMERS;
                $customers_id = (int)$customerId;
                $gender = BillpayDB::DBFetchValue("SELECT customers_gender FROM $table WHERE customers_id = '$customers_id'");
                if (empty($gender)) {
                    return null;
                }
                return $gender;
            }
            return null;
        }

        /**
         * Sets EULA confirmation status
         * @param $isChecked
         */
        protected function setEula($isChecked)
        {
            $isChecked = (bool)$isChecked;
            $this->_setDataValue('eula', $isChecked);
        }

        /**
         * Reads eula confirmation status
         * @return bool
         */
        private function getEula()
        {
            return $this->_getDataValue('eula') ? true : false;
        }

        /**
         * Sets customer's phone number.
         * @param string $phone
         */
        private function setPhone($phone)
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
        private function getPhone()
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
        private function getPermanentPhone()
        {
            $phone = null;
            $customerId = $this->_getCustomerId();
            if (empty($customerId) === false) {
                $table = TABLE_CUSTOMERS;
                $customers_id = (int)$customerId;
                $qry = "SELECT customers_telephone AS phone FROM $table WHERE customers_id = '$customers_id' LIMIT 1";
                $phone = BillpayDB::DBFetchValue($qry);
                if (empty($phone)) {
                    $phone = null;
                }
            }
            return $phone;
        }

        /**
         * Saves manual SEPA payment information in order status.
         *      Used by invoice and swiss TC.
         * @param ipl_preauthorize_request $req
         * @param int $orderId
         */
        protected function setManualSEPAPaymentInStatus($req, $orderId)
        {
            $dueDate 			= $req->get_invoice_duedate();
            $dueDateFormatted 	= substr($dueDate,6,2).".".substr($dueDate,4,-2).".".substr($dueDate,0,-4);

            $infoText  = MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER . ": " . $req->get_account_holder() . "\n";
            $infoText .= MODULE_PAYMENT_BILLPAY_TEXT_IBAN . ": " . $req->get_account_number() . "\n";
            $infoText .= MODULE_PAYMENT_BILLPAY_TEXT_BIC . ": " . $req->get_bank_code() . "\n";
            $infoText .= MODULE_PAYMENT_BILLPAY_TEXT_BANK . ": " . $req->get_bank_name() . "\n";
            $infoText .= MODULE_PAYMENT_BILLPAY_TEXT_PURPOSE . ": " . $this->generateInvoiceReference($orderId) . "\n";
            $infoText .= MODULE_PAYMENT_BILLPAY_DUEDATE_TITLE . ": " . $dueDateFormatted;
            $newStatus = $this->getOrderStatusFromBillpayState(billpayBase::STATE_COMPLETED);
            $this->addHistoryEntry($orderId, $infoText, $newStatus);
        }

        /**
         * Returns order's billing country iso code 2
         * Ie. "DE", "AU", "CH"
         * @param $orderId
         * @return mixed
         */
        protected function getOrderCountry2($orderId)
        {
            return BillpayDB::DBFetchValue("SELECT billing_country_iso_code_2 FROM orders WHERE orders_id = '".(int)$orderId."'");
        }

        /**
         * This is configuration function
         * @return bool
         * @abstract
         */
        public function isPhoneRequired()
        {
            return false;
        }

        /**
         * This is configuration function.
         * If invoice b2b -> false
         * else -> true
         * #param $data array $_POST onInput
         * @return bool
         */
        //public function isDobRequired()
        public function isDobRequired($data)
        {
            return true;
        }

        /**
         * Provides better way to recognize which shop modification is used by merchant.
         * @return array
         */
        public function getShopModification()
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

        /**
         * @return bool
         */
        public function isModified()
        {
            $modification = $this->getShopModification();
            return $modification['modification'] == 'xtcmod';
        }

        /**
         * @return bool
         */
        public function isGambio()
        {
            $modification = $this->getShopModification();
            return $modification['modification'] == 'gambio';
        }

        /**
         * Function appends additional prefix to order id to ensure unique order id.
         * Function used by CI.
         *
         * @param $localOrderId
         * @return string
         */
        private function getRemoteOrderId($localOrderId)
        {
            return $this->orderPrefix . $localOrderId;
        }

        /**
         * Executed after order creation.
         * Should display thankYou text.
         * @param $order_id int
         */
        public function onDisplayThankYou($order_id)
        {
            $thankYou = $this->getPaymentInfo($order_id);
            $thankYou = '<div class="bpy-thank-you">'.$thankYou['html'].'</div>';

            // Gambio 2.1
            $_SESSION['nc_checkout_success_info'] = $thankYou;
        }

        /**
         * Returns cart total and shipping.
         * @return array
         */
        private function _getCartBaseAndShipping()
        {
            $shippingAmount = $this->_getTrueShipping();

            $baseAmount = 0;
            $cart = $_SESSION['cart'];
            if ($cart)
            {
                //TODO: Why do we need such a special case for the modified?
                if($this->isModified()) {
                    $baseAmount = (float)$cart->total - $_SESSION['shipping']['cost'];
                } else {
                    $baseAmount = (float)$cart->total;
                }
            }

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
        private function _getTrueShipping()
        {
            $order = $GLOBALS['order'];
            $xtPrice = $GLOBALS['xtPrice'];
            list($shippingBase, ) = explode('_', $_SESSION['shipping']['id']);
            $nettoShipping = $_SESSION['shipping']['cost'];
            $currency_value = $xtPrice->currencies[$_SESSION['currency']]['value'];
            if ($currency_value != 1) {
                $nettoShipping = round($nettoShipping * $currency_value, 2);
            }
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
        private function _getRebateAmount()
        {
            global $order_total_modules;
            $order = new stdClass();
            $order->delivery = array();

            // If we instantiate order_total_origin then the global values are empty.
            // Therefore we want to have the list of all order total classes.
            if (is_null($order_total_modules)) {
                $order_total_modules = $this->getOrderTotalModuleList();
            }

            $ots = $this->_calculate_billpay_totals($order_total_modules, $order, true);
            return $ots['billpayRebateGross'];
        }

        /**
         *
         * @return stdClass
         */
        private function getOrderTotalModuleList()
        {
            $order_total_modules = new stdClass();
            if(defined('MODULE_ORDER_TOTAL_INSTALLED') && xtc_not_null(MODULE_ORDER_TOTAL_INSTALLED)) {
                $order_total_modules->modules = explode(';', MODULE_ORDER_TOTAL_INSTALLED);
            } else {
                $order_total_modules->modules = array();
            }

            return $order_total_modules;
        }

        /**
         * Checks if IBAN is defined.
         * @return bool
         */
        protected function requireIBAN() {
            $required = array(
                'account_holder'    =>  MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_NAME,
                'account_iban'      =>  MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_NUMBER,
            );
            if (strtoupper(substr($this->_getDataValue('account_iban'), 0, 2)) === 'AT') {
                $required['account_bic'] = MODULE_PAYMENT_BILLPAY_TEXT_ERROR_CODE;
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
            return true;
        }

        /**
         * Returns thank you text for selected payment method.
         * @return string
         * @abstract
         */
        public function getThankYouText()
        {
            return '!!!Thank you text not implemented for this method!!!';
        }

        /**
         * Returns "Pay Until" text for selected payment method and country.
         * @return string
         * @abstract
         */
        public function getPayUntilText($bank_data, $currency)
        //public function getPayUntilText()
        {
            return '!!!Pay until text not implemented for this method!!!';
        }

        /**
         * Returns HTML table of payment details for selected order.
         * @param Billpay_Base_Bankdata  $bank_data
         * @param string $currency
         * @return string
         */
        public function getPaymentDetails($bank_data, $currency)
        {
            $data = $this->gatherPaymentDetails($bank_data, $currency);
            return $this->renderPaymentDetails($data);
        }

        /**
         * @param Billpay_Base_Bankdata $bank_data
         * @param string $currency
         * @return array
         */
        public function gatherPaymentDetails($bank_data, $currency)
        {
            $data = array(
                'h_payee'          =>   MODULE_PAYMENT_BILLPAY_TEXT_PAYEE,
                'h_account_holder' =>   MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER,
                'h_iban'           =>   MODULE_PAYMENT_BILLPAY_TEXT_IBAN,
                'h_bic'            =>   MODULE_PAYMENT_BILLPAY_TEXT_BIC,
                'h_bank'           =>   MODULE_PAYMENT_BILLPAY_TEXT_BANK,
                'h_total_amount'   =>   MODULE_PAYMENT_BILLPAY_TEXT_TOTAL_AMOUNT,
                'h_reference'      =>   MODULE_PAYMENT_BILLPAY_TEXT_PURPOSE,
                'h_due_date'       =>   MODULE_PAYMENT_BILLPAY_DUEDATE_TITLE,
            );

            $dueDate = $bank_data->getInvoiceDueDate();
            if (empty($dueDate)) {
                $dueDateFormatted = '';
            } else {
                $dueDateFormatted = substr($dueDate,6,2).".".substr($dueDate,4,-2).".".substr($dueDate,0,-4);
            }
            $data['due_date'] = $dueDateFormatted;
            $data['total_amount'] = $this->renderMoney($bank_data->getTotalAmount()).' '.$currency;
            $data['account_holder'] = $bank_data->getAccountHolder();
            $data['account_number'] = $bank_data->getAccountNumber();
            $data['bank_code'] = $bank_data->getBankCode();
            $data['bank_name'] = $bank_data->getBankName();
            $data['invoice_reference'] = $bank_data->getInvoiceReference();
            return $data;
        }

        /**
         * Removing invoice data as specified here:
         * https://wiki.billpay.wonga.com/display/itdev/%5B1.7%5D+Shop+Invoice+in+plugins
         * @param $data
         * @return $data
         */
        protected function removeAutoSEPADetails($data)
        {
            unset($data['h_reference']);
            unset($data['h_due_date']);

            unset($data['h_payee']);
            unset($data['h_account_holder']);
            unset($data['h_iban']);
            unset($data['h_bic']);
            unset($data['h_bank']);

            return $data;
        }


        private function renderPaymentDetails($data)
        {
            $details = array(
                $data['h_account_holder'] => $data['account_holder'],
                $data['h_iban'] => $data['account_number'],
                $data['h_bic'] => $data['bank_code'],
                $data['h_bank'] => $data['bank_name'],
                $data['h_total_amount'] => $data['total_amount'],
                $data['h_reference'] => $data['invoice_reference'],
                $data['h_due_date'] => $data['due_date'],
            );
            return $details;
        }

        public function renderPaymentDetailsHTML($details)
        {
            $results = array();
            foreach ($details as $header => $value) {
                if (empty($value)) continue;
                if ($header === '!') {
                    $results[] = <<<HEREDOC
    <tr>
        <td colspan=2 style="font-weight: bold; color: red;">{$value}</td>
    </tr>
HEREDOC;
                    continue;
                }
                $header = !empty($header) ? $header.':' : '';
                $results[] = <<<HEREDOC
    <tr>
        <th>{$header}</th>
        <td>{$value}</td>
    </tr>
HEREDOC;
            }
            $results_string = join('', $results);
            return <<<HEREDOC
<table>
{$results_string}
</table>
HEREDOC;
        }

        /**
         * Returns email text displayed in the invoice.
         * @return string
         * @abstract
         */
        public function getEmailText()
        {
            return '!!!Email text not implemented for this method!!!';
        }


        public static function renderMoney($amount)
        {
            return number_format($amount, 2, ',', '');
        }

        public function isActivated($order_id)
        {
            $query = 'SELECT invoice_due_date FROM billpay_bankdata WHERE orders_id = '.(int)$order_id;
            $bank_data = BillpayDB::DBFetchRowNonCached($query);
            return !empty($bank_data['invoice_due_date']);
        }

        /**
         * Injects update checker JS on payments plugin page.
         */
        private function injectUpdateChecker()
        {
            $update_checker_definition = 'BPY_UPDATE_CHECKER';
            if (defined($update_checker_definition)) return;
            if (!preg_match('/modules\.php\?set=payment$/', $_SERVER['REQUEST_URI'])) return;

            define($update_checker_definition, true);
            $shop_data = $this->getShopModification();
            $shop_name = $shop_data['modification'];
            $shop_version = $shop_data['version'];
            $plugin_version = billpayBase::VERSION;
            $update_message = MODULE_PAYMENT_BILLPAY_UPDATE_AVAILABLE;
            $pds_base_url = '//pds.billpay.de';
            $update_widget = <<<HEREDOC
<div id="bpy_update_checker"></div>
<script type="text/javascript">
jQuery.getJSON("$pds_base_url/v1/plugin?shop_name=$shop_name&shop_version=$shop_version&min_version=$plugin_version", function(data) {
  if (data.total_items < 1) return;
  var plugin = data._embedded.plugin[0];
  document.getElementById('bpy_update_checker').innerHTML =
    '<div style="padding: 5px; background-color: rgb(0, 255, 0);">$update_message</div>'
        .replace('%1s$', '$plugin_version')
        .replace('%2s$', plugin.version)
        .replace('%3s$', plugin._links.download.href);
});
</script>
HEREDOC;
            echo $update_widget;
        }

        public function saveOrderId($transaction_id, $order_id)
        {
            $order_id = (int)$order_id;
            $invoiceReference = $this->generateInvoiceReference($order_id);

            $query = 'UPDATE billpay_bankdata
                    SET orders_id = ' . $order_id . ',
                        invoice_reference = "' . $invoiceReference . '"
                    WHERE tx_id= "' . $transaction_id.'"
                    LIMIT 1';
            BillpayDB::DBQuery($query);
        }

        public static function GetConfig($config_key)
        {
            $table = TABLE_CONFIGURATION;
            return BillpayDB::DBFetchValue("SELECT configuration_value FROM $table WHERE configuration_key='$config_key' ");
        }

        /**
         * Function required by XT:C 3
         * @return string
         */
        public function javascript_validation() { return ''; }
    }
}
