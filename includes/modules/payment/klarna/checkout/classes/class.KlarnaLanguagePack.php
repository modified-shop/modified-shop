<?php
/**
 * The Klarna Language Pack class. This class fetches translations from a
 * language pack.
 *
 * @package     Klarna Standard Kassa API
 * @version     ##KLARNA_CHECKOUT_VERSION##
 * @since       ##KLARNA_CHECKOUT_DATE##
 * @link        http://integration.klarna.com/
 * @copyright   Copyright (c) 2011 Klarna AB (http://klarna.com)
 */

class KlarnaLanguagePack {
    public static $charset = "ISO-8859-1";
    private $xml;

    public function __construct($path = 'data/language.xml') {
        $this -> xml = simplexml_load_file($path);
    }

    /**
     * Get a translated text from the language pack
     *
     * @param  string  $text  the string to be translated
     * @param  string|int  $ISO  target language, iso code or KlarnaLanguage
     * @return string  the translated text
     */
    public function fetch($text, $ISO) {
        if (is_numeric ($ISO)) {
            $ISO = KlarnaLanguage::getCode($ISO);
        } else {
            $ISO = strtolower ($ISO);
        }

        // XPath query to get translation
        $xpath = "//string[@id='$text']/$ISO";
        $aResult = (array) @$this -> xml -> xpath ($xpath);
        if (count($aResult) > 0) {
            $aResult = (array) $aResult[0];
            if (count($aResult) > 0) {
                return htmlentities(utf8_decode($aResult[0]));
            }
        }

        // Fallback to the english text
        if ($ISO != 'en') {
            return $this->fetch($text, 'en');
        }

        // Or failing that, the placeholder
        return $text;
    }

    /**
     * Get a translated text from the language pack
     * same as fetch but with a implicit creation of the instance
     */
    public static function fetch_from_file($text, $ISO, $path = null) {
        if ($path !== null) {
            $pack = new KlarnaLanguagePack($path);
        } else {
            $pack = new KlarnaLanguagePack();
        }

        return $pack -> fetch($text, $ISO);
    }
}
