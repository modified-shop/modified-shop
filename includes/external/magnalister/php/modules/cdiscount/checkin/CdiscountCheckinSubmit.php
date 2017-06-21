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
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/checkin/MagnaCompatibleCheckinSubmit.php');
require_once(DIR_MAGNALISTER_MODULES . 'cdiscount/CdiscountHelper.php');

/**
 * TODO: Siehe appendAdditionalData()
 */
class CdiscountCheckinSubmit extends MagnaCompatibleCheckinSubmit {
	const MARKETING_DESC_MAX_LENGTH = 5000;

	public function __construct($settings = array()) {
		global $_MagnaSession;
		$this->summaryAddText = "<br /><br />\n" . ML_CDISCOUNT_UPLOAD_EXPLANATION;

		$settings = array_merge(array(
			'language' => getDBConfigValue($settings['marketplace'] . '.lang', $_MagnaSession['mpID'], ''),
			'currency' => getCurrencyFromMarketplace($_MagnaSession['mpID']),
			'keytype' => getDBConfigValue('general.keytype', '0'),
			'itemsPerBatch' => 100,
			'mlProductsUseLegacy' => false,
		), $settings);

		parent::__construct($settings);
		$this->summaryAddText = "<br /><br />\n" . ML_CDISCOUNT_UPLOAD_EXPLANATION;

		$this->settings['SyncInventory'] = array(
			'Price' => getDBConfigValue($settings['marketplace'] . '.inventorysync.price', $this->mpID, '') == 'auto',
			'Quantity' => getDBConfigValue($settings['marketplace'] . '.stocksync.tomarketplace', $this->mpID, '') == 'auto',
		);
	}
	
