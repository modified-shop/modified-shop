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
 * (c) 2010 - 2019 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/checkin/MagnaCompatibleCheckinSubmit.php');
require_once(DIR_MAGNALISTER_MODULES . 'hitmeister/HitmeisterHelper.php');

class HitmeisterCheckinSubmit extends MagnaCompatibleCheckinSubmit {

	protected $useShippingtimeMatching = false;
	protected $defaultShippingtime = '';
	protected $shippingtimeMatching = array();
	protected $defaultHandlingtime = '';
	protected $handlingtimeMatching = array();
	protected $ignoreErrors = true;
	protected $shippingTimes = array();

	public function __construct($settings = array()) {
		global $_MagnaSession;

		$settings = array_merge(array(
			'language' => getDBConfigValue($settings['marketplace'] . '.lang', $_MagnaSession['mpID'], ''),
			'currency' => getCurrencyFromMarketplace($_MagnaSession['mpID']),
			'keytype' => getDBConfigValue('general.keytype', '0'),
			'itemsPerBatch' => 100,
			'mlProductsUseLegacy' => false,
		), $settings);

		parent::__construct($settings);
		$this->summaryAddText = "<br /><br />\n".ML_HITMEISTER_UPLOAD_EXPLANATION;

		$this->settings['SyncInventory'] = array(
			'Price' => getDBConfigValue($settings['marketplace'] . '.inventorysync.price', $this->mpID, '') == 'auto',
			'Quantity' => getDBConfigValue($settings['marketplace'] . '.stocksync.tomarketplace', $this->mpID, '') == 'auto',
		);
		
		$this->defaultShippingtime  = getDBConfigValue($this->marketplace.'.shippingtime', $this->mpID, 0); 
		$this->shippingtimeMatching = getDBConfigValue($this->marketplace.'.shippingtimematching.values', $this->mpID, array()); 
		$this->defaultHandlingtime  = getDBConfigValue($this->marketplace.'.handlingtime', $this->mpID, 0); 
		$this->handlingtimeMatching = getDBConfigValue($this->marketplace.'.handlingtimematching.values', $this->mpID, array()); 
		$this->useShippingtimeMatching = getDBConfigValue(array($this->marketplace.'.shippingtimematching.prefer', 'val'), $this->mpID, false); 
		$this->shippingTimes = HitmeisterHelper::GetShippingTimes();
		if (!is_array($this->shippingtimeMatching) || empty($this->shippingtimeMatching)) {
			$this->useShippingtimeMatching = false;
		}
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
		MLProduct::gi()->setPriceConfig(HitmeisterHelper::loadPriceSettings($this->mpID));
		MLProduct::gi()->setQuantityConfig(HitmeisterHelper::loadQuantitySettings($this->mpID));
		MLProduct::gi()->setOptions(array(
			'sameVariationsToAttributes' => false,
			'purgeVariations' => true,
			'useGambioProperties' => (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties')
		));
	}
	
	protected function appendAdditionalData($pID, $product, &$data) {
		$defaultLocation = getDBConfigValue($this->settings['marketplace'].'.itemcountry', $this->_magnasession['mpID']);
		$defaultTitle = isset($product['Title']) ? $product['Title'] : '';
		$defaultSubtitle = isset($product['ShortDescription']) ? $product['ShortDescription'] : '';
		$defaultDescription = isset($product['Description']) ? $product['Description'] : '';
		
		$prepare = MagnaDB::gi()->fetchRow('
			SELECT * FROM '.TABLE_MAGNA_HITMEISTER_PREPARE.'
			 WHERE '.((getDBConfigValue('general.keytype', '0') == 'artNr')
					     ? 'products_model=\''.MagnaDB::gi()->escape($product['ProductsModel']).'\''
					     : 'products_id=\''.$pID.'\''
					).' 
				   AND mpID = '.$this->_magnasession['mpID'].'
		');
		
		if (is_array($prepare)) {// how? $prepare cannot be array - for what is else ?
			$categoryAttributes = '';
			if (!empty($prepare['CategoryAttributes'])) {
				$categoryAttributes = HitmeisterHelper::gi()->convertMatchingToNameValue(
					json_decode($prepare['CategoryAttributes'], true),
					$product
				);
			}

			$data['submit']['SKU'] = magnaPID2SKU($pID);
			$data['submit']['ParentSKU'] = magnaPID2SKU($pID);
			$data['submit']['EAN'] = $product['EAN'];
			$data['submit']['MarketplaceCategory'] = isset($prepare['MarketplaceCategories']) ? $prepare['MarketplaceCategories'] : '';
			$data['submit']['MarketplaceCategoryName'] = isset($prepare['MarketplaceCategoriesName']) ? $prepare['MarketplaceCategoriesName'] : '';
			$data['submit']['CategoryAttributes'] = $categoryAttributes;
			$data['submit']['Title'] = isset($prepare['Title']) ? $prepare['Title'] : $defaultTitle;
			$data['submit']['Subtitle'] = isset($prepare['Subtitle']) ? $prepare['Subtitle'] : HitmeisterHelper::sanitizeDescription($defaultSubtitle);
			$data['submit']['Description'] = isset($prepare['Description']) ? $prepare['Description'] : $defaultDescription;

            $imagePath = getDBConfigValue($this->marketplace.'.imagepath', $this->_magnasession['mpID'], '');
            if (empty($imagePath)) {
                $imagePath = SHOP_URL_POPUP_IMAGES;
                $imagePath = trim($imagePath, '/ ').'/';
            }
			if (empty($prepare['PictureUrl']) === false) {
				$pictureUrls = json_decode($prepare['PictureUrl']);

				foreach ($pictureUrls as $image => $use) {
					if ($use == 'true') {
						$data['submit']['Images'][] = array(
							'URL' => (preg_match('/http(s{0,1}):\/\//', $image) ? '' : $imagePath).$image
						);
					}
				}
			} else if (isset($product['Images'])) {
				foreach($product['Images'] as $image) {
                    $data['submit']['Images'][] = array(
                        'URL' => (preg_match('/http(s{0,1}):\/\//', $image) ? '' : $imagePath).$image
                    );
				}
			}
			$shippingTime = 
				($this->useShippingtimeMatching || $prepare['ShippingTime'] === 'm') && array_key_exists($product['ShippingTimeId'], $this->shippingtimeMatching)
				? $this->shippingtimeMatching[$product['ShippingTimeId']]
				: $prepare['ShippingTime']
			;
			if (!array_key_exists($shippingTime, $this->shippingTimes)) {
				$shippingTime = $this->defaultShippingtime;
			}
			$handlingTime = 
				($this->useShippingtimeMatching || $prepare['ShippingTime'] === 'm') && array_key_exists($product['ShippingTimeId'], $this->handlingtimeMatching)
				? $this->handlingtimeMatching[$product['ShippingTimeId']]
				: $prepare['HandlingTime']
			;

			$data['submit']['ShippingTime'] = $shippingTime;
			$data['submit']['HandlingTime'] = $handlingTime;
			$data['submit']['ConditionType'] = $prepare['ConditionType'];
			if (isset($prepare['ShippingGroup']) && $prepare['ShippingGroup'] != 0) {
				$data['submit']['ShippingGroup'] = $prepare['ShippingGroup'];
			} else if (getDBConfigValue($this->marketplace.'.shippinggroup', $this->mpID, 0) != 0) {
				$data['submit']['ShippingGroup'] = getDBConfigValue($this->marketplace.'.shippinggroup', $this->mpID, 0);
			// if nothing set, don't submit
			}
			$data['submit']['Location'] = isset($prepare['Location']) ? $prepare['Location'] : $defaultLocation;
			$data['submit']['Comment'] = isset($prepare['Comment']) ? $prepare['Comment'] : '';
			$data['submit']['Matched'] = $prepare['PrepareType'] === 'Match' ? true : false;
		} else {
			$data['submit']['ShippingTime']  = isset($data['shippingtime']) && !empty($data['shippingtime'])
				? $data['shippingtime']
				: (($this->useShippingtimeMatching)
					? $this->shippingtimeMatching[$product['ShippingTime']]
					: $this->defaultShippingtime
				);
			$data['submit']['HandlingTime']  = isset($data['handlingtime']) && !empty($data['handlingtime'])
				? $data['handlingtime']
				: (($this->useShippingtimeMatching)
					? $this->handlingtimeMatching[$product['ShippingTime']]
					: $this->defaultHandlingtime
				);
			$data['submit']['ConditionType'] = getDBConfigValue($this->settings['marketplace'].'.itemcondition', $this->_magnasession['mpID']);
		}
		
		$data['submit']['Price'] = $data['price'];
		$data['submit']['Currency'] = $this->settings['currency'];
		$data['submit']['Quantity'] = $data['quantity'] < 0 ? 0 : $data['quantity'];
		
		$manufacturerName = $product['Manufacturer'];
		if (empty($manufacturerName)) {
			$manufacturerName = getDBConfigValue(
				$this->marketplace.'.checkin.manufacturerfallback',
				$this->mpID,
				''
			);
		}
		if (!empty($manufacturerName)) {
			$data['submit']['Manufacturer'] = $manufacturerName;
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
		
		$data['submit']['ItemTax'] = $product['TaxPercent'];
		
		if (!$this->getHitmeisterVariations($product, $data, $imagePath, json_decode($prepare['CategoryAttributes'], true))) {
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

	protected function generateRedirectURL($state) {
		return toURL(array(
			'mp' => $this->realUrl['mp'],
			'mode'   => ($state == 'fail') ? 'errorlog' : 'listings'
		), true);
	}

	protected function getHitmeisterVariations($product, &$data, $imagePath, $categoryAttributes) {
		if ($this->checkinSettings['Variations'] != 'yes') {
			return true;
		}

		$variations = array();
		if (!empty ($product['VariationPictures'])) {
			foreach ($product['Variations'] as $no => &$var) {
				if (    array_key_exists($no, $product['VariationPictures'])
				     && array_key_exists('Images', $product['VariationPictures'][$no])
				     && !empty($product['VariationPictures'][$no]['Images'])) {
				    $product['Variations'][$no]['Images'] = $product['VariationPictures'][$no]['Images'];
				}
			}
		}
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

			foreach ($v['Variation'] as $varAttribute) {
				$vi['Title'] .= ' ' . $varAttribute['Name'] . ' - ' . $varAttribute['Value'];
			}

			if (empty($product['ManufacturerPartNumber']) === false) {
				$vi['Mpn'] = $product['ManufacturerPartNumber'];
			}

			if (empty($v['Images'])) {
				$vi['Images'] = $data['submit']['Images'];
			} else {
				foreach ($v['Images'] as $image) {
					if (strpos($image, 'images/') === 0) {
						$currImagePath = rtrim(HTTP_CATALOG_SERVER, '/').'/';
					} else {
						$currImagePath = $imagePath;
					}
					$vi['Images'][] = array(
						'URL' => $currImagePath . $image,
						'id' => $image
					);
				}
			}

			$vi['CategoryAttributes'] = $this->fixVariationCategoryAttributes($categoryAttributes, $product, $v);

			$variations[] = $vi;
		}

		$data['submit']['Variations'] = $variations;
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

		$fixCatAttributes = HitmeisterHelper::gi()->convertMatchingToNameValue($aCatAttributes, $productDataForMatching);

		return $fixCatAttributes;
	}
}
