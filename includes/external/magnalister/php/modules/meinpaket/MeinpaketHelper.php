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
 * $Id$
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/AttributesMatchingHelper.php');

class MeinpaketHelper extends AttributesMatchingHelper
{

	private static $instance;

	protected $marketplaceTitle = 'Allyouneed';

	protected $numberOfMaxAdditionalAttributes = self::UNLIMITED_ADDITIONAL_ATTRIBUTES;

	public static function gi() {
		if (self::$instance === null) {
			self::$instance = new MeinpaketHelper();
		}

		return self::$instance;
	}

	public static function processCheckinErrors($result, $mpID) {
		// Empty is ok, the API has a method to fetch the error log later.
	}
	
	public static function loadPriceSettings($mpId) {
		$mp = magnaGetMarketplaceByID($mpId);
		
		$config = array(
			'AddKind' => getDBConfigValue($mp.'.price.addkind', $mpId, 'percent'),
			'Factor'  => (float)getDBConfigValue($mp.'.price.factor', $mpId, 0),
			'Signal'  => getDBConfigValue($mp.'.price.signal', $mpId, ''),
			'Group'   => getDBConfigValue($mp.'.price.group', $mpId, ''),
			'UseSpecialOffer' => getDBConfigValue(array($mp.'.price.usespecialoffer', 'val'), $mpId, false),
			'Currency' => getCurrencyFromMarketplace($mpId),
			'ConvertCurrency' => getDBConfigValue(array($mp.'.exchangerate', 'update'), $mpId, false),
		);
		
		return $config;
	}
	
	public static function loadQuantitySettings($mpId) {
		$mp = magnaGetMarketplaceByID($mpId);
		
		$config = array(
			'Type'  => getDBConfigValue($mp.'.quantity.type', $mpId, 'lump'),
			'Value' => (int)getDBConfigValue($mp.'.quantity.value', $mpId, 0),
			'MaxQuantity' => (int)getDBConfigValue($mp.'.quantity.maxquantity', $mpId, 0),
		);
		
		return $config;
	}
	
	protected function isProductPrepared($category, $prepare = false) {
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$sKeyType = 'products_model';
		} else {
			$sKeyType = 'products_id';
		}

		return MagnaDB::gi()->recordExists(TABLE_MAGNA_MEINPAKET_PROPERTIES, array(
			'mpID' => $this->mpId,
			$sKeyType => $prepare,
		));
	}

	protected function getPreparedData($category, $prepare = false, $customIdentifier = '') {
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$sSQLAnd = ' AND products_model = "' . $prepare . '"';
		} else {
			$sSQLAnd = ' AND products_id = "' . $prepare . '"';
		}

		$availableCustomConfigs = array();
		if ($prepare) {
			$availableCustomConfigs = json_decode(MagnaDB::gi()->fetchOne(eecho('
				SELECT CategoryAttributes
				FROM ' . TABLE_MAGNA_MEINPAKET_PROPERTIES. '
				WHERE mpID = ' . $this->mpId . '
					AND VariationConfiguration = "' . $category . '"
					' . $sSQLAnd . '
			', false)), true);
		}

		return !$availableCustomConfigs ? array() : $availableCustomConfigs;
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
				FROM ' . TABLE_MAGNA_MEINPAKET_PROPERTIES . '
				WHERE mpID = ' . $this->mpId . '
					AND VariationConfiguration = "' . $category . '"
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
		$data = MeinpaketApiConfigValues::gi()->getVariantConfigurationDefinition($category);
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

	public function renderMatchingTable($url, $categoryOptions, $addCategoryPick = true, $displayCategory = true, $customIdentifierHtml = '')
	{
		// Meinpaket does not have category pick button
		return parent::renderMatchingTable($url, $categoryOptions, false, $displayCategory, $customIdentifierHtml);
	}

	protected function getSavedVariationThemeCode($category, $prepare = false)
	{
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$sSQLAnd = ' AND products_model = "'.$prepare. '"';
		} else {
			$sSQLAnd = ' AND products_id = "'. $prepare . '"';
		}

		$variationTheme = null;
		if ($prepare) {
			$variationTheme = MagnaDB::gi()->fetchOne(eecho('
				SELECT variation_theme
				FROM ' . TABLE_MAGNA_MEINPAKET_PROPERTIES . '
				WHERE MpId = ' . $this->mpId . '
						AND va = "' . $category . '"
						' . $sSQLAnd
				)
			);
		}

		$variationTheme = json_decode($variationTheme, true);

		return is_array($variationTheme) ? key($variationTheme) : '';
	}

	protected function setMatchingTableTranslations() {
		return array(
			'mpTitle' => str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_TITLE),
			'mpAttributeTitle' => str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_MP_ATTRIBUTE),
			'mpOptionalAttributeTitle' => str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_MP_OPTIONAL_ATTRIBUTE),
			'mpCustomAttributeTitle' => str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_MP_CUSTOM_ATTRIBUTE),
		);
	}

	public function getVarMatchTranslations() {
		$translations = parent::getVarMatchTranslations();
		$translations['mpValue'] = str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_MP_VALUE);
		$translations['attributeChangedOnMp'] = str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_ATTRIBUTE_CHANGED_ON_MP);
		$translations['attributeDeletedOnMp'] = str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_ATTRIBUTE_DELETED_ON_MP);
		$translations['attributeValueDeletedOnMp'] = str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_ATTRIBUTE_VALUE_DELETED_ON_MP);;
		$translations['categoryWithoutAttributesInfo'] = str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_CATEGORY_WITHOUT_ATTRIBUTES_INFO);

		return $translations;
	}

}
