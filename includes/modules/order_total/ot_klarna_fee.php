<?php
/**
 * Invoice Fee Module
 *
 * PHP Version 5.2
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */

require_once DIR_FS_DOCUMENT_ROOT . 'includes/external/klarna/class.KlarnaCore.php';

/**
 * Klarna Order Total (Invoice Fee) module.
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
class ot_klarna_fee
{
    var $title, $output;

    private $_country;
    private $_lang;

    /**
     * @var KlarnaDB
     */
    private $_klarnaDB;

    /**
    * Constructor
    */
    function __construct()
    {
        global $order;
        $this->code = 'ot_klarna_fee';
        $this->_country = $order->delivery['country']['iso_code_2'];
        $this->_utils = new KlarnaUtils($this->_country);
        $this->_lang = KlarnaUtils::getLanguageCode();

        $this->_klarnaDB = new XtcKlarnaDB();
        // Configure KiTT
        $this->_utils->configureKiTT();

        $invFee =
         //~ htmlentities(
            $this->_utils->translate('ot_klarna_title', $this->_lang);
            //~ ENT_NOQUOTES,
            //~ 'ISO-8859-1'
        //~ );

        if (KlarnaConstant::isAdmin()
            && (!array_key_exists('action', $_GET)
            || !in_array($_GET['action'], array('install', 'remove')))
        ) {
            //echo "<link href='" . KlarnaUtils::getStaticPath() .
            //    "images.css' type='text/css' rel='stylesheet'/>";
            echo "<link href='" . DIR_WS_CATALOG .
                "includes/external/klarna/KITT/css/images.css' type='text/css' rel='stylesheet'/>";
            $this->title
                = "<span class='klarna_icon'></span> Klarna - {$invFee}";
        } else {
            $this->title = $invFee;
        }

        $this->description .= $this->_utils->translate(
            'ot_klarna_title', $this->_lang
        );
        $this->description
            .= "<br />All invoice fees should be set in that countries currency";
        $this->enabled = MODULE_ORDER_TOTAL_KLARNA_FEE_STATUS;
        $this->sort_order = MODULE_ORDER_TOTAL_KLARNA_FEE_SORT_ORDER;
        $this->tax_class = MODULE_ORDER_TOTAL_KLARNA_FEE_TAX_CLASS;
        $this->output = array();
    }

    /**
    * Show information
    *
    * @return void
    */
    function process()
    {
        global $order;

        $od_amount = $this->calculateInvoiceFee();

        //Disable module when $od_amount is <= 0
        if ($od_amount <= 0) {
            $this->enabled = false;
            return;
        }

        $formatter = new XtcFormatter;
        $this->output[] = array(
            'title' => $this->title . ':',
            'text' => KITT_String::decode(
                $formatter->formatPrice(
                    $od_amount, KiTT::locale($this->_country)
                ),
                "UTF-8",
                $_SESSION['language_charset']
            ),
            'value' => $od_amount
        );

        $order->info['total'] += $od_amount;
    }


    /**
    * Calculate the invoice fee and add the invoice fee tax to the order total
    * if it has one.
    *
    * @return float
    */
    public function calculateInvoiceFee()
    {
        global $order;

        $payment = $_SESSION['payment'];
        $customer_zone_id = $_SESSION['customer_zone_id'];
        $customer_country_id = $_SESSION['customer_country_id'];
        $currency = $_SESSION['currency'];

        if ($payment !== "klarna_invoice") {
            return 0;
        }

        $fee = $this->_utils->getInvoiceFee();

        if ($fee === 0 || MODULE_ORDER_TOTAL_KLARNA_FEE_TAX_CLASS <= 0) {
            return $fee;
        }

        $showTax = KlarnaConstant::showPriceTax();

        $feeTax = 0;
        $rate = xtc_get_tax_rate(MODULE_ORDER_TOTAL_KLARNA_FEE_TAX_CLASS);
        $feeExclTax = ($fee / ($rate / 100 + 1));
        $feeTax = ($fee - $feeExclTax);

        $tax_desc_prefix = "";
        if ($showTax === true) {
            $tax_desc_prefix = TAX_ADD_TAX;
        } else if (KlarnaConstant::addTaxOT() === true) {
            $tax_desc_prefix = TAX_NO_TAX;
        }

        $tax_desc = xtc_get_tax_description(
            MODULE_ORDER_TOTAL_KLARNA_FEE_TAX_CLASS,
            $customer_country_id, $customer_zone_id
        );

        $order->info['tax_groups'][$tax_desc_prefix . $tax_desc] += $feeTax;
        $order->info['tax'] += $feeTax;

        return  ($showTax === true) ? $fee : $feeExclTax;
    }

    /**
    * Check if module is installed/activated
    *
    * @return int   Installation status
    */
    function check()
    {
        if (!isset($this->check)) {
            $this->check = $this->_klarnaDB->query(
                "SELECT configuration_value FROM " . TABLE_CONFIGURATION .
                " where configuration_key = 'MODULE_ORDER_TOTAL_KLARNA_FEE_STATUS'"
            )->count();
        }

        return $this->check;
    }

    /**
    * Installation function
    *
    * @return void
    */
    function install()
    {
        $this->_klarnaDB->query(
            "INSERT INTO " . TABLE_CONFIGURATION .
            " (sort_order, configuration_key, configuration_value, ".
            "configuration_group_id, set_function, ".
            "date_added) ".
            "VALUES ('0', 'MODULE_ORDER_TOTAL_KLARNA_FEE_STATUS', 'true', '6', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())"
        );

        $this->_klarnaDB->query(
            "INSERT INTO " . TABLE_CONFIGURATION .
            " (sort_order, configuration_key, configuration_value, ".
            "configuration_group_id, set_function, date_added) ".
            "VALUES ('1', 'MODULE_ORDER_TOTAL_KLARNA_FEE_MODE', 'fixed', '6', ".
            "'xtc_cfg_select_option(array(\'fixed\', \'price\'), ', now())"
        );

        foreach (KiTT_CountryLogic::supportedCountries() as $country) {
            $flag = "<span class=\'klarna_flag_" . strtolower($country) . "\'></span>";
            $this->_klarnaDB->query(
                "INSERT INTO " . TABLE_CONFIGURATION .
                " (sort_order, configuration_key, configuration_value, configuration_group_id, date_added) ".
                "VALUES ('2', 'MODULE_ORDER_TOTAL_KLARNA_FEE_FIXED_{$country}', '20', '6', now())"
            );
            $this->_klarnaDB->query(
                "INSERT INTO " . TABLE_CONFIGURATION . " (sort_order, ".
                "configuration_key, configuration_value, configuration_group_id, date_added) ".
                "VALUES ('3', 'MODULE_ORDER_TOTAL_KLARNA_FEE_TABLE_{$country}', '200:20,500:10,10000:5', '6', now())"
            );
        }

        $this->_klarnaDB->query(
            "INSERT INTO " . TABLE_CONFIGURATION . " (sort_order, ".
            "configuration_key, configuration_value, configuration_group_id, use_function, ".
            "set_function, date_added) ".
            "VALUES ('4', 'MODULE_ORDER_TOTAL_KLARNA_FEE_TAX_CLASS', '0', '6', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())"
        );

        $this->_klarnaDB->query(
            "INSERT INTO " . TABLE_CONFIGURATION . " (sort_order, ".
            "configuration_key, configuration_value, configuration_group_id, date_added) ".
            "VALUES ('5', 'MODULE_ORDER_TOTAL_KLARNA_FEE_SORT_ORDER', '35', '6', now())"
        );
    }

    /**
    * Uninstall function
    *
    * @return void
    */
    function remove()
    {
        $uninstaller = new KlarnaUninstaller('ot_klarna');
        $uninstaller->uninstallModule($this->keys());
    }

    /**
    * Constants
    *
    * @return array     constants configured
    */
    function keys()
    {
        $keys = array(
            'MODULE_ORDER_TOTAL_KLARNA_FEE_STATUS',
            'MODULE_ORDER_TOTAL_KLARNA_FEE_MODE',
        );
        foreach (KiTT_CountryLogic::supportedCountries() as $country) {
            $keys[] = "MODULE_ORDER_TOTAL_KLARNA_FEE_FIXED_{$country}";
            $keys[] = "MODULE_ORDER_TOTAL_KLARNA_FEE_TABLE_{$country}";
        }
        $keys[] = 'MODULE_ORDER_TOTAL_KLARNA_FEE_TAX_CLASS';
        $keys[] = 'MODULE_ORDER_TOTAL_KLARNA_FEE_SORT_ORDER';

        return $keys;
    }
}
