<?php
/**
 * Localisation
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
 * Represent a locale with country, currency and language
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_Locale
{
    /**
     * Country constant
     *
     * @var int
     */
    private $_country;

    /**
     * Language constant
     *
     * @var int
     */
    private $_language;

    /**
     * Currency constant
     *
     * @var int
     */
    private $_currency;

    /**
     * Creates a locale
     *
     * @param string|int $country  country of the locale
     * @param string|int $language language of the locale (optional)
     * @param string|int $currency currency of the locale (optional)
     */
    public function __construct(
        $country, $language = null, $currency = null
    ) {
        $this->_country = self::parseCountry($country);

        // Set language from user input or from country default
        if (is_numeric($language)) {
            $language = KlarnaLanguage::getCode($language);
        }
        $this->_language = $language;

        // Set currency from user input or from country default
        if (is_numeric($currency)) {
            $currency = KlarnaCurrency::getCode($currency);
        }
        $this->_currency = $currency;
    }

    /**
     * Parse a valid Klarna country to uppercase iso alpha code
     *
     * @param string|int $country KlarnaCountry
     *
     * @return string|null
     */
    public static function parseCountry($country)
    {
        if (is_numeric($country)) {
            $country = KlarnaCountry::getCode($country);
            if ($country !== null) {
                return strtoupper($country);
            }
        }
        if (KlarnaCountry::fromCode($country) !== null) {
            return strtoupper($country);
        }
        return null;
    }

    /**
     * Parses a regular like locale string like en_US
     *
     * @param string $locale a locale string like 'sv_SE'
     *
     * @return KiTT_Locale
     */
    public static function parse($locale)
    {
        $collection = null;
        $result = preg_match('/([a-z]{2})_([A-Z]{2})/', $locale, $collection);
        if ($result !== 0) {
            return new KiTT_Locale($collection[2], $collection[1]);
        }
        throw new KiTT_InvalidLocaleException("Invalid locale string: {$locale}");
    }

    /**
     * get the country of the locale
     *
     * @return int country constant
     */
    public function getCountry()
    {
        return KlarnaCountry::fromCode($this->_country);
    }

    /**
     * Return the country code (iso-alpha-2)
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->_country;
    }

    /**
     * get the language of the locale
     *
     * @return int language constant
     */
    public function getLanguage()
    {
        return KlarnaLanguage::fromCode($this->_language);
    }

    /**
     * get the language ISO code
     *
     * @return string
     */
    public function getLanguageCode()
    {
        return $this->_language;
    }

    /**
     * get the currency of the locale
     *
     * @return int currency constant
     */
    public function getCurrency()
    {
        return KlarnaCurrency::fromCode($this->_currency);
    }
    /**
     * get the currency of the locale
     *
     * @return int currency constant
     */
    public function getCurrencyCode()
    {
        return $this->_currency;
    }

    /**
     * Serializes to a regular locale string
     *
     * @return a locale string like 'sv_SE'
     */
    public function __toString()
    {
        $country = $this->_country ? $this->_country : 'XX';
        $language = $this->_language ? $this->_language : 'xx';
        return "{$language}_{$country}";
    }
}

/**
 * Exception extension for invalid locale.
 *
 * @category  Payment
 * @package   KlarnaAPI
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_InvalidLocaleException extends KiTT_Exception
{
    /**
     * KiTT_InvalidLocaleException constructor
     *
     * @param mixed $locale The object in question
     */
    public function __construct($locale)
    {
        parent::__construct("({$locale}) is not a valid locale");
    }
}
