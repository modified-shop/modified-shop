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
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/checkin/MagnaCompatibleCheckinSubmit.php');
require_once(DIR_MAGNALISTER_MODULES.'dawanda/DawandaHelper.php');
require_once(DIR_MAGNALISTER_MODULES.'dawanda/classes/DawandaProductSaver.php');

class DawandaCheckinSubmit extends MagnaCompatibleCheckinSubmit {
	private $bVerify = false;
	private $oLastException = null;

	public function __construct($settings = array()) {
		global $_MagnaSession;

		$addLangsConfig = getDBConfigValue($settings['marketplace'] . '.lang', $_MagnaSession['mpID'], array());
		$mainLang = 0;
		$dawandaLangs = DawandaApiConfigValues::gi()->getLanguages();
		
		$addLangs = array();
		foreach ($addLangsConfig as $langKey => $langId) {
			if ($langId > 0) {
				$addLangs[] = $langId;
			}
		}
		
		if (isset($dawandaLangs['MainLanguage']) && isset($addLangsConfig[$dawandaLangs['MainLanguage']])) {
			$mainLang = $addLangsConfig[$dawandaLangs['MainLanguage']];
		} else {
			// Nasty fallback
			$mainLang = $addLangs[0];
		}
		
		$settings = array_merge(array(
			'language' => $mainLang,
			'additionalLanguages' => $addLangs,
			'currency' => 'EUR',
			'mlProductsUseLegacy' => false,
		), $settings);
		
		$this->summaryAddText = "<br /><br />\n".ML_DAWANDA_UPLOAD_EXPLANATION;
		parent::__construct($settings);
	}

	protected function generateRequestHeader() {
		# das Request braucht nur action, subsystem und data
		return array(
			'ACTION' => ($this->bVerify ? 'VerifyAddItems' : 'AddItems'),
			'SUBSYSTEM' => 'dawanda',
			'MODE' => isset($this->submitSession['mode']) ? $this->submitSession['mode'] : 'ADD',
		);
	}

	protected function processException($e) {
		$this->oLastException = $e;
	}

	public function getLastException() {
		return $this->oLastException;
	}

	protected function setUpMLProduct() {
		// Set the language
		MLProduct::gi()->setLanguage($this->settings['additionalLanguages']);
	}

	protected function appendAdditionalData($iPID, $aProduct, &$aData) {
		$aPropertiesRow = MagnaDB::gi()->fetchRow('
			SELECT * FROM '.TABLE_MAGNA_DAWANDA_PROPERTIES.'
			 WHERE ' . ((getDBConfigValue('general.keytype', '0') == 'artNr')
				? 'products_model = "'.MagnaDB::gi()->escape($aProduct['ProductsModel']).'"'
				: 'products_id = "'.$iPID.'"'
			) . '
			       AND mpID = '.$this->_magnasession['mpID']
		);
		// Will not happen in sumbit cycle but can happen in loadProductByPId.
		if (empty($aPropertiesRow)) {
			$data['submit'] = array();
			return;
		}

		foreach (array('MarketplaceCategories', 'StoreCategories', 'MpColors', 'Attributes') as $jsonKey) {
			$aPropertiesRow[$jsonKey] = json_decode($aPropertiesRow[$jsonKey], true);
		}
		
		#echo print_m(func_get_args());
		
