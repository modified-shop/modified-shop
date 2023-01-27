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

class CdiscountProductSaver {
	const DEBUG = false;
	const MARKETING_DESC_MAX_LENGTH = 5000;

	public $aErrors = array();

	protected $aMagnaSession = array();
	protected $sMarketplace = '';
	protected $sMpId = 0;
	protected $gambioPropertiesEnabled = false;
	protected $sEanColumn = '';

	protected $aConfig = array();

	public function __construct($magnaSession) {
		$this->aMagnaSession = &$magnaSession;
		$this->sMarketplace = $this->aMagnaSession['currentPlatform'];
		$this->mpId = $this->aMagnaSession['mpID'];

		$this->aConfig['keytype'] = getDBConfigValue('general.keytype', '0');
		$this->gambioPropertiesEnabled = (getDBConfigValue('general.options', '0', 'old') === 'gambioProperties');
		if (MagnaDB::gi()->columnExistsInTable('gm_ean', TABLE_PRODUCTS_ATTRIBUTES)) {
			$this->sEanColumn = 'gm_ean';
		} else if (MagnaDB::gi()->columnExistsInTable('attributes_ean', TABLE_PRODUCTS_ATTRIBUTES)) {
			$this->sEanColumn = 'attributes_ean';
		}
	}

	protected function insertPrepareData($aData) {
		if (($hp = magnaContribVerify('CdiscountInsertPrepareData', 1)) !== false) {
			require($hp);
		}
		if (self::DEBUG) {
			echo print_m($aData, __METHOD__);
			die();
		}
		#echo print_m($aData, __METHOD__);
		MagnaDB::gi()->insert(TABLE_MAGNA_CDISCOUNT_PREPARE, $aData, true);
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
		$aRow['EAN'] = $result[0]['products_ean'];
		$aRow['PrepareType'] = 'Apply';
		$aRow['Verified'] = 'OK';


		// If Title is not set multi prepare is used so Title and Description should be used from product.
		if (!isset($aItemDetails['Title'])) {
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

			$aItemDetails['Title'] = $prod[0]['Title'];
			$aItemDetails['Subtitle'] = $prod[0]['Subtitle'];
			$aItemDetails['Description'] = '';
			$aItemDetails['MarketingDescription'] = '';

			CdiscountHelper::setDescriptionAndMarketingDescription($prod[0]['products_id'], $prod[0]['Description'], $aItemDetails['Description'], $aItemDetails['MarketingDescription']);
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

		if (empty($aItemDetails['PrimaryCategory'])) {
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_CDISCOUNT_ERROR_CATEGORY'] = ML_CDISCOUNT_ERROR_CATEGORY;
		} else {
			$aRow['PrimaryCategory'] = $aItemDetails['PrimaryCategory'];
            $aRow['TopMarketplaceCategory'] = $aItemDetails['PrimaryCategory'];
			$aRow['MarketplaceCategoriesName'] = $aItemDetails['MarketplaceCategoriesName'];
		}

		if (empty($aRow['EAN'])) {
			$aRow['EAN'] = $this->getFirstVariationEAN($iProductId);
		}
		if (empty($aRow['EAN'])) {
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_CDISCOUNT_ERROR_EAN'] = ML_CDISCOUNT_ERROR_EAN;
		}

		$aRow['Title'] = CdiscountHelper::cdiscountSanitizeTitle($aItemDetails['Title']);
		if (empty($aRow['Title'])) {
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_CDISCOUNT_ERROR_TITLE'] = ML_CDISCOUNT_ERROR_TITLE;
		}

		if (!empty($aItemDetails['Subtitle'])) {
			$aRow['Subtitle'] = CdiscountHelper::cdiscountSanitizeSubtitle($aItemDetails['Subtitle']);
		} else {
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_CDISCOUNT_ERROR_SUBTITLE'] = ML_CDISCOUNT_ERROR_SUBTITLE;
		}

		$aRow['Description'] = fixHTMLUTF8Entities(CdiscountHelper::cdiscountSanitizeDesc($aItemDetails['Description']), ENT_COMPAT);
		if (empty($aRow['Description'])) {
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_CDISCOUNT_ERROR_DESCRIPTION'] = ML_CDISCOUNT_ERROR_DESCRIPTION;
		}

		$aRow['MarketingDescription'] = fixHTMLUTF8Entities(CdiscountHelper::truncateString($aItemDetails['MarketingDescription'], self::MARKETING_DESC_MAX_LENGTH), ENT_COMPAT);

		if ((int)$aItemDetails['PreparationTime'] < 1 || (int)$aItemDetails['PreparationTime'] > 10) {
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_CDISCOUML_ERROR_REPARATIONTIME'] = ML_CDISCOUNT_ERROR_REPARATIONTIME;
		} else {
			$aRow['PreparationTime'] = $aItemDetails['PreparationTime'];
		}

		$aRow['CategoryAttributes'] = $aItemDetails['CategoryAttributes'];
		
		$aRow['ShippingFeeStandard'] = $this->toFloat($aItemDetails['ShippingFeeStandard']);
		$aRow['ShippingFeeExtraStandard'] = $this->toFloat($aItemDetails['ShippingFeeExtraStandard']);
		$aRow['ShippingFeeTracked'] = $this->toFloat($aItemDetails['ShippingFeeTracked']);
		$aRow['ShippingFeeExtraTracked'] = $this->toFloat($aItemDetails['ShippingFeeExtraTracked']);
		$aRow['ShippingFeeRegistered'] = $this->toFloat($aItemDetails['ShippingFeeRegistered']);
		$aRow['ShippingFeeExtraRegistered'] = $this->toFloat($aItemDetails['ShippingFeeExtraRegistered']);

		$aRow['ShippingProfileName'] = json_encode(array_reverse(array_values($aItemDetails['conf']['cdiscount.shippingprofile.name'])));
		$aRow['ShippingFee'] = json_encode($aItemDetails['conf']['cdiscount.shippingprofile.fee']);
		$aRow['ShippingFeeAdditional'] = json_encode($aItemDetails['conf']['cdiscount.shippingprofile.feeadditional']);

		$aRow['ConditionType'] = $aItemDetails['condition_id'];
		$aRow['Comment'] = $aItemDetails['comment'];

		if (isset($aItemDetails['variationTheme'])) {
			$aRow['variation_theme'] = $aItemDetails['variationTheme'];
		}

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
		$sKeyType = 'products_'.((getDBConfigValue('general.keytype', '0') == 'artNr') ? 'model' : 'id');

		foreach ($itemDetails['model'] as $pId => $productModel) {

			$productId = $itemDetails['match'][$pId];

			if ($productId === 'false') {
				continue;
			}

			$matchedProduct = array(
				'mpID'				=> $this->mpId,
				'products_id'		=> $pId,
				'products_model'	=> $productModel,
				'Title'				=> $itemDetails['title'][$productId],
				'EAN'				=> $itemDetails['ean'][$productId],
				'ConditionType'		=> $itemDetails['unit']['condition_id'],
				'Comment'			=> $itemDetails['unit']['comment'],
				'PrepareType'		=> 'Match',
				'Verified'			=> 'OK',
				'PreparedTs'		=> date('Y-m-d H:i:s'),
			);

			MagnaDB::gi()->insert(TABLE_MAGNA_CDISCOUNT_PREPARE, $matchedProduct, true);

		}
	}

	/**
	 * Get the first EAN for a variation (EAN is mandatory, so we need at least 1 so that at least 1 variation can be uploaded)
	 *  @param $iProductId
	 *  @return string
	 */
	private function getFirstVariationEAN($iProductId) {
		if ($this->gambioPropertiesEnabled) {
			return (string)MagnaDB::gi()->fetchOne('SELECT combi_ean FROM products_properties_combis
			    WHERE products_id = '.$iProductId.'
			      AND LENGTH(combi_ean) > 0
			    ORDER BY products_properties_combis_id LIMIT 1');
		} else {
			if (empty($this->sEanColumn)) return '';
			return (string)MagnaDB::gi()->fetchOne('SELECT '.$this->sEanColumn.' FROM '.TABLE_PRODUCTS_ATTRIBUTES.'
			    WHERE products_id = '.$iProductId.'
			      AND LENGTH('.$this->sEanColumn.') > 0
			    ORDER BY products_attributes_id LIMIT 1');
		}
	}

	/**
	 * Converts string to float
	 * 
	 * @param $num
	 * @return float
	 */
	private function toFloat($num) {
		$dotPos = strrpos($num, '.');
		$commaPos = strrpos($num, ',');
		$sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
			((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

		if (!$sep) {
			return floatval(preg_replace("/[^0-9]/", "", $num));
		}

		return floatval(
			preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
			preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
		);
	}
	
}
