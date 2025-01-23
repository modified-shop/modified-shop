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
 * (c) 2010 - 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleSyncInventory.php');
require_once(DIR_MAGNALISTER_MODULES.'ebay/ebayFunctions.php');


class EbaySyncInventory extends MagnaCompatibleSyncInventory {
	
	protected $syncFixedStock = false;
	protected $syncChineseStock = false;
	protected $syncFixedPrice = false;
	protected $syncChinesePrice = false;
	
	# Bei Varianten kommt dieselbe ItemID mehrmals zurueck,
	# sollte aber nur einmal upgedatet werden
	protected $itemsProcessed = array();
	protected $variationsForItemCalculated = array();
	protected $totalQuantityForItemCalculated = array();
	
	protected $uqTime = 0.0;

	# wenn true, verwende alten Code
	private $mlProductsUseLegacy = false;

	# Synchro zw. einfachen Artikeln im Shop u. Varianten auf ebay
	private $blSimpleShop2VarEBay = false;
	private $aEBayVariations = array();
	
	public function __construct($mpID, $marketplace, $limit = 100) {
		global $_MagnaSession;

		# Ensure that $_MagnaSession contains needed data
		if (!isset($_MagnaSession) || !is_array($_MagnaSession)) {
			$_MagnaSession = array (
				'mpID' => $mpID,
				'currentPlatform' => $marketplace
			);
		} else {
			$_MagnaSession['mpID'] = $mpID;
			$_MagnaSession['currentPlatform'] = $marketplace;
		}

		parent::__construct($mpID, $marketplace, $limit);
		
		# Timeout ist standard 1 (wir warten nicht auf die Antwort), aber
		# wenn der Shop es nicht schafft, in der Zeit die Sbfrage abzuschicken, verlaengern wir
		# Das gilt dann fuer alle eBay Accounts im Shop, daher mpID 0
		$this->timeouts['UpdateItems'] = getDBConfigValue('ebay.updateitems.timeout', 0, 1);
		$this->timeouts['GetInventory'] = 1800;
		
		$this->startedAtTimestamp = time();
	}

