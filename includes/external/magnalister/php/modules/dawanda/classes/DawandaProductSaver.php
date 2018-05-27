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
 * $Id: $
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class DawandaProductSaver {
	const DEBUG = false;
	public $aErrors = array();

	protected $aMagnaSession = array();
	protected $sMarketplace = '';
	protected $sMpId = 0;

	protected $fPrice = null;

	protected $aConfig = array();

	public function __construct($magnaSession) {
		$this->aMagnaSession = &$magnaSession;
		$this->sMarketplace = $this->aMagnaSession['currentPlatform'];
		$this->mpId = $this->aMagnaSession['mpID'];

		$this->fPrice = new SimplePrice(null, getCurrencyFromMarketplace($this->sMpId));

		$this->aConfig['keytype'] = getDBConfigValue('general.keytype', '0');

		$this->aConfig['lang'] = getDBConfigValue($this->sMarketplace.'.lang', $this->sMpId, $_SESSION['languages_id']);
		$this->aConfig['hasShortDesc'] = MagnaDB::gi()->columnExistsInTable('products_short_description', TABLE_PRODUCTS_DESCRIPTION);

		$this->aConfig['imagepath'] = rtrim(getDBConfigValue($this->sMarketplace.'.imagepath', $this->sMpId), '/').'/';
	}

	protected function insertPrepareData($aData) {
		if (($hp = magnaContribVerify('DaWandaInsertPrepareData', 1)) !== false) {
			require($hp);
		}
		if (self::DEBUG) {
			echo print_m($aData, __METHOD__);
			die();
		}

		MagnaDB::gi()->insert(TABLE_MAGNA_DAWANDA_PROPERTIES, $aData, true);
	}

	protected function getManufacturerPartNumber(&$row) {
		$mfrmd = getDBConfigValue($this->marketplace.'.checkin.manufacturerpartnumber.table', $this->mpId, false);
		if (is_array($mfrmd) && !empty($mfrmd['column']) && !empty($mfrmd['table'])) {
			$pIDAlias = getDBConfigValue($this->marketplace.'.checkin.manufacturerpartnumber.alias', $this->mpId);
			if (empty($pIDAlias)) {
				$pIDAlias = 'products_id';
			}
			$row['ManufacturerPartNumber'] = MagnaDB::gi()->fetchOne('
				SELECT `' . $mfrmd['column'] . '`
				  FROM `' . $mfrmd['table'] . '`
				 WHERE `' . $pIDAlias . '`="' . MagnaDB::gi()->escape($row['products_id']) . '"
				 LIMIT 1
			');
		}
	}

	/**
	 * Hilfsfunktion fuer SaveHoodSingleProductProperties und SaveHoodMultipleProductProperties
	 * bereite die DB-Zeile vor mit allen Daten die sowohl fuer Single als auch Multiple inserts gelten
	 */
	protected function preparePropertiesRow($iProductId, $aItemDetails) {
		$aRow = array();
		$aRow['mpID'] = $this->mpId;
		$aRow['products_id'] = $iProductId;
		$aRow['products_model'] = MagnaDB::gi()->fetchOne('
			SELECT products_model
			  FROM '.TABLE_PRODUCTS.'
			 WHERE products_id =' . $iProductId
		);
		$aRow['Verified'] = 'OK';
		$aRow['ListingDuration'] = $aItemDetails['ListingDuration'];
		$aRow['ShippingService'] = $aItemDetails['ShippingService'];

		$aRow['ProductType'] = $aItemDetails['ProductType'];
		$aRow['ReturnPolicy'] = $aItemDetails['ReturnPolicy'];
		$aRow['MarketplaceCategories'] = $aItemDetails['PrimaryCategory'];
		$aRow['StoreCategories'] = $aItemDetails['StoreCategory'];

		//TopTenCategories
		$aRow['TopMarketplaceCategory'] = $aItemDetails['PrimaryCategory'];
		$aRow['TopStoreCategory'] = $aItemDetails['StoreCategory'];

		$aRow['CategoryAttributes'] = $aItemDetails['CategoryAttributes'];
		// Reset MpColors and Attributes if new prepare is done
		$aRow['MpColors'] = '';
		$aRow['Attributes'] = '';

		return $aRow;
	}

	public function saveSingleProductProperties($iProductId, $aItemDetails) {
		//No SingleProductSave at this Time so use Multi
		$this->saveMultipleProductProperties(array($iProductId), $aItemDetails);
	}

	public function saveMultipleProductProperties($iProductIds, $aItemDetails) {
		$preparedTs = date('Y-m-d H:i:s');
		foreach ($iProductIds as $iProductId) {
			$aRow = $this->preparePropertiesRow($iProductId, $aItemDetails);
			$aRow['PreparedTs'] = $preparedTs;
			$this->insertPrepareData($aRow);
		}
	}

	public function resetProductProperties($iProductIds) {
		
	}
}