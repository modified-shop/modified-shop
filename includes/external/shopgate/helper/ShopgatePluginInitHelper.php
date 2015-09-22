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
        if (!defined('_VALID_XTC')) {
            define('_VALID_XTC', true);
        }
        
        if (!defined('DIR_FS_LANGUAGES')) {
            define('DIR_FS_LANGUAGES', rtrim(DIR_FS_CATALOG, '/') . '/lang/');
        }
    }
    
    /**
     * include files which contain functions from the shop system. These function are used by
     * our plugin
     */
    public function includeNeededFiles()
    {
        // needs to be included before everything else because of the constant PROJECT_MAJOR_VERSION
        
        if (file_exists(DIR_FS_CATALOG . 'admin/includes/version.php')) {
            require_once(DIR_FS_CATALOG . 'admin/includes/version.php');
        }
        
        $requiredFiles = array(
            'inc/xtc_validate_password.inc.php',
            'inc/xtc_format_price_order.inc.php',
            'includes/classes/xtcPrice.php'
        );
        
        if (!defined('PROJECT_MAJOR_VERSION')) {
            $requiredFiles[] = 'inc/xtc_db_prepare_input.inc.php';
        } else {
            $requiredFiles[] = 'inc/db_functions_mysql.inc.php';
        }
        
        foreach ($requiredFiles as $file) {
            if (file_exists(DIR_FS_CATALOG . $file)) {
                require_once(DIR_FS_CATALOG . $file);
            }
        }
    }
    
    /**
     * load language files depending on the current language set in the shop system
     *
     * @param $language
     */
    public function includeShopgateLanguageFile($language)
    {
        $languageFile =
            DIR_FS_CATALOG . 'includes/external/shopgate/base/lang/' . $language . '/modules/payment/shopgate.php';
        if (file_exists($languageFile)) {
            require_once($languageFile);
        }
    }
    
    /**
     * include shopgate wrapper
     */
    public function includeShopgateWrapper()
    {
        $wrapperFile = DIR_FS_CATALOG . 'includes/external/shopgate/base/shopgate_wrapper.php';
        if (file_exists($wrapperFile)) {
            require_once($wrapperFile);
        }
    }
    
    /**
     * include ShopgateConfig file
     */
    public function includeShopgateConfig()
    {
        $configFile = DIR_FS_CATALOG . 'includes/external/shopgate/base/shopgate_config.php';
        if (file_exists($configFile)) {
            require_once($configFile);
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
