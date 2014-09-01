<?php
/**
 * Class wrapping the lookupTable.json
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
 * KiTT_Lookup
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_Lookup
{
    /**
     * @var array
     */
    protected static $lookup = array();

    /**
     * @var KiTT_VFS
     */
    private $_vfs;

    /**
     * @var KiTT_Config
     */
    private $_config;

    /**
     * Create a Lookup object
     *
     * @param KiTT_Config $configuration KiTT_Config implementation
     * @param KiTT_VFS    $vfs           KiTT_VFS object for file system tasks
     */
    public function __construct(KiTT_Config $configuration, KiTT_VFS $vfs)
    {
        $this->_config = $configuration;
        $this->_vfs = $vfs;
        if (self::$lookup === array()) {
            $this->_fromJSON();
        }
    }

    /**
     * Parse a json containing the lookup table.
     *
     * @return void
     */
    private function _fromJSON()
    {
        $uri = $this->_config->get('paths/lookup');
        try {
            $fileContent = $this->_vfs->file_get_contents($uri);
            $temp = json_decode($fileContent, true);
            self::$lookup = $temp['lookupTable'];
        } catch (Exception $e) {
            throw new KiTT_Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Convert supplied currency information to the matching iso-4217 code
     *
     * @param string|int $currency currency iso code
     *
     * @return iso-4217 code
     */
    private function _convertCurrency($currency)
    {
        if (is_numeric($currency)) {
            $currency = KlarnaCurrency::getCode($currency);
        }
        return strtoupper($currency);
    }

    /**
     * Get the default (first specified) language for a country.
     * For countries with one language this will be correct. For countries with
     * several this is used as a tiebreaker and last resort if no language is
     * given when creating a locale.
     *
     * @param string|int $country country iso code
     *
     * @return iso-639-1 language code or null if no match was found.
     */
    public function defaultLanguage($country)
    {
        $country = KiTT_Locale::parseCountry($country);
        $languages = $this->getAllLanguages($country);
        if ($languages === null) {
            return null;
        }
        $lang = array_values($languages);
        return $lang[0];
    }

    /**
     * Get all supported languages for a country.
     *
     * @param string|int $country country iso code
     *
     * @return array containing iso-639-1 language code or null
     *         if no match was found.
     */
    public function getAllLanguages($country)
    {
        $country = KiTT_Locale::parseCountry($country);
        foreach (self::$lookup as $currencies => $countries) {
            if (array_key_exists($country, $countries)) {
                return $countries[$country];
            }
        }
        return null;
    }

    /**
     * Get the currency used in a given country
     *
     * @param string|int $country country iso code
     *
     * @return iso-4217 currency code or null if no match was found.
     */
    public function getCurrency($country)
    {
        $country = KiTT_Locale::parseCountry($country);
        foreach (self::$lookup as $currency => $countries) {
            if (array_key_exists($country, $countries)) {
                return $currency;
            }
        }
        return null;
    }

    /**
     * Get all countries using a specific currency.
     *
     * @param string|int $currency iso-4217 currency code
     *
     * @return array containing iso-3166-alpha-2 codes and
     *         their corresponding languages.
     */
    public function getCountries($currency)
    {
        $currency = $this->_convertCurrency($currency);
        if (array_key_exists($currency, self::$lookup)) {
            return self::$lookup[$currency];
        }
        return null;
    }
}
