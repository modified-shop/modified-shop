<?php
/**
 * Base module extended by all payment options.
 *
 * PHP Version 5.2
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */

require_once 'class.KlarnaCore.php';

/**
 * Klarna Base Module
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
class KlarnaBase
{
    /**
     *
     * @var Klarna
     */
    private $_klarna;

    /**
     * The merchant it
     *
     * @var int
     */
    private $_eid;

    /**
     * The secret for merchant
     *
     * @var string
     */
    private $_secret;

    /**
     * The address object for the customer
     *
     * @var KlarnaAddr
     */
    private $_addrs;

    /**
     * The Klarna Standard Register API
     *
     * @var _checkout
     */
    private $_checkout;

    /**
     * Klarna data as an associative array
     * @var array
     */
    private $_klarna_data;

    /**
     * KlarnaUtils object
     */
    private $_utils;

    /**
     * Klarna Payment Option
     */
    private $_option;

    /**
     * KiTT_Locale
     */
    private $_locale;

    /**
     * ensure javascript only runs once
     *
     * @var boolean
     */
    private static $_hasRun;

    /**
     * @var KlarnaDB
     */
    private $_klarnaDB;

    /**
     * The constructor
     *
     * @param string $option 'part', 'spec' or 'invoice'
     *
     * @return void
     */
    public function __construct($option)
    {
        $this->_klarnaDB = new XtcKlarnaDB();

        $currency = $_SESSION['currency'];

        $this->api_version = KlarnaCore::getCurrentVersion();
        $this->jQuery = false;
        $this->enabled = true;

        $this->_option = $option;

        $country = KlarnaUtils::deduceCountry($option);
        $lang = KlarnaUtils::getLanguageCode();

        $this->_country = $country;

        $this->_utils = new KlarnaUtils($country);

        $this->_locale = KiTT::locale($country, $lang, $currency);
        
        $css = '';
        if (KlarnaConstant::isAdmin()
            && (!array_key_exists('action', $_GET)
            || !in_array($_GET['action'], array('install', 'remove', 'removeconfirm')))
        ) {
            //$css = "<link href='" . KlarnaUtils::getStaticPath() .
            //"images.css' type='text/css' rel='stylesheet'/>";
            $css = "<link href='" . DIR_WS_CATALOG .
            "includes/external/klarna/KITT/css/images.css' type='text/css' rel='stylesheet'/>";
            $this->_checkForLatestVersion();
            $this->description = $this->_buildDescription();
        }

        //Set the title for the payment method. This will be displayed on the
        //confirmation page and the backend order view.
        $this->title = $this->_title() . $css;

        $merchantID = KlarnaConstant::merchantID($option, $country);
        $secret = KlarnaConstant::secret($option, $country);

        if (!$merchantID || !$secret) {
            $this->enabled = false;
            return;
        }

        try {
            $this->setupModule();
        } catch (KiTT_Exception $e) {
            if (!KlarnaConstant::isAdmin()) {
                $this->enabled == false;
            }
        }
    }

    /**
     * Setup the payment module for usage.
     *
     * @return void
     */
    protected function setupModule()
    {
        global $order;

        KlarnaUtils::configureKlarna($this->_option);
        if ($this->_country === null) {
            return;
        }
        // Pass the api instance to the utils object.
        $this->_utils->setApi(KiTT::api($this->_locale->getCountry()));

        $this->_totalSum = $this->_utils->getCartSum();

        $this->order_status = KlarnaConstant::getOrderStatusId($this->_option);

        // if order is an object instead of an array, we're returning after
        // a purchase. Then we want to call update_status.
        if (is_object($order)) {
            $this->update_status();
        }

        $this->form_action_url = xtc_href_link(
            FILENAME_CHECKOUT_PROCESS, '', 'SSL', false
        );
    }

    /**
     * Update Status
     *
     * @return void
     */
    public function updateStatus()
    {
        global $order;

        if ($this->_isInvoice()) {
            $zone = (int) MODULE_PAYMENT_KLARNA_INVOICE_ZONE;
        } else if ($this->_isPart()) {
            $zone = (int) MODULE_PAYMENT_KLARNA_PARTPAYMENT_ZONE;
        } else if ($this->_isSpec()) {
            $zone = (int) MODULE_PAYMENT_KLARNA_SPECCAMP_ZONE;
        }

        if ( $this->enabled == true && $zone > 0) {
            $check_flag = false;
            $check_query = $this->_klarnaDB->query(
                "select zone_id from " .
                TABLE_ZONES_TO_GEO_ZONES .
                " where geo_zone_id = '" .
                $zone .
                "' and zone_country_id = '" .
                $order->delivery['country']['id'] .
                "' order by zone_id"
            );

            while ($check = $check_query->getArray()) {
                if ($check['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($check['zone_id'] == $order->delivery['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }

            if ($check_flag == false) {
                $this->enabled = false;
            }
        }
    }

    /**
     * This function outputs the payment method title/text and if required, the
     * input fields.
     *
     * @return array Data to present in page
     */
    public function selection()
    {
        global $order, $customer_id, $currencies;

        $co = KiTT::checkoutController($this->_locale, $this->_totalSum);
        $view = $co->getOption($this->_option);

        // Update the enabled variable that xtCommerce needs.
        $this->enabled = $this->_isEnabled($view);

        if (!$this->enabled) {
            return;
        }

        // Add CSS and Javascript just once.
        $css = $script = '';
        if (!self::$_hasRun) {
            $templateLoader = KiTT::templateLoader($this->_locale);
            $cssLoader = $templateLoader->load('css.mustache');
            $jsLoader = $templateLoader->load('javascript.mustache');
            $merchantID = KlarnaConstant::merchantID(
                $this->_option,
                $this->_country
            );

            $styles = array(
                //EXTERNAL_KITT . "res/v1.1/checkout.css?eid=" . $merchantID,
                DIR_WS_BASE."includes/external/klarna/KITT/css/checkout.css",
                DIR_WS_BASE."includes/modules/payment/klarna/checkout/style.css",
                DIR_WS_BASE."includes/external/klarna/template/css/xtcstyle.css"
            );

            self::$_hasRun = true;
            $script = $jsLoader->render(
                array(
                    "scripts" => array(
                        EXTERNAL_KITT . "core/v1.0/js/klarna.min.js",
                        EXTERNAL_KITT . "res/v1.1/js/klarna.lib.min.js",
                        EXTERNAL_KITT . "res/v1.1/js/checkout.min.js"
                    )
                )
            );

            $css = $cssLoader->render(array('styles' => $styles));
        }

        KiTT::configuration()->set(
            'agb_link', KlarnaConstant::agb($this->_option, $this->_country)
        );

        $fee = $this->_utils->getInvoiceFee();

        if (KlarnaConstant::showPriceTax() === false
            && MODULE_ORDER_TOTAL_KLARNA_FEE_TAX_CLASS > 0
        ) {
            $feeRate = xtc_get_tax_rate(MODULE_ORDER_TOTAL_KLARNA_FEE_TAX_CLASS);
            $fee = ($fee / ($feeRate / 100 + 1));
        }

        $view->setPaymentFee(round($fee, 2));
        $view->setPaymentId($this->code);

        // Have we returned from a failed purchase?
        if ($this->_utils->getErrorOption() == $this->_option) {
            $view->setError(html_entity_decode($this->_utils->getError()));
            $this->_utils->clearErrors();
            if ($this->_option == KiTT::PART
                && array_key_exists('klarna_paymentPlan', $_SESSION)
            ) {
                $view->selectPClass(intval($_SESSION['klarna_paymentPlan']));
            }
        }

        $this->_klarna_data = $this->_utils->collectKlarnaData($order);
        $this->_klarna_data['country'] = $this->_country;

        if (isset($_SESSION['klarna_data']) || isset(KlarnaUtils::$prefillData)) {
            $this->_klarna_data = array_merge(
                $this->_klarna_data,
                $this->_utils->getValuesFromSession()
            );
        }

        $view->prefill($this->_klarna_data);

        return array(
            'id' => $this->code,
            'module' => KITT_String::decode($view->getTitle(), "UTF-8", $_SESSION['language_charset']),
            'module_cost' => $view->getExtra(),
            'fields' => array(
                array(
                    'title' => $script.$css,
                    'field' => $view->show()
                )
            )
        );
    }

    /**
     * This function implements any checks of any conditions after payment
     * method has been selected.
     *
     * @return void
     */
    public function preConfirmationCheck()
    {
        global $order;

        $addressHandler = new KlarnaAddressXtc;

        $this->_utils->cleanPost();

        $this->_addrs = $this->_utils->handlePost($this->_option);

        if ($this->_isPart() || $this->_isSpec()) {
            $this->_paymentPlan = $_SESSION['klarna_paymentPlan']
                = (int)$_POST["klarna_{$this->_option}_paymentPlan"];
        } else {
            $this->_paymentPlan = -1;
        }

        $order->delivery = array_merge(
            $order->delivery, $addressHandler->klarnaAddrToXtcAddr(
                $this->_addrs
            )
        );

        if (KiTT_CountryLogic::shippingSameAsBilling($this->_country)) {
            $order->billing = $order->delivery;
        }

        $_SESSION['klarna_data']['serial_addr'] = serialize($this->_addrs);

    }

    /**
     * Implements any checks or processing on the order information before
     * proceeding to payment confirmation.
     *
     * @return array
     */
    public function confirmation()
    {
        $country = strtolower($this->_locale->getCountryCode());
        $desc = '';
        $type = '';

        switch ($this->_option) {
        case KiTT::INVOICE:
            $type = 'invoice';
            break;
        case KiTT::PART:
            $type = 'account';
            break;
        case KiTT::SPEC:
            $type = 'special';
            $desc = '<br>' . KiTT::api($country)->getPClass(
                $_POST['klarna_spec_paymentPlan']
            )->getDescription();
            break;
        default:
            return array('title' => '');
        }

        $cdn = KlarnaUtils::getStaticPath();
        $kitt_css = DIR_WS_CATALOG . "includes/external/klarna/KITT/css/";
        $title = <<<TITLE
<link href='{$kitt_css}images.css' type='text/css' rel='stylesheet'/>
<a href='http://www.klarna.com' target='_blank'>
    <div class='klarna_payment_info'>
        <span class='klarna_logo_{$type}_{$country}'></span>
    </div>
</a>{$desc}
TITLE;
        return array('title' => $title);
    }

    /**
     * Outputs the html form hidden elements sent as POST data to the payment
     * gateway.
     *
     * @return string
     */
    public function processButton()
    {
        global $order;

        $shipping = $_SESSION['shipping'];

        $invoiceType = $_POST["klarna_{$this->_option}_invoice_type"];
        $reference = $_POST["klarna_{$this->_option}_reference"];

        $process_button_string = $this->_utils->hiddenFieldString(
            $this->_addrs,
            $invoiceType,
            $this->_paymentPlan,
            $order->customer['email_address'],
            $reference
        );

        if ($this->_addrs->isCompany) {
            $process_button_string .= xtc_draw_hidden_field(
                'klarna_fname',
                $order->delivery['firstname']
            ) . xtc_draw_hidden_field(
                'klarna_lname',
                $order->delivery['lastname']
            );
        } else {
            $process_button_string .= xtc_draw_hidden_field(
                'klarna_fname',
                $this->_addrs->getFirstName()
            ) . xtc_draw_hidden_field(
                'klarna_lname',
                $this->_addrs->getLastName()
            );
        }

        $_SESSION['klarna_ot'] = $this->_utils->getOrderTotal();

        $process_button_string .= xtc_draw_hidden_field(
            xtc_session_name(),
            xtc_session_id()
        );

        return $process_button_string;
    }

    /**
     * Build the cart and do the actual call to Klarna Online
     *
     * @return void
     */
    public function beforeProcess()
    {
        global $order;

        $customer_id = $_SESSION['customer_id'];

        $this->_paymentPlan = $_POST['klarna_paymentPlan'];

        $this->_utils->buildCart(
            $customer_id,
            $order,
            $this->_option,
            $this->code,
            $this->_paymentPlan
        );

        $this->_addrs = unserialize($_SESSION['klarna_data']['serial_addr']);

        $this->_utils->performReservation(
            $this->_paymentPlan,
            $this->_addrs,
            $this->_option
        );
    }

    /**
     * Update order comments
     *
     * @return false
     */
    public function afterProcess()
    {
        global $order;

        $status = strtolower($_SESSION['klarna_orderstatus']);

        $customer = KlarnaConstant::getOrderStatusId($this->_option);

        $order->info['order_status'] = $customer;

        // Set the order status id for our pending status to -1  to prevent it
        // from being shown to customers.
        $this->_utils->updateOrderDatabase($customer, -1);

        return false;
    }

    /**
     * get error message.
     *
     * @return array     error title and error message
     */
    public function getError()
    {
        $error = $this->_utils->getError();
        return array(
            'title' => html_entity_decode(
                KiTT::translator($this->_locale)->translate('error_klarna_title')
            ),
            'error' => $error
        );
    }

    /**
     * Check if module is enabled
     *
     * @return int   1 if enabled
     */
    public function check()
    {
        $key = '';
        if ($this->_isInvoice()) {
            $key = 'MODULE_PAYMENT_KLARNA_INVOICE_STATUS';
        } else if ($this->_isPart()) {
            $key = 'MODULE_PAYMENT_KLARNA_PARTPAYMENT_STATUS';
        } else if ($this->_isSpec()) {
            $key = 'MODULE_PAYMENT_KLARNA_SPECCAMP_STATUS';
        }
        if (!isset($this->_check)) {
            $this->_check = $this->_klarnaDB->query(
                "SELECT configuration_value FROM " .
                TABLE_CONFIGURATION .
                " WHERE configuration_key = " .
                "'{$key}'"
            )->count();
        }
        return $this->_check;
    }

    /**
     * Install script
     *
     * @return void
     */
    public function install()
    {

        $installer = new KlarnaInstaller($this->_option);

        $installer->installPaymentModule();
    }

    /**
     * Uninstall script
     *
     * @return void
     */
    public function remove()
    {
        $unInstaller = new KlarnaUninstaller($this->_option);
        $unInstaller->uninstallModule($this->keys());
    }

    /**
     * Get/Show information about pclasses for the admin.
     *
     * @return void
     */
    protected function adminInfo()
    {
        $filename = explode('?', basename($_SERVER['REQUEST_URI'], 0));
        if ($filename[0] == "modules.php") {
            $this->_utils->checkForPClasses($this->_option);
            if ($_GET['view_pclasses'] == true
                || $_GET['get_pclasses'] == true
            ) {
                $eid_array = $this->_utils->prepareFetch($this->_option);
                $this->_utils->showPClasses($eid_array, $this->_option);
            }
        }
    }

    /**
     * Check if the instance of this object is for the invoice module.
     *
     * @return boolean
     */
    private function _isInvoice()
    {
        return ($this->_option == KiTT::INVOICE);
    }

    /**
     * Check if the instance of this object is for the special campaigns module.
     *
     * @return boolean
     */
    private function _isSpec()
    {
        return $this->_option == KiTT::SPEC;
    }

    /**
     * Check if the instance of this object is for the part payment module.
     *
     * @return boolean
     */
    private function _isPart()
    {
        return $this->_option == KiTT::PART;
    }

    /**
     * Build the apropriate description for the admin page.
     *
     * @return string
     */
    private function _buildDescription()
    {
        $data = array();
        if ($this->_isInvoice()) {
            $data['desc'] = KiTT::translator($this->_locale)->translate(
                'INVOICE_TEXT_DESCRIPTION'
            );
        } elseif ($this->_isPart()) {
            $data['desc'] = KiTT::translator($this->_locale)->translate(
                'PARTPAY_TEXT_DESCRIPTION'
            );
        } elseif ($this->_isSpec()) {
            $data['desc'] = KiTT::translator($this->_locale)->translate(
                'SPEC_TEXT_DESCRIPTION'
            );
        } else {
            return '';
        }

        $data['version'] = KlarnaCore::getCurrentVersion();

        if (KlarnaConstant::isAdmin()
            && KlarnaConstant::isEnabled(
                $this->_option, $this->_country
            )
            && ($this->_isPart() || $this->_isSpec())
        ) {
            $data['code'] = $this->code;
            $data['pclasses'] = true;
        }

        $templateLoader = KiTT::templateLoader($this->_locale);
        return $templateLoader->load('description.mustache')->render($data);
    }

    /**
     * Check for latest version.
     *
     * @return void
     */
    private function _checkForLatestVersion()
    {
        if (($this->_isInvoice()
            && strtolower(MODULE_PAYMENT_KLARNA_INVOICE_LATESTVERSION) == 'true')
            || ($this->_isSpec()
            && strtolower(MODULE_PAYMENT_KLARNA_SPECCAMP_LATESTVERSION) == 'true')
            || ($this->_isPart()
            && strtolower(MODULE_PAYMENT_KLARNA_PARTPAYMENT_LATESTVERSION) == 'true')
        ) {
            $this->_utils->checkForLatestVersion();
        }
    }

    /**
     * Build and return the title for the admin backend.
     *
     * @return string
     */
    private function _title()
    {
        $tulip = '';
        if (KlarnaConstant::isAdmin()) {
            $tulip = '<span class="klarna_icon"> </span>';
        }

        if ($this->_isInvoice()) {
            return $tulip . KiTT::translator($this->_locale)->translate(
                'MODULE_INVOICE_TEXT_TITLE'
            );
        }
        if ($this->_isPart()) {
            return $tulip . KiTT::translator($this->_locale)->translate(
                'MODULE_PARTPAY_TEXT_TITLE'
            );
        }
        if ($this->_isSpec()) {
            return $tulip . KiTT::translator($this->_locale)->translate(
                'MODULE_SPEC_TEXT_TITLE'
            );
        }
    }

    /**
     * Check if the instanced payment module should be shown.
     *
     * @param KiTT_Payment_Option $view KiTT Payment Option instance
     *
     * @return boolean
     */
    private function _isEnabled($view)
    {
        return (
            KlarnaConstant::isActivated(
                $this->_option, $this->_country
            ) && KlarnaConstant::isEnabled(
                $this->_option, $this->_country
            ) && $view->isAvailable()
        );
    }
}
