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

class EtsyProductSaver {
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
		/* {Hook} "EtsyInsertPrepareData": Enables you to modify the prepared product data before it will be saved.<br>
			Variables that can be used:
			<ul>
			 <li><code>$aData</code>: The data of a product.</li>
			 <li>$this->mpID</code>: The ID of the marketplace.</li>
			</ul>
		*/
		if (($hp = magnaContribVerify('EtsyInsertPrepareData', 1)) !== false) {
			require($hp);
		}
		if (self::DEBUG) {
			echo print_m($aData, __METHOD__);
			die();
		}
		#echo print_m($aData, __METHOD__.' '.__LINE__);
		// prevent double entries
		if ('artNr' == $this->aConfig['keytype']) {
			 MagnaDB::gi()->delete(TABLE_MAGNA_ETSY_PREPARE, array(
				'mpID' => $this->mpId,
				'products_model' => $aData['products_model']
			));
		} else {
			 MagnaDB::gi()->delete(TABLE_MAGNA_ETSY_PREPARE, array(
				'mpID' => $this->mpId,
				'products_id' => $aData['products_id']
			));
		}
			MagnaDB::gi()->insert(TABLE_MAGNA_ETSY_PREPARE, $aData, true);
	}

	/**
	 * Hilfsfunktion fuer SaveSingleProductProperties und SaveMultipleProductProperties
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
		
		$aRow['PreparedTS'] = date('Y-m-d H:i:s');
		$aRow['Verified'] = 'OK'; // MP & API provides no Verify Request
		// Title, Description -> depends if Single or Multi

		if (!isset($aItemDetails['PrimaryCategory']) || $aItemDetails['PrimaryCategory'] === '') {
			$this->aErrors['ML_RICARDO_ERROR_CATEGORY'] = ML_RICARDO_ERROR_CATEGORY;
		} else {
			$aRow['PrimaryCategory'] = $aItemDetails['PrimaryCategory'];
		}

		//$aRow['ShopVariation'] = $aItemDetails['CategoryAttributes'];
		// TODO Attributes Matching
		$aRow['ShopVariation'] = $aItemDetails['ShopVariation'];
		$aRow['ShippingProfile'] = $aItemDetails['shippingprofile'];
		$aRow['Whomade']  = $aItemDetails['whomade'];
		$aRow['Whenmade'] = $aItemDetails['whenmade'];
		$aRow['IsSupply'] = $aItemDetails['issuply'];
		// Image -> depends if Single or Multi

		if (!empty($this->aErrors)) {
			$aRow['Verified'] = 'ERROR';
		}

		return $aRow;
	}

	public function saveSingleProductProperties($iProductId, $aItemDetails) {
		$aRow = $this->preparePropertiesRow($iProductId, $aItemDetails);
		$aRow['Title'] = $aItemDetails['Title'];
		$aRow['Description'] = EtsyHelper::sanitizeDescription($aItemDetails['Description']);
		if (    empty($aItemDetails['GalleryPictures'])
		     || !isset($aItemDetails['GalleryPictures']['Images'])
		     || empty($aItemDetails['GalleryPictures']['Images'])  ) {
			$aRow['Image'] = '';
		} else {
			$aRow['Image'] = array();
			foreach ($aItemDetails['GalleryPictures']['Images'] as $name => $checked) {
				if ($checked === 'true') $aRow['Image'][] = $name;
			}
		}
		$aRow['Image'] = json_encode($aRow['Image']);
		$this->insertPrepareData($aRow);
	}

	// TODO testen
	public function saveMultipleProductProperties($aProductIds, $aItemDetails) {
		$aProductDescData = MagnaDB::gi()->fetchArray('
			SELECT products_id, products_name, products_description
			  FROM '.TABLE_PRODUCTS_DESCRIPTION.'
			 WHERE products_id IN ('. implode(', ', $aProductIds) .')
			   AND language_id = "' . getDBConfigValue('etsy.lang', $this->mpId) . '" 
		');
		$aProductMainImages = MagnaDB::gi()->fetchArray('
			SELECT products_id, products_image
			  FROM '.TABLE_PRODUCTS.'
			 WHERE products_id IN ('. implode(', ', $aProductIds) .')
		');
		$aProductImageData = MagnaDB::gi()->fetchArray('
			SELECT products_id, image_nr, image_name
			  FROM '.TABLE_PRODUCTS_IMAGES.'
			 WHERE products_id IN ('. implode(', ', $aProductIds) .')
			 ORDER BY products_id
		');
		$aProductDescDataByPId = array();
		// products data by pID
		foreach ($aProductDescData as $pdd) {
			$aProductDescDataByPId[$pdd['products_id']] = $pdd;
			unset($aProductDescDataByPId[$pdd['products_id']]['products_id']);
			unset($aProductDescDataByPId[$pdd['products_id']]['products_image']);
		}
		// add main image
		foreach ($aProductMainImages as $pmi) {
			$aProductDescDataByPId[$pmi['products_id']]['images'] = array (0 => $pmi['products_image']);
		}
		// add further images
		foreach ($aProductImageData as $imd) {
			$aProductDescDataByPId[$imd['products_id']]['images'][$imd['image_nr']] = $imd['image_name'];
		}
		// don't allow more than 10 images (otherwise Etsy rejects the item)
		foreach ($aProductIds as $iProductId) {
			if (count($aProductDescDataByPId[$iProductId]['images']) > 10) {
				$aProductDescDataByPId[$iProductId]['images'] = array_slice($aProductDescDataByPId[$iProductId]['images'], 0, 10);
			}
		}
		foreach ($aProductIds as $iProductId) {
			$aRow = $this->preparePropertiesRow($iProductId, $aItemDetails);
			$aRow['Title'] = $aProductDescDataByPId[$iProductId]['products_name'];
			$aRow['Description'] = EtsyHelper::sanitizeDescription($aProductDescDataByPId[$iProductId]['products_description']);
			$aRow['Image'] = json_encode($aProductDescDataByPId[$iProductId]['images']);
			$this->insertPrepareData($aRow);
		}
	}
}
