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

class TradoriaHelper extends AttributesMatchingHelper {

	private static $instance;
	protected $marketplaceTitle = 'Rakuten';

	protected $numberOfMaxAdditionalAttributes = self::UNLIMITED_ADDITIONAL_ATTRIBUTES;

	public static function gi()
	{
		if (self::$instance === null) {
			self::$instance = new TradoriaHelper();
		}

		return self::$instance;
	}

	public static function processCheckinErrors($result, $mpID) {
		$fieldname = 'MARKETPLACEERRORS';
		if (!isset($result[$fieldname])) {
			return;
		}
		$dbCharSet = MagnaDB::gi()->mysqlVariableValue('character_set_connection');
    	if (('utf8mb3' == $dbCharSet) || ('utf8mb4' == $dbCharSet)) {
		# means the same for us
			$dbCharSet = 'utf8';
		}
		if ($dbCharSet != 'utf8') {
			arrayEntitiesToLatin1($result[$fieldname]);
		}
		$supportedFields = array('ErrorMessage', 'DateAdded', 'AdditionalData');
		foreach ($result[$fieldname] as $err) {
			if (!isset($err['AdditionalData'])) {
				$err['AdditionalData'] = array();
			}
			foreach ($err as $key => $value) {
				if (!in_array($key, $supportedFields)) {
					$err['AdditionalData'][$key] = $value;
					unset($err[$key]);
				}
			}
			$err = array (
				'mpID' => $mpID,
				'errormessage' => $err['ErrorMessage'],
				'dateadded' => $err['DateAdded'],
				'additionaldata' => serialize($err['AdditionalData']),
			);
			MagnaDB::gi()->insert(TABLE_MAGNA_COMPAT_ERRORLOG, $err);
		}
	}

	protected function isProductPrepared($category, $prepare = false)
	{
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$sKeyType = 'products_model';
		} else {
			$sKeyType = 'products_id';
		}

		return MagnaDB::gi()->recordExists(TABLE_MAGNA_TRADORIA_PREPARE, array(
			'MpId' => $this->mpId,
			$sKeyType => $prepare,
			'MarketplaceCategoriesName' => $category,
		));
	}

	protected function getPreparedData($category, $prepare = false, $customIdentifier = '')
	{
		$availableCustomConfigs = array();

		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$sSQLAnd = ' AND products_model = "'.$prepare.'"';
		} else {
			$sSQLAnd = ' AND products_id = "'. $prepare . '"';
		}

		if ($prepare) {
			$availableCustomConfigs = json_decode(MagnaDB::gi()->fetchOne(eecho('
				SELECT CategoryAttributes
				FROM ' . TABLE_MAGNA_TRADORIA_PREPARE . '
				WHERE MpId = ' . $this->mpId . '
					AND MarketplaceCategoriesName = "' . $category . '"
					' . $sSQLAnd . '
			', false), true), true);
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
				FROM ' . TABLE_MAGNA_TRADORIA_PREPARE . '
				WHERE mpID = ' . $this->mpId . '
					AND MarketplaceCategoriesName = "' . $category . '"
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
		return array('attributes' => array());
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
