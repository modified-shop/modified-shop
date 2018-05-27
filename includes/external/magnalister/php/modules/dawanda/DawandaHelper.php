<?php
/**
 * 888888ba                 dP  .88888.                    dP
 * 88    `8b                88 d8'   `88                   88
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b.
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P'
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id: DawandaHelper.php 3830 2014-05-06 13:00:00Z tim.neumann $
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/AttributesMatchingHelper.php');

class DawandaHelper extends AttributesMatchingHelper {

	private static $instance;

	public static function gi() {
		if (self::$instance === null) {
			self::$instance = new DawandaHelper();
		}

		return self::$instance;
	}

	public static function checkProductSaveJsonArray($aCheckArray) {
		foreach ($aCheckArray as $sKey => &$sEntry) {
			if (empty($sEntry)) {
				unset($aCheckArray[$sKey]);
			}
		}

		if (0 < count($aCheckArray)) {
			return json_encode($aCheckArray);
		} else {
			return '';
		}
	}

	protected function isProductPrepared($category, $prepare = false) {
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$sKeyType = 'products_model';
		} else {
			$sKeyType = 'products_id';
		}

		return MagnaDB::gi()->recordExists(TABLE_MAGNA_DAWANDA_PROPERTIES, array(
			'mpID' => $this->mpId,
			$sKeyType => $prepare,
		));
	}

	protected function getPreparedData($category, $prepare = false, $customIdentifier = '') {
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$sSQLAnd = ' AND products_model = "'.$prepare.'"';
		} else {
			$sSQLAnd = ' AND products_id = "'. $prepare . '"';
		}

		$availableCustomConfigs = array();
		if ($prepare) {
			$availableCustomConfigs = json_decode(MagnaDB::gi()->fetchOne(eecho('
				SELECT CategoryAttributes
				FROM ' . TABLE_MAGNA_DAWANDA_PROPERTIES. '
				WHERE mpID = ' . $this->mpId . '
					AND MarketplaceCategories = "' . $category . '"
					' . $sSQLAnd. '
			', false)), true);

			if (!$availableCustomConfigs) {
				// Backward compatibility 
				$availableCustomConfigs = $this->fixOldAttributes($prepare, $category);
			}
		}

		return !$availableCustomConfigs ? array() : $availableCustomConfigs;
	}

	/**
	 * Covering situation if client prepared item before new variation matching concept
	 *
	 * @param $prepare
	 * @param $category
	 * @return array
	 */
	private function fixOldAttributes($prepare, $category) {
		$attributesFixed = array();
		$response = $this->getAttributesFromMP($category);
		$mpAttributes = $response['attributes'];

		$attributes = MagnaDB::gi()->fetchRow(eecho('
					SELECT Attributes, MpColors
					FROM ' . TABLE_MAGNA_DAWANDA_PROPERTIES. '
					WHERE mpID = ' . $this->mpId . '
						AND products_model = \'' . $prepare. '\'
				', false));

		if (!empty($attributes['MpColors'])) {
			$attributes['MpColors'] = json_decode($attributes['MpColors'], true);
			for ($i = 0; $i < 2; $i++) {
				if (!empty($attributes['MpColors'][$i])) {
					$attributesFixed['Color' . ($i + 1)] = array(
						'Kind' => 'Matching',
						'AttributeName' => $mpAttributes['Color' . ($i + 1)]['title'],
						'Values' => $attributes['MpColors'][$i],
						'Required' => false,
						'Code' => 'attribute_value',
						'DataType' => 'select',
						'Error' => false
					);
				}
			}

		}

		if (!empty($attributes['Attributes'])) {
			$attributes['Attributes'] = json_decode($attributes['Attributes'], true);
			foreach ($attributes['Attributes']['primary'] as $attributeValues) {
				foreach ($mpAttributes as $mpKeyAttribute => $mpAttribute) {
					// If at least of of the value id is in marketplace values then this is our attribute
					// We need this for attribute key, because we don't have that information in prepare table
					$mpAttribute['values'] = array_flip($mpAttribute['values']);
					if (is_array($attributeValues)) {
						$type = 'multiselect';
						$containsSearch = count(array_intersect($attributeValues, $mpAttribute['values'])) > 0;
					} else {
						$type = 'select';
						$containsSearch = in_array($attributeValues, $mpAttribute['values']);
					}

					if ($containsSearch) {
						$attributesFixed[$mpKeyAttribute] = array(
							'Kind' => 'Matching',
							'AttributeName' => $mpAttribute['title'],
							'Values' => $attributeValues,
							'Required' => false,
							'Code' => 'attribute_value',
							'DataType' => $type,
							'Limit' => $mpAttribute['limit'],
							'Error' => false
						);

						break;
					}
				}
			}
		}

		return $attributesFixed;
	}

	/**
	 * Gets prepared attributes data for products prepared for given category.
	 *
	 * @param string $category
	 * @return array|null
	 */
	protected function getPreparedProductsData($category) {
		$dataFromDB = MagnaDB::gi()->fetchArray(eecho('
				SELECT `CategoryAttributes`
				FROM ' . TABLE_MAGNA_DAWANDA_PROPERTIESE . '
				WHERE mpID = ' . $this->mpId . '
					AND MarketplaceCategories = "' . $category . '"
			', false), true);

		if ($dataFromDB) {
			$result = array();
			foreach ($dataFromDB as $preparedData) {
				if ($preparedData) {
					$result[] = json_decode($preparedData, true);
				}
			}

			return $result;
		}

		return null;
	}

	protected function getAttributesFromMP($category, $additionalData = null, $customIdentifier = '') {
		$data = DawandaApiConfigValues::gi()->getVariantConfigurationDefinition($category);
		if (!is_array($data) || !isset($data['attributes'])) {
			$data = array();
		}

		if (empty($data['attributes'])) {
			$data['attributes'] = array();
		}

		return $data;
	}

	protected function fixHTMLUTF8Entities($code) {
		return $code;
	}

}
