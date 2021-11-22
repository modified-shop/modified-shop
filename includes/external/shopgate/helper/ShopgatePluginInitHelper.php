<?php

/**
 *
 * Copyright Shopgate Inc.
 *
 * Licensed under the GNU General Public License, Version 2 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU General Public License, Version 2
 *
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
        $qry    = "SELECT * FROM `" . TABLE_COUNTRIES
            . "` WHERE UPPER(countries_iso_code_2) = UPPER('" . $country . "')";
        $result = xtc_db_query($qry);
        $qry    = xtc_db_fetch_array($result);

        return !empty($qry['countries_id']) ? $qry['countries_id'] : 'DE';
    }

    /**
     * @param $defaultLanguage
     * @param $languageId
     * @param $language
     */
    public function getDefaultLanguageData($defaultLanguage, &$languageId,
        &$language
    ) {
        $qry        = "SELECT * FROM `" . TABLE_LANGUAGES
            . "` WHERE UPPER(code) = UPPER('" . $defaultLanguage . "')";
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
    public function getDefaultCurrencyData($defaultCurrency, &$exchangeRate,
        &$currencyId, &$currency
    ) {
        $qry          = "SELECT * FROM `" . TABLE_CURRENCIES
            . "` WHERE UPPER(code) = UPPER('" . $defaultCurrency . "')";
        $result       = xtc_db_query($qry);
        $qry          = xtc_db_fetch_array($result);
        $exchangeRate = !empty($qry['value']) ? $qry['value'] : 1;
        $currencyId   = !empty($qry['currencies_id']) ? $qry['currencies_id']
            : 1;
        $currency     = !empty($qry)
            ? $qry
            : array(
                'code'            => 'EUR', 'symbol_left' => '',
                'symbol_right'    => ' EUR', 'decimal_point' => ',',
                'thousands_point' => '.', 'decimal_places' => '2',
                'value'           => 1.0
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

        $qry        = "SELECT * FROM `" . TABLE_LANGUAGES
            . "` WHERE UPPER(code) = UPPER('" . $isoCode . "')";
        $result     = ShopgateWrapper::db_query($qry);
        $resultItem = ShopgateWrapper::db_fetch_array($result);

        if (!isset($resultItem['languages_id'])) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::UNKNOWN_ERROR_CODE,
                'Invalid iso code given : ' . $isoCode
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

        $qry        = "SELECT * FROM `" . TABLE_LANGUAGES
            . "` WHERE UPPER(code) = UPPER('" . $isoCode . "')";
        $result     = ShopgateWrapper::db_query($qry);
        $resultItem = ShopgateWrapper::db_fetch_array($result);

        if (!isset($resultItem['languages_id'])) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::UNKNOWN_ERROR_CODE,
                'Invalid iso code given : ' . $isoCode
            );
        } else {
            return $resultItem['directory'];
        }
    }

    /**
     * Returns the version of the modified shop
     *
     * @return string
     */
    public function getModifiedVersion()
    {
        $modifiedVersion = PROJECT_VERSION;
        $versionFilePath = DIR_FS_CATALOG . (defined('DIR_ADMIN') ? DIR_ADMIN
                : 'admin/') . "includes/version.php";

        if (defined('PROJECT_MAJOR_VERSION')
            && defined(
                'PROJECT_MINOR_VERSION'
            )
        ) {
            $modifiedVersion = PROJECT_MAJOR_VERSION . '.'
                . PROJECT_MINOR_VERSION;
        } elseif (file_exists($versionFilePath)) {
            $versionContent = file_get_contents($versionFilePath);

            if (preg_match_all(
                "/define\(\s*'([^']+)'\,\s*'([^']+)'\);/si", $versionContent,
                $resultVersion
            )) {
                $resultVersion   = end($resultVersion);
                $modifiedVersion = $this->getVersionNumber($resultVersion[0]);
            }
        }

        return $modifiedVersion;
    }

    /**
     * parses the version number out of a string like
     * 'modified eCommerce Shopssoftware v1.06 rev 4642 SP2 dated: 2014-08-12'
     *
     * @param string $versionString
     *
     * @return string
     */
    private function getVersionNumber($versionString)
    {
        $pattern = '#v([0-9]+\.[0-9]+)#';
        if (preg_match($pattern, $versionString, $matches)
            && !empty($matches[1])
        ) {
            return $matches[1];
        }
        $pattern = '#^([0-9]+\.[0-9]+)(\.[0-9]+)*$#';
        if (preg_match($pattern, $versionString, $matches)
            && !empty($matches[1])
        ) {
            return $matches[1];
        }

        return '1.00';
    }
}
