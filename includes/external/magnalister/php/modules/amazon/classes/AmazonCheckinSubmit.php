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
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/CheckinSubmit.php');
require_once(DIR_MAGNALISTER_MODULES.'amazon/amazonFunctions.php');
require_once(DIR_MAGNALISTER_MODULES.'amazon/AmazonHelper.php');

class AmazonCheckinSubmit extends CheckinSubmit {
	private $checkinDetails = array();
	
	public function __construct($settings = array()) {
		global $_MagnaSession;
		/* Setzen der Currency nicht noetig, da Preisberechnungen bereits in 
		   der AmazonSummaryView Klasse gemacht wurden.
		 */
		$settings = array_merge(array(
			'mlProductsUseLegacy' => false,
			'language' => getDBConfigValue($_MagnaSession['currentPlatform'].'.lang', $_MagnaSession['mpID']),
			'itemsPerBatch' => 100,
			'keytype' => getDBConfigValue('general.keytype', '0'),
			'skuAsMfrPartNo' => getDBConfigValue(array('amazon.checkin.SkuAsMfrPartNo', 'val'), $_MagnaSession['mpID'], false),
		), $settings);
		
		parent::__construct($settings);
	}
	
	protected function setUpMLProduct() {
		parent::setUpMLProduct();
		
		if (!$this->settings['mlProductsUseLegacy']) {
			$useGambioVariations = (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties');
			
			if ($useGambioVariations) {
				MLProduct::gi()->setOptions(array('useGambioProperties' => true));
			}
			
			MLProduct::gi()
				->setPriceConfig(AmazonHelper::loadPriceSettings($this->mpID))
				->setQuantityConfig(AmazonHelper::loadQuantitySettings($this->mpID))
				->setOptions(array(
					'sameVariationsToAttributes' => true,
					'purgeVariations' => true,
				))
			;
		}
	}
	
	public function makeSelectionFromErrorLog() {}
	
	protected function generateRequestHeader() {
		return array(
			'ACTION' => 'AddItems',
			'MODE' => $this->submitSession['mode']
		);
	}
	
	protected function markAsFailed($sku) {
		MagnaDB::gi()->insert(
			TABLE_MAGNA_AMAZON_ERRORLOG, array(
			'mpID' => $this->_magnasession['mpID'],
			'batchid' => '-',
			'errormessage' => ML_GENERIC_ERROR_UNABLE_TO_LOAD_PREPARE_DATA,
			'dateadded' => gmdate('Y-m-d H:i:s'),
			'additionaldata' => serialize(array(
				'SKU' => $sku
			))
			)
		);
		$pID = magnaSKU2pID($sku);
		$this->badItems[] = $pID;
		unset($this->selection[$pID]);
	}

	protected function appendMatchingData($pID, $product, &$data) {
		$productMatching = MagnaDB::gi()->fetchRow("
			SELECT * 
			  FROM `".TABLE_MAGNA_AMAZON_PROPERTIES."`
			 WHERE mpID='".$this->_magnasession['mpID']."'
			       AND asin<>'' 
			       AND ".(($this->settings['keytype'] == 'artNr')
			            ? 'products_model="'.$product['ProductsModel'].'"'
			            : 'products_id="'.$pID.'"'
			       )."
			LIMIT 1
		");
		if ($productMatching === false) {
			return false;
		}

        # shipping templates are now mandatory (as discussed with Tim on 2022-07-20)
        $aTemplates = getDBConfigValue(array('amazon.shipping.template', 'values'), $this->mpID);
        if (isset($productMatching['ShippingTemplate'])) {
            $defaultTemplateIndex = $productMatching['ShippingTemplate'];
        } else {
            $aDefaultTemplate = getDBConfigValue(array('amazon.shipping.template', 'defaults'), $this->mpID);
            $defaultTemplateIndex = array_search('1', $aDefaultTemplate);
        }
        $defaultTemplate = $aTemplates[$defaultTemplateIndex];

		$data['submit']['ASIN'] = $productMatching['asin'];
		$data['submit']['ConditionType'] = empty($productMatching['item_condition']) ? $data['submit']['ConditionType'] : $productMatching['item_condition'];
		$data['submit']['ConditionNote'] = sanitizeProductDescription($productMatching['item_note']);
		$data['submit']['ShippingTemplate'] = isset($defaultTemplate) ? $defaultTemplate : "";
		if (is_numeric($productMatching['leadtimeToShip'])) {
			$data['submit']['LeadtimeToShip'] = $productMatching['leadtimeToShip'];
		}

		// B2B
		// if B2B is globally disabled, ignore prepared values
		if (getDBConfigValue('amazon.b2b.active', $this->mpID, 'false') === 'true') {
			if (isset($productMatching['B2BActive']) && $productMatching['B2BActive'] == 'true') {
				$b2bOnly = (getDBConfigValue('amazon.b2b.sell_to', $this->mpID, 'b2b_b2c') === 'b2b_only');
				$this->setB2BData($data, $pID, $product, $b2bOnly);
			}
		}
		
		$productVariations = isset($product['Variations']) && is_array($product['Variations'])? $product['Variations'] : array();
		$preparedVariations = array();

		// if the reduced price is available here it has been enabled in the module configuration and should be used.
		if (isset($product['PriceReduced'])) {
			$data['submit']['Price'] = $product['PriceReduced'];
		}
		
		foreach ($productVariations as $variation) {
			$variationProduct = array(
				'ProductsModel' => $variation['MarketplaceSku'], 
				'TaxPercent' => $product['TaxPercent']
			);
			$variationData = $data;
			$variationData['submit']['SKU'] = ($this->settings['keytype'] == 'artNr')
				? $variation['MarketplaceSku']
				: $variation['MarketplaceId'];
			if ($this->appendMatchingData($variation['VariationId'], $variationProduct, $variationData)) {
				unset($variationData['submit']['Variations']);
				$preparedVariations[] = $variationData['submit'];
			}
			if (isset($productMatching['B2BActive']) && $productMatching['B2BActive'] == 'true') {
				$this->setB2BVariationData($variation, $data, $pID, $b2bOnly);
			}
		}
		$data['submit']['Variations'] = empty($preparedVariations) ? array() : $preparedVariations;
		return true;
	}
	
	protected function appendApplyData($pID, $product, &$data) {
		$productApply = MagnaDB::gi()->fetchRow("
			SELECT data, category, leadtimeToShip, ConditionType, ConditionNote, ShippingTemplate, variation_theme
			  FROM `".TABLE_MAGNA_AMAZON_APPLY."`
			 WHERE data<>''
			       AND ".(($this->settings['keytype'] == 'artNr')
			            ? 'products_model="'.MagnaDb::gi()->escape($product['ProductsModel']).'"'
			            : 'products_id="'.$pID.'"'
			       )."
			       AND is_incomplete='false'
			       AND mpID='".$this->_magnasession['mpID']."'
			 LIMIT 1
		");

		#echo print_m($productApply, '$productApply');
		if ($productApply === false) {
			return false;
		}

		$productApply['data'] = @unserialize(@base64_decode($productApply['data']));
		if (empty($productApply['data']) || !is_array($productApply['data'])) {
			$productApply['data'] = array();
		}

		$productApply['category'] = @unserialize(@base64_decode($productApply['category']));
		if (empty($productApply['category']) || !is_array($productApply['category'])) {
			$productApply['category'] = array();
		}
		
		$productApply['data'] = array_merge($productApply['category'], $productApply['data']);
		if (empty($productApply['data'])) {
			return false;
		}
        if (array_key_exists('Keywords', $productApply['data']) && $productApply['data']['Keywords'] === null) {
            $productApply['data']['Keywords'] = $product['Keywords'];
        }
		if (!empty($product['Attributes'])) {
			$data['submit']['CustomAttributes'] = array();
			foreach ($product['Attributes'] as $attribSet) {
				// need to convert field name to utf8 for json if its not utf8 json_encode will set it to null
				$data['submit']['CustomAttributes'][stringToUTF8($attribSet['Name'])] = $attribSet['Value'];
			}
		}

		$categoryAttributes = '';
		if (!empty($productApply['data']['ShopVariation'])) {
			$categoryAttributes = AmazonHelper::gi()->convertMatchingToNameValue(
				json_decode($productApply['data']['ShopVariation'], true),
				$product
			);
		}

		// ConditionType should go as regular data attribute, not product attribute
		if (isset($categoryAttributes['ConditionType'])) {
			$productApply['ConditionType'] = $categoryAttributes['ConditionType'];
			unset($categoryAttributes['ConditionType']);
		}

		// ConditionNote should go as regular data attribute, not product attribute
		if (isset($categoryAttributes['ConditionNote'])) {
			$productApply['ConditionNote'] = $categoryAttributes['ConditionNote'];
			unset($categoryAttributes['ConditionNote']);
		}

		if (!empty($categoryAttributes)) {
			$data['submit']['Attributes'] = $categoryAttributes;
			unset($productApply['data']['Attributes']);
		}

		$data['submit'] = array_merge($data['submit'], $productApply['data']);

		if (isset($productApply['variation_theme'])) {
			$data['submit']['variation_theme'] = json_decode($productApply['variation_theme'], true);
			unset($data['submit']['variationTheme']);
		}

		//EAN for USA is UPC
		if (getDBConfigValue('amazon.site', $this->mpID) === 'US' && isset($categoryAttributes['UPC'])) {
			$data['submit']['EAN'] = $categoryAttributes['UPC'];
			unset($data['submit']['Attributes']['UPC']);
		}

		$data['submit']['SKU'] = ($this->settings['keytype'] == 'artNr')
			? $product['MarketplaceSku']
			: $product['MarketplaceId'];
		
		#echo print_m($productApply, '$productApply');
		
		$data['submit']['ConditionType'] = empty($productApply['ConditionType']) ? $data['submit']['ConditionType'] : $productApply['ConditionType'];
		$data['submit']['ConditionNote'] = empty($productApply['ConditionNote']) ? sanitizeProductDescription($data['submit']['ConditionNote']) : sanitizeProductDescription($productApply['ConditionNote']);
		
		if (!empty($data['submit']['BrowseNodes'])) {
			foreach ($data['submit']['BrowseNodes'] as $i => $bn) {
				if ($bn == 'null') {
					unset($data['submit']['BrowseNodes'][$i]);
				} elseif (preg_match("/([0-9]*)_?/", $bn, $aOutput)) {
					$data['submit']['BrowseNodes'][$i] = $aOutput[1];
				}
			}
		}
		
		if (!empty($product['Attributes'])) {
			$data['submit']['CustomAttributes'] = array();
			foreach ($product['Attributes'] as $attribSet) {
				// need to convert field name to utf8 for json if its not utf8 json_encode will set it to null
				$data['submit']['CustomAttributes'][stringToUTF8($attribSet['Name'])] = $attribSet['Value'];
			}
		}

        $imagePath = getDBConfigValue($this->_magnasession['currentPlatform'].'.imagepath', $this->_magnasession['mpID'], '');
        if (empty($imagePath)) {
            $imagePath = SHOP_URL_POPUP_IMAGES;
            $imagePath = trim($imagePath, '/ ').'/';
        }
        if ('gambioProperties' == getDBConfigValue('general.options', 0, 'old')) {
            // We only use path before Gambio 4.1 (since in Gambio 4.1 the image path is included in the Database)
            if (version_compare(ML_GAMBIO_VERSION, '4.1', '>=')) {
                $imagePathVariations = HTTP_CATALOG_SERVER.DIR_WS_CATALOG;
            } else {
                $imagePathVariations = getDBConfigValue($this->_magnasession['currentPlatform'].'.imagepath.variations', $this->_magnasession['mpID'], HTTP_CATALOG_SERVER.DIR_WS_CATALOG.DIR_WS_IMAGES.'product_images/properties_combis_images/');
            }
        } else {
           $imagePathVariations = $imagePath; 
        } 
		$images = array();
		if (!empty($data['submit']['Images'])) {
			foreach ($data['submit']['Images'] as $image => $use) {
				if ($use == 'true') {
                    $images[] = (preg_match('/http(s{0,1}):\/\//', $image) ? '' : $imagePath ).$image;
				}
			}
			$data['submit']['Images'] = $images;
		}

		if (is_numeric($productApply['leadtimeToShip'])) {
			$data['submit']['LeadtimeToShip'] = $productApply['leadtimeToShip'];
		}
			
		if (isset($product['Weight']) && is_array($product['Weight'])) {
			$data['submit']['Weight'] = $product['Weight'];
		}

		// B2B
		// if B2B is globally disabled, ignore prepared values
		if (getDBConfigValue('amazon.b2b.active', $this->mpID, 'false') !== 'true') {
			$productApply['data']['B2BActive'] = 'false';
			$data['submit']['B2BActive'] = 'false';
		}

		$b2bActive = $this->getB2BValue($productApply['data'], 'B2BActive', 'active', 'false') === 'true';
		$b2bOnly = $this->getB2BValue($productApply['data'], 'B2BSellTo', 'sell_to', 'b2b_b2c') === 'b2b_only';

		if ($b2bActive) {
			$this->setB2BData($data, $pID, $product, $b2bOnly);
		} else {
			$this->unsetB2BData($data['submit']);
		}

        // skip all variations if this is set
        $sVariationTheme = json_decode($productApply['variation_theme'], true);
        if (is_array($sVariationTheme) && key($sVariationTheme) == 'skip_variations') {
            $data['submit']['Variations'] = array();
        } else {
            $data['submit']['Variations'] = (isset($product['Variations']) && is_array($product['Variations'])) ? $product['Variations'] : array();
        }
        if (!is_array($sVariationTheme)) {
            $sVariationTheme = array();
        }

        $preparedAttributes = $this->getPreparedAttributes($productApply);
        foreach ($data['submit']['Variations'] as $vNo => &$vItem) {
			$vItem['SKU'] = ($this->settings['keytype'] == 'artNr')
				? $vItem['MarketplaceSku']
				: $vItem['MarketplaceId'];
				
			if (is_numeric($productApply['leadtimeToShip'])) {
				$vItem['LeadtimeToShip'] = $productApply['leadtimeToShip'];
			}

			if (
				(!isset($vItem['ManufacturerPartNumber']) || empty($vItem['ManufacturerPartNumber']))
				&& $this->settings['skuAsMfrPartNo']
			) {
				$vItem['ManufacturerPartNumber'] = $vItem['SKU'];
			}

            $vItem['variation_theme'] = $sVariationTheme;

            $vItem['Attributes'] = $this->fixVariationCategoryAttributes($preparedAttributes, $product, $vItem);
			if (isset($vItem['Attributes']['ConditionType'])) {
				unset($vItem['Attributes']['ConditionType']);
			}

			if (isset($vItem['Attributes']['ConditionNote'])) {
				unset($vItem['Attributes']['ConditionNote']);
			}

			if (getDBConfigValue('amazon.site', $this->mpID) === 'US' && isset($vItem['Attributes']['UPC'])) {
				$vItem['EAN'] = $vItem['Attributes']['UPC'];
				unset($vItem['Attributes']['UPC']);
			}

			if (isset($vItem['Images']) && !empty($vItem['Images'])) {
				foreach ($vItem['Images'] as $imgKey => $imgVal) {
					if (empty($imgVal)) continue;
					$vItem['Images'][$imgKey] = (preg_match('/http(s{0,1}):\/\//', $imgVal) ? '' : $imagePathVariations ).$imgVal;
				}
			} else if (    isset($product['VariationPictures'])
			            && ($product['VariationPictures'][$vNo]['VariationId'] == $vItem['VariationId'])
            ) {
                // Support for one Variation Image (if shop not support multiple variation images)
                if (empty($product['VariationPictures'][$vNo]['Images'])) {
                    $product['VariationPictures'][$vNo]['Images'] = array($product['VariationPictures'][$vNo]['Image']);
                }

                // Support for Multiple Variation Images - see Fallback above if shop supports only one variation image
                if (!isset($vItem['Images']) || !is_array($vItem['Images'])) {
                    $vItem['Images'] = array();
                }
                if (!empty($product['VariationPictures'][$vNo]['Images'])) {
                    foreach ($product['VariationPictures'][$vNo]['Images'] as $varImage) {
                        if (!empty($varImage)
                            && !in_array($imagePathVariations.$varImage, $vItem['Images'])
                        ) {
                            $vItem['Images'][] = (preg_match('/http(s{0,1}):\/\//', $varImage) ? '' : $imagePathVariations ).$varImage;
                        }
                    }
                }
			} else {
				unset($vItem['Images']);
			}

			// if the reduced price is available here it has been enabled in the module configuration and should be used.
			if (isset($vItem['PriceReduced'])) {
				$vItem['Price'] = $vItem['PriceReduced'];
			}

			// B2B
			if ($b2bActive) {
				$this->setB2BVariationData($vItem, $data, $pID, $b2bOnly);
			} else {
				$this->unsetB2BData($vItem);
			}
		}
		
		if (
			(!isset($data['submit']['ManufacturerPartNumber']) || empty($data['submit']['ManufacturerPartNumber']))
			&& $this->settings['skuAsMfrPartNo']
		) {
			$data['submit']['ManufacturerPartNumber'] = $data['submit']['SKU'];
		}

		unset($data['submit']['ShopVariation']);


        if (!isset($data['submit']['Attributes'])) {
            $data['submit']['Attributes'] = array();
        }
        $aTemplates = getDBConfigValue(array('amazon.shipping.template', 'values'), $this->mpID);
        if (isset($productApply['ShippingTemplate'])) {
            $defaultTemplateIndex = $productApply['ShippingTemplate'];
        } else {
            $aDefaultTemplate = getDBConfigValue(array('amazon.shipping.template', 'defaults'), $this->mpID);
            $defaultTemplateIndex = array_search('1', $aDefaultTemplate);
        }
        if (isset($aTemplates[$defaultTemplateIndex])) {
            $data['submit']['Attributes']['MerchantShippingGroupName'] = $aTemplates[$defaultTemplateIndex];
            // Set MerchantShippingGroup also for Variations!
            foreach ($data['submit']['Variations'] as &$varItem) {
                $varItem['Attributes']['MerchantShippingGroupName'] = $aTemplates[$defaultTemplateIndex];
            }
        }
        // ensure that variations have all attributes (also custom attributes)
        if (    is_array($data['submit']['Attributes'])
             && isset($data['submit']['Variations'])
             && is_array($data['submit']['Variations'])) {
            foreach($data['submit']['Variations'] as &$varItem) {
                if (!is_array($varItem['Attributes'])) continue;
                foreach ($data['submit']['Attributes'] as $sAttrName => $sAttrValue) {
                    if (!array_key_exists($sAttrName, $varItem['Attributes'])) {
                        $varItem['Attributes'][$sAttrName] = $sAttrValue;
                    }
                }
            }
        }

		return true;
	}

	private function setB2BData(&$data, $pID, $product, $b2bOnly) {
		$quantityDiscountType = $this->getB2BValue($data['submit'], 'QuantityPriceType', 'discount_type', '');
		$data['submit']['QuantityPriceType'] = $quantityDiscountType;
		$useTiers = $quantityDiscountType !== '';
		for ($i = 1; $i < 6; $i++) {
			$data['submit']['QuantityLowerBound' . $i] = $useTiers ?
				$this->getB2BValue($data['submit'], 'QuantityLowerBound' . $i, "discount_tier$i.quantity", '0') : '';
			$data['submit']['QuantityPrice' . $i] = $useTiers ?
				$this->getB2BValue($data['submit'], 'QuantityPrice' . $i, "discount_tier$i.discount", '0') : '';
		}

		$this->setB2BPrice($pID, $data, $product, $b2bOnly);
	}

	private function setB2BVariationData(&$vItem, $data, $productId, $b2bOnly) {
        $aPriceConfig = $this->simpleprice->loadPriceSettings($this->mpID, 'b2b.');

        if (getDBConfigValue(array('amazon.b2b.price.use.netto', 'val'), $this->mpID, false) == true) {
            $aPriceConfig['IncludeTax'] = false;
        }

        $vItem['BusinessPrice'] = $this->simpleprice
            ->setPriceFromDB($productId, $this->mpID, $aPriceConfig)
            ->addAttributeSurcharge((int)magnaSKU2aID($vItem['SKU']))
            ->finalizePrice($productId, $this->mpID, $aPriceConfig)
            ->getPrice();

		$vItem['ProductTaxCode'] = $data['submit']['ProductTaxCode'];
		$vItem['QuantityPriceType'] = $data['submit']['QuantityPriceType'];
		for ($i = 1; $i < 6; $i++) {
			$vItem['QuantityLowerBound' . $i] = $data['submit']['QuantityLowerBound' . $i];
			$vItem['QuantityPrice' . $i] = $data['submit']['QuantityPrice' . $i];
		}

		if ($b2bOnly) {
			unset($vItem['Price']);
		}
	}

	private function unsetB2BData(&$data) {
		unset($data['B2BSellTo']);
		unset($data['QuantityPriceType']);
		for ($i = 1; $i < 6; $i++) {
			unset($data['QuantityLowerBound' . $i]);
			unset($data['QuantityPrice' . $i]);
		}
	}

	private function getB2BValue($data, $key, $configKey, $default) {
		if (isset($data[$key])) {
			return $data[$key];
		}

		// for backward compatibility, there might be items prepared before B2B so we need values from config
		return getDBConfigValue('amazon.b2b.' . $configKey, $this->mpID, $default);
	}

	protected function setB2BPrice($pID, &$data, $product, $b2bOnly) {
		$this->simpleprice->setCurrency(getCurrencyFromMarketplace($this->mpID));
		// calculate business price
		if (getDBConfigValue(array('amazon.b2b.price.use.netto', 'val'), $this->mpID, false) == true) {
			$aPriceConfig = $this->simpleprice->loadPriceSettings($this->mpID, 'b2b.');
			$aPriceConfig['IncludeTax'] = false;
			$businessPrice = $this->simpleprice->setFinalPriceFromDB($pID, $this->mpID, $aPriceConfig)->getPrice();
		} else {
			$businessPrice = $this->simpleprice->setFinalPriceFromDB($pID, $this->mpID, 'b2b.')->getPrice();
		}
		$data['submit']['BusinessPrice'] = $businessPrice;
		if ($b2bOnly) {
			unset($data['submit']['Price']);
		}

		$taxMatch = getDBConfigValue('amazon.b2b.tax_code_specific', $this->mpID, array());
		if (isset($taxMatch[$data['submit']['MainCategory']])) {
			$taxMatch = $taxMatch[$data['submit']['MainCategory']];
		} else {
			$taxMatch = getDBConfigValue('amazon.b2b.tax_code', $this->mpID, array());
		}

		if (is_array($taxMatch) && array_key_exists($product['TaxClass'], $taxMatch)) {
			$data['submit']['ProductTaxCode'] = $taxMatch[$product['TaxClass']];
		} else {
			$data['submit']['ProductTaxCode'] = '';
		}

		unset($data['submit']['B2BSellTo']);
		unset($data['submit']['B2BActive']);
	}

	private function getPreparedAttributes($product) {
        $preparedAttributes = array();
        if (empty($product['data']['ShopVariation'])) {
            // product has been prepared before A-M is applied, so attributes are in Attributes array
            if (!empty($product['data']['Attributes'])) {
                $oldAttributes = $product['data']['Attributes'];
                foreach($oldAttributes as $attributeKey => $attributeValue) {
                    $preparedAttributes[$attributeKey] = array(
                        'Code' => 'attribute_value',
                        'Values' => $attributeValue,
                    );
                }
            }
        } else {
            $preparedAttributes = json_decode($product['data']['ShopVariation'], true);
        }

        return $preparedAttributes;
    }

	protected function appendAdditionalData($pID, $product, &$data) {
		if ($this->settings['mlProductsUseLegacy']) {
			return $this->appendAdditionalDataOld($pID, $product, $data); 
		}
		#echo print_m(func_get_args(), __METHOD__);
		
		
		if ($data['quantity'] < 0) {
			$data['quantity'] = 0;
		}
		
		$data['submit']['Quantity'] = $data['quantity'];
		$data['submit']['SKU'] = magnaPID2SKU($pID);

		if (!empty($data['price']) && $data['price'] != 0) {
			$data['submit']['Price'] = $data['price'];
		} elseif (isset($product['PriceReduced'])) {
			// if the reduced price is available here it has been enabled in the module configuration and should be used.
			$data['submit']['Price'] = $product['PriceReduced'];
		}
		
		#VPE
		if ((isset($product['BasePrice']['Value'])) && ($product['BasePrice']['Value'] > 0)) {
			$data['submit']['BasePrice'] = $product['BasePrice'];
		}
		
		$data['submit']['ConditionType'] = getDBConfigValue('amazon.itemCondition', $this->_magnasession['mpID']);
		if (false === $this->appendMatchingData($pID, $product, $data)) {
			if (false === $this->appendApplyData($pID, $product, $data)) {
				$data['submit'] = array();
				$this->markAsFailed(magnaPID2SKU($pID));
				return;
			}
		}
		if (!isset($data['submit']['Price'])) {
			if (    array_key_exists('Variations', $data['submit'])
			     && is_array($data['submit']['Variations'])        ) {
				foreach ($data['submit']['Variations'] as $aVariation) {
					if (isset($aVariation['Price'])) {
						$data['submit']['Price'] = $aVariation['Price'];
						break;
					}
				}
			}
		}
	}
	
	protected function getVariations($pID, $product, &$data) {
		$variationTheme = array();
		if (defined('MAGNA_FIELD_ATTRIBUTES_EAN') 
			&& MagnaDB::gi()->columnExistsInTable('attributes_stock', TABLE_PRODUCTS_ATTRIBUTES)
		) {
			$variationTheme = MagnaDB::gi()->fetchArray(eecho('
			    SELECT po.products_options_name AS VariationTitle,
			           pov.products_options_values_name AS VariationValue,
			           pa.products_attributes_id AS aID,
			           pa.options_values_price AS aPrice,
			           pa.price_prefix AS aPricePrefix,
			           pa.attributes_stock AS Quantity,
			           '.MAGNA_FIELD_ATTRIBUTES_EAN.' AS EAN
			      FROM '.TABLE_PRODUCTS_ATTRIBUTES.' pa,
			           '.TABLE_PRODUCTS_OPTIONS.' po, 
			           '.TABLE_PRODUCTS_OPTIONS_VALUES.' pov
			     WHERE pa.products_id = \''.$pID.'\'
			           AND po.language_id = \''.getDBConfigValue(
			                $this->_magnasession['currentPlatform'].'.lang',
			                $this->_magnasession['mpID'],
			                $_SESSION['languages_id']
			           ).'\'
			           AND po.products_options_id = pa.options_id
			           AND po.products_options_name<>\'\'
			           AND pov.language_id = po.language_id
			           AND pov.products_options_values_id = pa.options_values_id
			           AND pov.products_options_values_name<>\'\'
			           AND pa.attributes_stock IS NOT NULL
			           AND '.MAGNA_FIELD_ATTRIBUTES_EAN.' IS NOT NULL
			           AND '.MAGNA_FIELD_ATTRIBUTES_EAN.'<>\'\'
			', false));
			arrayEntitiesToUTF8($variationTheme);
			#print_r($variationTheme);
			$quantityType = getDBConfigValue(
				$this->_magnasession['currentPlatform'].'.quantity.type',
				$this->_magnasession['mpID']
			);
			$quantityValue = getDBConfigValue(
				$this->_magnasession['currentPlatform'].'.quantity.value',
				$this->_magnasession['mpID'],
				0
			);
		}

		if (empty($variationTheme)) {
			return;
		}

		$tax = SimplePrice::getTaxByPID($pID);

		foreach ($variationTheme as &$item) {
			$item['SKU'] = magnaAID2SKU($item['aID']);
			unset($item['aID']);
			switch ($quantityType) {
				case 'stock': {
					# Already set.
					break;
				}
				case 'stocksub': {
					$item['Quantity'] = (int)$item['Quantity'] - $quantityValue;
					break;
				}
				default: {
					$item['Quantity'] = $quantityValue;
				}
			}
			if ($item['Quantity'] < 0) {
				$item['Quantity'] = 0;
			}
			$item['Tax'] = $tax;
			if ($item['aPricePrefix'] != '=') {
				$this->simpleprice->setPrice($data['price']);
				if (getDBConfigValue(
						$this->_magnasession['currentPlatform'].'.price.addkind',
						$this->_magnasession['mpID']
					) == 'percent'
				) {
					$this->simpleprice->removeTax((float)getDBConfigValue(
						$this->_magnasession['currentPlatform'].'.price.factor',
						$this->_magnasession['mpID']
					));
				} else if (getDBConfigValue(
						$this->_magnasession['currentPlatform'].'.price.addkind',
						$this->_magnasession['mpID']
					) == 'addition'
				) {
					$this->simpleprice->subLump((float)getDBConfigValue(
						$this->_magnasession['currentPlatform'].'.price.factor',
						$this->_magnasession['mpID']
					));
				}
				$this->simpleprice->removeTax($tax);

				$this->simpleprice->addLump($item['aPrice'] * (($item['aPricePrefix'] == '-') ? -1 : 1));
			} else {
				$this->simpleprice->setPrice(0.00);
				$this->simpleprice->addLump($item['aPrice']);
			}

			$this->simpleprice->addTax($tax);
			if (getDBConfigValue(
					$this->_magnasession['currentPlatform'].'.price.addkind', 
					$this->_magnasession['mpID']
				) == 'percent'
			) {
				$this->simpleprice->addTax((float)getDBConfigValue(
					$this->_magnasession['currentPlatform'].'.price.factor',
					$this->_magnasession['mpID']
				));
			} else if (getDBConfigValue(
					$this->_magnasession['currentPlatform'].'.price.addkind',
					$this->_magnasession['mpID']
				) == 'addition'
			) {
				$this->simpleprice->addLump((float)getDBConfigValue(
					$this->_magnasession['currentPlatform'].'.price.factor',
					$this->_magnasession['mpID']
				));
			}

			$item['Price'] = $this->simpleprice->roundPrice()->makeSignalPrice(
					getDBConfigValue($this->_magnasession['currentPlatform'].'.price.signal', $this->_magnasession['mpID'], '')
			    )->getPrice();
			unset($item['aPrice']);
			unset($item['aPricePrefix']);
			
			if ($this->settings['skuAsMfrPartNo']
				&& (!isset($item['ManufacturerPartNumber']) || empty($item['ManufacturerPartNumber']))
			) {
				$item['ManufacturerPartNumber'] = $item['SKU'];
			}
		}
	
		$data['submit']['Variations'] = $variationTheme;
		#echo print_m($variationTheme);
	}

	protected function appendAdditionalDataOld($pID, $product, &$data) {

		$conditionType = getDBConfigValue('amazon.itemCondition', $this->_magnasession['mpID']);
		
		$productMatching = $productApply = false;
		
		if ($data['quantity'] < 0) {
			$data['quantity'] = 0;
		}

		if (($productMatching = MagnaDB::gi()->fetchRow('
			SELECT * FROM `'.TABLE_MAGNA_AMAZON_PROPERTIES.'`
			 WHERE asin<>\'\' AND 
			      '.((getDBConfigValue('general.keytype', '0') == 'artNr')
			            ? 'products_model=\''.MagnaDB::gi()->escape($product['products_model']).'\''
			            : 'products_id=\''.$pID.'\''
			        ).' AND
			       mpID=\''.$this->_magnasession['mpID'].'\'
			 LIMIT 1
		')) !== false) {
			$data['submit']['SKU'] = magnaPID2SKU($pID);
			$data['submit']['ASIN'] = $productMatching['asin'];
			$data['submit']['ConditionType'] = empty($productMatching['item_condition']) ? $conditionType : $productMatching['item_condition'];
			$data['submit']['Price'] = $data['price'];
			$data['submit']['Quantity'] = $data['quantity'];
			$data['submit']['WillShipInternationally'] = $productMatching['will_ship_internationally'];
			$data['submit']['ConditionNote'] = sanitizeProductDescription($productMatching['item_note']);
			if (is_numeric($productMatching['leadtimeToShip'])) {
				$data['submit']['LeadtimeToShip'] = $productMatching['leadtimeToShip'];
			}

		} else if (($productApply = MagnaDB::gi()->fetchRow('
			SELECT category, data, leadtimeToShip
			  FROM `'.TABLE_MAGNA_AMAZON_APPLY.'`
			 WHERE data<>\'\'
			       AND '.((getDBConfigValue('general.keytype', '0') == 'artNr')
			            ? 'products_model=\''.MagnaDB::gi()->escape($product['products_model']).'\''
			            : 'products_id=\''.$pID.'\''
			       ).'
			       AND is_incomplete=\'false\'
			       AND mpID=\''.$this->_magnasession['mpID'].'\'
			 LIMIT 1
		')) !== false) {
			$productApply['data'] = (array)@unserialize(@base64_decode($productApply['data']));
			$productApply['data'] = array_merge(
				(array)@unserialize(@base64_decode($productApply['category'])),
				$productApply['data']
			);
			unset($productApply['category']);
			if (!is_array($productApply['data']) || empty($productApply['data'])) {
				$this->markAsFailed($pID);
				return;
			} 
			$data['submit'] = array_merge(
				array(
					'SKU' => magnaPID2SKU($pID),
					'Price' => $data['price'],
					'Quantity' => $data['quantity'],
					'ConditionType' => $conditionType,
				),
				$productApply['data']
			); 
			if (!empty($data['submit']['BrowseNodes'])) {
				foreach ($data['submit']['BrowseNodes'] as $i => $bn) {
					if ($bn == 'null') {
						unset($data['submit']['BrowseNodes'][$i]);
					}
				}
			}
            $imagePath = getDBConfigValue($this->_magnasession['currentPlatform'].'.imagepath', $this->_magnasession['mpID'], '');
            if (empty($imagePath)) {
                $imagePath = SHOP_URL_POPUP_IMAGES;
                $imagePath = trim($imagePath, '/ ').'/';
            }
			$images = array();
			if (!empty($data['submit']['Images'])) {
				foreach ($data['submit']['Images'] as $image => $use) {
					if ($use == 'true') {
						$images[] = (preg_match('/http(s{0,1}):\/\//', $image) ? '' : $imagePath ).$image;
					}
				}
				$data['submit']['Images'] = $images;
			}
			
			if (is_numeric($productApply['leadtimeToShip'])) {
				$data['submit']['LeadtimeToShip'] = $productApply['leadtimeToShip'];
			}
			
			if ((float)$product['products_weight'] > 0) {
				$data['submit']['Weight'] = array (
					'Unit' => 'kg',
					'Value' => $product['products_weight'],
				);
			}
		} else {
			$this->markAsFailed($pID);
			return;
		}
		
		# BasePrice = Grundpreis
		if ((isset($product['products_vpe_name'])) && ($product['products_vpe_value'] > 0)) {
			$data['submit']['BasePrice'] = array (
				'Unit'  => htmlspecialchars(trim($product['products_vpe_name'])),
				'Value' => $product['products_vpe_value'],
			);
		}
		
		if ($productApply === false) {
			return;
		}
		
		if (
			(!isset($data['submit']['ManufacturerPartNumber']) || empty($data['submit']['ManufacturerPartNumber']))
			&& $this->settings['skuAsMfrPartNo']
		) {
			$data['submit']['ManufacturerPartNumber'] = $data['submit']['SKU'];
		}
		
		$this->getVariations($pID, $product, $data);
	}

	protected function processSubmitResult($result) { }

	protected function filterSelection() {
		#echo print_m($this->selection, __METHOD__.'{L:'.__LINE__.'}');
		/*
		foreach ($this->selection as $pID => &$data) {
			if ((int)$data['submit']['Quantity'] == 0) {
				unset($this->selection[$pID]);
				$this->disabledItems[] = $pID;
			}
		}
		*/
	}

    protected function preSubmit(&$request) {
        // if you checked to prefer matching we use leadtimetoship time from configuration
        if (getDBConfigValue(array('amazon.leadtimetoshipmatching.prefer', 'val'), $this->mpID, false)) {
            foreach ($request['DATA'] as &$product) {
                $products_shippingtime = MagnaDB::gi()->fetchOne("
            SELECT products_shippingtime
              FROM ".TABLE_PRODUCTS." p
             WHERE p.products_model = '".$product['SKU']."'
        ");

                $iTime = getDBConfigValue(
                    array('amazon.leadtimetoshipmatching.values', $products_shippingtime),
                    $this->mpID,
                    getDBConfigValue('amazon.leadtimetoship', $this->mpID, '-')
                );
                // if matching is not empty in the configuration it means we use that value for shipping time
                // instead of the value stored in the preparation form
                if ($iTime != '-') {
                    $product['LeadtimeToShip'] = $iTime;
                }
            }
        }
    }

	protected function postSubmit() {
        $doUploadItems = true;
        if (($hp = magnaContribVerify('AmazonCheckinSubmit_PostUploadItems', 1)) !== false) {
            require($hp);
        }

        if ($doUploadItems === false) {
            return;
        }

		try {
			//*
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'UploadItems',
			));
			//*/
		} catch (MagnaException $e) {
			$this->submitSession['api']['exception'] = $e;
			$this->submitSession['api']['html'] = MagnaError::gi()->exceptionsToHTML();
		}
	}

	protected function generateRedirectURL($state) {
		return toURL(array(
			'mp' => $this->realUrl['mp'],
			'mode' => 'listings',
		), true);
	}

	private function fixVariationCategoryAttributes($aCatAttributes, $product, &$variationDB)
	{
		$productDataForMatching = array_merge($product, $variationDB);
		$productDataForMatching['ProductId'] = $variationDB['VariationId'];
		$productDataForMatching['ProductsModel'] = $variationDB['MarketplaceSku'];
		$variationThemeCode = key($variationDB['variation_theme']);
		$variationThemeAttributes = current($variationDB['variation_theme']);
		$checkVariationTheme = !empty($variationThemeCode) && ('autodetect' != $variationThemeCode) && !empty($variationThemeAttributes);

		if (!isset($variationDB['Weight']['Value'])) {
			$productDataForMatching['Weight'] = $product['Weight'];
		}

		if (!isset($variationDB['BasePrice']['Value'])) {
			$productDataForMatching['BasePrice'] = $product['BasePrice'];
		}

		$fixCatAttributes = AmazonHelper::gi()->convertMatchingToNameValue($aCatAttributes, $productDataForMatching);

		if (!isset($aCatAttributes) || !is_array($aCatAttributes)) {
			return $fixCatAttributes;
		}

		$aVariants = array();
		foreach ($variationDB['Variation'] as $keyVDB => $variationAttribute) {
			$foundAttributeValue = false;
			$shopVariationAttributeIsMatched = false;
			foreach ($aCatAttributes as $key => $aCatAttribute) {
				$sCode = $aCatAttribute['Code'];
				if (!$checkVariationTheme) {
					if ($sCode == $variationAttribute['NameId']) {
						foreach ($aCatAttribute['Values'] as $value) {
                            if ($variationAttribute['ValueId'] == $value['Shop']['Key']) {
								$foundAttributeValue = true;

								if ($value['Marketplace']['Key'] === 'manual') {
									$fixCatAttributes[$key] = $value['Marketplace']['Value'];
								} else {
									$fixCatAttributes[$key] = $value['Marketplace']['Key'];
								}

								$aVariants[] = array_merge(
									$variationAttribute,
									array('Name' => $key, 'Value' => $fixCatAttributes[$key])
								);
							}
						}
					}
				} else {
					if ($sCode == $variationAttribute['NameId']) {
						if (in_array($key, $variationThemeAttributes)) {
						$shopVariationAttributeIsMatched = true;
						}
						foreach ($aCatAttribute['Values'] as $value) {
                            if ($variationAttribute['ValueId'] == $value['Shop']['Key']) {
								$foundAttributeValue = true;

								if ($value['Marketplace']['Key'] === 'manual') {
									$fixCatAttributes[$key] = $value['Marketplace']['Value'];
								} else {
									$fixCatAttributes[$key] = $value['Marketplace']['Key'];
								}

								if (in_array($key, $variationThemeAttributes)) {
									$aVariants[]  = array_merge(
										$variationAttribute,
										array('Name' => $key, 'Value' => $fixCatAttributes[$key])
									);
								}
							}
						}
					}
				}
			}

			if ($checkVariationTheme && !$shopVariationAttributeIsMatched) {
				$aVariants[] = array(
					'Name' => $variationAttribute['Name'],
					'Value' => $variationAttribute['Value']
				);
			} elseif (!$checkVariationTheme && !$foundAttributeValue) {
				$aVariants[] = array(
					'Name' => $variationAttribute['Name'],
					'Value' => $variationAttribute['Value']
				);
			}

			// Remove all variation definitions that are already set from MP
			foreach ($variationDB['Variation'] as $variationAttributeKey => $variationAttributeValue) {
				if (isset($variationAttributeValue['VariantSetFromAM']) && $variationAttributeValue['VariantSetFromAM']) {
					unset($variationDB['Variation'][$variationAttributeKey]);
				}
			}
		}

		$variationDB['Variation'] = $aVariants;

		return $fixCatAttributes;
	}
}
