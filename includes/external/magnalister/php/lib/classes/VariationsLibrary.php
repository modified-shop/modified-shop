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
 * $Id: VariationsLibrary.php 1214 2014-11-15 12:42:46Z MaW $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_INCLUDES . 'lib/classes/VariationsCalculator.php');
require_once(DIR_MAGNALISTER_INCLUDES . 'lib/classes/SimplePrice.php');

class VariationsLibrary {
	
	public function __construct() {
		# erschtmal leer, brauchma nix
	}
	
	/**
	 * @return VariationsLibrary Singleton - gets Instance
	 */
	public static function gi() {
		if (self::$instance == NULL) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	#============================== from magnaFunctionLib ==========================
	
	/**
	 * update magnalister's variations table for a single product
	 * return true if variations available, otherwise false
	 */
	public function setProductVariations($pID, $language = false, $resetModelNames = true) {
		$skutype = ('artNr' == getDBConfigValue('general.keytype', '0')) ? 'model' : 'id';
		
		$vc = new VariationsCalculator(array(
			'skubasetype' => $skutype, // [model | id]
			'skuvartype'  => $skutype  // [model | id]
		), $language);
		
		if (!$resetModelNames) {
			$namesArr = MagnaDB::gi()->fetchArray('
				SELECT variation_attributes, '.mlGetVariationSkuField().' AS variation_products_model
				  FROM ' . TABLE_MAGNA_VARIATIONS . '
				 WHERE products_id = ' . $pID
			);
			if (is_array($namesArr)) {
				$namesByAttr = array();
				foreach ($namesArr as $namesRow) {
					$namesByAttr[$namesRow['variation_attributes']] = $namesRow['variation_products_model'];
				}
				if (empty($namesByAttr)) {
					unset($namesByAttr);
					$namesByAttr = false;
				}
			} else {
				$namesByAttr = false;
			}
		}
		
		MagnaDB::gi()->query('DELETE FROM ' . TABLE_MAGNA_VARIATIONS . ' WHERE products_id = ' . $pID);
		
		$permutations = $vc->getVariationsByPID($pID);
		if (!$permutations) {
			return false;
		}
		
		# preserve variation products model names, if wished
		if (!$resetModelNames) {
			foreach ($permutations as &$permutation) {
				if (isset($namesByAttr[$permutation['variation_attributes']])) {
					$permutation['variation_products_model'] = $namesByAttr[$permutation['variation_attributes']];
				}
			}
		}
		
		if (MagnaDB::gi()->batchinsert(TABLE_MAGNA_VARIATIONS, $permutations, true)) {
			return true;
		}
		return false;
	}
	
	/**
	 * Gets the quantity of all variations of a product,
	 * without actually filling the variations table.
	 *
	 * @param int $minus
	 *   subtract from each variation's stock, for the case this is set by marketplace config
	 */
	public function getProductVariationsQuantity($pID, $minus = 0) {
		$quantity = 0;
		$skutype  = (getDBConfigValue('general.keytype', '0') == 'artNr') ? 'model' : 'id';
		$vc       = new VariationsCalculator(array(
			'skubasetype' => $skutype,
			'skuvartype'  => $skutype
		));
		return $vc->getProductVariationsTotalQuantity($pID, $minus);
	}
	
	/**
	 * does the product have variations? no matter if there's stock > 0
	 */
	public function variationsExist($pID) {
		return (MagnaDB::gi()->fetchOne('SELECT COUNT(*) FROM ' . TABLE_MAGNA_VARIATIONS . ' WHERE products_id = ' . $pID) > 0);
	}
	
	#============================== from ebayFunctions =============================
	
	
	/**
	 * Determines a price for a product, considering variations.
	 *
	 * @todo: rewrite this code. The method is then overwritten in eBayVariationsLibrary class.
	 * @param string $priceType
	 *   eBay specific: chinese, chinese.buyitnow or fixed
	 * @param boolean $takePrepared
	 *   if true, use prepared price as base, otherwise calculate anew
	 * @param float $variationPrice
	 *   add price for variation (netto)
	 */
	public function makePrice($pID, $priceType, $takePrepared = false, $variationPrice = 0.0) {
		global $_MagnaSession;
		if ($takePrepared) {
			
			if ('artNr' == getDBConfigValue('general.keytype', '0')) {
				$preparedPriceQuery = '
					SELECT ' . ('BuyItNowPrice' == $priceType ? 'ep.BuyItNowPrice' : 'ep.Price') . ' AS Price 
					  FROM ' . TABLE_MAGNA_EBAY_PROPERTIES . ' ep,
					       ' . TABLE_PRODUCTS . ' p 
					 WHERE ep.products_model = p.products_model
					       AND p.products_id = ' . $pID . '
					       AND ep.mpID = ' . $_MagnaSession['mpID'] . '
					 LIMIT 1
				';
			} else {
				$preparedPriceQuery = '
					SELECT ' . ('BuyItNowPrice' == $priceType ? 'BuyItNowPrice' : 'Price') . ' AS Price
					  FROM ' . TABLE_MAGNA_EBAY_PROPERTIES . '
					 WHERE products_id = ' . $pID . '
					       AND mpID = ' . $_MagnaSession['mpID'] . '
					 LIMIT 1
				';
			}
			$preparedPriceRow = MagnaDB::gi()->fetchArray($preparedPriceQuery);
			if (1 == MagnaDB::gi()->numRows()) {
				return ($preparedPriceRow[0]['Price']);
			}
		}
		
		switch ($priceType) {
			case 'Chinese': {
				$which = 'chinese';
				break;
			}
			case 'BuyItNowPrice': {
				$which = 'chinese.buyitnow';
				break;
			}
			default: { # 'FixedPriceItem' oder 'StoresFixedPrice'
				$which = 'fixed';
				break;
			}
		}
		$myPrice = new SimplePrice(null, getCurrencyFromMarketplace($_MagnaSession['mpID']));
		if ($variationPrice) {
			$myPrice->setPriceFromDB($pID, $_MagnaSession['mpID'], $which)->addLump($variationPrice)->finalizePrice($pID, $_MagnaSession['mpID'], $which);
		} else {
			$myPrice->setFinalPriceFromDB($pID, $_MagnaSession['mpID'], $which);
		}
		return $myPrice->getPrice();
	}
	
	/**
	 * Calculate variation price based on main price (for the case main price has been changed manually)
	 *
	 * Main price is brutto, variation extra charge is netto, so simple addition would not work
	 *
	 * @param float $mainPrice
	 *   main item price, brutto (value with config settings already in it)
	 * @param float $varPrice
	 *   variation extra charge, netto, from variations table
	 */
	public function addVarPriceToPrice($pID, $mainPrice, $varPrice) {
		global $_MagnaSession;
		
		if ('percent' == getDBConfigValue($_MagnaSession['currentPlatform'] . '.price.addkind', $_MagnaSession['mpID'])) {
			$varPrice *= (float) ((100.0 + getDBConfigValue($_MagnaSession['currentPlatform'] . '.price.factor', $_MagnaSession['mpID'], 0.0)) / 100.0);
		}
		$myPrice = new SimplePrice($mainPrice, getCurrencyFromMarketplace($_MagnaSession['mpID']));
		$myPrice->removeTaxByPID($pID)->addLump($varPrice)->addTaxByPID($pID);
		return $myPrice->getPrice();
	}
	
	public function makeVariationPrice($pID, $variation_products_model, $otherMainPrice = false) {
		global $_MagnaSession;
		$dbVarPrice = MagnaDB::gi()->fetchOne('
			SELECT variation_price
			  FROM ' . TABLE_MAGNA_VARIATIONS . '
			 WHERE products_id = ' . $pID . '
			       AND '.mlGetVariationSkuField().' = "' . MagnaDB::gi()->escape($variation_products_model) . '"
			');
		
		if (false === $dbVarPrice) {
			return false;
		}
		
		if (!$otherMainPrice) {
			return $this->makePrice($pID, 'fixed', false, (float) $dbVarPrice);
			
		} else {
			
			return $this->addVarPriceToPrice($pID, $otherMainPrice, (float) $dbVarPrice);
		}
	}
	
	/**
	 * Calculate total quantity of an item, including all variations,
	 * considering the marketplace config settings
	 * (e.g. if the setting is "Fixed" == 3, and we have 6 variations, total quantity == 18)
	 *
	 */
	public function makeQuantity($pID) {
		global $_MagnaSession;
		
		$calc_method = getDBConfigValue($_MagnaSession['currentPlatform'] . '.quantity.type', $_MagnaSession['mpID']);
		$qValue      = (int) getDBConfigValue($_MagnaSession['currentPlatform'] . '.quantity.value', $_MagnaSession['mpID']);
		$maxQuantity = (int) getDBConfigValue($_MagnaSession['currentPlatform'] . '.maxquantity', $_MagnaSession['mpID'], 0);
		
		if (0 == $maxQuantity) {
			$maxQuantity = PHP_INT_MAX;
		}
		
		if ('lump' == $calc_method) {
			return $qValue;
		}
		
		$shop_stock = 0;
		# Nehme Anzahl Varianten, soweit Varianten lt konfig aktiviert, und soweit solche existieren
		
		if (getDBConfigValue(array(
				$_MagnaSession['currentPlatform'] . '.usevariations',
				'val'
			), $_MagnaSession['mpID'], true)
			&& $this->variationsExist($pID)
		) {
			if ('stock' == $calc_method) {
				$shop_stock = min($this->getProductVariationsQuantity($pID), $maxQuantity);
			} else if ('stocksub' == $calc_method) {
				$shop_stock = min($this->getProductVariationsQuantity($pID, $qValue), $maxQuantity);
			}
			return $shop_stock;
		}
		
		# Keine Varianten da, nehme Stammartikel
		$shop_stock = MagnaDB::gi()->fetchOne('SELECT products_quantity FROM ' . TABLE_PRODUCTS . ' WHERE products_id =' . $pID);
		if ('stock' == $calc_method) {
			return min($shop_stock, $maxQuantity);
		} else if ('stocksub' == $calc_method) {
			return min(max(0, $shop_stock - $qValue), $maxQuantity);
		} else {
			return 0;
		}
	}
	
	/**
	 * get the quantity of a single variation
	 */
	public function makeVariationQuantity($pID, $variation_products_model) {
		global $_MagnaSession;
		
		$dbQuantity = MagnaDB::gi()->fetchOne('
			SELECT variation_quantity
			  FROM ' . TABLE_MAGNA_VARIATIONS . '
			 WHERE products_id = ' . $pID . '
			       AND '.mlGetVariationSkuField().' = "' . MagnaDB::gi()->escape($variation_products_model) . '"
		');
		
		if (false === $dbQuantity) {
			return false;
		}
		
		$maxQuantity = (int) getDBConfigValue($_MagnaSession['currentPlatform'] . '.maxquantity', $_MagnaSession['mpID'], 0);
		if (0 == $maxQuantity) {
			$maxQuantity = PHP_INT_MAX;
		}
		$calc_method = getDBConfigValue($_MagnaSession['currentPlatform'] . '.quantity.type', $_MagnaSession['mpID']);
		if ('stock' == $calc_method) {
			return (int) min($dbQuantity, $maxQuantity);
		}
		
		$calc_val = getDBConfigValue($_MagnaSession['currentPlatform'] . '.quantity.value', $_MagnaSession['mpID']);
		if ('lump' == $calc_method) {
			return (int) $calc_val;
		}
		if ('stocksub' == $calc_method) {
			return (int) min(($dbQuantity - $calc_val), $maxQuantity);
		}
	}
	
	/**
	 * build variation matrix for upload to marketplace
	 * @param float $otherMainPrice
	 *  in case when we have a frozen price
	 * @param boolean $set
	 *  write to DB or not
	 * @param boolean $withPrices
	 *  include prices into the matrix
	 *  (it's possible that a marketplace accepts a matrix without prices, when updating an item)
	 *
	 */
	public function getVariations($pID, $otherMainPrice = null, $set = true, $withPrices = true) {
		global $_MagnaSession;
		if (false == getDBConfigValue(array(
				$_MagnaSession['currentPlatform'] . '.usevariations',
				'val'
			), $_MagnaSession['mpID'], true)
		) {
			return false;
		}
		
		$variations = array();
		$namelist   = array();
		$valuelist  = array();
		
		if (!MagnaDB::gi()->tableExists(TABLE_MAGNA_VARIATIONS)) {
			return false;
		}
		if ($set) {
			$this->setProductVariations($pID, getDBConfigValue($_MagnaSession['currentPlatform'] . '.lang', $_MagnaSession['mpID'], true));
		}
		if (0 == MagnaDB::gi()->fetchOne('SELECT count(*) FROM ' . TABLE_MAGNA_VARIATIONS . ' WHERE products_id =' . $pID)) {
			return false;
		}
		# Anzahl-Settings
		$qType       = getDBConfigValue('ebay.fixed.quantity.type', $_MagnaSession['mpID']);
		$qValue      = getDBConfigValue('ebay.fixed.quantity.value', $_MagnaSession['mpID']);
		$maxQuantity = (int) getDBConfigValue('ebay.maxquantity', $_MagnaSession['mpID'], 0);
		
		if (0 == $maxQuantity) {
			$maxQuantity = PHP_INT_MAX;
		}
		
		$variations_data = MagnaDB::gi()->fetchArray('
		    SELECT '.mlGetVariationSkuField().' AS variation_products_model, variation_attributes, variation_quantity, variation_price 
		      FROM ' . TABLE_MAGNA_VARIATIONS . '
		     WHERE products_id =' . $pID . '
		  ORDER BY variation_id
		');
		
		# VariationsCalculator ermittelt bei der Initialisierung die Namen der Attribute und Werte
		$vc = new VariationsCalculator(array(), getDBConfigValue($_MagnaSession['currentPlatform'] . '.lang', $_MagnaSession['mpID'], false));
		# Einzelne Varianten
		foreach ($variations_data as $k => $dataRow) {
			# Quantity
			if ('lump' == $qType) {
				$variations['Variations'][$k]['Quantity'] = intval($qValue);
			} else if ('stock' == $qType) {
				$variations['Variations'][$k]['Quantity'] = min($maxQuantity, intval($dataRow['variation_quantity']));
			} else if ('stocksub' == $qType) {
				$variations['Variations'][$k]['Quantity'] = min($maxQuantity, intval($dataRow['variation_quantity'] - $qValue));
			}
			if ($variations['Variations'][$k]['Quantity'] < 0) {
				$variations['Variations'][$k]['Quantity'] = 0;
			}
			# SKU
			$variations['Variations'][$k]['SKU'] = $dataRow['variation_products_model'];
			if ($withPrices) {
				# Preis
				if (!$otherMainPrice) {
					$variations['Variations'][$k]['StartPrice'] = $this->makePrice($pID, 'fixed', false, (float) $dataRow['variation_price']);
				} else {
					$variations['Variations'][$k]['StartPrice'] = $this->addVarPriceToPrice($pID, $otherMainPrice, $dataRow['variation_price']);
				}
			}
			# Eigenschaften
			$attributes = explode('|', $dataRow['variation_attributes']);
			foreach ($attributes as $attr) {
				if (empty($attr)) {
					continue;
				}
				list($name, $value) = explode(',', $attr);
				if (!in_array($vc->optionNames[$name], $namelist)) {
					$namelist[] = $vc->optionNames[$name];
					# eBay akzeptiert nicht mehr als 50 Zeichen
					$valuelist[$vc->optionNames[$name]] = array(
						trim(substr($vc->optionValueNames[$value], 0, 50))
					);
				} else if (!in_array(trim(substr($vc->optionValueNames[$value], 0, 50)), $valuelist[$vc->optionNames[$name]])) {
					$valuelist[$vc->optionNames[$name]][] = trim(substr($vc->optionValueNames[$value], 0, 50));
				}
				$variations['Variations'][$k]['VariationSpecifics'][] = array(
					'Name' => trim(substr($vc->optionNames[$name], 0, 50)),
					'Value' => trim(substr($vc->optionValueNames[$value], 0, 50))
				);
			}
		}
		# Zusammenstellung der Namen und Werte
		foreach ($namelist as $name) {
			$variations['VariationSpecificsSet'][] = array(
				'Name' => trim(substr($name, 0, 50)),
				'Values' => $valuelist["$name"]
			);
		}
		return $variations;
	}
	
	/**
	 * Sets the stock of one variation (after a shop sale) in the variation matrix.
	 *
	 * @param array &$variationMatrix
	 *   variation matrix to process
	 * @param array $attributes
	 *   which attributes have been sold
	 * @param int $value
	 *   value used for changing stock
	 * @param string mode
	 *   SUB - subtract value from quntity, or SET - set quantity to value
	 */
	public function setVariationQuantity(&$variationMatrix, $pID, $attributes, $value, $mode) {
		$variation_products_model = '';
		if (!is_array($attributes)) {
			return false;
		}
		if (!$pID) {
			return false;
		}
		ksort($attributes);
		
		$variationDbData = MagnaDB::gi()->fetchArray('
			SELECT '.mlGetVariationSkuField().' AS variation_products_model, variation_attributes
			  FROM ' . TABLE_MAGNA_VARIATIONS . ' 
			 WHERE products_id = ' . $pID
		);
		foreach ($variationDbData as $row) {
			$row_attributes = explode('|', $row['variation_attributes']);
			$found          = true;
			if (count($row_attributes) < count($attributes)) {
				$found = false;
				continue;
			}
			foreach ($row_attributes as $attr) {
				list($akey, $aval) = $attr_arr = explode(',', $attr);
				if ($attributes[$akey] != $aval)
					$found = false;
			}
			if ($found) {
				$variation_products_model = $row['variation_products_model'];
				break;
			}
		}
		if ('' == $variation_products_model){
			return false;
		}
		
		# Variation in der Matrix heruntersetzen
		foreach ($variationMatrix['Variations'] as $no => &$spec) {
			if ($spec['SKU'] == $variation_products_model) {
				if ('SUB' == $mode) {
					$spec['Quantity'] -= $value;
				} else {
					$spec['Quantity'] = $value;
				}
				# keine Anzahlen < 0 an eBay schicken
				if ($spec['Quantity'] < 0) {
					$spec['Quantity'] = 0;
				}
			}
		}
		
		# Kein Datenbank-Update, da u.U. mehrere mpIDs betroffen.
		# Die Tabelle wird eh vor jeder Verwendung aktualisiert.
		return true;
	}
	
}
