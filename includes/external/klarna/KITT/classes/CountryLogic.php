<?php
/**
 * Country Logic
 *
 * PHP Version 5.2
 *
 * @category Payment
 * @package  KiTT
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */

/**
 * Class for country logic
 *
 * @category Payment
 * @package  KiTT
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
class KiTT_CountryLogic
{

    /**
     * Check if shipping and billing should be the same
     *
     * @param string $country country
     *
     * @return boolean
     */
    public static function shippingSameAsBilling($country)
    {
        $country = strtoupper($country);
        switch ($country) {
        case 'NL':
        case 'DE':
            return true;
        default:
            return false;
        }
    }

    /**
     * Check if consent is needed
     *
     * @param string $country country
     *
     * @return boolean
     */
    public static function needConsent($country)
    {
        $country = strtoupper($country);
        switch ($country) {
        case 'DE':
        case 'AT':
            return true;
        default:
            return false;
        }
    }

    /**
     * Check if an asterisk is needed
     *
     * @param string $country country
     *
     * @return boolean
     */
    public static function needAsterisk($country)
    {
        return false;
    }

    /**
     * Check if gender is needed
     *
     * @param string $country country
     *
     * @return boolean
     */
    public static function needGender($country)
    {
        $country = strtoupper($country);
        switch ($country) {
        case 'NL':
        case 'DE':
        case 'AT':
            return true;
        default:
            return false;
        }
    }

    /**
     * Check if date of birth is needed
     *
     * @param string $country country
     *
     * @return boolean
     */
    public static function needDateOfBirth($country)
    {
        $country = strtoupper($country);
        switch ($country) {
        case 'NL':
        case 'DE':
        case 'AT':
            return true;
        default:
            return false;
        }
    }

    /**
     * Return the fields a street should be split into.
     *
     * @param string $country country
     *
     * @return array
     */
    public static function getSplit($country)
    {
        $country = strtoupper($country);
        switch ($country) {
        case 'DE':
            return array('street', 'house_number');
        case 'NL':
            return array('street', 'house_number', 'house_extension');
        default:
            return array('street');
        }
    }

    /**
     * Is the sum below the limit allowed for the given country?
     *
     * @param string $country country
     * @param float  $sum     sum to check
     *
     * @return boolean
     */
    public static function isBelowLimit($country, $sum)
    {
        $country = strtoupper($country);
        return ($country !== 'NL' || ((double)$sum) <= 250.0);
    }

    /**
     * Get a list of supported countries
     *
     * @return array
     */
    public static function supportedCountries()
    {
        return array('SE', 'DK', 'NO', 'FI', 'NL', 'DE', 'AT');
    }

    /**
     * Is an AGB link needed?
     *
     * @param string $country country
     *
     * @return boolean
     */
    public static function needAGB($country)
    {
        $country = strtoupper($country);
        switch ($country) {
        case 'DE':
        case 'AT':
            return true;
        default:
            return false;
        }
    }

    /**
     * Do we need to call getAddresses
     *
     * @param string $country country
     *
     * @return boolean
     */
    public static function useGetAddresses($country)
    {
        $country = strtoupper($country);
        switch ($country) {
        case 'SE':
            return true;
        default:
            return false;
        }
    }

    /**
     * Are Company Purchases supported?
     *
     * @param string $country country
     *
     * @return boolean
     */
    public static function isCompanyAllowed($country)
    {
        $country = strtoupper($country);
        switch ($country) {
        case 'NL':
        case 'DE':
        case 'AT':
            return false;
        default:
            return true;
        }
    }
}
