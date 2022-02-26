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
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

class SimplePrice {
	private $settings = array();
	
	private $price = 0.0;
	private $actualCurr = '';
	private $currencies = array();
	
	private $isSpecialPrice = false;
	private $isGroupPrice   = false;
	
	private $addedTax = 0;
	
	protected static $cache = array();
	
	public function __construct($price = null, $actualCurr = null) {
		$this->settings['UseGambioProperties'] = getDBConfigValue('general.options', '0', 'old') == 'gambioProperties';
		
		$currencies_query = MagnaDB::gi()->query('SELECT * FROM '.TABLE_CURRENCIES);
		while ($currency = MagnaDB::gi()->fetchNext($currencies_query)) {
			$this->currencies[$currency['code']] = array (
				'title' => $currency['title'],
				'symbol_left' => fixHTMLUTF8Entities($currency['symbol_left']),
				'symbol_right' => fixHTMLUTF8Entities($currency['symbol_right']),
				'decimal_point' => $currency['decimal_point'],
				'thousands_point' => $currency['thousands_point'],
				'decimal_places' => (int)$currency['decimal_places'],
				'value' => (float)$currency['value']
			);
		}
		if ($actualCurr != null) {
			$this->setCurrency($actualCurr);
			if ($price != null) {
				$this->setPrice((float)$price);
			}
		}
	}

	private function triggerError($msg) {
		trigger_error($msg);
		if (MAGNA_DEBUG) {
			echo print_m(prepareErrorBacktrace(2));
		}
		return $this;
	}

	public function currencyExists($cur) {
		return array_key_exists($cur, $this->currencies);
	}

	public function setCurrency($actualCurr) {
		if (empty($actualCurr)) {
			return $this;
		}
		if (!$this->currencyExists($actualCurr)) {
			return $this->triggerError(__METHOD__.': This currency ('.$actualCurr.') is not yet available in your shop.');
		}
		$this->actualCurr = $actualCurr;
		return $this;
	}

	public function setPrice($price) {
		if ($this->actualCurr == null) {
			return $this->triggerError(__METHOD__.': Please set the currency first.');
		}
		$this->isSpecialPrice = false;
		$this->isGroupPrice   = false;

		$this->price = (float)$price;
		return $this;
	}

	public function setPriceAndCurrency($price, $actualCurr) {
		$this->isSpecialPrice = false;
		$this->isGroupPrice   = false;

		$this->setCurrency($actualCurr);
		$this->setPrice((float)$price);
		return $this;
	}

	public function getCurrency() {
		return $this->actualCurr;
	}

