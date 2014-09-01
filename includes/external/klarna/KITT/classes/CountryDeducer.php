<?php

/**
 * The Country Deducer
 *
 * PHP Version 5.3
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */

/**
 * KiTT_CountryDeducer
 *
 * Attempts to guess a customers country by ruling out all impossibilities one
 * by one.
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_CountryDeducer implements KiTT_Locator
{
    /**
     * Configuration
     * @var KiTT_Config
     */
    private $_config;

    /**
     * Lookup table
     * @var KiTT_Lookup
     */
    private $_lookup;

    /**
     * Construct a guesstimator
     *
     * @param KiTT_Config $config Configuration object
     * @param KiTT_Lookup $lookup Country/Currency/Language Lookup table object
     *
     * @return void
     */
    public function __construct($config, $lookup)
    {
        $this->_config = $config;
        $this->_lookup = $lookup;
    }

    /**
     * Checks wether a country is a supported sales country.
     *
     * @param string $country ISO-3166-alpha-2 code
     *
     * @return true if the country is supported by the store, false otherwise
     */
    private function _isSalesCountry($country)
    {
        return $this->_config->has("sales_countries/{$country}");
    }

    /**
     * Check a country and return it if it is valid, or null if it is not.
     *
     * @param string $country  iso-3166-alpha-2 code
     * @param string $currency iso-4217 currency code
     *
     * @return iso-3166-alpha-2 code or null if the country is not valid
     */
    private function _getValid($country, $currency)
    {
        if (!$this->_isSalesCountry($country)) {
            return null;
        }

        if (!array_key_exists($country, $this->_lookup->getCountries($currency))) {
            return null;
        }

        return $country;
    }

    /**
     * Get the default country from the config.
     *
     * @return iso-3166-alpha-2 code or null if none is present
     */
    private function _getDefaultCountry()
    {
        try {
            return $this->_config->get('default');
        } catch(KiTT_MissingConfigurationException $e) {
            return null;
        }
    }

    /**
     * Attempt to locate where a user is from based on currency,
     * language and address.
     *
     * @param string     $currency  iso-4217 currency code
     * @param string     $language  iso-639-1 language code
     * @param KlarnaAddr $address   Customers KlarnaAddr object
     * @param string     $ipAddress Customer IPAddress for eventual geoIP lookup
     *
     * @return Country iso-3166-alpha-2 code if a match is found, null
     *          otherwise.
     */
    public function locate(
        $currency = null, $language = null, $address = null, $ipAddress = null
    ) {
        // Polish input to be the expected uppercase and lowercase
        if ($currency !== null) {
            $currency = strtoupper($currency);
        }
        if ($language !== null) {
            $language = strtolower($language);
        }

        // If the currency is not in the list of supported currencies, exit
        if ($this->_lookup->getCountries($currency) === null) {
            return null;
        }

        // If we do have an address, check if it is valid to sell to. If it is,
        // return it, otherwise exit
        if ($address !== null) {
            $country = strtoupper(
                KlarnaCountry::getCode($address->getCountry())
            );
            return $this->_getValid($country, $currency);
        }

        // Get all possible valid candidates based on currency and language.
        $candidates = $this->_getCandidates($currency, $language);

        // If there is only one valid candidate, return it
        if (count($candidates) === 1) {
            return $candidates[0];
        }

        // Check if the default store is a valid candidate, and if it is,
        // return it.
        $default = $this->_getDefaultCountry();
        if ($default !== null && in_array($default, $candidates)) {
            return $default;
        }

        // No match has been found, exit.
        return null;
    }

    /**
     * Get all possible viable candidates based on a currency and language
     *
     * @param string $currency iso-4217 currency code
     * @param string $language iso-639-1 language code
     *
     * @return array containing possible candidates
     */
    private function _getCandidates($currency, $language)
    {
        $candidates = $this->_lookup->getCountries($currency);

        // If there is only one country for this currency we ignore language
        // and just check to see if that country is a sales country.
        if (count($candidates) === 1) {
            $country = array_keys($candidates);
            if (!$this->_isSalesCountry($country[0])) {
                return array();
            }
            return $country;
        }

        // Collect sales countries that support the given language
        $collector = array();
        foreach ($candidates as $country => $languages) {
            if (!$this->_isSalesCountry($country)) {
                continue;
            }
            if (in_array($language, $languages)) {
                $collector[] = $country;
            }
        }
        return $collector;
    }
}
