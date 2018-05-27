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
 * (c) 2010 - 2016 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class TradoriaProductSaver {
	const DEBUG = false;
	public $aErrors = array();

	protected $aMagnaSession = array();
	protected $sMarketplace = '';
	protected $sMpId = 0;

	protected $aConfig = array();

	public function __construct($magnaSession) {
		$this->aMagnaSession = &$magnaSession;
		$this->sMarketplace = $this->aMagnaSession['currentPlatform'];
		$this->mpId = $this->aMagnaSession['mpID'];

		$this->aConfig['keytype'] = getDBConfigValue('general.keytype', '0');
	}

	protected function insertPrepareData($aData) {
		/* {Hook} "TradoriaInsertPrepareData": Enables you to modify the prepared product data before it will be saved.<br>
			Variables that can be used:
			<ul>
			 <li><code>$aData</code>: The data of a product.</li>
			 <li>$this->mpID</code>: The ID of the marketplace.</li>
			</ul>
		*/
		if (($hp = magnaContribVerify('TradoriaInsertPrepareData', 1)) !== false) {
			require($hp);
		}
		if (self::DEBUG) {
			echo print_m($aData, __METHOD__);
			die();
		}
		#echo print_m($aData, __METHOD__);
			MagnaDB::gi()->insert(TABLE_MAGNA_TRADORIA_PREPARE, $aData, true);
	}

	/**
	 * Hilfsfunktion fuer SaveHoodSingleProductProperties und SaveHoodMultipleProductProperties
	 * bereite die DB-Zeile vor mit allen Daten die sowohl fuer Single als auch Multiple inserts gelten
	 */
	protected function preparePropertiesRow($iProductId, $aItemDetails) {
		$aRow = array();
		$aRow['mpID'] = $this->mpId;
		$aRow['products_id'] = $iProductId;
		if (MagnaDB::gi()->columnExistsInTable('products_ean', TABLE_PRODUCTS)) {
			$selectEAN = 'products_ean';
		} else {
			$selectEAN = "'' as products_ean";
		}
		$result = MagnaDB::gi()->fetchArray('
			SELECT products_model, '.$selectEAN.'
			  FROM '.TABLE_PRODUCTS.'
			 WHERE products_id =' . $iProductId
		);
		
		$aRow['products_model'] = $result[0]['products_model'];
		$aRow['EAN'] = $result[0]['products_ean'];
		$aRow['PrepareType'] = 'Apply';
		$aRow['Verified'] = 'OK';

		if (!isset($aItemDetails['PrimaryCategory']) || $aItemDetails['PrimaryCategory'] === '') {
			$this->aErrors['ML_RICARDO_ERROR_CATEGORY'] = ML_RICARDO_ERROR_CATEGORY;
		} else {
			$aRow['PrimaryCategory'] = $aItemDetails['PrimaryCategory'];
			$aRow['MarketplaceCategoriesName'] = $aItemDetails['PrimaryCategory'];
			$aRow['TopMarketplaceCategory'] = $aItemDetails['PrimaryCategory'];
		}

		$aRow['CategoryAttributes'] = $aItemDetails['CategoryAttributes'];

		if (!empty($this->aErrors)) {
			$aRow['Verified'] = 'ERROR';
		}

		return $aRow;
	}

	public function saveSingleProductProperties($iProductId, $aItemDetails, $prepareType) {
		//No SingleProductSave at this Time so use Multi
		$this->saveMultipleProductProperties(array($iProductId), $aItemDetails, $prepareType);
	}

	public function saveMultipleProductProperties($iProductIds, $aItemDetails, $prepareType) {
		if ($prepareType === 'match') {
			$this->insertMatchProduct($aItemDetails);
			return;
		}
		
		$preparedTs = date('Y-m-d H:i:s');
		foreach ($iProductIds as $iProductId) {
			$aRow = $this->preparePropertiesRow($iProductId, $aItemDetails);
			$aRow['PreparedTs'] = $preparedTs;
			$this->insertPrepareData($aRow);
		}
	}
	
	private function insertMatchProduct($itemDetails) {
		foreach ($itemDetails['model'] as $pId => $productModel) {

			$productId = $itemDetails['match'][$pId];

			if ($productId === 'false') {
				continue;
			}

			$matchedProduct = array(
				'mpID'				=> $this->mpId,
				'products_id'		=> $pId,
				'products_model'	=> $productModel,
				'Title'				=> $itemDetails['matching'][$pId]['title'],
				'EAN'				=> $itemDetails['matching'][$pId]['ean'],
				'ConditionType'		=> $itemDetails['unit']['condition_id'],
				'ShippingTime'		=> $itemDetails['unit']['shippingtime'],
				'Location'			=> $itemDetails['unit']['deliverycountry'],
				'Comment'			=> $itemDetails['unit']['comment'],
				'PrepareType'		=> 'Match',
				'Verified'			=> 'OK',
				'PreparedTs'		=> date('Y-m-d H:i:s'),
			);

				MagnaDB::gi()->insert(TABLE_MAGNA_TRADORIA_PREPARE, $matchedProduct, true);
		}
	}
}