		/*
		 * set product data to submit array
		 * language based
		 */
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$aData['submit']['SKU'] = $aProduct['ProductsModel'];
		} else {
			$aData['submit']['SKU'] = 'ML'.$aProduct['ProductId'];
		}
		foreach ($this->settings['additionalLanguages'] as $sLangId) {
			$sLangCode = MLProduct::gi()->languageIdToCode($sLangId);
			$aData['submit']['Descriptions'][$sLangCode] = array(
				'Title' => $aProduct['Title'][$sLangCode],
				'Description' => $aProduct['Description'][$sLangCode],
				/*
				'Manufacturing' => '',
				'Customization' => '',
				'Material' => '',
				'Size' => '',
				*/
				'Tags' => $aProduct['Keywords'][$sLangCode],
			);
		}

		$categoryAttributes = '';
		if (!empty($aPropertiesRow['CategoryAttributes'])) {
			$categoryAttributes = DawandaHelper::gi()->convertMatchingToNameValue(
				json_decode($aPropertiesRow['CategoryAttributes'], true),
				$aProduct
			);
		}

		$aData['submit']['CategoryAttributes'] = $categoryAttributes;

		//Images
		$sImagePath = getDBConfigValue($this->marketplace.'.imagepath', $this->mpID, '');
		if (empty($sImagePath)) {
			$sImagePath = SHOP_URL_POPUP_IMAGES;
		}
		$aImages = array();
		if (!empty($aProduct['Images'])) {
			foreach($aProduct['Images'] as $sImg) {
				$aImages[] = array('URL' => $sImagePath.$sImg);
			}
		}
		$aData['submit']['Images'] = $aImages;

		//Quantity
		if ($aData['quantity'] < 0) {
			$aData['quantity'] = 0;
		}
		$aData['submit']['Quantity'] = $aData['quantity'];

		//Price
		if (isset($aData['price']) && !empty($aData['price'])) {
			$aData['submit']['Price'] = $aData['price'];
		} else {
			$aData['submit']['Price'] = $aProduct['Price'];
		}

		//BasePrice
		if (!empty($aProduct['BasePrice'])) {
			$aData['submit']['BasePrice'] = array (
				'Unit' => $aProduct['BasePrice']['Unit'][MLProduct::gi()->languageIdToCode($this->settings['language'])],
				'Value' => $aProduct['BasePrice']['Value'],
			);
		}

		// ShippingService
		$aData['submit']['ShippingService'] = $aPropertiesRow['ShippingService'];
		// MarketplaceCategories
		if (is_array($aPropertiesRow['MarketplaceCategories'])) {
			$aData['submit']['MarketplaceCategories'] = array_values($aPropertiesRow['MarketplaceCategories']);
			// tmp hack, because DaWanda doesn't support the second marketplace category
			if (isset($aData['submit']['MarketplaceCategories'][0])) {
				$aData['submit']['MarketplaceCategories'] = array($aData['submit']['MarketplaceCategories'][0]);
			}
		} else {
			$aData['submit']['MarketplaceCategories'] = array($aPropertiesRow['MarketplaceCategories']);
		}
		
		// StoreCategories
		if (is_array($aPropertiesRow['StoreCategories'])) {
			$aData['submit']['StoreCategories'] = array_values($aPropertiesRow['StoreCategories']);
		} else {
			$aData['submit']['StoreCategories'] = array($aPropertiesRow['StoreCategories']);
		}
		
		// ShippingTime
		if (getDBConfigValue(array('dawanda.leadtimetoshipmatching.prefer', 'val'), $this->mpID, false)) {
			$sProductsShippingTime = MagnaDB::gi()->fetchOne("
				SELECT products_shippingtime
				  FROM ".TABLE_PRODUCTS." p
				 WHERE p.products_id = '".$iPID."'
			");
			$aData['submit']['ShippingTime'] = getDBConfigValue(
				array('dawanda.leadtimetoshipmatching.values', $sProductsShippingTime),
				$this->mpID,
				getDBConfigValue('dawanda.checkin.leadtimetoship', $this->mpID, 0)
			);
		} else {
			$aData['submit']['ShippingTime'] = getDBConfigValue('dawanda.checkin.leadtimetoship', $this->mpID, 0);
		}
		// MpColors
		if (is_array($aPropertiesRow['MpColors'])) {
			$aData['submit']['MpColors'] = $aPropertiesRow['MpColors'];
		}
		
		if (!empty($aPropertiesRow['Attributes'])) {
			$aData['submit']['Attributes'] = array();
			foreach ($aPropertiesRow['Attributes'] as $attribGroup => $attribSets) {
				if (!is_array($attribSets)) {
					$attribSets = array($attribSets);
				}
				foreach ($attribSets as $attribs) {
					if (!is_array($attribs)) {
						$attribs = array($attribs);
					}
					$aData['submit']['Attributes'] = array_merge($aData['submit']['Attributes'], $attribs);
				}
			}
			$aData['submit']['Attributes'] = array_unique($aData['submit']['Attributes']);
		}
		
		// ListingDuration
		$aData['submit']['ListingDuration'] = $aPropertiesRow['ListingDuration'];
		
		$aData['submit']['ProductType'] = $aPropertiesRow['ProductType'];
		$aData['submit']['ReturnPolicy'] = $aPropertiesRow['ReturnPolicy'];

		if (!$this->getDawandaVariations($aProduct, $aData, $sImagePath, json_decode($aPropertiesRow['CategoryAttributes'], true))) {
			return;
		}
	}

	protected function getDawandaVariations($product, &$data, $imagePath, $categoryAttributes) {
		if ($this->checkinSettings['Variations'] !== 'yes') {
			return true;
		}

		$variations = array();
		foreach ($product['Variations'] as $v) {
			$this->simpleprice->setPrice($v['Price']);
			$price = $this->simpleprice->roundPrice()->makeSignalPrice(
				getDBConfigValue($this->marketplace.'.price.signal', $this->mpID, '')
			)->getPrice();

			$vi = array(
				'SKU' => ($this->settings['keytype'] == 'artNr') ? $v['MarketplaceSku'] : $v['MarketplaceId'],
				'Price' => $price,
				'Quantity' => ($this->quantityLumb === false)
					? max(0, $v['Quantity'] - (int)$this->quantitySub)
					: $this->quantityLumb,
			);

			foreach ($this->settings['additionalLanguages'] as $sLangId) {
				$sLangCode = MLProduct::gi()->languageIdToCode($sLangId);
				$viTitle = $product['Title'][$sLangCode];
				foreach ($v['Variation'] as $varAttribute) {
					$viTitle .= ' ' . $varAttribute['Name'][$sLangCode] . ' - ' . $varAttribute['Value'][$sLangCode];
				}

				$vi['Descriptions'][$sLangCode] = array(
					'Title' => $viTitle,
					'Description' => isset($v['Description'][$sLangCode]) ? $v['Description'][$sLangCode] : $product['Description'][$sLangCode],
					/*
                    'Manufacturing' => '',
                    'Customization' => '',
                    'Material' => '',
                    'Size' => '',
                    */
					'Tags' => isset($v['Keywords'][$sLangCode]) ? $v['Keywords'][$sLangCode] : $product['Keywords'][$sLangCode],
				);
			}


			if (empty($v['Images'])) {
				$vi['Images'] = $data['submit']['Images'];
			} else {
				foreach ($v['Images'] as $image) {
					$vi['Images'][] = array(
						'URL' => $imagePath . $image
					);
				}
			}

			//implementing the base price
			if (isset($v['BasePrice']) && empty($v['BasePrice']) === false ) {
				$vi['BasePrice']['Unit'] = $v['BasePrice']['Unit'];
				$vi['BasePrice']['Value'] = number_format((float)$v['BasePrice']['Value'], 2, '.','');
			}

			$vi['CategoryAttributes'] = $this->fixVariationCategoryAttributes($categoryAttributes, $product, $v);

			$variations[] = $vi;
		}

		if (!empty($variations)) {
			$data['submit']['Variations'] = $variations;
		}

		return true;
	}

	private function fixVariationCategoryAttributes($aCatAttributes, $product, $variationDB)
	{
		$productDataForMatching = array_merge($product, $variationDB);
		$productDataForMatching['ProductId'] = $variationDB['VariationId'];
		$productDataForMatching['ProductsModel'] = $variationDB['MarketplaceSku'];

		if (!isset($variationDB['Weight']['Value'])) {
			$productDataForMatching['Weight'] = $product['Weight'];
		}

		if (!isset($variationDB['BasePrice']['Value'])) {
			$productDataForMatching['BasePrice'] = $product['BasePrice'];
		}

		// Since variation attributes are not set directly on product and their key is number, we should prefix them for
		// standard AM conversion because otherwise variation attributes are no different from any other shop attribute
		foreach ($variationDB['Variation'] as $variationAttribute) {
			$productDataForMatching["variant_{$variationAttribute['NameId']}"] = $variationAttribute['ValueId'];
		}

		$fixCatAttributes = DawandaHelper::gi()->convertMatchingToNameValue($aCatAttributes, $productDataForMatching);

		return $fixCatAttributes;
	}

	protected function preSubmit(&$request) {
		$request['DATA'] = array();
		foreach ($this->selection as $iProductId => &$aProduct) {
			if (empty($aProduct['submit']['Variations'])) {
				$request['DATA'][] = $aProduct['submit'];
				continue;
			}

			foreach ($aProduct['submit']['Variations'] as $aVariation) {
				$aVariationData = $aProduct;
				unset($aVariationData['submit']['Variations']);
				foreach ($aVariation as $sParameter => $mParameterValue) {
					$aVariationData['submit'][$sParameter] = $mParameterValue;
				}

				$request['DATA'][] = $aVariationData['submit'];
			}
		}

		arrayEntitiesToUTF8($request['DATA']);
	}

	protected function markAsFailed($sku) {
		$iPID = magnaSKU2pID($sku);
		$this->badItems[] = $iPID;
		unset($this->selection[$iPID]);
	}

	public function verifyOneItem($bEchoRequest = false) {
		$this->bVerify = true;
		MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
			'mpID' => $this->_magnasession['mpID'],
			'selectionname' => $this->settings['selectionName'].'Verify',
			'session_id' => session_id()
		));
		$item = MagnaDB::gi()->fetchRow('
			SELECT * FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID="'.$this->_magnasession['mpID'].'" AND
			       selectionname="'.$this->settings['selectionName'].'" AND
			       session_id="'.session_id().'"
			 LIMIT 1
		');
		if (empty($item)) {
			return false;
		}

		$oldSelectionName = $this->settings['selectionName'];
		$this->settings['selectionName'] = $this->settings['selectionName'].'Verify';
		$item['selectionname'] = $this->settings['selectionName'];
		MagnaDB::gi()->insert(TABLE_MAGNA_SELECTION, $item);

		//echo print_m($this->settings, '$this->settings');

		$this->initSelection(0, 1);
		//echo print_m($this->selection, '$this->selection[1]');
		foreach ($this->selection as $pID => &$data) {
			if (!isset($data['quantity']) || ($data['quantity'] == 0)) {
				$data['quantity'] = 1; // hack to get verification of zero quantity items working
			}
		}

		$this->populateSelectionWithData();
		//echo print_m($this->selection, '$this->selection[2]');

		//Debug no sendRequest Fake result
		$aResult = array(
			'STATUS' => 'SUCCESS'
		);

		#$this->sendRequest(true, true);
		#$result = $this->sendRequest(false, $bEchoRequest);

		MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
			'mpID' => $this->_magnasession['mpID'],
			'selectionname' => $this->settings['selectionName'],
			'session_id' => session_id()
		));

		// restore selection name
		$this->settings['selectionName'] = $oldSelectionName;

		# Liste der pIDs um die ebay_properties upzudaten
		$aSelectedPIDs = MagnaDB::gi()->fetchArray('
			SELECT DISTINCT pID
			  FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID = "'.$this->_magnasession['mpID'].'"
			       AND selectionname = "'.$this->settings['selectionName'].'"
			       AND session_id = "'.session_id().'"
		');
		$sSelectedPIDsList = '';
		foreach ($aSelectedPIDs as $pIDsRow) {
			if (is_numeric($pIDsRow['pID'])) $sSelectedPIDsList .= $pIDsRow['pID'].', ';
		}
		$sSelectedPIDsList = trim($sSelectedPIDsList, ', ');
		MagnaDB::gi()->query('
			UPDATE '.TABLE_MAGNA_DAWANDA_PROPERTIES. '
			   SET Verified = "'.(('SUCCESS' == $aResult['STATUS']) ? 'OK' : 'ERROR').'"
			 WHERE mpID = '.$this->_magnasession['mpID'].'
				   AND products_id IN ('.$sSelectedPIDsList.')
		');

		return $aResult;
	}

	protected function postSubmit() {
		try {
			/*
			// wait only 15s on that request
			MagnaConnector::gi()->setTimeOutInSeconds(15);
			// this request can took some time because its live and uploads also update item requests
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'UploadItems',
			));
			//*/
		} catch (MagnaException $e) {
			$this->submitSession['api']['exception'] = $e;
			$this->submitSession['api']['html'] = MagnaError::gi()->exceptionsToHTML();
		}
		MagnaConnector::gi()->resetTimeOut();
	}

}
