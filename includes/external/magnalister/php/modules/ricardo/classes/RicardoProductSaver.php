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

class RicardoProductSaver {
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
		if (($hp = magnaContribVerify('RicardoInsertPrepareData', 1)) !== false) {
			require($hp);
		}
		if (self::DEBUG) {
			echo print_m($aData, __METHOD__);
			die();
		}
		#echo print_m($aData, __METHOD__);
		MagnaDB::gi()->insert(TABLE_MAGNA_RICARDO_PROPERTIES, $aData, true);
	}

	/**
	 * Hilfsfunktion fuer SaveHoodSingleProductProperties und SaveHoodMultipleProductProperties
	 * bereite die DB-Zeile vor mit allen Daten die sowohl fuer Single als auch Multiple inserts gelten
	 */
	protected function preparePropertiesRow($iProductId, $aItemDetails) {
		#echo print_m(func_get_args(), __METHOD__);
		$aRow = array();
		$aRow['mpID'] = $this->mpId;
		$aRow['products_id'] = $iProductId;
		$aRow['products_model'] = MagnaDB::gi()->fetchOne('
			SELECT products_model
			  FROM ' . TABLE_PRODUCTS . '
			 WHERE products_id = ' . $iProductId
		);

		$aRow['Verified'] = 'OK';
		$aRow['LangDe'] = (isset($aItemDetails['LangDe']) && $aItemDetails['LangDe'] === 'on') ? 'true' : 'false';
		$aRow['LangFr'] = (isset($aItemDetails['LangFr']) && $aItemDetails['LangFr'] === 'on') ? 'true' : 'false';

		// If neither TitleDe or TitleFr is set multi prepare is used so Title and Description should be used from product.
		if (isset($aItemDetails['TitleDe']) === false || isset($aItemDetails['TitleFr']) === false) {
			$bShortDescColumnExists = MagnaDB::gi()->columnExistsInTable('products_short_description', TABLE_PRODUCTS_DESCRIPTION);
			$lang = getDBConfigValue($this->sMarketplace.'.lang', $this->mpId);

			$prod = MagnaDB::gi()->fetchArray('
				SELECT 
					p.products_id,
					p.products_model,
					p.products_image as PictureUrl,
					pdde.products_name as TitleDe,
					pdfr.products_name as TitleFr,
					'.(($bShortDescColumnExists) ? 'pdde.products_short_description' : "''").' as SubtitleDe,
					'.(($bShortDescColumnExists) ? 'pdfr.products_short_description' : "''").' as SubtitleFr,
					pdde.products_description as DescriptionDe,
					pdfr.products_description as DescriptionFr
				  FROM ' . TABLE_PRODUCTS . ' p
			 LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pdde ON pdde.products_id = p.products_id AND pdde.language_id = "' . $lang['DE'] . '"
			 LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pdfr ON pdfr.products_id = p.products_id AND pdfr.language_id = "' . $lang['FR'] . '"
				 WHERE p.products_id = ' . $iProductId
			);

			RicardoHelper::getTitleAndDescription('De', $prod, $this->mpId);
			RicardoHelper::getTitleAndDescription('Fr', $prod, $this->mpId);

			$aItemDetails['TitleDe'] = $prod[0]['TitleDe'];
			$aItemDetails['TitleFr'] = $prod[0]['TitleFr'];
			$aItemDetails['SubtitleDe'] = $prod[0]['SubtitleDe'];
			$aItemDetails['SubtitleFr'] = $prod[0]['SubtitleFr'];
			$aItemDetails['DescriptionDe'] = $prod[0]['DescriptionDe'];
			$aItemDetails['DescriptionFr'] = $prod[0]['DescriptionFr'];
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
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_RICARDO_ERROR_CATEGORY'] = ML_RICARDO_ERROR_CATEGORY;
		} else {
			$aRow['MarketplaceCategories'] = $aItemDetails['PrimaryCategory'];
			$aRow['TopMarketplaceCategory'] = $aItemDetails['PrimaryCategory'];
		}

		$aRow['TitleDe'] = $aItemDetails['TitleDe'];
		if ((!isset($aRow['TitleDe']) || $aRow['TitleDe'] === '') && ($aRow['LangDe'] === 'true')) {
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_RICARDO_ERROR_TITLE'] = ML_RICARDO_ERROR_TITLE;
		}

		$aRow['TitleFr'] = $aItemDetails['TitleFr'];
		if ((!isset($aRow['TitleFr']) || $aRow['TitleFr'] === '') && ($aRow['LangFr'] === 'true')) {
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_RICARDO_ERROR_TITLE'] = ML_RICARDO_ERROR_TITLE;
		}

		if (isset($aItemDetails['SubtitleDe']) === true) {
			$aRow['SubtitleDe'] = RicardoHelper::ricardoSanitizeSubtitle($aItemDetails['SubtitleDe']);
		}

		if (isset($aItemDetails['SubtitleFr']) === true) {
			$aRow['SubtitleFr'] = RicardoHelper::ricardoSanitizeSubtitle($aItemDetails['SubtitleFr']);
		}

		$aRow['DescriptionDe'] = $aItemDetails['DescriptionDe'];
		if ((!isset($aRow['DescriptionDe']) || $aRow['DescriptionDe'] === '') && ($aRow['LangDe'] === 'true')) {
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_RICARDO_ERROR_DESCRIPTION'] = ML_RICARDO_ERROR_DESCRIPTION;
		}

		$aRow['DescriptionFr'] = $aItemDetails['DescriptionFr'];
		if ((!isset($aRow['DescriptionFr']) || $aRow['DescriptionFr'] === '') && ($aRow['LangFr'] === 'true')) {
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_RICARDO_ERROR_DESCRIPTION'] = ML_RICARDO_ERROR_DESCRIPTION;
		}
		
		$aRow['DescriptionTemplate'] = $aItemDetails['DescriptionTemplate'];
		
		$aRow['ArticleCondition'] = $aItemDetails['ArticleCondition'];
		$aRow['BuyingMode'] = $aItemDetails['conf']['ricardo.checkin.buyingmode'];
		
		$aRow['StartDate'] = $aItemDetails['conf']['ricardo.start_date'];		
		$aRow['EndTime'] = $aItemDetails['conf']['ricardo.end_time'];
		$aRow['Duration'] = $aItemDetails['conf']['ricardo.checkin.duration'];
		$aRow['MaxRelistCount'] = $aItemDetails['conf']['ricardo.checkin.maxrelistcount'];
		$aRow['Warranty'] = (bool)$aItemDetails['conf']['ricardo.checkin.warranty'];
		
		$aRow['WarrantyDescriptionDe'] = $aItemDetails['conf']['ricardo.checkin.warranty.description.de'];
		if ((!isset($aRow['WarrantyDescriptionDe']) || $aRow['WarrantyDescriptionDe'] === '') && ($aRow['Warranty'] == 0) && ($aRow['LangDe'] === 'true')) {
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_RICARDO_ERROR_WARRANTY'] = ML_RICARDO_ERROR_WARRANTY;
		}
		
		$aRow['WarrantyDescriptionFr'] = $aItemDetails['conf']['ricardo.checkin.warranty.description.fr'];
		if ((!isset($aRow['WarrantyDescriptionFr']) || $aRow['WarrantyDescriptionFr'] === '') && ($aRow['Warranty'] == 0) && ($aRow['LangFr'] === 'true')) {
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_RICARDO_ERROR_WARRANTY'] = ML_RICARDO_ERROR_WARRANTY;
		}

		$posible = array('0', '8192', '1073741824');

		$payment = true;
		if (!empty($aItemDetails['conf']['ricardo.checkin.paymentdetails']) && count($aItemDetails['conf']['ricardo.checkin.paymentdetails']) <= 2) {
			$intersect = array_intersect($posible, $aItemDetails['conf']['ricardo.checkin.paymentdetails']);
			if (count($intersect) !== 1 ||
				(count($aItemDetails['conf']['ricardo.checkin.paymentdetails']) == 2 && in_array('1073741824', $aItemDetails['conf']['ricardo.checkin.paymentdetails']))) {
				$payment = false;
			}
		} else {
			$payment = false;
		}

		if ($payment === true) {
			$aRow['PaymentDetails'] = json_encode($aItemDetails['conf']['ricardo.checkin.paymentdetails']);
		} else {
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_RICARDO_ERROR_PAYMENTDETAILS'] = ML_RICARDO_ERROR_PAYMENTDETAILS;
		}

		$needsPaymentDesc = in_array('0', $aItemDetails['conf']['ricardo.checkin.paymentdetails']);
		$aRow['PaymentdetailsDescriptionDe'] = $aItemDetails['conf']['ricardo.checkin.paymentdetails.description.de'];
		if ($needsPaymentDesc && (!isset($aRow['PaymentdetailsDescriptionDe']) || $aRow['PaymentdetailsDescriptionDe'] === '') && ($aRow['LangDe'] === 'true')) {
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_RICARDO_ERROR_PAYMENT'] = ML_RICARDO_ERROR_PAYMENT;
		}

		$aRow['PaymentdetailsDescriptionFr'] = $aItemDetails['conf']['ricardo.checkin.paymentdetails.description.fr'];
		if ($needsPaymentDesc && (!isset($aRow['PaymentdetailsDescriptionFr']) || $aRow['PaymentdetailsDescriptionFr'] === '') && ($aRow['LangFr'] === 'true')) {
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_RICARDO_ERROR_PAYMENT'] = ML_RICARDO_ERROR_PAYMENT;
		}

		$aRow['ShippingDetails'] = $aItemDetails['conf']['ricardo.checkin.shippingdetails'];
		$aRow['ShippingCost'] = $aItemDetails['conf']['ricardo.checkin.shippingdetails.shippingcost'];
		$aRow['PackageSize'] = $aItemDetails['conf']['ricardo.checkin.shippingdetails.packagesize'];
		$aRow['ShippingCumulative'] = $aItemDetails['conf']['ricardo.checkin.shippingdetails.shippingcumulative'];
		
		$aRow['ShippingDescriptionDe'] = $aItemDetails['conf']['ricardo.checkin.shippingdetails.description.de'];
		if ((!isset($aRow['ShippingDescriptionDe']) || $aRow['ShippingDescriptionDe'] === '') && ($aRow['ShippingDetails'] == 0) && ($aRow['LangDe'] === 'true')) {
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_RICARDO_ERROR_SHIPPING'] = ML_RICARDO_ERROR_SHIPPING;
		}

		$aRow['ShippingDescriptionFr'] = $aItemDetails['conf']['ricardo.checkin.shippingdetails.description.fr'];
		if ((!isset($aRow['ShippingDescriptionFr']) || $aRow['ShippingDescriptionFr'] === '') && ($aRow['ShippingDetails'] == 0) && ($aRow['LangFr'] === 'true')) {
			$aRow['Verified'] = 'ERROR';
			$this->aErrors['ML_RICARDO_ERROR_SHIPPING'] = ML_RICARDO_ERROR_SHIPPING;
		}

		if (isset($aItemDetails['BuyNowPrice'])) {
			$aRow['BuyNowPrice'] = $aItemDetails['BuyNowPrice'];
		}

		$aRow['EnableBuyNowPrice'] = isset($aItemDetails['EnableBuyNowPrice']) ? $aItemDetails['EnableBuyNowPrice'] : 'off';

		if (isset($aItemDetails['StartPrice'])) {
			$aRow['StartPrice'] = $aItemDetails['StartPrice'];
		}

		if (isset($aItemDetails['Increment'])) {
			$aRow['Increment'] = $aItemDetails['Increment'];
		}

		if ($aRow['BuyingMode'] === 'auction') {
			if (isset($aItemDetails['StartPrice']) === false || ((double) $aItemDetails['StartPrice']) <= 0) {
				$aRow['Verified'] = 'ERROR';
				$this->aErrors['ML_RICARDO_ERROR_START_PRICE'] = ML_RICARDO_ERROR_START_PRICE;
			}

			if (isset($aItemDetails['Increment']) === false || ((double) $aItemDetails['Increment']) <= 0) {
				$aRow['Verified'] = 'ERROR';
				$this->aErrors['ML_RICARDO_ERROR_INCREMENT'] = ML_RICARDO_ERROR_INCREMENT;
			}
		}

		if (isset($aItemDetails['Availability'])) {
			$aRow['Availability'] = $aItemDetails['Availability'];
		}

		if (isset($aItemDetails['FirstPromotion'])) {
			$aRow['FirstPromotion'] = $aItemDetails['FirstPromotion'];
		}

		if (isset($aItemDetails['SecondPromotion'])) {
			$aRow['SecondPromotion'] = $aItemDetails['SecondPromotion'];
		}

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
		$lang = getDBConfigValue($this->sMarketplace.'.lang', $this->mpId);

		$prod = MagnaDB::gi()->fetchArray('
			SELECT p.products_model,
				   p.products_id,
				   pdde.products_name as TitleDe,
				   pdfr.products_name as TitleFr,
				   pdde.products_description as DescriptionDe,
				   pdfr.products_description as DescriptionFr
			  FROM ' . TABLE_PRODUCTS . ' p
		 LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pdde ON pdde.products_id = p.products_id AND pdde.language_id = "' . $lang['DE'] . '"
		 LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pdfr ON pdfr.products_id = p.products_id AND pdfr.language_id = "' . $lang['FR'] . '"
			 WHERE p.products_id = ' . $iProductId
		);

		if (empty($prod)) {
			return;
		}

		$product = array();
		$product['TitleDe'] = $prod[0]['TitleDe'];
		$product['TitleFr'] = $prod[0]['TitleFr'];
		$product['DescriptionDe'] = $prod[0]['DescriptionDe'];
		$product['DescriptionFr'] = $prod[0]['DescriptionFr'];

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

		MagnaDB::gi()->update(TABLE_MAGNA_RICARDO_PROPERTIES, $product, $where);
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
}
