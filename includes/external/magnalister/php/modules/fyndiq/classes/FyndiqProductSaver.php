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

require_once(DIR_MAGNALISTER_MODULES.'fyndiq/FyndiqHelper.php');

class FyndiqProductSaver {
	const DEBUG = false;
	const TITLE_MAX_LENGTH = 64;
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
		if (($hp = magnaContribVerify('FyndiqInsertPrepareData', 1)) !== false) {
			require($hp);
		}
		if (self::DEBUG) {
			echo print_m($aData, __METHOD__);
			die();
		}
		#echo print_m($aData, __METHOD__);
		MagnaDB::gi()->insert(TABLE_MAGNA_FYNDIQ_PROPERTIES, $aData, true);
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
			SELECT products_model
			  FROM '.TABLE_PRODUCTS.'
			 WHERE products_id =' . $iProductId
		);

		$aRow['products_model'] = $result[0]['products_model'];
		$aRow['Verified'] = 'OK';


		// If Title is not set multi prepare is used so Title and Description should be used from product.
		if (isset($aItemDetails['Title']) === false) {
			$lang = getDBConfigValue($this->sMarketplace . '.lang', $this->mpId);

			$prod = MagnaDB::gi()->fetchArray('
				SELECT
					p.products_id,
					p.products_model,
					p.products_image as PictureUrl,
					pd.products_name as Title,
					pd.products_description as Description
				FROM ' . TABLE_PRODUCTS . ' p
				LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON pd.products_id = p.products_id AND pd.language_id = "' . $lang . '"
				WHERE p.products_id = ' . $iProductId
			);

			$aItemDetails['Title'] = $prod[0]['Title'];
			$aItemDetails['Description'] = $prod[0]['Description'];
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

		if (!isset($aItemDetails['mpCategory']) || $aItemDetails['mpCategory'] === '') {
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_FYNDIQ_ERROR_CATEGORY'] = ML_FYNDIQ_ERROR_CATEGORY;
		} else {
			$aRow['MarketplaceCategory'] = $aItemDetails['mpCategory'];
			$aRow['TopMarketplaceCategory'] = $aItemDetails['mpCategory'];
		}

		$aRow['Title'] = fixHTMLUTF8Entities($aItemDetails['Title'], ENT_COMPAT);
		if (!isset($aRow['Title']) || $aRow['Title'] === '') {
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_FYNDIQ_ERROR_TITLE'] = ML_FYNDIQ_ERROR_TITLE;
		}

		$aRow['Description'] = fixHTMLUTF8Entities(FyndiqHelper::fyndiqSanitizeDesc($aItemDetails['Description']), ENT_COMPAT);
		if (!isset($aRow['Description']) || $aRow['Description'] === '') {
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_FYNDIQ_ERROR_DESCRIPTION'] = ML_FYNDIQ_ERROR_DESCRIPTION;
		}

		$aRow['ShippingCost'] = $this->toFloat($aItemDetails['ShippingCost']);

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

	public function resetProductProperties($iProductId) {
		$sLanguageCode = getDBConfigValue($this->sMarketplace . '.lang', $this->mpId);
		$prod = MagnaDB::gi()->fetchArray('
				SELECT 	p.products_id,
						p.products_model,
						p.products_image as PictureUrl,
						pd.products_name as Title,
						pd.products_description as Description
			  	FROM ' . TABLE_PRODUCTS . ' p
			 	LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON pd.products_id = p.products_id AND pd.language_id = "' . $sLanguageCode . '"
				WHERE p.products_id = ' . $iProductId
		);

		if (empty($prod)) {
			return;
		}

		$product = array();
		$product['Title'] = $prod[0]['Title'];
		$product['Description'] = $prod[0]['Description'];

		$aProduct = MLProduct::gi()->setLanguage(getDBConfigValue($this->sMarketplace . '.lang', $this->mpId))->getProductById($iProductId);

		$images = array();

		foreach ($aProduct['Images'] as $image) {
			$images[$image] = 'true';
		}

		$product['PictureURL'] = json_encode($images);


		if (empty($product)) {
			return;
		}

		$where = ($this->config['keytype'] == 'artNr')
			? array ('products_model' => $product['products_model'])
			: array ('products_id' => $iProductId);
		$where['mpID'] = $this->mpId;

		MagnaDB::gi()->update(TABLE_MAGNA_FYNDIQ_PROPERTIES, $product, $where);
	}
	
	private function addToErrorLog($errorMessage, $sku) {
		$errorData = array('SKU' => $sku);
		$error = array (
				'mpID' => $this->mpId,
				'origin' => 'plugin',
				'errormessage' => $errorMessage,
				'dateadded' =>date("Y-m-d H:i:s"),
				'additionaldata' => serialize($errorData),
			);
		MagnaDB::gi()->insert(TABLE_MAGNA_COMPAT_ERRORLOG, $error);
	}

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
