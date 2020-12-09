<?php

namespace EasyCredit;

/**
 * Class Config
 *
 * @package EasyCredit
 */
class Config
{
    /**
     * @const string
     */
    const EASYCREDIT_API_HOSTNAME = 'ratenkauf.easycredit.de';

    /**
     * @const string
     */
    const EASYCREDIT_API_ROOT_URI = 'ratenkauf-ws/rest';

    /**
     * @const string
     */
    const RISK_IDENT_URL_HOSTNAME = 'www.cdntool.com';

    /**
     * @const integer
     */
    const EASYCREDIT_API_PORT = 443;

    /**
     * @const string
     */
    const EXAMPLE_CALCULATION_LINK = 'https://ratenkauf.easycredit.de/ratenkauf/content/intern/paymentPageBeispielrechnung.jsf?shopKennung=%s&bestellwert=%s';
    
    /**
     * @const string
     */
    const PAYMENT_PAGE_URL = 'https://ratenkauf.easycredit.de/ratenkauf/content/intern/einstieg.jsf?vorgangskennung=%s';

    /**
     * Minimal valid amount of a shopping basket
     * to be available for payment with EasyCredit.
     * In EUR.
     *
     * @const float
     */
    const MIN_ORDER_AMOUNT = 200.0;

    /**
     * Maximal valid amount of a shopping basket
     * to be available for payment with EasyCredit.
     * In EUR.
     *
     * @const float
     */
    const MAX_ORDER_AMOUNT = 10000.0;
    
    const ALLOWED_ISO2_CODES = ['DE'];


    /**
     * Returns the minimal valid amount of a shopping basket
     * to be available for payment with EasyCredit.
     * In EUR.
     *
     * @return float
     */
    public static function getMinOrderAmount()
    {
        return self::MIN_ORDER_AMOUNT;
    }

    /**
     * Returns the maximal valid amount of a shopping basket
     * to be available for payment with EasyCredit.
     * In EUR.
     *
     * @return float
     */
    public static function getMaxOrderAmount()
    {
        return self::MAX_ORDER_AMOUNT;
    }

    /**
     * Checks whether the given amount is a valid amount of a shopping basket
     * to be available for payment with EasyCredit.
     *
     * @param float $amount
     * @return boolean
     */
    public static function isValidOrderAmount($amount)
    {
        return (($amount >= self::MIN_ORDER_AMOUNT) && ($amount <= self::MAX_ORDER_AMOUNT));
    }
    
    /**
     * checks if payment method is available for given country iso 2 code  
     * 
     * @param string $isoCode
     * @return boolean
     */
    public static function isValidCountryIso2Code($isoCode)
    {
        return in_array(strtoupper($isoCode), self::ALLOWED_ISO2_CODES);
    }
}
