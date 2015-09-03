<?php
/*
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
*  @author Shopgate GmbH <interfaces@shopgate.com>
*/


class ShopgateCouponModel {

	function __construct() {
	}

	/*#######################
	 # 	check_cart
	 ######################*/

	/**
	 * @param $className
	 * @return array
	 */
	public function getShippingConfigurationValuesByClassName($className){
		$query  = "SELECT c.configuration_key, c.configuration_value FROM configuration AS c WHERE configuration_key like \"MODULE_SHIPPING_".strtoupper($className)."%\" ;";
		$result = xtc_db_query($query);
		$shippingConfig = array();
		while($config = xtc_db_fetch_array($result)){
			$shippingConfig[$config["configuration_key"]] = $config["configuration_value"];
		}
		return $shippingConfig;
	}
	/**
	 * @param $zoneCountryId
	 * @return array
	 */
	public function getZoneByCountryId($zoneCountryId) {
		$query = "select * from " . TABLE_ZONES_TO_GEO_ZONES . " where zone_country_id = '" . $zoneCountryId . "' order by zone_id";
		$result = xtc_db_query($query);
		$CountryResult = xtc_db_fetch_array($result);
		return $CountryResult;
	}

	/**
	 * @param $taxValue
	 * @return string
	 */
	public function getTaxClassByValue($taxValue) {
		$query = "SELECT tc.tax_class_title AS title FROM ".TABLE_TAX_RATES." AS tr
					JOIN ".TABLE_TAX_CLASS." AS tc ON tc.tax_class_id = tr.tax_class_id
					WHERE tr.tax_rate = {$taxValue}";
		$result = xtc_db_query($query);
		$taxClassResult = xtc_db_fetch_array($result);
		return $taxClassResult["title"];
	}
	/**
	 * @param $name
	 * @return array
	 */
	public function getCountryByIso2Name($name) {
		$query = "SELECT c.* FROM ".TABLE_COUNTRIES." AS c WHERE c.countries_iso_code_2 = \"{$name}\"";
		$result = xtc_db_query($query);
		$CountryResult = xtc_db_fetch_array($result);
		return $CountryResult;
	}
	/**
	 * @param $name
	 * @return mixed
	 */
	public function getCountryIdByName($name) {
		$query = "SELECT c.countries_id AS id FROM countries AS c WHERE c.countries_iso_code_2 = \"{$name}\"";
		$result = xtc_db_query($query);
		$CountryResult = xtc_db_fetch_array($result);
		return $CountryResult["id"];
	}

	/**
	 * @param $zoneCountryId
	 * @return mixed
	 */
	public function getZoneId($zoneCountryId) {
		$query = "select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_FLAT_ZONE . "' and zone_country_id = '" . $zoneCountryId . "' order by zone_id";
		$result = xtc_db_query($query);
		$CountryResult = xtc_db_fetch_array($result);
		return $CountryResult["zone_id"];
	}

	public function getTaxClassById($id) {
		if(empty($id)){
			return null;
		}
		$query = "SELECT tc.tax_class_title AS title FROM " . TABLE_TAX_CLASS . " AS tc WHERE tc.tax_class_id = {$id}";
		$result = xtc_db_query($query);
		$taxResult = xtc_db_fetch_array($result);
		return $taxResult["title"];
	}

    /**
     * @param ShopgateOrderItem $sgOrderItem
     * @return string
     */
	public function getProductIdFromCartItem(ShopgateOrderItem $sgOrderItem) {
        $parentId = $sgOrderItem->getParentItemNumber();
        if (empty($parentId)) {
            $id = $sgOrderItem->getItemNumber();
            if (strpos($id, "_") !== false) {
                $productIdArr = explode('_', $id);
                return $productIdArr[0];
            }
            return $id;
        }
        return $parentId;
	}

	/**
	 * @param $products
	 * @return mixed
	 */
	public function getProductsWeight($products) {
		$tmpArr = array();

		foreach($products as $product){
			$tmpArr[] = $this->getProductIdFromCartItem($product);
		}

		$query = "SELECT SUM(p.products_weight) AS weight FROM ".TABLE_PRODUCTS." AS p WHERE p.products_id IN (".implode(',',$tmpArr).")";
		$result = xtc_db_query($query);
		$CountryResult = xtc_db_fetch_array($result);
		return $CountryResult["weight"];
	}

	/*#######################
	 # 	redeem_coupons
	 ######################*/
} 