	public function getGroupPrice($groupID, $productID) {
		if (!MagnaDB::gi()->tableExists(TABLE_PERSONAL_OFFERS_BY.$groupID)) {
			return 0.0;
		}
		return (float)MagnaDB::gi()->fetchOne('
		    SELECT personal_offer FROM '.TABLE_PERSONAL_OFFERS_BY.$groupID.' 
		     WHERE products_id = "'.$productID.'" 
		           AND quantity=1
		  ORDER BY price_id DESC
		     LIMIT 1
		');
	}

	public function tryGetSpecialOffer($iProductId) {
		if (MagnaDB::gi()->columnExistsInTable('begins_date', TABLE_SPECIALS)) {
			$sAndBeginsDate = '
		     AND (begins_date IS NULL OR UNIX_TIMESTAMP(begins_date) IS NULL OR UNIX_TIMESTAMP(begins_date) <= UNIX_TIMESTAMP())';
		} else if (MagnaDB::gi()->columnExistsInTable('start_date', TABLE_SPECIALS)) {
			$sAndBeginsDate = '
		     AND (start_date IS NULL OR UNIX_TIMESTAMP(start_date) IS NULL OR UNIX_TIMESTAMP(start_date) <= UNIX_TIMESTAMP())';
		} else {
			$sAndBeginsDate = '';
		}
        return MagnaDB::gi()->fetchOne('
            SELECT specials_new_products_price 
              FROM '.TABLE_SPECIALS.'
             WHERE     products_id = "'.$iProductId.'"'.$sAndBeginsDate.'
             AND (expires_date IS NULL OR UNIX_TIMESTAMP(expires_date) IS NULL OR UNIX_TIMESTAMP(expires_date) = 0 OR UNIX_TIMESTAMP(expires_date) >= UNIX_TIMESTAMP())
             AND status = 1
             LIMIT 1
        ');
    }

    /**
     * Get Special Price from Shop
     *
     * @param $pID
     * @return float
     */
    public function getSpecialOffer($pID) {
		if (MagnaDB::gi()->columnExistsInTable('begins_date', TABLE_SPECIALS)) { // Gambio Column "begins_date"
			$sAndBeginsDate = '
		     AND (begins_date IS NULL OR UNIX_TIMESTAMP(begins_date) IS NULL OR UNIX_TIMESTAMP(begins_date) <= UNIX_TIMESTAMP())';
		} else if (MagnaDB::gi()->columnExistsInTable('start_date', TABLE_SPECIALS)) { // modified Column "start_date"
			$sAndBeginsDate = '
		     AND (start_date IS NULL OR UNIX_TIMESTAMP(start_date) IS NULL OR UNIX_TIMESTAMP(start_date) <= UNIX_TIMESTAMP())';
		} else {
			$sAndBeginsDate = '';
		}
		return (float)MagnaDB::gi()->fetchOne('
		    SELECT specials_new_products_price 
		      FROM '.TABLE_SPECIALS.'
		     WHERE products_id = "'.$pID.'"'.$sAndBeginsDate.'
		     AND (expires_date IS NULL OR UNIX_TIMESTAMP(expires_date) IS NULL OR UNIX_TIMESTAMP(expires_date) = 0 OR UNIX_TIMESTAMP(expires_date) >= UNIX_TIMESTAMP())
		     AND status=1
		     LIMIT 1
		');
	}

	public function getCustomizedPrice($pID, $mpID = null) {
		global $_MagnaSession;
		if (!isset($mpID)) {
			if (   is_array($_MagnaSession)
			    && array_key_exists('mpID', $_MagnaSession)) {
				$mpID = $_MagnaSession['mpID'];
			} else {
				$mpID = 0;
			}
		}
		
		$iPrice = false;
		/* {Hook} "CustomizePrice": is called while the Prices are determined, within a method
				getCustomizedPrice. The code should set $iPrice to the new value.
				If it's not set, or set to false, the price won't be changed.
			Variables that can be used:
			<ul><li>$pID - product's ID</li>
			    <li>$mpID - current marketplace ID</li>
			    <li>&$iPrice - new price you will set by the contrib</li>
			</ul>
		*/
		if (($hp = magnaContribVerify('CustomizePrice', 1)) !== false) {
			require($hp);
		}
		return $iPrice;
	}
	
	public static function loadPriceSettings($mpId, $extra = '') {
		$mp = magnaGetMarketplaceByID($mpId);
		
		# extra name extensions like 'chinese' or 'fixed' for eBay
		if (!empty($extra) && is_string($extra)) {
			$extra = '.'.trim($extra, '.');
		} else {
			$extra = '';
		}
		
		return array (
			'AddKind' => getDBConfigValue($mp.$extra.'.price.addkind', $mpId, 'percent'),
			'Factor'  => (float)getDBConfigValue($mp.$extra.'.price.factor', $mpId, 0),
			'Signal'  => getDBConfigValue($mp.$extra.'.price.signal', $mpId, ''),
			'Group'   => getDBConfigValue($mp.$extra.'.price.group', $mpId, ''),
			'UseSpecialOffer' => getDBConfigValue(array($mp.$extra.'.price.usespecialoffer', 'val'), $mpId, false),
			'IncludeTax' => true,
		);
	}
	
	protected static function isValidPriceConfig($pConfig) {
		return is_array($pConfig)
			&& isset($pConfig['AddKind']) && isset($pConfig['Factor'])
			&& isset($pConfig['Signal']) && isset($pConfig['Group'])
			&& isset($pConfig['UseSpecialOffer']);
	}
	
	public function setPriceFromDB($pID, $mpID, $extra = '') {
		if ($this->actualCurr == null) {
			$this->setCurrency(getCurrencyFromMarketplace($mpID));
		}
		$this->isSpecialPrice = false;
		$this->isGroupPrice   = false;
		$this->addedTax       = 0.0;
		
		if (self::isValidPriceConfig($extra)) {
			$pConfig = $extra;
		} else {
			$pConfig = self::loadPriceSettings($mpID, $extra);
		}
		
		if (($fCustomizedPrice = $this->getCustomizedPrice($pID, $mpID)) != false) {
			$this->price = $fCustomizedPrice;
			return $this;
		}

		if ($pConfig['UseSpecialOffer'] && (($price = $this->getSpecialOffer($pID)) > 0)) {
			$this->price = $price;
			$this->isSpecialPrice = true;
			return $this;
		}

		if (((int)$pConfig['Group'] > 0)
		    && (($price = $this->getGroupPrice((int)$pConfig['Group'], $pID)) > 0)
		) {
			$this->price = $price;
			$this->isGroupPrice = true;
			return $this;
		}

		$this->price = (float)MagnaDB::gi()->fetchOne('
		    SELECT products_price 
		      FROM '.TABLE_PRODUCTS.'
		     WHERE products_id="'.$pID.'"
		');
		return $this;
	}

	public function finalizePrice($pID, $mpID, $extra = '') {
		if (self::isValidPriceConfig($extra)) {
			$pConfig = $extra;
		} else {
			$pConfig = self::loadPriceSettings($mpID, $extra);
		}
		if (!isset($pConfig['IncludeTax']) || ($pConfig['IncludeTax'] !== false)) {
			$this->addTaxByPID($pID);
		}
		$this->calculateCurr();
		
		switch ($pConfig['AddKind']) {
			case 'percent': {
				$this->addTax((float)$pConfig['Factor']);
				break;
			}
			case 'addition': {
				$this->addLump((float)$pConfig['Factor']);
				break;
			}
			case 'constant': {
				$this->price = (float)$pConfig['Factor'];
				break;
			}
		}
		
		$this->roundPrice()->makeSignalPrice($pConfig['Signal']);
		return $this;
	}

	public function setFinalPriceFromDB($pID, $mpID, $extra = '') {
		if (!self::isValidPriceConfig($extra)) {
			$extra = self::loadPriceSettings($mpID, $extra);
		}
		
		$this->setPriceFromDB($pID, $mpID, $extra)->finalizePrice($pID, $mpID, $extra);
		return $this;
	}

	public function isSpecialPrice() {
		return $this->isSpecialPrice;
	}

	public function isGroupPrice() {
		return $this->isGroupPrice;
	}

	public function addTax($tax) {
		$this->addedTax = (float)$tax;
		if ($this->addedTax == 0.0) {
			return $this;
		}
		$this->price = $this->price + $this->price / 100 * $tax;
		return $this;
	}

	public function getTaxValue($tax) {
		return $this->price / 100 * $tax;
	}

	public function getTaxValueBrutto($tax) {
		return $this->price - ($this->price / (1 + ($tax / 100)));
	}

	public function removeTax($tax = false) {
		if ($tax === false) {
			$tax = $this->addedTax;
		}
		$this->addedTax = 0.0;
		if ($tax == 0.0) {
			return $this;
		}
		$this->price = ($this->price / (($tax + 100) / 100));
		return $this;
	}
	
	protected static function queryCache($query, $invalidate = false) {
		$sMd5 = md5($query);
		if (!isset(self::$cache[$sMd5]) || $invalidate) {
			self::$cache[$sMd5] = MagnaDB::gi()->fetchOne($query);
		}
		return self::$cache[$sMd5];
	}
	
	public static function getTaxByClassID($taxClassID, $countryID = -1, $sFallback = 0.00) {
		if ($countryID == -1) {
            if (defined('STORE_COUNTRY') && (int)(STORE_COUNTRY) > 0) {
                $countryID = (int)STORE_COUNTRY;
            } elseif (defined('ML_GAMBIO_41_NEW_CONFIG_TABLE')) {
                $countryID = (int)self::queryCache("
                    SELECT `value`
                      FROM ".TABLE_CONFIGURATION."
                     WHERE `key` = 'configuration/STORE_COUNTRY'
                ");
            } else {
                $countryID = (int)self::queryCache('
                    SELECT `configuration_value` 
                      FROM '.TABLE_CONFIGURATION.'
                     WHERE `configuration_key` = "STORE_COUNTRY"
                ');
            }
		}
		$taxRate = self::queryCache(eecho('
			SELECT MAX(tax_rate)
			  FROM '.TABLE_TAX_RATES.' tr, '.TABLE_ZONES_TO_GEO_ZONES.' zgz 
			 WHERE tr.tax_class_id="'.$taxClassID.'"
			       AND tr.tax_zone_id=zgz.geo_zone_id
			       AND zgz.zone_country_id="'.$countryID.'"
			 LIMIT 1
		', false));

		//echo var_dump_pre($taxRate);
		// special case: Tax rate "none" in product data
		if (    (($taxRate === false) || ($taxRate === null))
		     && ('0' == $taxClassID) && (self::queryCache('SELECT COUNT(*)
			 FROM '.TABLE_TAX_RATES.'
			WHERE tax_class_id = 0') == 0)) {
			return (float)$sFallback;
		}

		if (($taxRate === false) || ($taxRate === null)) {
			// Fallback for shops with broken zgz <--> tr tables
			// Try tax_class_id = 1
			// If not filled, return 0
			$taxRate = self::queryCache(eecho('
				SELECT MAX(tax_rate)
				  FROM '.TABLE_TAX_RATES.' tr, '.TABLE_ZONES_TO_GEO_ZONES.' zgz 
				 WHERE tr.tax_class_id=1
				       AND tr.tax_zone_id=zgz.geo_zone_id
				       AND zgz.zone_country_id="'.$countryID.'"
				 LIMIT 1
			', false));
		}
		if (($taxRate === false) || ($taxRate === null)) {
			$taxRate = $sFallback;
		}
		return (float)$taxRate;
	}

	public function addTaxByTaxID($taxClassID) {
		return $this->addTax(
			self::getTaxByClassID($taxClassID)
		);
	}

	public function removeTaxByTaxID($taxClassID) {
		return $this->removeTax(
			self::getTaxByClassID($taxClassID)
		);
	}

	public static function getTaxByPID($pID) {
		$taxClassID = MagnaDB::gi()->fetchOne('
			SELECT products_tax_class_id
			  FROM '.TABLE_PRODUCTS.' p
			 WHERE products_id="'.$pID.'"
			 LIMIT 1
		');
		if (($taxClassID === false) || ($taxClassID === null)) {

			return 0;
		}
		return self::getTaxByClassID($taxClassID);
	}

	public function addTaxByPID($pID) {
		return $this->addTax(
			self::getTaxByPID($pID)
		);
	}

	public function removeTaxByPID($pID) {
		return $this->removeTax(
			self::getTaxByPID($pID)
		);
	}

	public function addLump($add) {
		$this->price += (float)$add;
		return $this;
	}
	
	public function subLump($sub) {
		$this->price -= (float)$sub;
		return $this;
	}

	public function addAttributeSurcharge($aID) {
		if ((int)$aID <= 0) {
			return $this;
		}
		
		if ($this->settings['UseGambioProperties']) {
			$attr = MagnaDB::gi()->fetchRow('
				SELECT combi_price_type AS Type, combi_price AS Price
				  FROM products_properties_combis
				 WHERE products_properties_combis_id="'.$aID.'"
			');
			if (!is_array($attr)) {
				return $this;
			}
		} else {
			$attr = MagnaDB::gi()->fetchRow('
				SELECT options_values_price AS price,
				       price_prefix AS prefix
				  FROM '.TABLE_PRODUCTS_ATTRIBUTES.'
				 WHERE products_attributes_id="'.$aID.'"
			');
			if (!is_array($attr)) {
				return $this;
			}
			if ($attr['prefix'] == '=') {
				$attr = array (
					'Type' => 'fix',
					'Price' => $attr['price'],
				);
			} else if ($attr['prefix'] == '%') {
				$attr = array (
					'Type' => 'calc',
					'Price' => $this->price * $attr['price'] / 100,
				);
			} else {
				$attr = array (
					'Type' => 'calc',
					'Price' => $attr['price'] * (($attr['prefix'] == '+') ? 1 : -1)
				);
			}

		}
		
		if ($attr['Price'] == 0) {
			return $this;
		}
		
		$tmpTax = $this->addedTax;
		$this->removeTax();

		// for gambio properties we need to always use addLump
		if ($attr['Type'] == 'fix' && (!$this->settings['UseGambioProperties'])) {
			$this->price = $attr['Price'];
		} else {
			$this->addLump($attr['Price']);
		}
		
		$this->addTax($tmpTax);
		
		return $this;
	}

	/**
	 * Geht immer von DEFAULT_CURRENCY mit Umrechnungsfaktor == 1.0 aus
	 */
	public function calculateCurr() {
		$this->price = $this->currencies[$this->actualCurr]['value'] * $this->price;
		return $this;
	}

	/**
	 * Geht immer von DEFAULT_CURRENCY mit Umrechnungsfaktor == 1.0 aus
	 */	
	public function updateCurrency($val) {
		if (empty($this->actualCurr)) {
			return $this->triggerError(__METHOD__.': Please set the currency first.');
		}
		$val = (float)$val;
		if ($val <= 0) {
			return $this;
		}

		MagnaDB::gi()->update(TABLE_CURRENCIES, array(
			'value' => $val,
		), array (
			'code' => $this->actualCurr
		));
		$this->currencies[$this->actualCurr]['value'] = $val;
		return $this;
	}
	
	public function updateCurrencyByService(&$success = false) {
		if (empty($this->actualCurr)) {
			return $this->triggerError(__METHOD__.': Please set the currency first.');
		}
		if ($this->actualCurr == DEFAULT_CURRENCY) {
			$success = true;
			return $this;
		}
		try {
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetExchangeRate',
				'SUBSYSTEM' => 'Core',
				'FROM' => strtoupper(DEFAULT_CURRENCY),
				'TO' => strtoupper($this->getCurrency()),
			));
			if ($result['EXCHANGERATE'] > 0) {
				$this->updateCurrency($result['EXCHANGERATE']);
			}
		} catch (MagnaException $e) { 
			$success = false;
			return $this;
		}
		$success = true;
		return $this;
	}
	
	public function getCurrencyValue() {
		if (empty($this->actualCurr)) {
			return $this->triggerError(__METHOD__.': Please set the currency first.');
		}
		return $this->currencies[$this->actualCurr]['value'];
	}
	

	public function roundPrice() {
		$this->price = number_format($this->price, $this->currencies[$this->actualCurr]['decimal_places'], '.', '');
		return $this;
	}

	public function makeSignalPrice($decimalDigits) {
		if (isset($decimalDigits) && $decimalDigits !== '') {
			//If price signal is single digit then just add price signal as last digit
			if (strlen((string)$decimalDigits) == 1) {
				$this->price = (0.1 * (int)($this->price * 10)) + ($decimalDigits / 100);
			} else {
				$this->price = ((int)$this->price) + ($decimalDigits / 100);
			}
		}

		return $this;
	}

	public function getPrice() {
		return $this->price;
	}

	public function format($default = false) {
		return trim(
			$this->currencies[$default ? DEFAULT_CURRENCY : $this->actualCurr]['symbol_left'].
			' '.$this->formatWOCurrency($default).' '.
			$this->currencies[$default ? DEFAULT_CURRENCY : $this->actualCurr]['symbol_right']
		);
	}

	public function formatWOCurrency($default = false) {
		$format = $this->getFormatOptions($default);
		return trim(number_format($this->price, $format[0], $format[1], $format[2]));
	}

	public function getFormatOptions($default = false) {
		return array (
			$this->currencies[$default ? DEFAULT_CURRENCY : $this->actualCurr]['decimal_places'],
			$this->currencies[$default ? DEFAULT_CURRENCY : $this->actualCurr]['decimal_point'],
			$this->currencies[$default ? DEFAULT_CURRENCY : $this->actualCurr]['thousands_point']
		);
	}
}