	protected function initMLProduct() {
		parent::initMLProduct();
		MLProduct::gi()->useMultiDimensionalVariations(true);
		MLProduct::gi()->setOptions(array(
			'sameVariationsToAttributes' => false,
			'purgeVariations' => true,
			'useGambioProperties' => (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties')
		));
	}

	protected function getConfigKeys() {
		return array (
			'FixedStockSync' => array (
				'key' => 'stocksync.tomarketplace',
				'default' => '',
			),
			'ChineseStockSync' => array (
				'key' => 'chinese.stocksync.tomarketplace',
				'default' => '',
			),
			'FixedPriceSync' => array (
				'key' => 'inventorysync.price',
				'default' => '',
			),
			'ChinesePriceSync' => array (
				'key' => 'chinese.inventorysync.price',
				'default' => '',
			),
			'FixedQuantityType' => array (
				'key' => 'fixed.quantity.type',
				'default' => '',
			),
			'FixedQuantityValue' => array (
				'key' => 'fixed.quantity.value',
				'default' => 0,
			),
			'Lang' => array (
				'key' => 'lang',
				'default' => false,
			),
			'StatusMode' => array (
				'key' => 'general.inventar.productstatus',
				'default' => 'false',
			),
			'SKUType' => array (
				'key' => 'general.keytype',
			),
		);
	}
	
	protected function initQuantitySub() {
		$this->config['FixedQuantitySub'] = 0;
		if ($this->syncStock) {
			if ($this->config['FixedQuantityType'] == 'stocksub') {
				$this->config['FixedQuantitySub'] = $this->config['FixedQuantityValue'];
			}
		}
		$this->config['ChineseQuantitySub'] = 0;
		$this->config['ChineseQuantityType'] = 'lump';
		$this->config['ChineseQuantityValue'] = 1;
	}
	
	protected function uploadItems() {}
	
	protected function extendGetInventoryRequest(&$request) {
		$request['ORDERBY'] = 'DateAdded';
		$request['SORTORDER'] = 'DESC';
		$this->mlProductsUseLegacy or $request['EXTRA'] = 'useVariationMatrix';
	}
	
	protected function postProcessRequest(&$request) {
		$newUqTime = microtime(true);
		$border = 2.0;
		$throttleTime = 5;
		if (($newUqTime - $this->uqTime) < $border) {
			if ($this->_debugLevel >= self::DBGLV_HIGH) {
				$this->log("\n".
					"\n /|\\   Throttle UpdateQuantity requests, because 2 requests were created within ".$border." seconds.".
					"\n/_*_\\  New receive timeout is ".$throttleTime." seconds ".
					          "(old was ".$this->timeouts['UpdateItems']." seconds).\n"
				);
			}
			$this->timeouts['UpdateItems'] = $throttleTime;
		}
		$this->uqTime = $newUqTime;
		$request['ACTION'] = 'UpdateQuantity';
	}
	
	protected function updateItems($data) {
		if (!is_array($data) || empty($data)) {
			if ($this->_debug) $this->log("\n\nNothing to update in this batch.");
			return false;
		}
		$request = $this->getBaseRequest();
		$request['ACTION'] = 'UpdateItems';
		$request['DATA'] = $data;
		$this->postProcessRequest($request);
		
		if ($this->_debug) {
			if (!self::isAssociativeArray($request['DATA'])) {
				$this->log("\nUpdating ".count($request['DATA']).' item(s) in this batch.');
			} else {
				$this->log("\nUpdating items.");
			}
		}
		if ($this->_debugLevel >= self::DBGLV_HIGH) $this->logAPIRequest($request);
		if ($this->_debug && $this->_debugDryRun) {
			return true;
		}
		MagnaConnector::gi()->setTimeOutInSeconds($this->timeouts['UpdateItems']);
		try {
			$r = MagnaConnector::gi()->submitRequest($request);
			if ($this->_debug && ($this->_debugLevel >= self::DBGLV_HIGH)) $this->logAPIResponse($r);
			$this->processUpdateItemsErrors($r);
			
		} catch (MagnaException $e) {
			if ($this->_debugLevel >= self::DBGLV_HIGH) $this->logException($e);
			if ($e->getCode() == MagnaException::TIMEOUT) {
				//$e->saveRequest();
				setDBConfigValue('ebay.updateitems.timeout', 0, min(10, getDBConfigValue('ebay.updateitems.timeout', 0, 1) + 1), true);
				$e->setCriticalStatus(false);
			}
			return false;
		}
		return true;
	}

	protected function isAutoSyncEnabled() {
		$this->syncFixedStock   = $this->config['FixedStockSync']   == 'auto';
		$this->syncChineseStock = $this->config['ChineseStockSync'] == 'auto';
		$this->syncFixedPrice   = $this->config['FixedPriceSync']   == 'auto';
		$this->syncChinesePrice = $this->config['ChinesePriceSync'] == 'auto';
		
		/*
		if ($this->_debugDryRun) {
			$this->syncFixedStock = $this->syncChineseStock = $this->syncFixedPrice = $this->syncChinesePrice = true;
		}
		//*/

		if (!($this->syncFixedStock || $this->syncChineseStock || $this->syncFixedPrice || $this->syncChinesePrice)) {
			$this->log('== '.$this->marketplace.' ('.$this->mpID.'): no autosync =='."\n");
			return false;
		}
		$this->log(
			'== '.$this->marketplace.' ('.$this->mpID.'): '.
			'Sync fixed stock: '.($this->syncFixedStock ? 'true' : 'false').'; '.
			'Sync chinese stock: '.($this->syncChineseStock ? 'true' : 'false').'; '.
			'Sync fixed price: '.($this->syncFixedPrice ? 'true' : 'false').'; '.
			'Sync chinese price: '.($this->syncChinesePrice ? 'true' : 'false')." ==\n"
		);
		return true;
	}

	protected function identifySKU() {

		// case: Variation on eBay, simple in the shop
		if (!empty($this->aEBayVariations)) {
			$this->cItem = array_shift($this->aEBayVariations);
			return;
		}

		// regular
		if (!empty($this->cItem['MasterSKU'])) {
			$this->cItem['pID'] = (int)magnaSKU2pID($this->cItem['MasterSKU'], true);
		} else {
			$this->cItem['pID'] = (int)magnaSKU2pID($this->cItem['SKU']);
		}

		// case: Variation on eBay, simple in the shop
		if (    empty($this->cItem['pID'])
		     && isset($this->cItem['Variations'])
		     && is_array($this->cItem['Variations'])) {
			// make an own cItem for each variation,
			// so that the plugin can process them as single items
			foreach ($this->cItem['Variations'] as $no => $aVariation) {
				$iCurrPid = (int)magnaSKU2pID($aVariation['SKU'], true);
				if (!empty($iCurrPid)) {
					$this->aEBayVariations[$no] = $this->cItem;
					$this->aEBayVariations[$no]['pID'] = $iCurrPid;
					$this->aEBayVariations[$no]['MasterSKU'] = $aVariation['SKU'];
					$this->aEBayVariations[$no]['Price'] = $aVariation['Price'];
					$this->aEBayVariations[$no]['Quantity'] = $aVariation['Quantity'];
					unset($this->aEBayVariations[$no]['Variations']);
				}
			}
			if (!empty($this->aEBayVariations)) {
				$this->blSimpleShop2VarEBay = true;
				$this->cItem = array_shift($this->aEBayVariations);
				return;
			}
		}
		$this->blSimpleShop2VarEBay = false;
	}

	protected function updateInternalVariations() {
		# Varianten neu berechnen bevor man Anzahl berechnet
		if (MagnaDB::gi()->tableExists(TABLE_MAGNA_VARIATIONS)
			 && !in_array($this->cItem['ItemID'], $this->variationsForItemCalculated)
		) {
			setProductVariations($this->cItem['pID'], $this->config['Lang']);
			$this->variationsForItemCalculated[] = $this->cItem['ItemID'];
		}
	}

	protected function calcMainQuantity() {
		# Aktuelle Anzahl berechnen
		if (!isset($this->totalQuantityForItemCalculated[$this->cItem['ItemID']])) {
			$this->totalQuantityForItemCalculated[$this->cItem['ItemID']] = makeQuantity($this->cItem['pID'], $this->cItem['ListingType']);
		}
		return $this->totalQuantityForItemCalculated[$this->cItem['ItemID']];
	}

	protected function calcPrice($isVariation) {
		if ($this->cItem['ListingType'] != 'Chinese') {
			$priceFrozen = 0.0;
		} else {
			# schauen ob Preis eingefroren
			$priceFrozen = (float)MagnaDB::gi()->fetchOne('
				SELECT Price FROM '.TABLE_MAGNA_EBAY_PROPERTIES.'
				 WHERE mpID = '.$this->mpID.'
					   AND '.(('artNr' == $this->config['SKUType'])
							? 'products_model = "'.MagnaDB::gi()->escape(magnaPID2SKU($this->cItem['pID'], true)).'"'
							: 'products_id = '.$this->cItem['pID']
				).'
			');
		}
		
		# Preis berechnen
		if ($isVariation) {
			return makeVariationPrice($this->cItem['pID'], $this->cItem['SKU'], ((0.0 == $priceFrozen) ? false : $priceFrozen));
		} else {
			return makePrice($this->cItem['pID'], $this->cItem['ListingType'], ((0.0 == $priceFrozen) ? false : $priceFrozen));
		}
	}

        # Strike price (master item only, variation STPs come with the product data)
	protected function calcStrikePrice() {
		$jPreparedStrikePriceConfig = MagnaDB::gi()->fetchOne('
			SELECT StrikePriceConf FROM '.TABLE_MAGNA_EBAY_PROPERTIES.'
			 WHERE mpID = '.$this->mpID.'
				   AND '.(('artNr' == $this->config['SKUType'])
						? 'products_model = "'.MagnaDB::gi()->escape(magnaPID2SKU($this->cItem['pID'], true)).'"'
						: 'products_id = '.$this->cItem['pID']
			).'
		');
		if (!empty($jPreparedStrikePriceConfig)) {
			$aPreparedStrikePriceConfig = json_decode($jPreparedStrikePriceConfig, true);
			$blUseStrikePrice = ($aPreparedStrikePriceConfig['ebay.strike.price.kind'] != 'DontUse');
		} else {
			$blUseStrikePrice = (getDBConfigValue('ebay.strike.price.kind', $this->mpID, 'DontUse') != 'DontUse');
		}
		if (!$blUseStrikePrice) return false;
		$ret = makePrice($this->cItem['pID'], 'StrikePrice');
		return $ret;
	}

	protected function calcVariationMatrix($cleanVariations, $currPrice) {
		if (('Chinese' != $this->cItem['ListingType']) && $cleanVariations) {
			# getVariations mit set == false, schon neu gesetzt
			# Preise immer mit uebergeben, auch wenn !$this->syncFixedPrice (ggf. als Fallback)
			$variationMatrix = getVariations($this->cItem['pID'], $currPrice, false, ($currPrice !== false));
			arrayEntitiesToUTF8($variationMatrix);
		} else {
			$variationMatrix = false;
		}
		return $variationMatrix;
	}

	protected function updateItem() {
		global $_MagnaSession;
		if ($this->mlProductsUseLegacy) {
			$this->updateItemOld();
			return;
		}
		if (   in_array($this->cItem['ItemID'], $this->itemsProcessed)
		    && !$this->blSimpleShop2VarEBay) {
			$this->log("\nItemID ".$this->cItem['ItemID'].' already processed.');
			return;
		}
		$this->cItem['SKU'] = trim($this->cItem['SKU']);
		if (empty($this->cItem['SKU'])) {
			$this->log("\nItemID ".$this->cItem['ItemID'].' has an emtpy SKU.');
			return;
		}

		@set_time_limit(180);
		$this->identifySKU();

		if (($hp = magnaContribVerify('EBay_SyncInventory_PreUpdateItem', 1)) !== false) {
			require($hp);
		}

		$articleIdent = 'SKU: '.$this->cItem['SKU'].(empty($this->cItem['MasterSKU']) ? '' : ' [MasterSKU: '.$this->cItem['MasterSKU'].']').' ('.$this->cItem['ItemTitle'].'); eBay-ItemID: '.$this->cItem['ItemID'].'; ListingType: '.$this->cItem['ListingType'].' ';
		if ((int)$this->cItem['pID'] <= 0) {
			$this->log("\n".$articleIdent.' not found');
			return;
		} else {
			$this->log("\n".$articleIdent.' found (pID: '.$this->cItem['pID'].')');
		}

		//prepare product
		MLProduct::gi()->setLanguage($this->config['Lang']);
		MLProduct::gi()->setPriceConfig(
			EbayHelper::getPriceSettingsByListingType($this->mpID, $this->cItem['ListingType'])
		);
		MLProduct::gi()->setQuantityConfig(
			EbayHelper::getQuantitySettingsByListingType($this->mpID, $this->cItem['ListingType'])
		);
		$product =  MLProduct::gi()->getProductById($this->cItem['pID']);
		arrayEntitiesToUTF8($product);
		$process = false;
		$blVarStockOnEbay = false; // if zero stock control active
		$data = array(
			'fixed.stocksync' => $this->config['FixedStockSync'],
			'fixed.pricesync' => $this->config['FixedPriceSync'],
			'chinese.stocksync' => $this->config['ChineseStockSync'],
			'chinese.pricesync' => $this->config['ChinesePriceSync'],
		);
		$listingMasterType = ($this->cItem['ListingType'] == 'Chinese') ? 'chinese' : 'fixed';
		$syncStock = $this->config[ucfirst($listingMasterType).'StockSync'] != 'no';
		$syncPrice = $this->config[ucfirst($listingMasterType).'PriceSync'] != 'no';
		$data['SKU'] = magnaPID2SKU($product['ProductId']);
		$data['ItemID'] = $this->cItem['ItemID'];

		// Check Quantity variants or master
		if ($syncStock) {
			// QuantityTotal is only set if product has variants
			if ((isset($this->cItem['Variations']) && isset($product['Variations'])) && isset($product['QuantityTotal'])) {
				$data['NewQuantity'] = $product['QuantityTotal'];
			} else {
				$data['NewQuantity'] = $product['Quantity'];
			}
			$process = ($process || ($this->cItem['Quantity'] != $data['NewQuantity']));
		}

		// Check Price variants or master
		if ($syncPrice && !isset($this->cItem['Variations'])) {
			$data['Price'] = $product['Price'][$listingMasterType];

			// if PriceReduced is set use this one
			if (isset($product['PriceReduced'][$listingMasterType])) {
				$data['Price'] = $product['PriceReduced'][$listingMasterType];
			}
			if ($this->blSimpleShop2VarEBay) {
				// API expects it so in this case
				$data['StartPrice'] = $data['Price'];
				unset($data['Price']);
			}

			// Strikethrough Prices
			$currStrikePrice = $this->calcStrikePrice();
			if (   isset($this->cItem['OldPrice'])
			    || isset($this->cItem['ManufacturersPrice'])
			    || (($currStrikePrice = $this->calcStrikePrice()) != false)
			) {
				$sStrikePrice = (getDBConfigValue(array('ebay.strike.price.isUVP', 'val'), $this->mpID, true) == true ? 'ManufacturersPrice' : 'OldPrice');
				if ($currStrikePrice > $data['Price']) {
					$data[$sStrikePrice] = $currStrikePrice;
				}
			}

			// if listing type is chinese check for prepared price
			if ($listingMasterType == 'chinese') {
				$fPrice = magnalisterEbayGetPriceByType($product['ProductId']);
				if ($fPrice != 0.00) {
					$data['Price'] = $fPrice;
				}
			}
			// master price is empty for variation items, therefore check if isset
			$process = $process 
			|| (isset($data['Price']) && (float)$this->cItem['Price'] != (float)$data['Price'])
			|| (isset($data['StartPrice']) && (float)$this->cItem['Price'] != (float)$data['StartPrice']);
			// consider strike prices
			// STP on eBay, not in the Shop
			if (   (   (   isset($this->cItem['OldPrice'])
			            || isset($this->cItem['ManufacturersPrice']))
			        && (   !isset($sStrikePrice)
			            || !isset($data[$sStrikePrice]))
			       ) 
			// STP in the Shop, on eBay none or not the same kind
			    || (   isset($sStrikePrice)
			        && isset($data[$sStrikePrice])
			        && !isset($this->cItem[$sStrikePrice])
			       )
			// STP on eBay and in the Shop, values differ
			    || (   isset($sStrikePrice)
			        && isset($data[$sStrikePrice])
			        && isset($this->cItem[$sStrikePrice])
				&& (float)$data[$sStrikePrice] != (float)$this->cItem[$sStrikePrice]
			       )
			) {
				$process = $process || true;
			}
		}
		$aMatching = getDBConfigValue($_MagnaSession['currentPlatform'] . '.listingdetails.'.strtolower('EAN').'.dbmatching.table', $_MagnaSession['mpID'], false);
		if (   array_key_exists('EAN', $product)
		    && !empty($product['EAN'])
		    && is_array($aMatching)
		    && !empty($aMatching['column'])
		    && !empty($aMatching['table'])
		) {
			$data['EAN'] = $product['EAN'];
		}

		if (isset($this->cItem['Variations']) && isset($product['Variations'])) {
			$data['Variations'] = array();
			$sStrikePrice = (getDBConfigValue(array('ebay.strike.price.isUVP', 'val'), $this->mpID, true) == true ? 'ManufacturersPrice' : 'OldPrice');
			foreach ($product['Variations'] as $variantData) {
				$variant = array();
				$variationSpecifics = array();
				foreach ($variantData['Variation'] as $specific) {
					$variationSpecifics[] = array(
						'Name' => $specific['Name'],
						'Value' => $specific['Value'],
					);
				}
				$variant['SKU'] = ($this->config['SKUType'] == 'artNr') ? $variantData['MarketplaceSku'] : $variantData['MarketplaceId'];
				$currentCVariation = array();
				foreach ($this->cItem['Variations'] as $cVariation){
					if ($cVariation['SKU'] == $variant['SKU']) {
						$currentCVariation = $cVariation;
						break;
					}
				}

				if (    array_key_exists('EAN', $variantData)
				     && !empty($variantData['EAN'])) {
					$variant['EAN'] = $variantData['EAN'];
				}

				$process = ($process || (count($currentCVariation) == 0));
				if ($syncStock) {
					$variant['Quantity'] = $variantData['Quantity'];
					$process = ($process || (count($currentCVariation) > 0 && $currentCVariation['Quantity'] != $variant['Quantity']));
				}
				$blVarStockOnEbay = ($blVarStockOnEbay || (count($currentCVariation) > 0 && $currentCVariation['Quantity'] > 0));
				if ($syncPrice) {
					$variant['StartPrice'] = $variantData['Price'][$listingMasterType];
					// if PriceReduced is set use this one
					if (isset($variantData['PriceReduced'][$listingMasterType])) {
						$variant['StartPrice'] = $variantData['PriceReduced'][$listingMasterType];
					}

					// check strike prices
					if (    array_key_exists('Price', $variantData)
					     && array_key_exists('fixed', $variantData['Price'])
					     && array_key_exists('strike', $variantData['Price'])
					     && $variantData['Price']['strike'] > $variantData['Price']['fixed']
					) {
						$variant[$sStrikePrice] = $variantData['Price']['strike'];
					}

					// if listing type is chinese check for prepared price
					if ($listingMasterType == 'chinese') {
						$fPrice = magnalisterEbayGetPriceByType($variantData['VariationId']);
						if ($fPrice != 0.00) {
							$variant['StartPrice'] = $fPrice;
						}
					}
					$process = ($process || (count($currentCVariation) > 0 && (float)$currentCVariation['Price'] != (float)$variant['StartPrice']));
					// consider strike prices
					// STP on eBay, not in the Shop
					if (   (   (   isset($currentCVariation['OldPrice'])
				            || isset($currentCVariation['ManufacturersPrice']))
				        && (   !isset($sStrikePrice)
				            || !isset($variant[$sStrikePrice]))
				       ) 
					// STP in the Shop, on eBay none or not the same kind
					    || (   isset($sStrikePrice)
					        && isset($variant[$sStrikePrice])
					        && !isset($currentCVariation[$sStrikePrice])
					       )
					// STP on eBay and in the Shop, values differ
					    || (   isset($sStrikePrice)
					        && isset($variant[$sStrikePrice])
					        && isset($currentCVariation[$sStrikePrice])
						&& (float)$variant[$sStrikePrice] != (float)$currentCVariation[$sStrikePrice]
					       )
					) {
						$process = $process || true;
					}
				}
				$variant['Variation'] = $variationSpecifics;
				$data['Variations'][] = $variant;
			}
		}
		if (array_key_exists('OldPrice', $this->cItem)) {
			$sAddEbayStrikePrice = "\n\teBay OldPrice: ".$this->cItem['OldPrice'];
		} else if (array_key_exists('ManufacturersPrice', $this->cItem)) {
			$sAddEbayStrikePrice = "\n\teBay ManufacturersPrice: ".$this->cItem['ManufacturersPrice'];
		} else {
			$sAddEbayStrikePrice = '';
		}
		if (isset($sStrikePrice) && array_key_exists($sStrikePrice, $data)) {
			$sAddShopStrikePrice = "\n\tShop ".$sStrikePrice.": ".$data[$sStrikePrice];
		} else {
			$sAddShopStrikePrice = '';
		}
		$this->log(
		"\n\teBay Quantity: ".$this->cItem['Quantity'].
		"\n\tShop Main Quantity: ". ( array_key_exists('NewQuantity', $data)
			? $data['NewQuantity']
			: $product['Quantity'] ).
		"\n\teBay Price: ".$this->cItem['Price']. $sAddEbayStrikePrice .
		"\n\tShop Price: ".$product['Price'][$listingMasterType]. $sAddShopStrikePrice
		);

		if ($this->config['StatusMode'] == 'true') {
			$iStatus = MagnaDB::gi()->fetchOne('
				SELECT products_status FROM ' . TABLE_PRODUCTS . '
				WHERE products_id = "' . $this->cItem['pID'] . '"
			');
			if ($iStatus == 0) {//notavailible => noStock
				if (    (0  != $this->cItem['Quantity'])
				     || (isset($this->cItem['Variations']) && $blVarStockOnEbay)
				   ) {
					$process = true;
					if (getDBConfigValue('ebay.zerostockontrol', $this->mpID, false) !== 'true') {
						$this->log(
						"\n\tDeleting Item due to inactive Status"
						);
					}
				}
				$data['NewQuantity'] = 0;
				if (getDBConfigValue('ebay.zerostockontrol', $this->mpID, false) === 'true'
				     && array_key_exists('Variations', $data)
				     && is_array($data['Variations'])        ) {
					foreach($data['Variations'] as &$vv) {
						$vv['Quantity'] = 0;
					}
				} else {
					if (isset($data['Variations'])) unset($data['Variations']);
				}
			}
		}

		// catch the case when there's no quantity and price (product data broken)
		if (    (    !array_key_exists('NewQuantity', $data)
		          || $data['NewQuantity'] === null)
		     && (    !array_key_exists($listingMasterType, $product['Price'])
		          || $product['Price'][$listingMasterType] === null)
		     && !isset($product['Variations'])) {
			$process = false;
		}

		// log Variations
		if(isset($this->cItem['Variations']) && isset($product['Variations'])){
			$this->log(
				"\n\tVariations:"
			);
			foreach ($this->cItem['Variations'] as $aEBayVariation) {
				foreach ($product['Variations'] as $aShopVariantData) {
					if ($aEBayVariation['SKU'] == $aShopVariantData[(($this->config['SKUType'] == 'artNr') ? 'MarketplaceSku' : 'MarketplaceId')]) {
						if (array_key_exists('OldPrice', $aEBayVariation)) {
							$sAddEbayStrikePrice = "\n\t\teBay OldPrice: ".$aEBayVariation['OldPrice'];
						} else if (array_key_exists('ManufacturersPrice', $aEBayVariation)) {
							$sAddEbayStrikePrice = "\n\t\teBay ManufacturersPrice: ".$aEBayVariation['ManufacturersPrice'];
						} else {
							$sAddEbayStrikePrice = '';
						}
						if (array_key_exists('strike', $aShopVariantData['Price'])) {
							$sAddShopStrikePrice = "\n\t\tShop ".$sStrikePrice.": ".$aShopVariantData['Price']['strike'];
						} else {
							$sAddShopStrikePrice = '';
						}
						$this->log(
							"\n\t\tVariation SKU: ".$aEBayVariation['SKU'].
							"\n\t\teBay Quantity: ".$aEBayVariation['Quantity'].
							"\n\t\tShop Main Quantity: ". $aShopVariantData['Quantity'].
							"\n\t\teBay Price: ".$aEBayVariation['Price']. $sAddEbayStrikePrice .
							"\n\t\tShop Price: ".(isset($aShopVariantData['PriceReduced'][$listingMasterType])
								? $aShopVariantData['PriceReduced'][$listingMasterType]
								: $aShopVariantData['Price'][$listingMasterType]). $sAddShopStrikePrice .
							"\n"
						);
						break;
					}
				}
			}
		}
		
		/* {Hook} "EBay_SyncInventory_UpdateItem": Runs during the inventory synchronization from your shop to eBay, directly before the 
			   update will be send to eBay.<br>
			   Variables that can be used: 
			   <ul><li>$this->mpID: The ID of the marketplace.</li>
				   <li>$data (array): The content of the changes of one product (used to generate the <code>UpdateItems</code> request).<br>
					   Supported are <span class="tt">Price</span> and <span class="tt">NewQuantity</span>
				   </li>
				   <li>$this->cItem (array): The current product from the marketplaces inventory including some identification information.
					   <ul><li>SKU: Article number of marketplace</li>
						   <li>pID: products_id of product</li></ul>
				   </li>
				   <li>$currMainQty: Quantity of main product (same as in $data['NewQuantity']). Has to be modified in order to trigger an update.</li>
				   <li>$currPrice: Price of main product (same as in $data['Price'], if set). Has to be modified in order to trigger an update.</li>
				   <li>$currVarQty: Quantity of variation. Has to be modified in order to trigger an update. $data['Variations'] has to be updated
					   in order to modify the quantity of a variation.
				   </li>
			   </ul>
			   <p>Notice: It is only possible to modify products that have been identified by the magnalister plugin!<br>
				  Additionally the eBay inventory synchronisation is very complex. Be carefull, because in case of mistakes all your
				  active autions may be terminated.</p>
		*/
		if (($hp = magnaContribVerify('EBay_SyncInventory_UpdateItem', 1)) !== false) {
			/* Calculate the variations customers who use the EBay_SyncInventory_UpdateItem hook. Has to be done before the hook
			   is executed.
			   Usually the martix only has to be calcullated when there is a definitive update,
			   but the data has to be available for the hook as well. Slows things down.
			*/
			require($hp);
			
			// submit the contrib for debugging purposes.
			if (    isset($data)
			     && is_array($data)) {
				$data['DEBUG']['contrib'] = file_get_contents($hp);
			} else {
				$process = false; // $data unsetted by contrib
			}
		}

		if ($process) {
			$this->updateItems($data);
			$this->itemsProcessed[] = $this->cItem['ItemID'];
		}

		if (!empty($this->aEBayVariations)) {
		// case: Variation on eBay, simple in the shop
		// $this->identifySKU gets the next variation
			$this->updateItem();
		}
	}

	protected function updateItemOld() {
		if (in_array($this->cItem['ItemID'], $this->itemsProcessed)) {
			$this->log("\nItemID ".$this->cItem['ItemID'].' already processed.');
			return;
		}
		$this->cItem['SKU'] = trim($this->cItem['SKU']);
		if (empty($this->cItem['SKU'])) {
			$this->log("\nItemID ".$this->cItem['ItemID'].' has an emtpy SKU.');
			return;
		}

		@set_time_limit(180);
		$this->identifySKU();

		if (($hp = magnaContribVerify('EBay_SyncInventory_PreUpdateItem', 1)) !== false) {
			require($hp);
		}

		$articleIdent = 'SKU: '.$this->cItem['SKU'].(empty($this->cItem['MasterSKU']) ? '' : ' [MasterSKU: '.$this->cItem['MasterSKU'].']').' ('.$this->cItem['ItemTitle'].'); eBay-ItemID: '.$this->cItem['ItemID'].'; ListingType: '.$this->cItem['ListingType'].' ';
		if ((int)$this->cItem['pID'] <= 0) {
			$this->log("\n".$articleIdent.' not found');
			return;
		} else {
			$this->log("\n".$articleIdent.' found (pID: '.$this->cItem['pID'].')');
		}

		if (getDBConfigValue(array($this->marketplace.'.usevariations', 'val'), $this->mpID, true)) {
			$this->updateInternalVariations();
		}
		
		$currMainQty = $this->calcMainQuantity();

		# Bei 'Chinese' moegliche Option: eBay-Bestand nur reduzieren
		# d.h. wenn gewachsen, nichts tun
		if (   ('Chinese' == $this->cItem['ListingType'])
			&& ($this->cItem['Quantity'] < $currMainQty)
			&& ('onlydecr' == $this->config['ChineseStockSync'])
		) {
			return;
		}
		
		# ist es eine Variante?
		$currVarQty = makeVariationQuantity($this->cItem['pID'], $this->cItem['SKU']);
		if ($currVarQty !== false) {
			$currPrice = $this->calcPrice(true);
			$mainPrice = $this->calcPrice(false);
			$currTotalVariationSKUs = getTotalVariationSKUs($this->cItem['pID']);
		} else {
			$currPrice = $mainPrice = $this->calcPrice(false);
			$currTotalVariationSKUs = 0;
		}

		$this->log(
			"\n\teBay Quantity: ".$this->cItem['Quantity'].
			"\n\tShop Main Quantity: ".(($currMainQty === false) ? 'false' : $currMainQty).
			"\n\tShop Variation Quantity: ".(($currVarQty === false) ? 'false' : $currVarQty).
			"\n\teBay Price: ".$this->cItem['Price'].
			"\n\tShop Price: ".(($currPrice === false) ? ((($this->syncFixedPrice && 'Chinese' != $this->cItem['ListingType'] ) || ($this->syncChinesePrice && ('Chinese' == $this->cItem['ListingType']))) ? 'frozen' : 'false') : $currPrice)
		);
		
		// check if article status is true - if not, set stock to 0 | uses config
		$blVariations = true; //helper, to clean variations
		if ($this->config['StatusMode'] == 'true') {
			$iStatus = MagnaDB::gi()->fetchOne('
				SELECT products_status FROM ' . TABLE_PRODUCTS . '
				WHERE products_id = "' . $this->cItem['pID'] . '"
			');
			if ($iStatus == 0) {//notavailible => noStock
				$currMainQty = 0;
				$blVariations = false;
			}
		}

		$data = array (
			'ItemID' => $this->cItem['ItemID'],
			'NewQuantity' => (int)$currMainQty,
			'Variations' => false,
			'fixed.stocksync' => $this->config['FixedStockSync'],
			'fixed.pricesync' => $this->config['FixedPriceSync'],
			'chinese.stocksync' => $this->config['ChineseStockSync'],
			'chinese.pricesync' => $this->config['ChinesePriceSync'],
			'DEBUG' => getDebugDataForUpdateRequests($this->cItem['pID']),
		);
		$data['DEBUG']['syncConf'] = $this->config;
		$data['DEBUG']['contrib'] = false;
		$data['DEBUG']['calledBy'] = 'SyncInventory';
		
		if ($mainPrice !== false) {
			$data['Price'] = $mainPrice;
		}
		
		/* {Hook} "EBay_SyncInventory_UpdateItem": Runs during the inventory synchronization from your shop to eBay, directly before the 
			   update will be send to eBay.<br>
			   Variables that can be used: 
			   <ul><li>$this->mpID: The ID of the marketplace.</li>
				   <li>$data (array): The content of the changes of one product (used to generate the <code>UpdateItems</code> request).<br>
					   Supported are <span class="tt">Price</span> and <span class="tt">NewQuantity</span>
				   </li>
				   <li>$this->cItem (array): The current product from the marketplaces inventory including some identification information.
					   <ul><li>SKU: Article number of marketplace</li>
						   <li>pID: products_id of product</li></ul>
				   </li>
				   <li>$currMainQty: Quantity of main product (same as in $data['NewQuantity']). Has to be modified in order to trigger an update.</li>
				   <li>$currPrice: Price of main product (same as in $data['Price'], if set). Has to be modified in order to trigger an update.</li>
				   <li>$currVarQty: Quantity of variation. Has to be modified in order to trigger an update. $data['Variations'] has to be updated
					   in order to modify the quantity of a variation.
				   </li>
			   </ul>
			   <p>Notice: It is only possible to modify products that have been identified by the magnalister plugin!<br>
				  Additionally the eBay inventory synchronisation is very complex. Be carefull, because in case of mistakes all your
				  active autions may be terminated.</p>
		*/
		if (($hp = magnaContribVerify('EBay_SyncInventory_UpdateItem', 1)) !== false) {
			/* Calculate the variations customers who use the EBay_SyncInventory_UpdateItem hook. Has to be done before the hook
			   is executed.
			   Usually the martix only has to be calcullated when there is a definitive update,
			   but the data has to be available for the hook as well. Slows things down.
			*/
			$data['Variations'] = $this->calcVariationMatrix($blVariations, $mainPrice);
			require($hp);
			
			// submit the contrib for debugging purposes.
			$data['DEBUG']['contrib'] = file_get_contents($hp);
		}
		
		/*
		Hier Bedingungen: Variation quantity & -price sowie Stammart. qu & pri
		*/
		if (
			/* FixedPrice Article */ 
			(
				   ($this->syncFixedStock && ('Chinese' != $this->cItem['ListingType']))
				&& (
						/* Quantity changed (Article Variation) */
						((false !== $currVarQty) && ($this->cItem['Quantity'] != $currVarQty))
						/* Quantity changed (Article w/o Variation) */
					 || ((false === $currVarQty) && ($this->cItem['Quantity'] != $currMainQty))
						/* Number of Variations in Shop is different than on eBay */
					 || (array_key_exists('TotalVariations', $this->cItem) && ($this->cItem['TotalVariations'] != $currTotalVariationSKUs))
						/* Product has been ordered after the last Sync */
					 || ($this->cItem['LastOrdered'] > $this->cItem['LastSync'])
				)
			)
			/* Chinese Article */
			|| (
				   ($this->syncChineseStock && ('Chinese' == $this->cItem['ListingType']))
				&& ($this->cItem['Quantity'] != $currMainQty)
			)
			
			/* Sync FixedPrice price */
			|| (
				   ($this->syncFixedPrice && ($currPrice !== false) 
				&& ('Chinese' != $this->cItem['ListingType']))
				#&& ($this->cItem['Price'] != $currPrice)
			/* ignore price differences below 0.01 */
				&& (abs($this->cItem['Price'] - $currPrice) >= 0.01)
			)
			/* Sync Chinese price */
			|| (
				   ($this->syncChinesePrice && ($currPrice !== false) 
				&& ('Chinese' == $this->cItem['ListingType']))
				#&& ($this->cItem['Price'] != $currPrice)
				&& (abs($this->cItem['Price'] - $currPrice) >= 0.01)
			)
		) {
			/* Calculate the variations for all other customers who don't use the EBay_SyncInventory_UpdateItem hook */
			if ($hp === false) {
				$data['Variations'] = $this->calcVariationMatrix($blVariations, $mainPrice);
			}
			
			$this->updateItems($data);
			
			$this->itemsProcessed[] = $this->cItem['ItemID'];
		}
	}

	protected function submitStockBatch() {
		// Do nothing, as items are already updated one by one in updateItem().
	}
}
