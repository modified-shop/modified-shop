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

class PriceministerProductSaver {
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
		if (($hp = magnaContribVerify('PriceministerInsertPrepareData', 1)) !== false) {
			require($hp);
		}
		if (self::DEBUG) {
			echo print_m($aData, __METHOD__);
			die();
		}
		#echo print_m($aData, __METHOD__);
		MagnaDB::gi()->insert(TABLE_MAGNA_PRICEMINISTER_PREPARE, $aData, true);
	}

	/**
	 * Hilfsfunktion fuer SaveHoodSingleProductProperties und SaveHoodMultipleProductProperties
	 * bereite die DB-Zeile vor mit allen Daten die sowohl fuer Single als auch Multiple inserts gelten
	 */
	protected function preparePropertiesRow($iProductId, $aItemDetails) {
		$aRow = array();
		$aRow['mpID'] = $this->mpId;
		$aRow['products_id'] = $iProductId;
		$result = MagnaDB::gi()->fetchArray('
			SELECT products_model, products_ean
			  FROM '.TABLE_PRODUCTS.'
			 WHERE products_id =' . $iProductId
		);
		
		$aRow['products_model'] = $result[0]['products_model'];
		$aRow['PrepareType'] = 'Apply';
		$aRow['Verified'] = 'OK';

		// If Title is not set multi prepare is used so Title and Description should be used from product.
		if (isset($aItemDetails['Title']) === false) {
			$lang = getDBConfigValue($this->sMarketplace . '.lang', $this->mpId);

			$prod = MagnaDB::gi()->fetchArray('
				SELECT 
					p.products_id,
					p.products_model,
					p.products_ean as EAN,
					p.products_image as PictureUrl,
					pd.products_name as Title,
					'.(MagnaDB::gi()->columnExistsInTable('products_short_description', TABLE_PRODUCTS_DESCRIPTION) ? 'pd.products_short_description' : '"" AS Subtitle').',
					pd.products_description as Description
				FROM ' . TABLE_PRODUCTS . ' p
				LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON pd.products_id = p.products_id AND pd.language_id = "' . $lang . '"
				WHERE p.products_id = ' . $iProductId
			);

            PriceministerHelper::getTitleAndDescription($prod, $this->mpId);
			$aItemDetails['Title'] = $prod[0]['Title'];
			$aItemDetails['Description'] = $prod[0]['Description'];
			$aItemDetails['EAN'] = $prod[0]['EAN'];
		}

		if (isset($aItemDetails['Images'])) {
			$aImages = (array)$aItemDetails['Images'];
			if (in_array('false', $aImages) && count($aImages) > 1) {
				array_shift($aImages);
			}
			
			$aPictureURL = array();
			foreach ($aImages as $key => $value) {
				$aPictureURL[urldecode($key)] = $value;
			}
			
			$aRow['PictureURL'] = json_encode($aPictureURL);
		} else {
			$aProduct = MLProduct::gi()->setLanguage(getDBConfigValue($this->sMarketplace . '.lang', $this->mpId))->getProductById($iProductId);

			$images = array();

			foreach ($aProduct['Images'] as $image) {
				$images[$image] = 'true';
			}

			$aRow['PictureURL'] = json_encode($images);
		}

		if (!isset($aItemDetails['PrimaryCategory']) || $aItemDetails['PrimaryCategory'] === '') {
			$this->aErrors['ML_RICARDO_ERROR_CATEGORY'] = ML_RICARDO_ERROR_CATEGORY;
		} else {
            $aRow['MarketplaceCategoriesName'] = $aItemDetails['PrimaryCategory'];
			$aRow['MarketplaceCategories'] = $aItemDetails['PrimaryCategory'];
			$aRow['TopMarketplaceCategory'] = $aItemDetails['PrimaryCategory'];
		}

		$aRow['Title'] = PriceministerHelper::sanitizeTitle($aItemDetails['Title'], PriceministerHelper::$TITLE_MAX_LENGTH);
		if (!isset($aRow['Title']) || $aRow['Title'] === '') {
			$this->aErrors['ML_PRICEMINISTER_ERROR_TITLE'] = ML_PRICEMINISTER_ERROR_TITLE;
		}

		$aRow['Description'] = PriceministerHelper::truncateString($aItemDetails['Description'], PriceministerHelper::$DESC_MAX_LENGTH);
		if (!isset($aRow['Description']) || $aRow['Description'] === '') {
			$this->aErrors['ML_PRICEMINISTER_ERROR_DESCRIPTION'] = ML_PRICEMINISTER_ERROR_DESCRIPTION;
		}
		
		$aRow['CategoryAttributes'] = $aItemDetails['CategoryAttributes'];
		$aRow['ConditionType'] = $aItemDetails['condition_id'];
        $aRow['EAN'] = $aItemDetails['EAN'];

		if (!empty($this->aErrors)) {
			$aRow['Verified'] = 'ERROR';
		}

		return $aRow;
	}

    public function saveSingleProductProperties($iProductId, $aItemDetails, $prepareType, $isAjax = false)
    {
		//No SingleProductSave at this Time so use Multi
        $this->saveMultipleProductProperties(array($iProductId), $aItemDetails, $prepareType, $isAjax);
	}

    public function saveMultipleProductProperties($iProductIds, $aItemDetails, $prepareType, $isAjax = false)
    {
        if ($prepareType === 'match'){
            $this->insertMatchProduct($aItemDetails, $isAjax, 1 != count($iProductIds));
			return;
		}
		
		$preparedTs = date('Y-m-d H:i:s');
		foreach ($iProductIds as $iProductId) {
			$aRow = $this->preparePropertiesRow($iProductId, $aItemDetails);
			$aRow['PreparedTs'] = $preparedTs;
			$this->insertPrepareData($aRow);
		}
	}
	
    private function insertMatchProduct($itemDetails, $isAjax = false, $multiMatching = false)
    {
        $missingCategories = array();

        foreach ($itemDetails['model'] as $pId => $productModel){
            $isOk = true;
			$productId = $itemDetails['match'][$pId];

			if ($productId === 'false') {
				continue;
			}

            if ($multiMatching){
                $itemDetails['matching'][$pId]['CategoryAttributes'] = MagnaDB::gi()->fetchOne('SELECT ShopVariation FROM ' . TABLE_MAGNA_PRICEMINISTER_VARIANTMATCHING . " WHERE MpIdentifier like '{$itemDetails['matching'][$pId]['category_id']}'");
                if (!$itemDetails['matching'][$pId]['CategoryAttributes']){
                    $missingCategories[] = $itemDetails['matching'][$pId]['category_name'];
                    $isOk = false;
                }
            } else if (!empty($this->aErrors)){
                $isOk = false;
            }

			$matchedProduct = array(
				'mpID'				=> $this->mpId,
				'products_id'		=> $pId,
				'products_model'	=> $productModel,
                'Title' => $itemDetails['matching'][$pId]['title'],
                'MarketplaceCategories' => $itemDetails['matching'][$pId]['category_id'],
                'MarketplaceCategoriesName' => $itemDetails['matching'][$pId]['category_id'],
                'TopMarketplaceCategory' => $itemDetails['matching'][$pId]['category_id'],
                'CategoryAttributes' => !$multiMatching ? $itemDetails['CategoryAttributes'] : $itemDetails['matching'][$pId]['CategoryAttributes'],
				'ConditionType'		=> $itemDetails['unit']['condition_id'],
				'PrepareType'		=> 'Match',
                'MPProductId' => $productId,
                'Verified' => ($isAjax || !$isOk ? 'ERROR' : 'OK'),
				'PreparedTs'		=> date('Y-m-d H:i:s'),
			);

			MagnaDB::gi()->insert(TABLE_MAGNA_PRICEMINISTER_PREPARE, $matchedProduct, true);
        }

        if ($multiMatching && count($missingCategories)){
            $this->aErrors['ML_PRICEMINISTER_NOT_MATCHED_CATEGORY'] = ML_PRICEMINISTER_NOT_MATCHED_CATEGORY . ' ' . implode(', ', $missingCategories);
        }
    }
}
