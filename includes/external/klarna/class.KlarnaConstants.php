<?php
/**
 * Class to handle the OSC constants (that are configured in the backend)
 *
 * PHP Version 5.2
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */

/**
 * Class to handle static constants.
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
class KlarnaConstant
{

    /**
    * return merchant id for specified countrty and payment option.
    *
    * @param string $option  payment method
    * @param string $country country
    *
    * @return int|null
    */
    public static function merchantID($option, $country)
    {
        $eidstring = "";
        switch ($option) {
        case KiTT::PART:
            $eidstring = "MODULE_PAYMENT_KLARNA_PARTPAYMENT_EID_";
            break;
        case KiTT::SPEC:
            $eidstring = "MODULE_PAYMENT_KLARNA_SPECCAMP_EID_";
            break;
        case KiTT::INVOICE:
            $eidstring = "MODULE_PAYMENT_KLARNA_INVOICE_EID_";
            break;
        default:
            return null;
        }

        $eidstring .= strtoupper($country);
        if (defined($eidstring)) {
            return (int) constant($eidstring);
        }
        return null;
    }


    /**
    * return shared secret for specified countrty and payment option.
    *
    * @param string $option  payment method
    * @param string $country country
    *
    * @return string|null
    */
    public static function secret($option, $country)
    {
        $secretstring = "";
        switch ($option) {
        case KiTT::PART:
            $secretstring = "MODULE_PAYMENT_KLARNA_PARTPAYMENT_SECRET_";
            break;
        case KiTT::SPEC:
            $secretstring = "MODULE_PAYMENT_KLARNA_SPECCAMP_SECRET_";
            break;
        case KiTT::INVOICE:
            $secretstring = "MODULE_PAYMENT_KLARNA_INVOICE_SECRET_";
            break;
        default:
            return null;

        }

        $secretstring .= strtoupper($country);
        if (defined($secretstring)) {
            return constant($secretstring);
        }
        return null;
    }

    /**
     * Returns the AGB link
     *
     * @param string $option  payment option
     * @param string $country country
     *
     * @return AGB Link specified in backend, or null if not set.
     */
    public static function agb($option, $country)
    {
        $country = strtoupper($country);
        switch ($option) {
        case KiTT::PART:
            if (defined("MODULE_PAYMENT_KLARNA_PARTPAYMENT_AGB_LINK_{$country}")) {
                return constant(
                    "MODULE_PAYMENT_KLARNA_PARTPAYMENT_AGB_LINK_{$country}"
                );
            }
        case KiTT::SPEC:
            if (defined("MODULE_PAYMENT_KLARNA_SPECCAMP_AGB_LINK_{$country}")) {
                return constant(
                    "MODULE_PAYMENT_KLARNA_SPECCAMP_AGB_LINK_{$country}"
                );
            }
        case KiTT::INVOICE:
            if (defined("MODULE_PAYMENT_KLARNA_INVOICE_AGB_LINK_{$country}")) {
                return constant(
                    "MODULE_PAYMENT_KLARNA_INVOICE_AGB_LINK_{$country}"
                );
            }
        }
        return "";
    }

    /**
     * Get the mode for the given payment option.
     *
     * @param string $option invoice, spec or part
     *
     * @return int Klarna::LIVE or Klarna::BETA
     */
    public static function mode($option)
    {
        switch ($option) {
        case KiTT::PART:
             return (defined('MODULE_PAYMENT_KLARNA_PARTPAYMENT_LIVEMODE') && strtolower(MODULE_PAYMENT_KLARNA_PARTPAYMENT_LIVEMODE) == "true") 
                ? Klarna::LIVE : Klarna::BETA;
        case KiTT::SPEC:
            return (defined('MODULE_PAYMENT_KLARNA_SPECCAMP_LIVEMODE') && strtolower(MODULE_PAYMENT_KLARNA_SPECCAMP_LIVEMODE) == "true") 
                ? Klarna::LIVE : Klarna::BETA;
        case KiTT::INVOICE:
             return (defined('MODULE_PAYMENT_KLARNA_INVOICE_LIVEMODE') && strtolower(MODULE_PAYMENT_KLARNA_INVOICE_LIVEMODE) == "true") 
                ? Klarna::LIVE : Klarna::BETA;
        default:
            return null;
        }
    }


    /**
     * Check to see if given country is activated for given payment option.
     *
     * @param string $option  payment option
     * @param string $country country
     *
     * @return boolean
     */
    public static function isActivated($option, $country)
    {
        $_activated = self::getActivated($option);

        return in_array(
            strtoupper($country),
            $_activated
        );
    }

    /**
     * retrieve the URI for pclass storage.
     *
     * @return array
     */
    public static function pcURI()
    {
        return array(
            'user' => DB_SERVER_USERNAME,
            'passwd' => DB_SERVER_PASSWORD,
            'dsn' => DB_SERVER,
            'db' => DB_DATABASE,
            'table' => 'klarna_pclasses'
        );
    }

    /**
     * get activated countries for a specific payment option as an array
     *
     * @param string $option payment option
     *
     * @return array
     */
    public static function getActivated($option)
    {
        switch (strtolower($option)) {
        case KiTT::PART:
            return explode(
                ",",
                 (defined('MODULE_PAYMENT_KLARNA_PARTPAYMENT_ALLOWED')?MODULE_PAYMENT_KLARNA_PARTPAYMENT_ALLOWED:'') 
            );
        case KiTT::SPEC:
            return explode(
                ",",
                 (defined('MODULE_PAYMENT_KLARNA_SPECCAMP_ALLOWED')?MODULE_PAYMENT_KLARNA_SPECCAMP_ALLOWED:'') 
            );
        case KiTT::INVOICE:
            return explode(
                ",",
                (defined('MODULE_PAYMENT_KLARNA_INVOICE_ALLOWED')?MODULE_PAYMENT_KLARNA_INVOICE_ALLOWED:'') 
            );
        default:
            return array();
        }
    }

    /**
     * Check if logged in as admin
     *
     * @return true if logged in as admin
     */
    public static function isAdmin()
    {
        return (defined('DIR_WS_ADMIN'));
    }

    /**
     * Assert that the current shop installation is the legacy version 2.2rc2a
     *
     * @return bool
     */
    public static function isLegacyShop()
    {
        $version = str_replace(" ", "", strtolower(PROJECT_VERSION));
        return strpos($version, "2.2") !== false;
    }


    /**
    * Checks if the module setting is set to enabled.
    *
    * @param string $option payment option
    *
    * @return boolean  true if the specified payment option is enabled
    */
    public static function isEnabled($option)
    {
        switch ($option) {
        case KiTT::PART:
           return (defined('MODULE_PAYMENT_KLARNA_PARTPAYMENT_STATUS')?strtolower(MODULE_PAYMENT_KLARNA_PARTPAYMENT_STATUS) == 'true':false); 
        case KiTT::SPEC:
            return (defined('MODULE_PAYMENT_KLARNA_SPECCAMP_STATUS')?strtolower(MODULE_PAYMENT_KLARNA_SPECCAMP_STATUS) == 'true':false); 
        case KiTT::INVOICE:
            return (defined('MODULE_PAYMENT_KLARNA_INVOICE_STATUS')?strtolower(MODULE_PAYMENT_KLARNA_INVOICE_STATUS) == 'true':false); 
        default:
            return false;
        }
    }

    /**
     * Get the instanced payment modules configured orderstatus id.
     *
     * @param string $option payment option
     *
     * @return int
     */
    public static function getOrderStatusId($option)
    {
        $id = 0;
        switch ($option) {
        case KiTT::INVOICE:
            $id = MODULE_PAYMENT_KLARNA_INVOICE_ORDER_STATUS_ID;
            break;
        case KiTT::PART:
            $id = MODULE_PAYMENT_KLARNA_PARTPAYMENT_ORDER_STATUS_ID;
            break;
        case KiTT::SPEC:
            $id = MODULE_PAYMENT_KLARNA_SPECCAMP_ORDER_STATUS_ID;
            break;
        }

        if ((int)$id > 0) {
            return $id;
        }

        return DEFAULT_ORDERS_STATUS_ID;
    }

    /**
     * Get the instanced payment modules configured pending orderstatus id.
     *
     * @param string $option payment option
     *
     * @return int
     */
    public static function getPendingOrderStatusId($option)
    {
        $id = 0;
        switch ($option) {
        case KiTT::INVOICE:
            $id = MODULE_PAYMENT_KLARNA_INVOICE_ORDER_STATUS_PENDING_ID;
            break;
        case KiTT::PART:
            $id = MODULE_PAYMENT_KLARNA_PARTPAYMENT_ORDER_STATUS_PENDING_ID;
            break;
        case KiTT::SPEC:
            $id = MODULE_PAYMENT_KLARNA_SPECCAMP_ORDER_STATUS_PENDING_ID;
            break;
        }

        if ((int)$id > 0) {
            return $id;
        }

        return DEFAULT_ORDERS_STATUS_ID;
    }

    /**
     * Get the show price with tax settings from the customer status session
     *
     * @return bool
     */
    public static function showPriceTax()
    {
        return (bool) $_SESSION['customers_status']
            ['customers_status_show_price_tax'];
    }

    /**
     * Get the add tax to order total tax settings from the customer status session
     *
     * @return bool
     */
    public static function addTaxOT()
    {
        return (bool) $_SESSION['customers_status']['customers_status_add_tax_ot'];
    }

    /**
     * Get the KiTT constant for a payment code
     *
     * @param string $paymentCode payment module code
     *
     * @return string
     */
    public static function getKiTTOption($paymentCode)
    {
        switch (strtolower($paymentCode)) {
        case 'klarna_partpayment':
            return KiTT::PART;
        case 'klarna_speccamp':
            return KiTT::SPEC;
        case 'klarna_invoice':
            return KiTT::INVOICE;
        }
        return '';
    }

}