	public function init($mode, $items = -1) {
		parent::init($mode, $items);
		$this->initSession['RequiredFileds'] = array();
		try {
			$requiredFileds = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetRequiredKeys',
			));
			if (!empty($requiredFileds['DATA'])) {
				foreach ($requiredFileds['DATA'] as $key) {
					$this->initSession['RequiredFileds'][$key] = true;
				}
			}
		} catch (MagnaException $e) { }

	}

	protected function setUpMLProduct()
	{
		parent::setUpMLProduct();

		// Set Price and Quantity settings
		MLProduct::gi()->setPriceConfig(CdiscountHelper::loadPriceSettings($this->mpID));
		MLProduct::gi()->setQuantityConfig(CdiscountHelper::loadQuantitySettings($this->mpID));
		MLProduct::gi()->setOptions(array(
			'sameVariationsToAttributes' => false,
			'purgeVariations' => true,
			'useGambioProperties' => (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties')
		));
	}

	protected function appendAdditionalData($pID, $product, &$data) {
		if (defined('MAGNA_FIELD_PRODUCTS_EAN') && array_key_exists(MAGNA_FIELD_PRODUCTS_EAN, $product)) {
			$ean = $product[MAGNA_FIELD_PRODUCTS_EAN];
		}
		
		$defaultDescription = '';
		$defaultMarketingDescription = '';

		CdiscountHelper::setDescriptionAndMarketingDescription($pID, $product['Description'], $defaultDescription, $defaultMarketingDescription);

		$defaultTitle = isset($product['Title']) ? $product['Title'] : '';
		$defaultSubtitle = isset($product['ShortDescription']) ? $product['ShortDescription'] : '';

		$prepare = MagnaDB::gi()->fetchRow('
			SELECT * FROM '.TABLE_MAGNA_CDISCOUNT_PREPARE.'
			 WHERE '.((getDBConfigValue('general.keytype', '0') == 'artNr')
					     ? 'products_model=\''.MagnaDB::gi()->escape($product['ProductsModel']).'\''
					     : 'products_id=\''.$pID.'\''
					).' 
				   AND mpID = '.$this->_magnasession['mpID'].'
		');
		
		if (is_array($prepare)) {
			$categoryAttributes = (!empty($prepare['CategoryAttributes'])) ? $this->fixCategoryAttributes(json_decode($prepare['CategoryAttributes'], true), $product) : '';
			$data['submit']['SKU'] = magnaPID2SKU($pID);
			$data['submit']['ParentSKU'] = magnaPID2SKU($pID);
			$data['submit']['EAN'] = isset($prepare['EAN']) ? $prepare['EAN'] : $ean;
			$data['submit']['MarketplaceCategory'] = isset($prepare['PrimaryCategory']) ? $prepare['PrimaryCategory'] : '';
			$data['submit']['MarketplaceCategoryName'] = isset($prepare['MarketplaceCategoriesName']) ? $prepare['MarketplaceCategoriesName'] : '';
			$data['submit']['CategoryAttributes'] = $categoryAttributes;
			$data['submit']['Title'] = isset($prepare['Title']) ? CdiscountHelper::cdiscountSanitizeTitle($prepare['Title']) : CdiscountHelper::cdiscountSanitizeTitle($defaultTitle);
			$data['submit']['Subtitle'] = isset($prepare['Subtitle']) ? CdiscountHelper::cdiscountSanitizeDesc($prepare['Subtitle']) : CdiscountHelper::cdiscountSanitizeDesc($defaultSubtitle);
			$data['submit']['Description'] = isset($prepare['Description']) ? CdiscountHelper::cdiscountSanitizeDesc($prepare['Description']) : CdiscountHelper::cdiscountSanitizeDesc($defaultDescription);
			$data['submit']['MarketingDescription'] = isset($prepare['MarketingDescription']) ?
				CdiscountHelper::truncateString($prepare['MarketingDescription'], self::MARKETING_DESC_MAX_LENGTH) :
				CdiscountHelper::truncateString($defaultMarketingDescription, self::MARKETING_DESC_MAX_LENGTH);

			//should check where should it be implemented in this way, should check if we get BasePrice in this step and check for variation
			//couldnt check because of the problem with cDiscount authentication
			/*if(isset($prepare['BasePrice']) && !empty($prepare['BasePrice'])){
				$data['submit']['BasePrice']['Unit'] = $prepare['BasePrice']['Unit'];
				$data['submit']['BasePrice']['Value'] =  number_format((float)$prepare['BasePrice']['Value'], 2, '.','');
			}*/

			$imagePath = getDBConfigValue($this->marketplace . '.imagepath', $this->_magnasession['mpID'], SHOP_URL_POPUP_IMAGES);
			$imagePath = trim($imagePath, '/ ').'/';
			if (empty($prepare['PictureUrl']) === false) {
				$pictureUrls = json_decode($prepare['PictureUrl']);

				foreach ($pictureUrls as $image => $use) {
					if ($use == 'true') {
						$data['submit']['Images'][] = array(
							'URL' => $imagePath . $image
						);
					}
				}
			} else if (isset($product['Images'])) {
				foreach($product['Images'] as $image) {
					$data['submit']['Images'][] = array(
							'URL' => $imagePath . $image
						);
				}
			}					

			$data['submit']['OfferCondition'] = $prepare['ConditionType'];
//			$data['submit']['Location'] = isset($prepare['Location']) ? $prepare['Location'] : $defaultLocation;
			$data['submit']['OfferComment'] = isset($prepare['Comment']) ? $prepare['Comment'] : '';
//			$data['submit']['Matched'] = $prepare['PrepareType'] === 'Match' ? true : false;
		} else {
			$data['submit']['OfferCondition'] = getDBConfigValue($this->settings['marketplace'].'.itemcondition', $this->_magnasession['mpID']);
		}

		//implementing the base price
		if(isset($product['BasePrice']) && empty($product['BasePrice']) === false ){
			$data['submit']['BasePrice']['Unit'] = $product['BasePrice']['Unit'];
			$data['submit']['BasePrice']['Value'] = number_format((float)$product['BasePrice']['Value'], 2, '.','');
		}

        $data['submit']['ManufacturerPartNumber'] = '';
		$data['submit']['Price'] = $data['price'];
		$data['submit']['Currency'] = $this->settings['currency'];
		$data['submit']['Tax'] = !empty($product['Tax']) ? $product['Tax'] : 0;
		$data['submit']['Quantity'] = $data['quantity'] < 0 ? 0 : $data['quantity'];

		$data['submit']['ShippingInfo'] =
			array(
				'PreparationTime'           => $prepare['PreparationTime'],
				'ShippingFeeStandard'       => $prepare['ShippingFeeStandard'],
				'ShippingFeeExtraStandard'  => $prepare['ShippingFeeExtraStandard'],
				'ShippingFeeTracked'        => $prepare['ShippingFeeTracked'],
				'ShippingFeeExtraTracked'   => $prepare['ShippingFeeExtraTracked'],
				'ShippingFeeRegistered'     => $prepare['ShippingFeeRegistered'],
				'ShippingFeeExtraRegistered'=> $prepare['ShippingFeeExtraRegistered'],
			);

		$data['submit']['Brand'] = !empty($product['Manufacturer']) ? $product['Manufacturer'] : '';

		if (empty($data['submit']['Brand'])) {
			$data['submit']['Brand'] = getDBConfigValue(
				$this->marketplace.'.checkin.manufacturerfallback',
				$this->mpID,
				''
			);
		}

		$mfrmd = getDBConfigValue($this->marketplace.'.checkin.manufacturerpartnumber.table', $this->mpID, false);
		if (is_array($mfrmd) && !empty($mfrmd['column']) && !empty($mfrmd['table'])) {
			$pIDAlias = getDBConfigValue($this->marketplace.'.checkin.manufacturerpartnumber.alias', $this->mpID);
			if (empty($pIDAlias)) {
				$pIDAlias = 'products_id';
			}
			$data['submit']['ManufacturerPartNumber'] = MagnaDB::gi()->fetchOne('
				SELECT `'.$mfrmd['column'].'` 
				  FROM `'.$mfrmd['table'].'` 
				 WHERE `'.$pIDAlias.'`=\''.MagnaDB::gi()->escape($pID).'\'
				 LIMIT 1
			');
		}

		if (!$this->getCategoryMatching($pID, $product, $data)) {
			return;
		}

		if (!$this->getCdiscountVariations($product, $data, $imagePath, json_decode($prepare['CategoryAttributes'], true))) {
			return;
		}
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


	protected function getCdiscountVariations($product, &$data, $imagePath, $categoryAttributes) {
		if ($this->checkinSettings['Variations'] !== 'yes') {
			return true;
		}

		$variations = array();
		foreach ($product['Variations'] as $v) {
			$this->simpleprice->setPrice($v['Price']['Price']);
			$price = $this->simpleprice->roundPrice()->makeSignalPrice(
				getDBConfigValue($this->marketplace.'.price.signal', $this->mpID, '')
			)->getPrice();

			$vi = array(
				'SKU' => ($this->settings['keytype'] == 'artNr') ? $v['MarketplaceSku'] : $v['MarketplaceId'],
				'Price' => $price,
				'Currency' => $this->settings['currency'],
				'Quantity' => ($this->quantityLumb === false)
					? max(0, $v['Quantity'] - (int)$this->quantitySub)
					: $this->quantityLumb,
				'EAN' => $v['EAN']
			);

//			$variation = MLProduct::gi()->getProductById($v['VariationId']);
//			$vi['Title'] = $variation['Title'];

			$vi['Title'] = $product['Title'];
			$vi['VariantTitle'] = $product['Title'];

			foreach ($v['Variation'] as $varAttribute) {
				$vi['VariantTitle'] .= ' ' . $varAttribute['Name'] . ' - ' . $varAttribute['Value'];
			}

			$vi['VariantTitle'] = CdiscountHelper::cdiscountSanitizeTitle($vi['VariantTitle']);

			if (empty($product['ManufacturerPartNumber']) === false) {
				$vi['Mpn'] = $product['ManufacturerPartNumber'];
			}

			if (empty($v['Images'])) {
				$vi['Images'] = $data['submit']['Images'];
			} else {
				foreach ($v['Images'] as $image) {
					$vi['Images'][] = array(
						'URL' => $imagePath . $image,
						'id' => $image
					);
				}
			}

			//implementing the base price
			if( isset( $v['BasePrice']) && empty($v['BasePrice']) === false ){
				$vi['BasePrice']['Unit'] = $v['BasePrice']['Unit'];
				$vi['BasePrice']['Value'] = number_format((float)$v['BasePrice']['Value'], 2, '.','');
			}

			$vi['CategoryAttributes'] = $this->fixVariationCategoryAttributes($categoryAttributes, $product, $v, $vi);

			$variations[] = $vi;
		}

		if (!empty($variations)) {
			$data['submit']['Variations'] = $variations;
		}

		return true;
	}

	protected function filterItem($pID, $data) {
		return array();
	}
	
	protected function filterSelection() {
		$b = parent::filterSelection();

		$shitHappend = false;
		$missingFields = array();
		foreach ($this->selection as $pID => &$data) {
			if ($data['submit']['Price'] <= 0) {
				// Loesche das Feld, um eine Fehlermeldung zu erhalten
				unset($data['submit']['Price']);
			}
			
			$mfC = array();
			
			$this->requirementsMet($data['submit'], $this->initSession['RequiredFileds'], $mfC);
			$mfC = array_merge($mfC, $this->filterItem($pID, $data['submit']));

			if (!empty($mfC)) {
				foreach ($mfC as $key => $field) {
					$mfC[$key] = $field;
				}
				$sku = magnaPID2SKU($pID);
				//echo print_m($mfC, $sku);
				//*
				MagnaDB::gi()->insert(
					TABLE_MAGNA_COMPAT_ERRORLOG,
					array (
						'mpID' => $this->mpID,
						'errormessage' => json_encode(array (
							'MissingFields' => $mfC
						)),
						'dateadded' => gmdate('Y-m-d H:i:s'),
						'additionaldata' => serialize(array(
							'SKU' => $sku
						))
					)
				);
				//*/
				$shitHappend = true;
				$this->badItems[] = $pID;
				unset($this->selection[$pID]);
			}
		}
		$this->badItems = array_unique($this->badItems);
		return $b || $shitHappend;
	}

	protected function postSubmit() {
		#echo 'postSubmit';
		/*if (isset($this->initSession['selectionFromErrorLog']) && !empty($this->initSession['selectionFromErrorLog'])) {
			foreach ($this->initSession['selectionFromErrorLog'] as $errID => $pID) {
				MagnaDB::gi()->delete(
					TABLE_MAGNA_CS_ERRORLOG,
					array(
						'id' => (int)$errID
					)
				);
			}
		}*/
		#echo var_dump_pre($this->initSession['upload']);
		try {
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'UploadItems',
			));
			#echo print_m($result, true);
		} catch (MagnaException $e) {
			#echo print_m($e, 'Exception', true);
			$this->submitSession['api']['exception'] = $e->getErrorArray();
		}
	}

	protected function generateRedirectURL($state) {
		return toURL(array(
			'mp' => $this->realUrl['mp'],
			'mode'   => ($state == 'fail') ? 'errorlog' : 'listings'
		), true);
	}

	private function fixCategoryAttributes($aCatAttributes, $product) {
		$fixCatAttributes = array();
		if (isset($aCatAttributes) && is_array($aCatAttributes)) {
			foreach ($aCatAttributes as $key => &$aCatAttribute) {
				$sCode = $aCatAttribute['Code'];
				switch ($sCode) {
					case 'freetext':
					case 'attribute_value': {
						if (isset($aCatAttribute['Values']) && !empty($aCatAttribute['Values'])) {
							$fixCatAttributes[$key] = $aCatAttribute['Values'];
						}
						break;
					}
					case 'category': {
						if (isset($aCatAttribute['Values']) && !empty($aCatAttribute['Values'])) {
							if (!empty($aCatAttribute['Values']['Value'])) {
								$fixCatAttributes[$key] = $this->getCategoryNameById($aCatAttribute['Values']['Value']);
							}
						}
						break;
					}
					case 'title': {
						if (isset($aCatAttribute['Values']) && !empty($aCatAttribute['Values'])) {
							$fixCatAttributes[$key] = $product['Title'];
						}
						break;
					}
					case 'description': {
						if (isset($aCatAttribute['Values']) && !empty($aCatAttribute['Values'])) {
							$fixCatAttributes[$key] = $product['Description'];
						}
						break;
					}
					case 'ean': {
						if (isset($aCatAttribute['Values']) && !empty($aCatAttribute['Values'])) {
							$fixCatAttributes[$key] = $product['EAN'];
						}

						break;
					}
					case 'weight': {
						if (isset($aCatAttribute['Values']) && !empty($aCatAttribute['Values'])) {
							$fixCatAttributes[$key] = $product['Weight']['Value'].$product['Weight']['Unit'];
						}
						break;
					}
					case 'contentvolume': {
						if (isset($aCatAttribute['Values']) && !empty($aCatAttribute['Values'])) {
							$fixCatAttributes[$key] = $product['BasePrice']['Value'].$product['BasePrice']['Unit'];
						}
						break;
					}
					default:
						break;
				}

				if (empty($fixCatAttributes[$key])) {
					unset($aCatAttributes[$key]);
				}

				if (!isset($fixCatAttributes[$key])) {
					continue;
				}

				if ($this->stringStartsWith($key, 'additional_attribute')) {
					$sNewKey = ucfirst($sCode);
					$fixCatAttributes[$sNewKey] = $fixCatAttributes[$key];
					unset($fixCatAttributes[$key]);
				}
			}
		}

		return $fixCatAttributes;
	}

	private function fixVariationCategoryAttributes($aCatAttributes, $product, $variationDB, $variation) {
		$fixCatAttributes = array();
		if (isset($aCatAttributes) && is_array($aCatAttributes)) {
			foreach ($aCatAttributes as $key => &$aCatAttribute) {
				$sCode = $aCatAttribute['Code'];
				switch ($sCode) {
					case 'freetext':
					case 'attribute_value': {
						if (isset($aCatAttribute['Values']) && !empty($aCatAttribute['Values'])) {
							$fixCatAttributes[$key] = $aCatAttribute['Values'];
						}
						break;
					}
					case 'category': {
						if (isset($aCatAttribute['Values']) && !empty($aCatAttribute['Values'])) {
							if (!empty($aCatAttribute['Values']['Value'])) {
								$fixCatAttributes[$key] = $this->getCategoryNameById($aCatAttribute['Values']['Value']);
							}
						}
						break;
					}
					case 'title': {
						if (isset($aCatAttribute['Values']) && !empty($aCatAttribute['Values'])) {
							$fixCatAttributes[$key] = $product['Title'];
						}
						break;
					}
					case 'description': {
						if (isset($aCatAttribute['Values']) && !empty($aCatAttribute['Values'])) {
							$fixCatAttributes[$key] = $product['Description'];
						}
						break;
					}
					case 'ean': {
						if (isset($aCatAttribute['Values']) && !empty($aCatAttribute['Values'])) {
							$fixCatAttributes[$key] = isset($variationDB['EAN']) ? $variationDB['EAN'] : $product['EAN'];
						}

						break;
					}
					case 'weight': {
						if (isset($aCatAttribute['Values']) && !empty($aCatAttribute['Values'])) {
							if (isset($variationDB['Weight']['Value'])) {
								$fixCatAttributes[$key] = $variationDB['Weight']['Value'].$variationDB['Weight']['Unit'];
							} else {
								$fixCatAttributes[$key] = $product['Weight']['Value'].$product['Weight']['Unit'];
							}
						}
						break;
					}
					case 'contentvolume': {
						if (isset($aCatAttribute['Values']) && !empty($aCatAttribute['Values'])) {
							if (isset($variationDB['BasePrice']['Value'])) {
								$fixCatAttributes[$key] = $variationDB['BasePrice']['Value'].$variationDB['BasePrice']['Unit'];
							} else {
								$fixCatAttributes[$key] = $product['BasePrice']['Value'].$product['BasePrice']['Unit'];
							}
						}
						break;
					}
					default:
						foreach ($variationDB['Variation'] as $variationAttribute) {
							if ($sCode == $variationAttribute['NameId']) {
								foreach ($aCatAttribute['Values'] as $value) {
									if ($variationAttribute['Value'] === $value['Shop']['Value']) {
										$fixCatAttributes[$key] = str_replace(array(ML_GENERAL_VARMATCH_MANUALY_MATCHED, ML_GENERAL_VARMATCH_AUTO_MATCHED, ML_GENERAL_VARMATCH_FREE_TEXT), '', $value['Marketplace']['Value']);
										$sCode = $variationAttribute['Name'];
									}
								}
							}
						}
				}

				if (empty($fixCatAttributes[$key])) {
					unset($fixCatAttributes[$key]);
				}

				if (!isset($fixCatAttributes[$key])) {
					continue;
				}

				if ($this->stringStartsWith($key, 'additional_attribute')) {
					$sNewKey = ucfirst($sCode);
					$fixCatAttributes[$sNewKey] = $fixCatAttributes[$key];
					unset($fixCatAttributes[$key]);
				}
			}
		}

		return $fixCatAttributes;
	}

	private function stringStartsWith($haystack, $needle) {
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}

	private function getCategoryNameById($categoryID) {
		try {
			$aRequest = array(
				'ACTION' => 'GetCategoryDetails',
				'DATA' => array(
					'CategoryID' => $categoryID
				)
			);

			$aResponse = MagnaConnector::gi()->submitRequest($aRequest);
			if ($aResponse['STATUS'] == 'SUCCESS' && isset($aResponse['DATA']) && is_array($aResponse['DATA'])) {
				return $aResponse['DATA']['title_plural'];
			} else {
				return $categoryID;
			}

		} catch (MagnaException $e) {
			return $categoryID;
		}
	}

	protected function processSubmitResult($result) {
		if (array_key_exists('ERRORS', $result)
			&& is_array($result['ERRORS'])
			&& !empty($result['ERRORS'])
		) {
			foreach ($result['ERRORS'] as $err) {
				if (isset($err['ERRORDATA']['SKU'])) {
					$SKU = $err['ERRORDATA']['SKU'];
					foreach ($this->selection as $pID => &$data) {
						if ($data['submit']['SKU'] === $SKU) {
							$this->badItems[] = $pID;
							unset($this->selection[$pID]);
							break;
						}
					}
				}
			}
		}
	}

}
