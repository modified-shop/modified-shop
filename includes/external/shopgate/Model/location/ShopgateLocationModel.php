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
        $query         = "select * from " . TABLE_ZONES_TO_GEO_ZONES . " where zone_country_id = '" . $zoneCountryId
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
        $query          = "SELECT tc.tax_class_title AS title FROM " . TABLE_TAX_RATES . " AS tr
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
        $query         = "SELECT c.* FROM " . TABLE_COUNTRIES . " AS c WHERE c.countries_iso_code_2 = \"{$name}\"";
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
        $query         =
            "select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_FLAT_ZONE
            . "' and zone_country_id = '" . $zoneCountryId . "' order by zone_id";
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
        $query     =
            "SELECT tc.tax_class_title AS title FROM " . TABLE_TAX_CLASS . " AS tc WHERE tc.tax_class_id = {$id}";
        $result    = xtc_db_query($query);
        $taxResult = xtc_db_fetch_array($result);
        
        return $taxResult["title"];
    }
}
