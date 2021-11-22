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
class ShopgateLocationModel
{
    /**
     * read the zone data from database regarding the zone country id
     *
     * @param $zoneCountryId
     *
     * @return array
     */
    public function getZoneByCountryId($zoneCountryId)
    {
        $query         = "select * from " . TABLE_ZONES_TO_GEO_ZONES
            . " where zone_country_id = '" . $zoneCountryId
            . "' order by zone_id";
        $result        = xtc_db_query($query);
        $CountryResult = xtc_db_fetch_array($result);

        return $CountryResult;
    }

    /**
     * read the tax class title from database regarding the tax value
     *
     * @param $taxValue
     *
     * @return string
     */
    public function getTaxClassByValue($taxValue)
    {
        $query          = "SELECT tc.tax_class_title AS title FROM "
            . TABLE_TAX_RATES . " AS tr
                            JOIN " . TABLE_TAX_CLASS . " AS tc ON tc.tax_class_id = tr.tax_class_id
                            WHERE tr.tax_rate = {$taxValue}";
        $result         = xtc_db_query($query);
        $taxClassResult = xtc_db_fetch_array($result);

        return $taxClassResult["title"];
    }

    /**
     * read the country id from database regarding the iso code 2
     *
     * @param $name
     *
     * @return array
     */
    public function getCountryByIso2Name($name)
    {
        $query         = "SELECT c.* FROM " . TABLE_COUNTRIES
            . " AS c WHERE c.countries_iso_code_2 = \"{$name}\"";
        $result        = xtc_db_query($query);
        $CountryResult = xtc_db_fetch_array($result);

        return $CountryResult;
    }

    /**
     * read the zone id from database regarding the zone country id
     *
     * @param $zoneCountryId
     *
     * @return int
     */
    public function getZoneId($zoneCountryId)
    {
        $query
                       = "select zone_id from " . TABLE_ZONES_TO_GEO_ZONES
            . " where geo_zone_id = '" . MODULE_SHIPPING_FLAT_ZONE
            . "' and zone_country_id = '" . $zoneCountryId
            . "' order by zone_id";
        $result        = xtc_db_query($query);
        $CountryResult = xtc_db_fetch_array($result);

        return $CountryResult["zone_id"];
    }

    /**
     * read the tax class title from database regarding the tax class id
     *
     * @param $id
     *
     * @return null|string
     */
    public function getTaxClassById($id)
    {
        if (empty($id)) {
            return null;
        }
        $query
                   = "SELECT tc.tax_class_title AS title FROM "
            . TABLE_TAX_CLASS . " AS tc WHERE tc.tax_class_id = {$id}";
        $result    = xtc_db_query($query);
        $taxResult = xtc_db_fetch_array($result);

        return $taxResult["title"];
    }
}
