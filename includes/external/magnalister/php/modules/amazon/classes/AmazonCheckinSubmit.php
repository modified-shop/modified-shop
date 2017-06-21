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
 * $Id: AmazonCheckinSubmit.php 6865 2016-08-23 08:22:31Z tim.neumann $
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
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
			} else {
				MLProduct::gi()->useMultiDimensionalVariations(false);
			}
			
			MLProduct::gi()
				->setPriceConfig(AmazonHelper::loadPriceSettings($this->mpID))
				->setQuantityConfig(AmazonHelper::loadQuantitySettings($this->mpID))
				->setOptions(array(
					'sameVariationsToAttributes' => true,
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
			TABLE_MAGNA_AMAZON_ERRORLOG,
			array (
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
		$data['submit']['ASIN'] = $productMatching['asin'];
		$data['submit']['ConditionType'] = empty($productMatching['item_condition']) ? $data['submit']['ConditionType'] : $productMatching['item_condition'];
		$data['submit']['ConditionNote'] = sanitizeProductDescription($productMatching['item_note']);
		$data['submit']['WillShipInternationally'] = $productMatching['will_ship_internationally'];
		if ($productMatching['leadtimeToShip'] > 0) {
			$data['submit']['LeadtimeToShip'] = $productMatching['leadtimeToShip'];
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
		}
		$data['submit']['Variations'] = empty($preparedVariations) ? array() : $preparedVariations;
		return true;
	}
	
	protected function appendApplyData($pID, $product, &$data) {
		$productApply = MagnaDB::gi()->fetchRow("
			SELECT data, category, leadtimeToShip, ConditionType, ConditionNote, ShippingTemplate
			  FROM `".TABLE_MAGNA_AMAZON_APPLY."`
			 WHERE data<>''
			       AND ".(($this->settings['keytype'] == 'artNr')
			            ? 'products_model="'.$product['ProductsModel'].'"'
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

		if (!empty($product['Attributes'])) {
			$data['submit']['CustomAttributes'] = array();
			foreach ($product['Attributes'] as $attribSet) {
				// need to convert field name to utf8 for json if its not utf8 json_encode will set it to null
				$data['submit']['CustomAttributes'][stringToUTF8($attribSet['Name'])] = $attribSet['Value'];
			}
		}

		$categoryAttributes = (!empty($productApply['data']['ShopVariation'])) ? $this->fixCategoryAttributes(json_decode($productApply['data']['ShopVariation'], true), $product) : '';

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
		$images = array();
		if (!empty($data['submit']['Images'])) {
			foreach ($data['submit']['Images'] as $image => $use) {
				if ($use == 'true') {
                    $images[] = (preg_match('/http(s{0,1}):\/\//', $image) ? '' : $imagePath ).$image;
				}
			}
			$data['submit']['Images'] = $images;
		}

		if ($productApply['leadtimeToShip'] > 0) {
			$data['submit']['LeadtimeToShip'] = $productApply['leadtimeToShip'];
		}
			
		if (isset($product['Weight']) && is_array($product['Weight'])) {
			$data['submit']['Weight'] = $product['Weight'];
		}

		$data['submit']['Variations'] = (isset($product['Variations']) && is_array($product['Variations'])) ? $product['Variations'] : array();
        $preparedAttributes = $this->getPreparedAttributes($productApply);
        foreach ($data['submit']['Variations'] as &$vItem) {
			$vItem['SKU'] = ($this->settings['keytype'] == 'artNr')
				? $vItem['MarketplaceSku']
				: $vItem['MarketplaceId'];
				
			if ($productApply['leadtimeToShip'] > 0) {
				$vItem['LeadtimeToShip'] = $productApply['leadtimeToShip'];
			}
			if (
				(!isset($vItem['ManufacturerPartNumber']) || empty($vItem['ManufacturerPartNumber']))
				&& $this->settings['skuAsMfrPartNo']
			) {
				$vItem['ManufacturerPartNumber'] = $vItem['SKU'];
			}

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
					$vItem['Images'][$imgKey] = (preg_match('/http(s{0,1}):\/\//', $imgVal) ? '' : $imagePath ).$imgVal;
				}
			} else {
				unset($vItem['Images']);
			}

			// if the reduced price is available here it has been enabled in the module configuration and should be used.
			if (isset($vItem['PriceReduced'])) {
				$vItem['Price'] = $vItem['PriceReduced'];
			}
		}
		
		if (
			(!isset($data['submit']['ManufacturerPartNumber']) || empty($data['submit']['ManufacturerPartNumber']))
			&& $this->settings['skuAsMfrPartNo']
		) {
			$data['submit']['ManufacturerPartNumber'] = $data['submit']['SKU'];
		}

		unset($data['submit']['ShopVariation']);
		
		
		if(getDBConfigValue(array('amazon.shipping.template.active', 'val'), $this->mpID, false)){
			if(!isset($data['submit']['Attributes'])){
				$data['submit']['Attributes'] = array();
			}
			$aTemplates = getDBConfigValue(array('amazon.shipping.template', 'values'), $this->mpID);
			if(isset($productApply['ShippingTemplate'])){
				$defaultTemplateIndex = $productApply['ShippingTemplate'];
			} else {
				$aDefaultTemplate = getDBConfigValue(array('amazon.shipping.template', 'defaults'),  $this->mpID);
				$defaultTemplateIndex = array_search('1', $aDefaultTemplate);
			}
			if(isset($aTemplates[$defaultTemplateIndex])){
				$data['submit']['Attributes']['MerchantShippingGroupName'] = $aTemplates[$defaultTemplateIndex];
			}
		} else if(isset ($data['submit']['Attributes']) && isset ($data['submit']['Attributes']['MerchantShippingGroupName'])) {
			unset($data['submit']['Attributes']['MerchantShippingGroupName']);
		}

		return true;
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
			if ($productMatching['leadtimeToShip'] > 0) {
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
			
			if ($productApply['leadtimeToShip'] > 0) {
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

	protected function postSubmit() {
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
					case 'database_value': {
						if (isset($aCatAttribute['Values']) && !empty($aCatAttribute['Values'])) {
							$databaseValue = $this->runDbMatching(array(
								'Table' => array(
									'table' => $aCatAttribute['Values']['Table'],
									'column' => $aCatAttribute['Values']['Column'],
								),
								'Alias' => $aCatAttribute['Values']['Alias']
							), 'products_id', $product['ProductId']);

							if ($databaseValue) {
								$fixCatAttributes[$key] = $databaseValue;
							}
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
							$fixCatAttributes[$key] = $product['Weight']['Value'] . $product['Weight']['Unit'];
						}
						break;
					}
					case 'contentvolume': {
						if (isset($aCatAttribute['Values']) && !empty($aCatAttribute['Values'])) {
							$fixCatAttributes[$key] = $product['BasePrice']['Value'] . $product['BasePrice']['Unit'];
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

	private function fixVariationCategoryAttributes($aCatAttributes, $product, &$variationDB) {
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
					case 'database_value': {
						if (isset($aCatAttribute['Values']) && !empty($aCatAttribute['Values'])) {
							$databaseValue = $this->runDbMatching(array(
								'Table' => array(
									'table' => $aCatAttribute['Values']['Table'],
									'column' => $aCatAttribute['Values']['Column'],
								),
								'Alias' => $aCatAttribute['Values']['Alias']
							), 'products_id', $variationDB['VariationId']);

							if ($databaseValue) {
								$fixCatAttributes[$key] = $databaseValue;
							}
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
						foreach ($variationDB['Variation'] as &$variationAttribute) {
							if ($sCode == $variationAttribute['NameId']) {
								foreach ($aCatAttribute['Values'] as $value) {
									if (fixHTMLUTF8Entities($variationAttribute['Value']) === fixHTMLUTF8Entities($value['Shop']['Value'])) {
										if ($value['Marketplace']['Key'] === 'manual') {
											$fixCatAttributes[$key] = $value['Marketplace']['Value'];
										} else {
											$fixCatAttributes[$key] = $value['Marketplace']['Key'];
										}

										$sCode = $variationAttribute['Name'];

										$variationAttribute['Name'] = $key;
										$variationAttribute['Value'] = $fixCatAttributes[$key];
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

	/**
	 * Helper method to execute a db matching query.
	 * @return mixed
	 *   A string or false if the matching config is empty.
	 */
	protected function runDbMatching($tableSettings, $defaultAlias, $where) {
		if (!isset($tableSettings['Table']['table'])
			|| empty($tableSettings['Table']['table'])
			|| empty($tableSettings['Table']['column'])
		) {
			return false;
		}
		if (empty($tableSettings['Alias'])) {
			$tableSettings['Alias'] = $defaultAlias;
		}

		if (!is_numeric($where)) {
			$where = '"'.MagnaDB::gi()->escape($where).'"';
		}

		return (string)MagnaDB::gi()->fetchOne('
			SELECT `' . $tableSettings['Table']['column'] . '`
			FROM `' . $tableSettings['Table']['table'] . '`
			WHERE `' . $tableSettings['Alias'] . '` = ' . $where . '
				AND `' . $tableSettings['Table']['column'] . '` <> \'\'
		');
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
}
