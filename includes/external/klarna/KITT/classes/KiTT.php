<?php
/**
 * Entry point to KiTT
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
define('KITT_CLASS', dirname(__FILE__));
define('KITT_INT', dirname(KITT_CLASS) . '/interfaces');

require_once KITT_INT . '/Baptizer.php';
require_once KITT_INT . '/Formatter.php';
require_once KITT_INT . '/LanguagePack.php';
require_once KITT_INT . '/Config.php';
require_once KITT_INT . '/Locator.php';

require_once KITT_CLASS . '/CountryLogic.php';
require_once KITT_CLASS . '/Exception.php';
require_once KITT_CLASS . '/XMLLanguagePack.php';
require_once KITT_CLASS . '/Translator.php';
require_once KITT_CLASS . '/String.php';
require_once KITT_CLASS . '/DefaultBaptizer.php';
require_once KITT_CLASS . '/HTTPContext.php';
require_once KITT_CLASS . '/Ajax.php';
require_once KITT_CLASS . '/Payment/Title.php';
require_once KITT_CLASS . '/SimpleConfig.php';
require_once KITT_CLASS . '/Lookup.php';
require_once KITT_CLASS . '/Locale.php';
require_once KITT_CLASS . '/Session.php';
require_once KITT_CLASS . '/Dispatcher.php';
require_once KITT_CLASS . '/mustache/Mustache.php';
require_once KITT_CLASS . '/Template.php';
require_once KITT_CLASS . '/TemplateLoader.php';
require_once KITT_CLASS . '/VFS.php';
require_once KITT_CLASS . '/PClassCollection.php';
require_once KITT_CLASS . '/InputValues.php';
require_once KITT_CLASS . '/View/Helper.php';
require_once KITT_CLASS . '/Widget.php';
require_once KITT_CLASS . '/Installment/Widget.php';
require_once KITT_CLASS . '/CountryDeducer.php';
require_once KITT_CLASS . '/ErrorMessage.php';
require_once KITT_CLASS . '/DefaultFormatter.php';
require_once KITT_CLASS . '/Addresses.php';
require_once KITT_CLASS . '/TemplateFields.php';
require_once KITT_CLASS . '/Payment/Widget.php';
require_once KITT_CLASS . '/API.php';
require_once KITT_CLASS . '/Checkout/Controller.php';
require_once KITT_CLASS . '/Payment/Option.php';


/**
 * KiTT
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT
{

    /**
     * @var string
     */
    const INVOICE = "invoice";

    /**
     * @var string
     */
    const PART = "part";

    /**
     * @var string
     */
    const SPEC = "spec";

    /**
     * @var KiTT_Config
     */
    private static $_config;

    /**
     * @var KiTT_Formatter
     */
    private static $_formatter;

    /**
     * @var KiTT_LanguagePack
     */
    private static $_languagePack;

    /**
     * Cache of loaded templates
     *
     * @var array
     */
    private static $_templateCache = array();

    /**
     * @var KiTT_ErrorMessage
     */
    private static $_errorMessage;

    /**
     * @var KiTT_Lookup
     */
    private static $_lookup;

    /**
     * Get the shared configuration instance
     *
     * @return KiTT_Config
     */
    public static function configuration()
    {
        if (self::$_config === null) {
            self::$_config = new KiTT_SimpleConfig();
        }
        return self::$_config;
    }

    /**
     * Update the shared configuration
     *
     * @param array $options array with key values to update configuration with
     *
     * @return void
     */
    public static function configure(array $options)
    {
        $config = self::configuration();
        foreach ($options as $key => $value) {
            $config->set($key, $value);
        }
    }

    /**
     * Configure a Klarna object for a specific country
     *
     * @param KiTT_Locale $locale Country code
     * @param int         $eid    Merchant id
     * @param string      $secret Merchant secret
     *
     * @throws InvalidArgumentException
     *
     * @return Klarna
     */
    public static function configureKlarna(KiTT_Locale $locale, $eid, $secret)
    {
        $country = $locale->getCountryCode();
        if ($country === null) {
            throw new InvalidArgumentException(
                "country must be a valid Klarna country ({$country})"
            );
        }
        $country = strtoupper($country);
        self::configuration()->set(
            "sales_countries/{$country}", array(
                'eid' => $eid,
                'secret' => $secret,
                'country' => $locale->getCountry(),
                'currency' => $locale->getCurrency(),
                'language' => $locale->getLanguage()
            )
        );
    }

    /**
     * Create a new Klarna instance with the configuration provided
     *
     * Required:
     *  + Configuration value 'api' which must hold the KlarnaConfig keys:
     *      - mode
     *      - pcStorage
     *      - pcURI
     *  + Configuration value 'module'
     *  + Configuration value 'version'
     *
     * @param string|int $country KlarnaCountry constant or string representative
     *
     * @throws InvalidArgumentException, KiTT_MissingSalesCountryException,
     *         KiTT_MissingConfigurationException, KlarnaException
     *
     * @return KiTT_API
     */
    public static function api($country)
    {
        $country = KiTT_Locale::parseCountry($country);
        if ($country === null) {
            throw new InvalidArgumentException(
                '$country must be a valid Klarna country'
            );
        }
        $configuration = self::configuration();
        $salesCountry = $configuration->get("sales_countries/{$country}");
        $array = new ArrayObject(
            array_merge($salesCountry, $configuration->get("api"))
        );
        $klarna = new KiTT_API($configuration);
        $klarna->setConfig($array);
        return $klarna;
    }

    /**
     * Get the active sales countries
     *
     * @return array
     */
    public static function salesCountries()
    {
        return array_keys(self::configuration()->get("sales_countries"));
    }

    /**
     * Get a locale object representing the given options
     *
     * If language or currency is null, this information will be determined
     * using a lookup table pointed to by the configurations 'lookup' path.
     *
     * @param string|int $country  country of the locale
     * @param string|int $language language of the locale (optional)
     * @param string|int $currency currency of the locale (optional)
     *
     * @return KiTT_Locale
     */
    public static function locale($country, $language = null, $currency = null)
    {
        if ($language === null) {
            $language = self::lookup()->defaultLanguage($country);
        }
        if ($currency === null) {
            $currency = self::lookup()->getCurrency($country);
        }
        return new KiTT_Locale($country, $language, $currency);
    }

    /**
     * Set the Formatter to use for this instance.
     *
     * @param KiTT_Formatter $formatter The formatter to set
     *
     * @return void
     */
    public static function setFormatter(KiTT_Formatter $formatter = null)
    {
        self::$_formatter = $formatter;
    }

    /**
     * Get the shared KiTT_Formatter used for this instance
     *
     * @return KiTT_Formatter
     */
    public static function getFormatter()
    {
        if (self::$_formatter === null) {
            self::$_formatter = new KiTT_DefaultFormatter();
        }
        return self::$_formatter;
    }

    /**
     * Create a KiTT_VFS object
     *
     * @return KiTT_VFS
     */
    protected static function vfs()
    {
        return new KiTT_VFS();
    }

    /**
     * Get the shared KiTT_LanguagePack used for this instance
     *
     * @return KiTT_LanguagePack
     */
    public static function languagePack()
    {
        if (self::$_languagePack === null) {
            self::$_languagePack = new KiTT_XMLLanguagePack(
                self::configuration(),
                self::vfs()
            );
        }
        return self::$_languagePack;
    }

    /**
     * Create a KiTT_Translator object for the specified KiTT_Locale
     *
     * @param KiTT_Locale $locale The locale to fetch translations for
     *
     * @return KiTT_Translator
     */
    public static function translator(KiTT_Locale $locale)
    {
        return new KiTT_Translator(self::languagePack(), $locale);
    }

    /**
     * Get a PClassCollection for the specified payment method
     *
     * @param string     $payment Payment type constant
     * @param string|int $country Klarna Country
     * @param int        $sum     The amount to calculate with
     * @param int        $page    KlarnaFlags PRODUCT_PAGE or CHECKOUT_PAGE
     *
     * @return KiTT_PClassCollection
     */
    public static function pclassCollection($payment, $country, $sum, $page)
    {
        return new KiTT_PClassCollection(
            self::api($country), self::getFormatter(), $sum, $page, $payment
        );
    }

    /**
     * Factory for templateLoader
     *
     * @param KiTT_Locale $locale locale
     *
     * @return KiTT_TemplateLoader
     */
    public static function templateLoader(KiTT_Locale $locale)
    {
        return new KiTT_TemplateLoader(
            self::configuration(),
            $locale,
            self::vfs(),
            self::$_templateCache
        );
    }

    /**
     * Get a checkout controller
     *
     * @param KiTT_Locale $locale locale
     * @param numeric     $sum    total price
     *
     * @return KiTT_Checkout_Controller
     */
    public static function checkoutController(KiTT_Locale $locale, $sum)
    {
        return new KiTT_Checkout_Controller(
            self::configuration(),
            $locale,
            $sum,
            self::getFormatter(),
            self::translator($locale),
            self::templateLoader($locale),
            self::lookup(),
            self::api($locale->getCountry())
        );
    }

    /**
     * Get a KiTT_Dispatcher object
     *
     * @param KiTT_Addresses $addresses The object used to fetch addresses
     *
     * @return KiTT_Dispatcher
     */
    public static function ajaxDispatcher(KiTT_Addresses $addresses)
    {
        return new KiTT_Dispatcher(
            new KiTT_Session(),
            new KiTT_Ajax(self::configuration(), $addresses)
        );
    }

    /**
     * Factory for the part payment box
     *
     * @param KiTT_Locale $locale locale
     * @param float       $sum    Product cost
     * @param int         $page   KlarnaFlags PRODUCT_PAGE or CHECKOUT_PAGE
     *
     * @return KiTT_Installment_Widget
     */
    public static function partPaymentBox(
        KiTT_Locale $locale, $sum, $page = KlarnaFlags::PRODUCT_PAGE
    ) {
        return new KiTT_Installment_Widget(
            self::configuration(),
            $locale,
            self::pclassCollection(KiTT::PART, $locale->getCountry(), $sum, $page),
            self::templateLoader($locale),
            self::translator($locale)
        );
    }

    /**
     * Get an ErrorMessage singleton
     *
     * @return KiTT_ErrorMessage
     */
    public static function errorMessage()
    {
        if (self::$_errorMessage === null) {
            self::$_errorMessage = new KiTT_ErrorMessage;
        }
        return self::$_errorMessage;
    }

    /**
     * Get a KiTT_Locator
     *
     * Requires configuration to be made with sales countries, and optionally
     * a default store country.
     *
     * @return KiTT_Locator
     */
    public static function locator()
    {
        return new KiTT_CountryDeducer(
            self::configuration(),
            self::lookup()
        );
    }

    /**
     *  Gets a Lookup object.
     *
     * Requires configuration to be made with a lookup path to a json containing
     * a lookup table.
     *
     * @return KiTT_Lookup
     */
    public static function lookup()
    {
        if (self::$_lookup === null) {
            self::$_lookup = new KiTT_Lookup(
                self::configuration(),
                self::vfs()
            );
        }
        return self::$_lookup;
    }
}
