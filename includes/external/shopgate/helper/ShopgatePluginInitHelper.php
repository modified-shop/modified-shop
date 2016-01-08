<?php

/**
 * Shopgate GmbH
 *
 * URHEBERRECHTSHINWEIS
 *
 * Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
 * zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
 * Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
 * öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
 * schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
 *
 * COPYRIGHT NOTICE
 *
 * This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
 * for the purpose of facilitating communication between the IT system of the customer and the IT system
 * of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
 * transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */
class ShopgatePluginInitHelper
{
    
    /**
     * check if needed shop system constants were defined
     */
    public function defineXtcValidationConstant()
    {
        if (!defined('DIR_FS_LANGUAGES')) {
            define('DIR_FS_LANGUAGES', rtrim(DIR_FS_CATALOG, '/') . '/lang/');
        }
    }
    
    /**
     * @param $country
     *
     * @return string
     */
    public function getDefaultCountryId($country)
    {
        $qry    =
            "SELECT * FROM `" . TABLE_COUNTRIES . "` WHERE UPPER(countries_iso_code_2) = UPPER('" . $country . "')";
        $result = xtc_db_query($qry);
        $qry    = xtc_db_fetch_array($result);
        
        return !empty($qry['countries_id']) ? $qry['countries_id'] : 'DE';
    }
    
    /**
     * @param $defaultLanguage
     * @param $languageId
     * @param $language
     */
    public function getDefaultLanguageData($defaultLanguage, &$languageId, &$language)
    {
        $qry        = "SELECT * FROM `" . TABLE_LANGUAGES . "` WHERE UPPER(code) = UPPER('" . $defaultLanguage . "')";
        $result     = xtc_db_query($qry);
        $qry        = xtc_db_fetch_array($result);
        $languageId = !empty($qry['languages_id']) ? $qry['languages_id'] : 2;
        $language   = !empty($qry['directory']) ? $qry['directory'] : 'german';
    }
    
    /**
     * @param $defaultCurrency
     * @param $exchangeRate
     * @param $currencyId
     * @param $currency
     */
    public function getDefaultCurrencyData($defaultCurrency, &$exchangeRate, &$currencyId, &$currency)
    {
        $qry          =
            "SELECT * FROM `" . TABLE_CURRENCIES . "` WHERE UPPER(code) = UPPER('" . $defaultCurrency . "')";
        $result       = xtc_db_query($qry);
        $qry          = xtc_db_fetch_array($result);
        $exchangeRate = !empty($qry['value']) ? $qry['value'] : 1;
        $currencyId   = !empty($qry['currencies_id']) ? $qry['currencies_id'] : 1;
        $currency     = !empty($qry)
            ? $qry
            : array(
                'code'            => 'EUR', 'symbol_left' => '', 'symbol_right' => ' EUR', 'decimal_point' => ',',
                'thousands_point' => '.', 'decimal_places' => '2', 'value' => 1.0
            );
    }
    
    /**
     * @param $isoCode
     *
     * @return mixed
     * @throws ShopgateLibraryException
     */
    public static function getLanguageIdByIsoCode($isoCode)
    {
        $isoCodeParts = explode('_', $isoCode);
        $isoCode      = isset($isoCodeParts[0]) ? $isoCodeParts[0] : $isoCode;
        
        $qry        = "SELECT * FROM `" . TABLE_LANGUAGES . "` WHERE UPPER(code) = UPPER('" . $isoCode . "')";
        $result     = ShopgateWrapper::db_query($qry);
        $resultItem = ShopgateWrapper::db_fetch_array($result);
        
        if (!isset($resultItem['languages_id'])) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::UNKNOWN_ERROR_CODE, 'Invalid iso code given : ' . $isoCode
            );
        } else {
            return $resultItem['languages_id'];
        }
    }
    
    /**
     * @param $isoCode
     *
     * @return mixed
     * @throws ShopgateLibraryException
     */
    public static function getLanguageDirectoryByIsoCode($isoCode)
    {
        $isoCodeParts = explode('_', $isoCode);
        $isoCode      = isset($isoCodeParts[0]) ? $isoCodeParts[0] : $isoCode;
        
        $qry        = "SELECT * FROM `" . TABLE_LANGUAGES . "` WHERE UPPER(code) = UPPER('" . $isoCode . "')";
        $result     = ShopgateWrapper::db_query($qry);
        $resultItem = ShopgateWrapper::db_fetch_array($result);
        
        if (!isset($resultItem['languages_id'])) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::UNKNOWN_ERROR_CODE, 'Invalid iso code given : ' . $isoCode
            );
        } else {
            return $resultItem['directory'];
        }
    }
}
