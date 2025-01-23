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

require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/VariationsCalculator.php');

class MLProduct {
	private static $instance = null;
	
	protected $languagesAvailable = array();
	protected $languagesSelected = array(
		'type' => 'single',
		'values' => array(), // array of languages.languages_id
	);
	
	/**
	 * type can be `single` or `multiple`
	 * @var array $priceConfig
	 */
	protected $priceConfig = array('type' => 'single', 'values' => array());
	protected $quantityConfig = array();
	protected $blUseMultiDimensionalVariations = true;
	
	protected $allowedVariationDimensions = array();
	protected $options = array();
	// Same as $options but specific to the current call.
	protected $optionsTmp = array();
	
	protected $simpleprice = null;
	protected $variationCalculator = null;
	
	protected $dbMatchings = array(
		'ManufacturerPartNumber' => array(),
		'tecDocKType' => array(),
	);
	
	protected $productMainSelectFields = '';
	protected $productOfferSelectFields = '';
	protected $attributesMainSelectFields = '';
	protected $productDescriptionSelectFields = '';
	protected $attributesOfferSelectFields = '';
	
	protected $existingTables = array();
	protected $existingColumns = array();
	
	protected $cache = array();
	
	private function __construct() {
		foreach (array(
			'ShippingStatus' => 'TABLE_SHIPPING_STATUS'
		) as $key => $tableDefine) {
			$this->existingTables[$key] = defined($tableDefine) && MagnaDB::gi()->tableExists(constant($tableDefine));
		}
		foreach (array (
			'pa.attributes_stock' => array ('table' => TABLE_PRODUCTS_ATTRIBUTES, 'column' => 'attributes_stock'),
			'pa.sortorder'        => array ('table' => TABLE_PRODUCTS_ATTRIBUTES, 'column' => 'sortorder'),
			'pa.attributes_model' => array ('table' => TABLE_PRODUCTS_ATTRIBUTES, 'column' => 'attributes_model'),
		) as $key => $define) {
			$this->existingColumns[$key] = MagnaDB::gi()->columnExistsInTable($define['column'], $define['table']);
		}
		
		$this->resetOptions();
		
		$this->simpleprice = new SimplePrice();
		$this->variationCalculator = new VariationsCalculator();
		
		$this->loadLanguagesAvailable();
		$this->buildSelectFields();
		$this->reset();
	}
	
	/**
	 * Singleton - gets Instance
	 * @return MLProduct
	 */
	public static function gi() {
		if (self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Gets the information if the shop has variations
	 * (dummy, used in several scripts which come from the veyton version)
	 * @return true
	 */
	public function hasMasterItems() {
		return true;
	}
	
	/**
	 * Sets the internal options. The options affect the behavior of the
	 * class. These options are very shop specific and might not do
	 * anything for certain conditions/shopsystems.
	 *
	 * @param array $options
	 *    Currently implemented options:
	 *      * purgeVariations (default: false)
	 *        If set to true multi-dimensional variations will be purged and re-calculated
	 *      * useGambioProperties (default: false)
	 *        If set to true gambio properties will be used to fetch all variations.
	 *        This disables the option 'purgeVariations'.
	 *        Additionally self::$blUseMultiDimensionalVariations will be ignored
	 *        as well as self::$allowedVariationDimensions.
	 *      * includeVariations (default: true)
	 *        If set to false no variations will be loaded for the main product.
	 *      * sameVariationsToAttributes (default: false)
	 *        If the values of a variation dimension are the same for all variation products this dimension
	 *        will be converted to a simple attribute.
	 *      * allowSingleVariations (default: true)
	 *        If a product has only one variation (not dimension) it will be kept if this setting is true.
	 *        Otherwise the variation will be merged with the master product and the variation attributes
	 *        will be added to the master as regular attributes.
	 *      * sExtendFetchSingeVariationsQueryWhere (default: '')
	 *        Implemented especially for Amazon because there we only want the most attributes with ean
	 *        You can use is to specify the query more detailed
	 * @return $this
	 */
	public function setOptions(array $options) {
		$this->options = array_replace($this->options, $options);
		return $this;
	}
	
	/**
	 * Reset all options to their defaults. See setOptions() for more details.
	 *
	 * @return $this
	 */
	public function resetOptions() {
		$this->options = array (
			'purgeVariations' => false,
			'useGambioProperties' => false,
			'includeVariations' => true,
			'sameVariationsToAttributes' => false,
			'allowSingleVariations' => true,
			'sExtendFetchSingeVariationsQueryWhere' => '',
		);
		return $this;
	}
	
	/**
	 * Sets the temporary options that will be resetted after the product has been fetched.
	 *
	 * @param array $optionsTmp
	 *    @see self::setOptions() for defails
	 *
	 * @return void
	 */
	protected function setOptionsTmp(array $optionsTmp) {
		$this->optionsTmp = array_replace($this->options, $optionsTmp);
	}
	
	/**
	 * Resets the temporarily setted options back to the default.
	 */
	protected function resetOptionsTmp() {
		$this->optionsTmp = $this->options;
	}
	
	/**
	 * Loads the available languages from the languages table and stores them in
	 * self::languagesAvailable where the key is the language id and the value
	 * is the language code.
	 *
	 * @return void
	 */
	protected function loadLanguagesAvailable() {
		$languages = MagnaDB::gi()->fetchArray('SELECT languages_id, code FROM '.TABLE_LANGUAGES);
		$this->languagesAvailable = array();
		foreach ($languages as $lang) {
			$this->languagesAvailable[$lang['languages_id']] = strtolower($lang['code']);
		}
	}
	
	/**
	 * Returns the language code of a language id if it exists.
	 *
	 * @param int $id
	 *    The language id
	 *
	 * @return string|boolean
	 *    The language code or false if the id is not a valid language.
	 */
	public function languageIdToCode($id) {
		return isset($this->languagesAvailable[$id])
			? $this->languagesAvailable[$id]
			: false;
	}
	
	/**
	 * Returns the language id of a language code if it exists.
	 *
	 * @param string $code
	 *    The language code
	 *
	 * @return int|boolean
	 *    The language id or false if the code is not a valid language.
	 */
	protected function languageCodeToId($code) {
		return array_search(strtolower($code), $this->languagesAvailable);
	}
	
	/**
	 * Convert a language iso code or full name to the 
	 * languages_id.
	 *
	 * @param string $language
	 *    The language to convert
	 * @return int
	 *    The languages_id
	 */
	protected function convertLanguageIso($language) {
		$lang = MagnaDB::gi()->escape($language);
		$langid = MagnaDB::gi()->fetchOne('
			SELECT languages_id 
			  FROM '.TABLE_LANGUAGES.'
			 WHERE code = "'.$lang.'"
			       OR directory = "'.$lang.'"
			 LIMIT 1
		');
		if (empty($langid)) {
			$langid = '2'; // nasty fallback
		}
		return $langid;
	}
	
	/**
	 * Set the language that shall be used when loading language specific
	 * product data.
	 *
	 * @param mixed $language
	 *    The language as languages_id, ISO Code or the full name of the language.
	 * @return $this
	 */
	public function setLanguage($languages) {
		if (!is_array($languages)) {
			$this->languagesSelected['type'] = 'single';
			$languages = array($languages);
		} else {
			$this->languagesSelected['type'] = 'multiple';
		}
		
		$this->languagesSelected['values'] = array();
		foreach ($languages as $language) {
			if (!is_numeric($language)) {
				$language = $this->convertLanguageIso($language);
			}
			
			$this->languagesSelected['values'][$language] = $this->languageIdToCode($language);
		}
		
		return $this;
	}
	
	/**
	 * Converts a multi-language property to a single language property if requested.
	 *
	 * @param array $item
	 *    The property as array
	 * @return string
	 *    The property as string
	 */
	protected function processLanguageSpecificsSingle($item) {
		if (($this->languagesSelected['type'] != 'single') || !is_array($item)) {
			return $item;
		}
		return array_pop($item);
	}
	
	/**
	 * Converts a multiple multi-language properties to a single language properties if requested.
	 *
	 * @param array $items
	 *    The items with multi-language properties as arrays
	 * @return array
	 *    The items with a single language property as string
	 */
	protected function processLanguageSpecificsMulti($items) {
		if ($this->languagesSelected['type'] != 'single') {
			return $items;
		}
		foreach ($items as &$item) {
			if (!is_array($item)) {
				continue;
			}
			$item = array_pop($item);
		}
		return $items;
	}
	
	/**
	 * Populates the internal cache using a SQL query.
	 *
	 * @param string $cacheName
	 *    The name of the cache
	 * @param string $query
	 *    A database query that selects the columns `Key`, `Value` and `LanguageId` (optional).
	 *    If the table does not have these exact fiels use an alias for the columns.
	 *
	 * @return void
	 */
	protected function cachePopulate($cacheName, $query) {
		if (!isset($this->cache[$cacheName])) {
			$this->cache[$cacheName] = array();
		}
		$data = MagnaDB::gi()->fetchArray($query);
		if (empty($data)) {
			return;
		}
		$multiLang = isset($data[0]['LanguageId']);
		
		foreach ($data as $row) {
			if ($multiLang) {
				$this->cache[$cacheName][$row['Key']][$row['LanguageId']] = $row['Value'];
			} else {
				$this->cache[$cacheName][$row['Key']] = $row['Value'];
			}
		}
	}
	
	/**
	 * Checks if a cache block or a cached value exists.
	 *
	 * @param string $cacheName
	 *    The name of the cache
	 * @param string|bool $key
	 *    If set to false this method returns true if the cache block exists.
	 *    otherwise it checks for the key in the block.
	 *
	 * @return bool
	 */
	 protected function cacheKeyExists($cacheName, $key = false) {
		return $key === false
			? isset($this->cache[$cacheName])
			: isset($this->cache[$cacheName][$key]);
	}
	
	/**
	 * Returns the cached value for a cache block and key.
	 *
	 * @param string $cacheName
	 *    The name of the cache
	 * @param string $key
	 * @param mixed $default
	 *    A default in case the cache entry doesn't exist.
	 *
	 * @return mixed
	 */
	protected function cacheGetValue($cacheName, $key, $default = false) {
		return $this->cacheKeyExists($cacheName, $key)
			? $this->cache[$cacheName][$key]
			: $default;
	}
	
	/**
	 * Filters what ever has been returned from self::cacheGetValue() based
	 * on the requested language.
	 *
	 * @param array $data
	 *    The data that will be filtered. Keys are the language ids, values are their translations.
	 *
	 * @return array
	 *    The same as the parameter, except that the keys are now language codes and only those are
	 *    included that have been requested.
	 */
	protected function cacheFilterLanguage($data) {
		$r = array();
		foreach ($this->languagesSelected['values'] as $langId => $langCode) {
			$r[$langCode] = is_array($data) && isset($data[$langId])
				? $data[$langId]
				: '';
		}
		$r = $this->processLanguageSpecificsSingle($r);
		return $r;
	}
	
	/**
	 * Validates a price config array.
	 * 
	 * @param array $pConfig
	 * @return bool
	 *    true if valid, false otherwise.
	 */
	protected static function isValidPriceConfig($pConfig) {
		return is_array($pConfig)
			&& isset($pConfig['AddKind']) && isset($pConfig['Factor'])
			&& isset($pConfig['Signal']) && isset($pConfig['Group'])
			&& isset($pConfig['UseSpecialOffer']);
	}
	
	/**
	 * Returns a default price config that simply uses the normal 
	 * shop price.
	 *
	 * @return array
	 *    A simple price config array.
	 */
	protected function getDefaultPriceConfig() {
		return array (
			'AddKind' => 'percent',
			'Factor' => 0,
			'Signal' => '',
			'Group' => '',
			'UseSpecialOffer' => false,
			'Currency' => DEFAULT_CURRENCY,
			'ConvertCurrency' => false,
		);
	}
	
	/**
	 * Itereates $this->priceconfig[values] and updates
	 * the currency conversion rate if requested.
	 */
	protected function currencySetup() {
		$updated = array();
		foreach ($this->priceConfig['values'] as $name => $config) {
			$this->simpleprice->setCurrency($config['Currency']);
			if ($config['ConvertCurrency']
				// no update if this is the shop currency
				&& ($config['Currency'] != DEFAULT_CURRENCY)
				// already updated
				&& !in_array($config['Currency'], $updated)
			) {
				$updated[] = $config['Currency'];
				$success = false;
				$this->simpleprice->updateCurrencyByService($success);
				// @todo: handle case $success == false
			}
		}
	}
	
	/**
	 * Sets the price config that will be used to calculate prices.
	 *
	 * @param array $priceConfig
	 *    The price config, @see getDefaultPriceConfig() for the required keys.
	 *
	 * @return $this
	 */
	public function setPriceConfig($priceConfig) {
		$defaultConfig = $this->getDefaultPriceConfig();
		if (
			is_array($priceConfig)
			&& !self::isValidPriceConfig($priceConfig)
			&& is_array(current($priceConfig))
			&& self::isValidPriceConfig(current($priceConfig))
		) {
			$this->priceConfig['type'] = 'multiple';
			foreach ($priceConfig as $name => $config) {
				if (!self::isValidPriceConfig($config)) {
					unset($priceConfig[$name]);
				}
			}
		} else {
			if (!self::isValidPriceConfig($priceConfig)) {
				$priceConfig = $defaultConfig;
			}
			$this->priceConfig['type'] = 'single';
			$priceConfig = array('single' => $priceConfig);
		}
		foreach ($priceConfig as $name => $config) {
			$priceConfig[$name] = array_merge($defaultConfig, $config);
		}
		
		$this->priceConfig['values'] = $priceConfig;
		$this->currencySetup();
		return $this;
	}
	
	/**
	 * Validates a quantiy config array.
	 * 
	 * @param array $qConfig
	 * @return bool
	 *    true if valid, false otherwise.
	 */
	protected static function isValidQuantityConfig($qConfig) {
		return is_array($qConfig)
			&& isset($qConfig['Type']) && isset($qConfig['Value']);
	}
	
	/**
	 * Returns a default quantity config that simply uses the normal 
	 * shop quantity.
	 *
	 * @return array
	 *    A simple quantity config array.
	 */
	protected function getDefaultQuantityConfig() {
		return array (
			'Type' => 'stocksub',
			'Value' => 0, 
			'MaxQuantity' => 0,
		);
	}
	
	/**
	 * Sets the quantity config that will be used to calculate quantities.
	 *
	 * @param array $quantityConfig
	 *    The quantity config, @see getDefaultQuantityConfig for the required keys.
	 *
	 * @return $this
	 */
	public function setQuantityConfig($quantityConfig) {
		if (!self::isValidQuantityConfig($quantityConfig)) {
			$quantityConfig = $this->getDefaultQuantityConfig();
		}
		// set optional values
		if (!isset($quantityConfig['MaxQuantity'])) {
			$quantityConfig['MaxQuantity'] = 0;
		}
		$this->quantityConfig = $quantityConfig;
		return $this;
	}
	
	/**
	 * Sets a db matching to extend the product with additional information.
	 * 
	 * @param string $for
	 *    The db matching category
	 * @param array $matchingConfig
	 *    The matching config. Required fields are Table, Column and Alias.
	 *
	 * @return $this
	 */
	public function setDbMatching($for, $matchingConfig) {
		#if (!array_key_exists($for, $this->dbMatchings)) {
		#	return $this;
		#}
		if (!isset($matchingConfig['Table']) || empty($matchingConfig['Table'])
			|| !isset($matchingConfig['Column']) || empty($matchingConfig['Column'])
			|| !isset($matchingConfig['Alias']) // may be empty!
		) {
			return $this;
		}
		$this->dbMatchings[$for] = $matchingConfig;
		return $this;
	}
	
	/**
	 * Sets whether the product loading functions return products with multiple variation
	 * dimensions or just one dimension.
	 *
	 * @param bool $bl
	 *    true: use multi-dimensional variations; false: use only one dimension
	 *
	 * @return $this
	 */
	public function useMultiDimensionalVariations($bl) {
		$this->blUseMultiDimensionalVariations = $bl;
		return $this;
	}
	
	/**
	 * Sets the blacklist with variation dimension that shall not be used when fetching
	 * or calculating variations.
	 *
	 * @param array $blacklist
	 *    A list of unallowed variation dimensions
	 *
	 * @return $this
	 */
	public function setVariationDimensionBlacklist($blacklist) {
		if (empty($blacklist)) {
			$whitelist = array();
		} else {
			$whitelist = MagnaDB::gi()->fetchArray('
				SELECT DISTINCT products_options_id
				  FROM '.TABLE_PRODUCTS_OPTIONS.'
				 WHERE products_options_id NOT IN ("'.implode('", "', $blacklist).'")
			', true);
			if (!is_array($whitelist)) {
				$whitelist = array();
			}
		}
		$this->setVariationDimensionWhitelist($whitelist);
		return $this;
	}
	
	/**
	 * Sets the whitelist with the only allowed variation dimensions that shall be used
	 * when fetching or calculating variations.
	 *
	 * @param array $whitelist
	 *    A list of the only allowd variation dimensions
	 *
	 * @return $this
	 */
	public function setVariationDimensionWhitelist($whitelist) {
		if (is_array($whitelist)) {
			$this->allowedVariationDimensions = $whitelist;
		} else {
			$this->allowedVariationDimensions = array();
		}
		// Tell the variations calculator about the allowed list.
		$this->variationCalculator->setOptionsWhitelist($this->allowedVariationDimensions);
		return $this;
	}
	
	/**
	 * Resets the list of allowed variation dimensions that shall be used
	 * when fetching or calculating variations.
	 *
	 * @return $this
	 */
	public function resetVariationDimensionLists() {
		$this->setVariationDimensionWhitelist(array());
		return $this;
	}
	
	/**
	 * Resets the settings excpet for the language.
	 *
	 * @return $this
	 */
	public function reset() {
		$this->setPriceConfig(false);
		$this->setQuantityConfig(false);
		
		foreach ($this->dbMatchings as $for => $matchingConfig) {
			$this->dbMatchings[$for] = array();
		}
		
		$this->useMultiDimensionalVariations(true);
		
		return $this;
	}
	
	/**
	 * Calculates the final price of the product based on the price config.
	 * This algorithm is basically a copy of SimplePrice::finalizePrice()
	 * with a few minor changes.
	 *
	 * @param float $basePrice
	 *    The netto price
	 * @param float $tax
	 *    The tax as percent value
	 * @param array $config
	 *    The config that will be used to calculate a price.
	 *
	 * @return float
	 *    The final price
	 */
	protected function calcPrice($basePrice, $tax, $config) {
		$this->simpleprice->setPrice($basePrice)->setCurrency($config['Currency']); // add the variation price
		
		if (!isset($config['IncludeTax']) || ($config['IncludeTax'] !== false)) {
			$this->simpleprice->addTax($tax);
		}
		
		$this->simpleprice->calculateCurr();

		switch ($config['AddKind']) {
			case 'percent': {
				$this->simpleprice->addTax((float)$config['Factor']);
				break;
			}
			case 'addition': {
				$this->simpleprice->addLump((float)$config['Factor']);
				break;
			}
			case 'constant': {
				$this->simpleprice->setPrice((float)$config['Factor']);
				break;
			}
		}
		return $this->simpleprice->roundPrice()
			->makeSignalPrice($config['Signal'])
			->getPrice()
		;
	}
	
	/**
	 * Calculates the price for a variation item.
	 *
	 * @param array $variation
	 *    The entire variation item as reference.
	 * @param float $surcharge
	 *    The surcharge.
	 * @param array $parentPrices
	 *    An array containing all the parent prices.
	 * @param float $tax
	 *    The tax for the price.
	 *
	 * @return void
	 */
	protected function calcPriceVariation(&$variation, $surcharge, $parentPrices, $tax) {
		$variation['Price'] = array();
		$variation['PriceReduced'] = array();
		foreach ($this->priceConfig['values'] as $name => $config) {
            $variation['Price'][$name] = $this->calcPrice($parentPrices[$name]['Price'] + $surcharge, $tax, $config);
            // null means no special price
            if ($parentPrices[$name]['Reduced'] !== null) {
                $variation['PriceReduced'][$name] = $this->calcPrice($parentPrices[$name]['Reduced'] + $surcharge, $tax, $config);
                if (($variation['PriceReduced'][$name] > $variation['Price'][$name]) || ($variation['PriceReduced'][$name] <= 0)) {
                    unset($variation['PriceReduced'][$name]);
                }
            }
		}
		if ($this->priceConfig['type'] == 'single') {
			$variation['Price'] = current($variation['Price']);
			$variation['PriceReduced'] = current($variation['PriceReduced']);
		}
		if (empty($variation['PriceReduced'])) {
			unset($variation['PriceReduced']);
		}
	}
	
	/**
	 * Calculates the final quantity of the product based on the quantity config.
	 *
	 * @param int $dbQuantity
	 *
	 * @return int
	 *    The final Quantity
	 */
	protected function calcQuantity($dbQuantity) {
		$dbQuantity = (int)$dbQuantity;
		switch ($this->quantityConfig['Type']) {
			case 'stocksub': {
				$dbQuantity -= $this->quantityConfig['Value'];
				break;
			}
			case 'lump': {
				$dbQuantity = $this->quantityConfig['Value'];
				break;
			}
		}
		
		if (($this->quantityConfig['MaxQuantity'] > 0) && ($this->quantityConfig['Type'] != 'lump')) {
			$dbQuantity = min($dbQuantity, $this->quantityConfig['MaxQuantity']);
		}
		$dbQuantity = max($dbQuantity, 0); // make sure it is always >= 0
		return $dbQuantity;
	}
	
	/**
	 * Translates magnalister_variations.variation_attributes array to
	 * language specific strings.
	 *
	 * @param array $productOptions
	 *    A list of arrays with the keys 'Group' and 'Value'
	 *
	 * @return array
	 *    A list of arrays with the keys 'Name' and 'Value'
	 */
	protected function translateProductsOptions($productOptions) {
		if (empty($productOptions)) {
			return array();
		}
		
		// Check if the cache covers the ids.
		$groupsToLoad = array();
		$valuesToLoad = array();
		foreach ($productOptions as $set) {
			if (!$this->cacheKeyExists('ProductOptionGroups', $set['Group'])) {
				$groupsToLoad[] = $set['Group'];
			}
			if (!$this->cacheKeyExists('ProductOptionValues', $set['Value'])) {
				$valuesToLoad[] = $set['Value'];
			}
		}
		
		// Populate the cache
		if (!empty($groupsToLoad)) {
			$this->cachePopulate('ProductOptionGroups', '
				SELECT products_options_id AS `Key`, language_id AS LanguageId, products_options_name AS Value
				  FROM '.TABLE_PRODUCTS_OPTIONS.'
				 WHERE products_options_id IN ("'.implode('", "', $groupsToLoad).'")
			');
		}
		if (!empty($valuesToLoad)) {
			$this->cachePopulate('ProductOptionValues', '
				SELECT products_options_values_id AS `Key`, language_id AS LanguageId, products_options_values_name AS Value
				  FROM '.TABLE_PRODUCTS_OPTIONS_VALUES.'
				 WHERE products_options_values_id IN ("'.implode('", "', $valuesToLoad).'")
			');
		}
		
		// Translate using the cache.
		$new = array();
		foreach ($productOptions as $set) {
			$new[] = array(
				'NameId' => $set['Group'],
				'Name' => $this->cacheFilterLanguage(
					$this->cacheGetValue('ProductOptionGroups', $set['Group'], $set['Group'])
				),
				'ValueId' => $set['Value'],
				'Value' => $this->cacheFilterLanguage(
					$this->cacheGetValue('ProductOptionValues', $set['Value'], $set['Value'])
				),
			);
		}
		return $new;
	}
	
	/**
	 * Loads the multi-dimensional variations to a product.
	 *
	 * @param array $parent
	 *    The parent product
	 * @param bool $onlyOffer
	 *    If this is set to true only the offer data will be included
	 *    along with everything needed to "identify" the variation.
	 * @param bool $purgeVariations
	 *    If this is set to true the multi-dimensional variations will be recalculated
	 *
	 * @return array
	 *    List of variations or empty if no variations exist.
	 */
	protected function fetchMultiVariations(&$parent, $onlyOffer, $purgeVariations) {
		if ($purgeVariations) {
			// @todo: Implement some smart logic to execute the following line when the data is possibly out of sync.
			$this->variationCalculator->purgeProductVariations($parent['ProductId']);
		}
		
		$var = $this->variationCalculator->getVariationsByPIDFromDB($parent['ProductId']);
		if (empty($var)) {
			return array();
		}
		
		$variations = array();
		$quantity = 0;
		
		foreach ($var as $vi) {
			$v = array (
				'VariationId' => $vi['variation_id'],
				'MarketplaceId' => $vi['marketplace_id'],
				'MarketplaceSku' => $vi['marketplace_sku'],
				'Variation' => $this->translateProductsOptions($vi['variation_attributes']),
				'Price' => $vi['variation_price'],
				'Surcharge' => $vi['variation_price'],
				'Quantity' => $vi['variation_quantity'],
				'Status' => $vi['variation_status'],
				'ShippingTimeId' => $vi['variation_shipping_time'],
				'ShippingTime' => $this->getShippingTimeStringById($vi['variation_shipping_time']),
			);
			
			$this->calcPriceVariation($v, $v['Price'], $parent['Prices'], $parent['TaxPercent']);
			
			$v['Quantity'] = $this->calcQuantity($v['Quantity']);
			$quantity += $v['Quantity'];
			
			if (!$onlyOffer) {
				$v['EAN'] = $vi['variation_ean'];
				
				if ((float)$vi['variation_weight'] != 0) {
					$weight = (float)$vi['variation_weight'];
					$bweight = isset($parent['Weight']['Value']) ? $parent['Weight']['Value'] : 0.0;
					$v['Weight'] = array();
					if (($bweight + $weight) > 0) {
						$v['Weight']['Unit'] = isset($parent['Weight']['Unit']) ? $parent['Weight']['Unit'] : 'kg';
						$v['Weight']['Value'] = ($bweight + $weight);
					}
				}
				if (!empty($vi['variation_unit_of_measure']) && ((float)$vi['variation_volume'] > 0)) {
					$v['BasePrice'] = array (
						'Unit' => $this->getVpeUnitById($vi['variation_unit_of_measure']),
						'Value' => $vi['variation_volume']
					);
				}
			}
			$variations[] = $v;
		}
		
		$parent['QuantityTotal'] = $quantity;
		
		return $variations;
	}
	
	/**
	 * Loads the single-dimensional variations to a product.
	 *
	 * @todo: In case products_attributes.attributes_stock does not exist fall back to products.quantity.
	 *        Use $this->existingColumns['pa.attributes_stock'] to check.
	 *
	 * @param array $parent
	 *    The parent product
	 * @param bool $onlyOffer
	 *    If this is set to true only the offer data will be included
	 *    along with everything needed to "identify" the variation.
	 *
	 * @return array
	 *    List of variations or empty if no variations exist.
	 */
	protected function fetchSingleVariations(&$parent, $onlyOffer) {
		// This is limited to one dimension.
		// Start with guessing the "right" one, aka using the one that has the most variations.
		$pVID = MagnaDB::gi()->fetchArray(eecho('
		    SELECT pa.options_id, COUNT(pa.options_id) AS rate
		      FROM '.TABLE_PRODUCTS_ATTRIBUTES.' pa
		     WHERE pa.products_id = "'.$parent['ProductId'].'"
		           '.(empty($this->allowedVariationDimensions)
		               ? ''
		               : 'AND pa.options_id IN ("'.implode('", "', $this->allowedVariationDimensions).'")'
		           ).
		           $this->options['sExtendFetchSingeVariationsQueryWhere'].'
		  GROUP BY pa.options_id
		  ORDER BY rate DESC, pa.options_id ASC
		', false));
		
		if (empty($pVID)) {
			return false;
		}
		
		$attributes = array();
		if ($this->optionsTmp['sameVariationsToAttributes'] && (count($pVID) > 1)) {
			foreach ($pVID as $pVSet) {
				if ($pVSet['rate'] == 1) {
					$attributes[] = $pVSet['options_id'];
				}
			}
			
			if (!empty($attributes)) {
				$customAttributes = MagnaDB::gi()->fetcharray(eecho("
				    SELECT DISTINCT pa.options_id AS `Group`, options_values_id AS `Value`
				      FROM ".TABLE_PRODUCTS_ATTRIBUTES." pa
				     WHERE pa.products_id = '".$parent['ProductId']."'
				           AND pa.options_id IN ('".implode("','", $attributes)."')
				", false));
				$parent['Attributes'] = $this->translateProductsOptions($customAttributes);
			}
		}
		
		// Select the variation with the highest number of values.
		$pVID = $pVID[0];
		
		$selectFields = ($onlyOffer ? $this->attributesOfferSelectFields : $this->attributesMainSelectFields);
		if (!$this->existingColumns['pa.attributes_stock']) {
			$selectFields = str_replace('pa.attributes_stock', '\''.$parent['Quantity'].'\'', $selectFields);
		}
		$variations = MagnaDB::gi()->fetchArray(eecho('
		    SELECT '.$selectFields.'
		      FROM '.TABLE_PRODUCTS_ATTRIBUTES.' pa
		     WHERE pa.products_id = '.$parent['ProductId'].'
		           AND pa.options_id = '.$pVID['options_id'].'
		           -- '.($this->existingColumns['pa.attributes_stock'] ? 'AND pa.attributes_stock IS NOT NULL' : '').'
		  '.($this->existingColumns['pa.sortorder'] ? 'ORDER BY pa.sortorder' : '').'
		', false));
		
		if ($variations == false) {
			return array();
		}
		
		$quantity = 0;
		
		foreach ($variations as &$v) {
			$v['MarketplaceId'] = 'MLV'.$parent['ProductId'].'_'.$v['VariationNameId'].'_'.$v['VariationValueId'];
			if (empty($v['VariationModel'])) {
				if (empty($parent['ProductsModel'])) {
					$v['MarketplaceSku'] = $v['MarketplaceId'];
				} else {
					$v['MarketplaceSku'] = $parent['ProductsModel'].'_MLV'.$v['VariationNameId'].'_'.$v['VariationValueId'];
				}
			} else {
				$v['MarketplaceSku'] = $v['VariationModel'];
			}
			unset($v['VariationModel']);
			
			$v['Variation'] = $this->translateProductsOptions(array (array (
				'Group' => $v['VariationNameId'],
				'Value' => $v['VariationValueId'],
			)));
			
			unset($v['VariationNameId']);
			unset($v['VariationValueId']);

			if ($v['PricePrefix'] == '=') {
				$v['Surcharge'] = $v['Price'];
			    $aPrices = $parent['Prices'];
				foreach ($aPrices as &$aPrice) {
					$aPrice['Price'] = 0.00;
					$aPrice['Reduced'] = null;
				}
				$this->calcPriceVariation($v, $v['Price'], $aPrices, $parent['TaxPercent']);
            } elseif ($v['PricePrefix'] == '%') {
                $v['Surcharge'] = (float)($parent['Price'] * ($v['Price'] / 100));
                $this->calcPriceVariation($v, $v['Surcharge'], $parent['Prices'], $parent['TaxPercent']);
			} else {
				$vPriceSurcharge = $v['Price'] * (($v['PricePrefix'] == '+') ? 1 : -1);
                $v['Surcharge'] = $vPriceSurcharge;
				$this->calcPriceVariation($v, $vPriceSurcharge, $parent['Prices'], $parent['TaxPercent']);
			}
			unset($v['PricePrefix']);
			
			$v['Quantity'] = $this->calcQuantity($v['Quantity']);
			$quantity += $v['Quantity'];
			
			if (isset($v['WeightPrefix']) && !empty($v['WeightPrefix'])) {
				if ($v['WeightPrefix'] == '=') {
					$weight = (float)$v['Weight'];
					$bweight = 0.0;
				} else {
					$weight = (float)$v['Weight'] * (($v['WeightPrefix'] == '+') ? 1 : -1);
					$bweight = isset($parent['Weight']['Value']) ? $parent['Weight']['Value'] : 0.0;
				}
				$v['Weight'] = array();
				if (($bweight + $weight) > 0) {
					$v['Weight']['Unit'] = isset($parent['Weight']['Unit']) ? $parent['Weight']['Unit'] : 'kg';
					$v['Weight']['Value'] = ($bweight + $weight);
				}
			}
			unset($v['WeightPrefix']);

			if (   isset($v['VpeUnit'])
				&& isset($v['VpeValue'])
				&& ($v['VpeUnit'] != 0)
				&& ((float)$v['VpeValue'] > 0)
			) {
				$v['BasePrice'] = array (
					'Unit' => $this->getVpeUnitById($v['VpeUnit']),
					'Value' => $v['VpeValue']
				);
			}
			unset($v['VpeUnit']);
			unset($v['VpeValue']);
		}
		
		$parent['QuantityTotal'] = $quantity;
		
		return $variations;
	}
	
	/**
	 * {Gambio GX >= 2.1 only}
	 * Gets the variation information for a property combination.
	 * 
	 * @param int $productsPropertiesCombisId
	 *    The id of the property combination
	 *
	 * @return array
	 *    Vector<array> of variation properties. Each element has the keys
	 *    NameId, Name, ValueId, Value.
	 */
	protected function translateProductsProperties($productsPropertiesCombisId) {
		$index = MagnaDB::gi()->fetchArray('
			SELECT DISTINCT properties_id, properties_values_id
			  FROM '.'products_properties_index'.'
			 WHERE products_properties_combis_id = '.$productsPropertiesCombisId.'
		');
		
		// Check if the cache covers the ids.
		$groupsToLoad = array();
		$valuesToLoad = array();
		
		$variation = array();
		foreach ($index as $set) {
			$variation[] = array (
				'NameId' => $set['properties_id'],
				'Name' => '',
				'ValueId' => $set['properties_values_id'],
				'Value' => '',
			);
			
			if (!$this->cacheKeyExists('ProductOptionGroups', $set['properties_id'])) {
				$groupsToLoad[] = $set['properties_id'];
			}
			if (!$this->cacheKeyExists('ProductOptionValues', $set['properties_values_id'])) {
				$valuesToLoad[] = $set['properties_values_id'];
			}
		}
		
		// Populate the cache
		if (!empty($groupsToLoad)) {
			$this->cachePopulate('ProductOptionGroups', '
				SELECT properties_id AS `Key`, language_id AS LanguageId, properties_name AS Value
				  FROM '.'properties_description'.'
				 WHERE properties_id IN ("'.implode('", "', $groupsToLoad).'")
			');
		}
		if (!empty($valuesToLoad)) {
			$this->cachePopulate('ProductOptionValues', '
				SELECT properties_values_id AS `Key`, language_id AS LanguageId, values_name AS Value
				  FROM '.'properties_values_description'.'
				 WHERE properties_values_id IN ("'.implode('", "', $valuesToLoad).'")
			');
		}
		
		// Translate using the cache.
		foreach ($variation as &$set) {
			$set['Name'] = $this->cacheFilterLanguage(
				$this->cacheGetValue('ProductOptionGroups', $set['NameId'], $set['NameId'])
			);
			$set['Value'] = $this->cacheFilterLanguage(
				$this->cacheGetValue('ProductOptionValues', $set['ValueId'], $set['ValueId'])
			);
		}
		
		return $variation;
	}
	
	/**
	 * {Gambio GX >= 2.1 only}
	 * Uses gambios multi-dimensional variation management tables to load
	 * the multi-dimensional variations for the specified master product.
	 *
	 * @param array $parent
	 *    The parent product
	 * @param bool $onlyOffer
	 *    If this is set to true only the offer data will be included
	 *    along with everything needed to "identify" the variation.
	 *
	 * @return array
	 *    Vector<array> of variations or empty if no variations exist.
	 */
	protected function fetchMultiVariationProperties(&$parent, $onlyOffer) {
		$combis = MagnaDB::gi()->fetchArray('
			  SELECT *
			    FROM '.'products_properties_combis'.'
			   WHERE products_id = "'.$parent['ProductId'].'"
			ORDER BY sort_order ASC
		');
		if (empty($combis)) {
			return array();
		}
		
		$quantity = 0;
		
		$attrs = array();
		foreach ($combis as $combi) {
			$v = array (
				'VariationId' => $combi['products_properties_combis_id'],
				'MarketplaceId' => 'ML'.$parent['ProductId'],
				'MarketplaceSku' => $parent['ProductsModel'].'-'.$combi['combi_model'], // '-' instead of '_', because Gambio expects '-' in its orders module.
				'Variation' => $this->translateProductsProperties($combi['products_properties_combis_id']),
				'Price' => $combi['combi_price'],
				'Surcharge' => $combi['combi_price'],
				'PriceReduced' => 0,
				'Quantity' => $combi['combi_quantity'],
				'Status' => true, // I can't find a status flag.
				'ShippingTimeId' => $combi['combi_shipping_status_id'],
				'ShippingTime' => $this->getShippingTimeStringById($combi['combi_shipping_status_id']),
			);
			
			foreach ($v['Variation'] as $varDef) {
				$v['MarketplaceId'] .= '-'.$varDef['NameId'].'.'.$varDef['ValueId'];
			}
			
			$this->calcPriceVariation($v, $v['Price'], $parent['Prices'], $parent['TaxPercent']);
			
			$v['Quantity'] = $this->calcQuantity($v['Quantity']);
			$quantity += $v['Quantity'];
			
			if (!$onlyOffer) {
				$v['EAN'] = $combi['combi_ean'];
				
				if ((float)$combi['combi_weight'] > 0) {
					$weight = (float)$combi['combi_weight'];
					$bweight = isset($parent['Weight']['Value']) ? $parent['Weight']['Value'] : 0.0;
					$v['Weight'] = array();
					if (($bweight + $weight) > 0) {
						$v['Weight']['Unit'] = isset($parent['Weight']['Unit']) ? $parent['Weight']['Unit'] : 'kg';
						$v['Weight']['Value'] = ($bweight + $weight);
					}
				}
				if (!empty($combi['products_vpe_id']) && ((float)$combi['vpe_value'] > 0)) {
					$v['BasePrice'] = array (
						'Unit' => $this->getVpeUnitById($combi['products_vpe_id']),
						'Value' => $combi['vpe_value']
					);
				}
			}
			
			$attrs[] = $v;
		}
		
		$parent['QuantityTotal'] = $quantity;
		
		return $attrs;
	}
	
	/**
	 * Loads the variations to a product.
	 *
	 * @param array $parent
	 *    The parent product
	 * @param bool $onlyOffer
	 *    If this is set to true only the offer data will be included
	 *    along with everything needed to "identify" the variation.
	 *
	 * @return array
	 *    List of variations or empty if no variations exist.
	 */
	protected function fetchVariations(&$parent, $onlyOffer) {
		$variations = array();
		if (!$this->optionsTmp['includeVariations']) {
			$variations = array();
		} else if ($this->optionsTmp['useGambioProperties']) {
			$variations = $this->fetchMultiVariationProperties($parent, $onlyOffer);
		} else if ($this->blUseMultiDimensionalVariations) {
			$variations = $this->fetchMultiVariations($parent, $onlyOffer, $this->optionsTmp['purgeVariations']);
		} else {
			$variations = $this->fetchSingleVariations($parent, $onlyOffer);
		}
		return $variations;
	}
	
	/**
	 * Loads the variations images to a product.
	 *
	 * @param array $parent
	 *    The parent product
	 * @param bool $onlyOffer
	 *    If this is set to true only the offer data will be included
	 *    along with everything needed to "identify" the variation.
	 *
	 * @return array
	 *    List of variations or empty if no variations exist.
	 */
	protected function fetchVariationImages(&$parent) {
		$attrs = array();
		if ($this->optionsTmp['useGambioProperties']) {
			$combis = MagnaDB::gi()->fetchArray('
			  SELECT *
			    FROM '.'products_properties_combis'.'
			   WHERE products_id = "'.$parent['ProductId'].'"
			ORDER BY sort_order ASC
			');
			if (empty($combis)) {
				return array();
			}
			foreach ($combis as $combi) {
				$v = array (
					'VariationId' => $combi['products_properties_combis_id'],
					'Image' => (array_key_exists('combi_image', $combi)) ? $combi['combi_image'] : '',
					'Variation' => $this->translateProductsProperties($combi['products_properties_combis_id']),
				);

				// Since Gambio 4.1 Multiple Images for one Variation are supported
                if (!MagnaDB::gi()->columnExistsInTable('combi_image', 'products_properties_combis')) {
                    // product_image_list_combi -> products_properties_combis_id we get product_image_list_id
                    // products_properties_combis -> products_properties_combis_id
                    $imageListId = MagnaDB::gi()->fetchOne("
                        SELECT product_image_list_id 
                          FROM product_image_list_combi
                         WHERE products_properties_combis_id = ".(int)MagnaDB::gi()->escape($combi['products_properties_combis_id'])."
                    ");
                    $variationImages = MagnaDB::gi()->fetchArray("
                        SELECT * 
                          FROM product_image_list_image
                         WHERE product_image_list_id = ".(int)MagnaDB::gi()->escape($imageListId)."
                      ORDER BY product_image_list_image_sort_order ASC
                    ");

                    $firstImage = current($variationImages);
                    if (is_array($firstImage)) {
                        $v['Image'] = $firstImage['product_image_list_image_local_path'];
                    } else {
                        $v['Image'] = '';
                    }

                    $v['Images'] = array();
                    foreach ($variationImages as $variationImage) {
                        $localPath = $variationImage['product_image_list_image_local_path'];
                        // When using Gambio 4.5+ the local path has not "images/product_images/original_images/" in database anymore - If it's not there add
                        if (version_compare(ML_GAMBIO_VERSION, '4.5', '>=')
                            && strpos($localPath, 'images/product_images/original_images/') !== 0)
                        {
                            $localPath = 'images/product_images/original_images/'.$localPath;
                        }
                        $v['Images'][] = $localPath;
                    }
                }

				$attrs[] = $v;
			}
		}
		return $attrs;
	}

	/**
	 * Loads data based of a config db matching.
	 * 
	 * @param array &$product
	 *    The product
	 * @param string $matchingName
	 *    The name of the matching
	 * 
	 * @return void
	 */
	protected function getDataByMatching(&$product, $matchingName) {
		if (empty($this->dbMatchings[$matchingName])) {
			return;
		}
		if (empty($this->dbMatchings[$matchingName]['Alias'])) {
			$this->dbMatchings[$matchingName]['Alias'] = 'products_id';
		}
		$product[$matchingName] = MagnaDB::gi()->fetchOne('
			SELECT `' . $this->dbMatchings[$matchingName]['Column'] . '`
			  FROM `' . $this->dbMatchings[$matchingName]['Table'] . '`
			 WHERE `' . $this->dbMatchings[$matchingName]['Alias'] . '`="' . $product['ProductId'] . '"
			 LIMIT 1
		');
	}

	/**
	 * getDataByMatching for all matchings set
	 * 
	 * @param array &$product
	 *    The product
	 * 
	 * @return void
	 */
	protected function getAllDataByMatching(&$product) {
		foreach ($this->dbMatchings as $matchingName => $matching) {
			if (empty($matching)) {
				continue;
			}
			$this->getDataByMatching($product, $matchingName);
		}
	}
	
	/**
	 * Loads the manufacturer part number based of a config db matching.
	 * 
	 * @param array &$product
	 *    The product
	 * 
	 * @return void
	 */
	protected function getManufacturerPartNumber(&$product) {
	    // if data is prefilled return
	    if (!empty($product['ManufacturerPartNumber'])) {
	        return;
        }

	    // if matching fills the data return
		$this->getDataByMatching($product, 'ManufacturerPartNumber');
        if (!empty($product['ManufacturerPartNumber'])) {
            return;
        }

        // modified 2.0 try to get manufacturer from database
        if (MagnaDB::gi()->columnExistsInTable('products_manufacturers_model', TABLE_PRODUCTS)) {
            $product['ManufacturerPartNumber'] = MagnaDB::gi()->fetchOne("
                SELECT `products_manufacturers_model`
                  FROM `".TABLE_PRODUCTS."`
                 WHERE `products_id` = '".$product['ProductId']."'
                LIMIT 1 
            ");

        // modified 1.0 try to get manufacturer from database
        } elseif (MagnaDB::gi()->columnExistsInTable('products_manufacturers_sku', TABLE_PRODUCTS)) {
            $product['ManufacturerPartNumber'] = MagnaDB::gi()->fetchOne("
                SELECT `products_manufacturers_sku`
                  FROM `".TABLE_PRODUCTS."`
                 WHERE `products_id` = '".$product['ProductId']."'
                LIMIT 1 
            ");

        // Gambio 3+ try to get manufacturer from database
        } elseif (MagnaDB::gi()->tableExists('products_item_codes') && MagnaDB::gi()->columnExistsInTable('code_mpn', 'products_item_codes')) {
            $product['ManufacturerPartNumber'] = MagnaDB::gi()->fetchOne("
                SELECT `code_mpn`
                  FROM `products_item_codes`
                 WHERE `products_id` = '".$product['ProductId']."'
                LIMIT 1 
            ");
        }
	}
	
	/**
	 * Fetches the additional images for a product id. This does not include the main image of the product.
	 * 
	 * @param int $pID
	 *    The products id
	 * @return array
	 *    The image list
	 */
	public function getAdditionalImagesByProductsId($pId) {
		/* {Hook} "MLProduct_getProductImagesByID": Enables you to fetch additional product images in a different
		   method than using the products_images table.<br>
		   Variables that can be used: <ul>
		       <li>$pId: The ID of the product (Table <code>products.products_id</code>).</li>
		       <li>$images: Array that this function will return</li>
		   </ul>
		   Set $images array in this format:<br>
		   <pre>
$images = array (
	0 => 'image1.jpg',
	1 => 'image2.jpg',
	2 => ...
);
</pre>
		 */
		if (($hp = magnaContribVerify('MLProduct_getProductImagesByID', 1)) !== false) {
			$images = array();
			require($hp);
			
			if (is_array($images) && isset($images[0])) {
				return $images;
			}
			
			return array();
			
		} else if (defined('TABLE_PRODUCTS_IMAGES') && MagnaDB::gi()->tableExists(TABLE_PRODUCTS_IMAGES)) {
			# Tabelle nur bei xtCommerce- und Gambio- Shops vorhanden (nicht OsC)
			
			$cols = MagnaDB::gi()->getTableCols(TABLE_PRODUCTS_IMAGES);
			$orderBy = (in_array('image_nr', $cols)
				? 'image_nr'
				: (in_array('sort_order', $cols)
					? 'sort_order'
					: ''
				)
			);
			if (!empty($orderBy)) {
				$orderBy = 'ORDER BY '.$orderBy;
			}
			$colname = (in_array('image', $cols)
				? 'image'
				: (in_array('image_name', $cols)
					? 'image_name'
					: ''
				)
			);
			
			$return = array();
			if (!empty($colname)) {
				$return = MagnaDB::gi()->fetchArray('
				    SELECT '.$colname.'
				      FROM '.TABLE_PRODUCTS_IMAGES.'
				     WHERE products_id = "'.$pId.'"
				           AND '.$colname.' <> ""
				           AND '.$colname.' IS NOT NULL
				  '.$orderBy.'
				', true);
				if (empty($return)) {
					$return = array();
				}
			}
			return $return;
		}
		return array();
	}
	
	/**
	 * @deprecated
	 * Alias of self::getAdditionalProductImagesById()
	 * 
	 * @param int $pID
	 *    The products id
	 * @return array
	 *    The image list
	 */
	public function getProductImagesByID($pId) {
		return $this->getAdditionalImagesByProductsId($pId);
	}
	
	/**
	 * Completes the Images field and loads additional product images if there are any.
	 * 
	 * @param array &$product
	 *    The product
	 * 
	 * @return void
	 */
	protected function completeImages(&$product) {
		if (empty($product['Images'])) {
			$product['Images'] = array();
		} else {
			$product['Images'] = array($product['Images']);
		}
		
		$product['Images'] = array_merge($product['Images'], $this->getAdditionalImagesByProductsId($product['ProductId']));
	}
	
	/**
	 * Returns all images of a master product.
	 * @param int $pId
	 *    The id of the product
	 *
	 * @return array
	 *    A list of image names
	 */
	public function getAllImagesByProductsId($pId) {
		$product = MagnaDB::gi()->fetchRow('
			SELECT products_id AS ProductId, products_image AS Images 
			  FROM '.TABLE_PRODUCTS.'
			 WHERE products_id="'.$pId.'"
		');
		$this->completeImages($product);
		return $product['Images'];
	}
	
	/**
	 * Loads the shippingtime cache if it is empty and converts the
	 * shipping time id to a string if it exists.
	 *
	 * @return string
	 *    The shipping time string in human readable format or an empty string in case of a failure.
	 */
	protected function getShippingTimeStringById($id) {
		if (empty($id)) {
			return '';
		}
		// use lazy loading
		if ($this->existingTables['ShippingStatus'] && !$this->cacheKeyExists('ShippingTime')) {
			$this->cachePopulate('ShippingTime', '
				SELECT shipping_status_id AS `Key`, language_id AS LanguageId, shipping_status_name AS Value
				  FROM '.TABLE_SHIPPING_STATUS.'
			');
		}
		return $this->cacheFilterLanguage(
			$this->cacheGetValue('ShippingTime', $id, '')
		);
	}
	
	/**
	 * Loads the manufacturer cache if it is empty and converts the
	 * manufacturer id to a string if it exists.
	 *
	 * @return string
	 *    The manufactuerer string or an empty string in case of a failure.
	 */
	protected function getManufacturerNameById($id) {
		// use lazy loading
		if (!$this->cacheKeyExists('ManufacturerName')) {
			$this->cachePopulate('ManufacturerName', '
				SELECT manufacturers_id AS `Key`, manufacturers_name AS Value
				  FROM '.TABLE_MANUFACTURERS.'
			');
		}
		return $this->cacheGetValue('ManufacturerName', $id, '');
	}
	
	/**
	 * Loads the manufacturer cache if it is empty and converts the
	 * manufacturer id to a string if it exists.
	 *
	 * @return string
	 *    The manufactuerer string or an empty string in case of a failure.
	 */
	protected function getVpeUnitById($id) {
		// use lazy loading
		if (!$this->cacheKeyExists('BasePriceUnit') && MagnaDB::gi()->tableExists(TABLE_PRODUCTS_VPE)) {
			$this->cachePopulate('BasePriceUnit', '
				SELECT products_vpe_id AS `Key`, language_id AS LanguageId, products_vpe_name AS Value
				  FROM '.TABLE_PRODUCTS_VPE.'
			');
		}
		return $this->cacheFilterLanguage(
			$this->cacheGetValue('BasePriceUnit', $id, '')
		);
	}
	
	/**
	 * Completes the Vpe field and removes all Vpe helper fields.
	 * 
	 * @param array &$product
	 *    The product
	 * 
	 * @return void
	 */
	protected function completeBasePrice(&$product) {
		if ($product['VpeStatus']) {
			$product['VpeUnit'] = $this->getVpeUnitById($product['VpeUnit']);
		}
		if ($product['VpeStatus'] && !empty($product['VpeUnit']) && ((float)$product['VpeValue'] > 0)) {
			$product['BasePrice'] = array (
				'Unit' => $product['VpeUnit'],
				'Value' => $product['VpeValue'],
			);
		} else {
			$product['BasePrice'] = array ();
		}
		unset($product['VpeStatus']);
		unset($product['VpeUnit']);
		unset($product['VpeValue']);
	}
	
	/**
	 * Translates the tax class to a percent value and caches the result from SimplePrice.
	 * @return void
	 */
	protected function completeTax(&$product) {
		if (!((int)$product['TaxClass'] > 0)) {
			$product['TaxPercent'] = 0.0;
			return;
		}
		if (!isset($this->cache['Tax'][$product['TaxClass']])) {
			if (!isset($this->cache['Tax'])) {
				$this->cache['Tax'] = array();
			}
			$this->cache['Tax'][$product['TaxClass']] = SimplePrice::getTaxByClassID($product['TaxClass']);
		}
		$product['TaxPercent'] = (float)$this->cache['Tax'][$product['TaxClass']];
	}
	
	/**
	 * Prepares the price for the parent product and checks if a reduce price should be used if it exists.
	 * 
	 * @param array &$product
	 *    The product
	 * 
	 * @return void
	 */
	protected function prepareParentPrices(&$product) {
		foreach ($this->priceConfig['values'] as $name => $config) {
			$price = is_array($product['Price']) ? $product['Price'][$name] : $product['Price'];
			$product['Currency'] = is_array($product['Currency']) ? $product['Currency'] : array();
			$product['Currency'][$name] = $config['Currency'];
			$product['Prices'][$name] = array (
				'Price' => $config['Group'] > 0
					? $this->simpleprice->setCurrency($config['Currency'])->getGroupPrice($config['Group'], $product['ProductId'])
					: $price,
				'Reduced' => $config['UseSpecialOffer']
					? $this->simpleprice->setCurrency($config['Currency'])->tryGetSpecialOffer($product['ProductId'])
					: null
			);

            // if special offer is not set
            if ($product['Prices'][$name]['Reduced'] === false) {
                $product['Prices'][$name]['Reduced'] = null;
            }

			if (($hp = magnaContribVerify('CustomizePrice', 1)) !== false) {
				$product['Prices'][$name]['Price'] = $this->simpleprice->setCurrency($config['Currency'])->getCustomizedPrice($product['ProductId']);
			}

			// Make sure the group price is > 0
			if (!((float)$product['Prices'][$name]['Price'] > 0)) {
				$product['Prices'][$name]['Price'] = $price;
			}

			// Make sure the reduced price is not greater than the normal price.
			if (   $product['Prices'][$name]['Reduced'] !== null
			    && ($product['Prices'][$name]['Reduced'] > $product['Prices'][$name]['Price'])
            ) {
				$product['Prices'][$name]['Reduced'] = null;
			}
		}
	}
	
	/**
	 * Completes the parent offer. Finalizes the price and quantity.
	 * 
	 * @param array &$product
	 *    The product
	 * 
	 * @return void
	 */
	protected function completeParentOffer(&$product) {
		$product['PriceReduced'] = array();
		$product['Price'] = array();
		foreach ($this->priceConfig['values'] as $name => $config) {
			// Price foo
			$product['Price'][$name] = $this->calcPrice($product['Prices'][$name]['Price'], $product['TaxPercent'], $config);
			if ((float)$product['Prices'][$name]['Reduced'] > 0) {
				$product['PriceReduced'][$name] = $this->calcPrice($product['Prices'][$name]['Reduced'], $product['TaxPercent'], $config);
			} else if (isset($product['PriceReduced'][$name])){
				unset($product['PriceReduced'][$name]);
			}
			unset($product['Prices'][$name]);
		}
		if (empty($product['PriceReduced'])) {
			unset($product['PriceReduced']);
		}
		unset($product['Prices']);
		
		if ($this->priceConfig['type'] == 'single') {
			$product['Price'] = current($product['Price']);
			if (isset($product['PriceReduced'])) {
				$product['PriceReduced'] = current($product['PriceReduced']);
			}
			$product['Currency'] = current($product['Currency']);
		}
		
		// Quantity
		$product['Quantity'] = $this->calcQuantity($product['Quantity']);
	}
	
	/**
	 * Builds the SELECT string for the product and offer query and stores them in class attributes.
	 */
	protected function buildSelectFields() {
		$productsOffer = array ( // These fields are order specific and they exists in every osC fork
			'ProductId' => 'products_id',
			'ProductsModel' => 'products_model',
			'MarketplaceId' => 'products_id',
			'MarketplaceSku' => 'products_model',
			'Quantity' => 'products_quantity',
			'Price' => 'products_price',
			'PriceReduced' => '',
			'Currency' => '',
			'Status' => 'products_status',
			'TaxClass' => 'products_tax_class_id',
			'TaxPercent' => '',
		);
		$productFields = array ( // Some of these fields don't exist in every osC fork.
			'EAN' => 'products_ean',
			'ShippingTimeId' => 'products_shippingtime',
			'ShippingTime' => '',
			'Images' => 'products_image',
			'DateAdded' => 'products_date_added',
			'LastModified' => 'products_last_modified',
			'DateAvailable' => 'products_date_available',
			'Weight' => 'products_weight',
			'ManufacturerId' => 'manufacturers_id',
			'Manufacturer' => '',
			'ManufacturerPartNumber' => '',
			'IsFSK18' => 'products_fsk18',
			'BasePrice' => '',
			'VpeUnit' => 'products_vpe',
			'VpeValue' => 'products_vpe_value',
			'VpeStatus' => 'products_vpe_status',
			'ProductUrl' => '',
			'MinOrderQuantity' => 'gm_min_order',
			'PossibleAmountInterval' => 'gm_graduated_qty',
		);
		$descriptionFields = array ( // Some of these fields don't exist in every osC fork.
			'Title' => 'products_name',
			'Description' => 'products_description',
			'ShortDescription' => 'products_short_description',
			'Keywords' => array('products_meta_keywords', 'products_head_keywords_tag'),
			'BulletPoints' => array('products_meta_description', 'products_head_desc_tag'),
		);
		
		$prod = MagnaDB::gi()->fetchRow('SELECT * FROM '.TABLE_PRODUCTS.' LIMIT 1');
		$desc = MagnaDB::gi()->fetchRow('SELECT * FROM '.TABLE_PRODUCTS_DESCRIPTION.' LIMIT 1');
		
		if (!empty($prod)) {
			foreach ($productFields as $ml => $db) {
				if (!empty($db) && !array_key_exists($db, $prod)) {
					$productFields[$ml] = '';
				}
			}
		}
		if (!empty($desc)) {
			foreach ($descriptionFields as $ml => $dbs) {
				if (!is_array($dbs)) {
					$dbs = array($dbs);
				}
				$found = false;
				foreach ($dbs as $db) {
					if (!empty($db) && array_key_exists($db, $desc)) {
						$descriptionFields[$ml] = $db;
						$found = true;
						break;
					}
				}
				if (!$found) {
					$descriptionFields[$ml] = '';
				}
			}
		}
		
		// build select statements
		$productSelectFields = array();
		foreach ($productsOffer as $ml => $db) {
			$productSelectFields[] = (empty($db) ? '""' : 'p.'.$db).' AS '.$ml;
		}
		$this->productOfferSelectFields = implode(', ', $productSelectFields);
		
		foreach ($productFields as $ml => $db) {
			$productSelectFields[] = (empty($db) ? '""' : 'p.'.$db).' AS '.$ml;
		}
		$this->productMainSelectFields = implode(', ', $productSelectFields);
		
		$productSelectFields = array();
		foreach ($descriptionFields as $ml => $db) {
			$productSelectFields[] = (empty($db) ? '""' : 'pd.'.(is_array($db)? current($db) : $db)).' AS '.$ml;
		}
		$this->productDescriptionSelectFields = implode(', ', $productSelectFields);
		
		// attributes (flat)
		$addAttributesFields = array (
			'EAN' => array('attributes_ean', 'gm_ean'),
			'Weight' => 'options_values_weight',
			'WeightPrefix' => 'weight_prefix',
			'VpeUnit' => 'products_vpe_id', // {Gambio GX >= 2.1 only}
			'VpeValue' => 'gm_vpe_value', // {Gambio GX >= 2.1 only}
		);
		$attr = MagnaDB::gi()->fetchRow('SELECT * FROM '.TABLE_PRODUCTS_ATTRIBUTES.' LIMIT 1');
		if (empty($attr)) {
			$attr = array();
		}
		foreach ($addAttributesFields as $ml => $dbs) {
			if (!is_array($dbs)) {
				$dbs = array($dbs);
			}
			$found = false;
			foreach ($dbs as $db) {
				if (!empty($db) && array_key_exists($db, $attr)) {
					$addAttributesFields[$ml] = $db;
					$found = true;
					break;
				}
			}
			if (!$found) {
				$addAttributesFields[$ml] = '';
			}
		}
		
		$attributesSelectFields = array (
			'pa.products_attributes_id AS VariationId',
			($this->existingColumns['pa.attributes_model'] ? 'pa.attributes_model' : '""').' AS VariationModel',
			'"" AS MarketplaceId',
			'"" AS MarketplaceSku',
			'"" AS Variation',
			'pa.options_id AS VariationNameId',
			'pa.options_values_id AS VariationValueId',
			'pa.options_values_price AS Price',
			'pa.price_prefix AS PricePrefix',
			'pa.attributes_stock AS Quantity',
			'"1" AS Status',
		);
		$this->attributesOfferSelectFields = implode(', ', $attributesSelectFields);
		
		foreach ($addAttributesFields as $ml => $db) {
			$attributesSelectFields[] = (empty($db) ? '""' : 'pa.'.$db).' AS '.$ml;
		}
		$this->attributesMainSelectFields = implode(', ', $attributesSelectFields);
	}
	
	/**
	 * Returns the products description for a product in the requested language.
	 *
	 * @param int $pId
	 *    The products id of the product
	 * @param int $language
	 *    The language id
	 *
	 * @return array|bool
	 *    The products description or false if the description does not exist for the
	 *    requested language.
	 */
	protected function loadProductsDescription($pId, $language) {
		$desc = MagnaDB::gi()->fetchRow('
			SELECT '.$this->productDescriptionSelectFields.'
			  FROM '.TABLE_PRODUCTS_DESCRIPTION.' pd
			 WHERE pd.products_id = '.$pId.'
			       AND pd.language_id = "'.$language.'"
			 LIMIT 1
		');
		if (empty($desc)) {
			return false;
		}
		
		// Filter JNH Tab
		if (getDBConfigValue('gambio.tabs.display', 0, 'h1') == 'none') {
			if (strpos($desc['Description'], '[TAB:')) {
				$desc['Description'] = substr($desc['Description'], 0, strpos($desc['Description'], '[TAB:'));
			}
		} else {
			$desc['Description'] = preg_replace('/\[TAB:([^\]]*)\]/', '<h1>${1}</h1>', $desc['Description']);
		}
		
		return $desc;
	}
	
	/**
	 * Post processes the variations. It might make some final modifications on the Variations element
	 * based on the set options.
	 *
	 * @param array $parent
	 *    The main product with its variations as Variations element
	 * @return array
	 *    The main product with the modified Variations element
	 */
	protected function postProcessVariations(array $parent) {
		if (empty($parent['Variations'])) {
			return $parent;
		}
		
		// If there is only one variation (not dimension but variation) don't try to convert the unique
		// variation attributes to real attributes. Otherwise the product would end up with an invalid
		// variation structure.
		if ($this->optionsTmp['sameVariationsToAttributes'] && (count($parent['Variations']) > 1)) {
			$variationSets = array();
			foreach ($parent['Variations'] as $product) {
				foreach ($product['Variation'] as $vSet) {
					$variationSets[$vSet['NameId']][$vSet['ValueId']] = $vSet;
				}
			}
			
			// Move fixed variations that don't differ in their values for all variations to the attributes part of the parent product.
			$attributes = array();
			if (!isset($parent['Attributes'])) {
				$parent['Attributes'] = array();
			}
			foreach ($variationSets as $nameId => $valueSets) {
				if (count($valueSets) == 1) {
					$attributes[$nameId] = true;
					$parent['Attributes'][] = array_shift($valueSets);
				}
			}
			
			if (!empty($attributes)) {
				foreach ($parent['Variations'] as &$product) {
					foreach ($product['Variation'] as $idx => $vSet) {
						if (isset($attributes[$vSet['NameId']])) {
							unset($product['Variation'][$idx]);
						}
					}
				}
			}
			
			if (empty($parent['Attributes'])) {
				unset($parent['Attributes']);
			}
		}
		
		if (!$this->optionsTmp['allowSingleVariations'] && (count($parent['Variations']) == 1)) {
			#echo print_m($parent);
			if (!isset($parent['Attributes'])) {
				$parent['Attributes'] = array();
			}
			$vItem = $parent['Variations'][0];
			foreach ($vItem['Variation'] as $vSet) {
				$parent['Attributes'][] = $vSet;
			}
			unset($vItem['Variation']);
			unset($vItem['VariationId']);
			
			unset($parent['QuantityTotal']);
			unset($parent['Variations']);
			
			$parent = array_replace($parent, $vItem);
			#echo print_m($parent);
		}
		
		return $parent;
	}
	
	/**
	 * Loads a complete product with full detail and its variations.
	 *
	 * @param int $pId
	 *    The id of the product
	 * @param array $optionsTmp
	 *    Affects the options only for this one call. They will be reseted to their
	 *    previous state. For the available options @see self::setOptions().
	 *
	 * @return array
	 *    The loaded product
	 */
	public function getProductById($pId, array $optionsTmp = array()) {
		if (empty($this->languagesSelected['values'])) {
			throw new Exception('Please set a language first.');
		}
		
		$this->setOptionsTmp($optionsTmp);
		
		$product = MagnaDB::gi()->fetchRow(eecho('
		    SELECT '.$this->productMainSelectFields.'
		      FROM '.TABLE_PRODUCTS.' p
		     WHERE p.products_id = '.(int)$pId.'
		', false));
		
		if (empty($product)) {
			$this->resetOptionsTmp();
			return false;
		}
		
		$product['MarketplaceId'] = 'ML'.$product['MarketplaceId'];
		
		$desc = array();
		foreach ($this->languagesSelected['values'] as $langId => $langCode) {
			$descTmp = $this->loadProductsDescription($pId, $langId);
			if (!empty($descTmp)) {
				foreach ($descTmp as $descKey => $descVal) {
					if (!isset($desc[$descKey])) {
						$desc[$descKey] = array();
					}
					$desc[$descKey][$langCode] = $descVal;
				}
			}
		}
		unset($descTmp);
		
		if (empty($desc)) {
			$this->resetOptionsTmp();
			return false;
		}
		$desc = $this->processLanguageSpecificsMulti($desc);
		$product = array_merge($product, $desc);
		
		$product['VpeStatus'] = (bool)$product['VpeStatus'];
		$product['IsFSK18']   = (bool)$product['IsFSK18'];
		
		$product['ShippingTime'] = $this->getShippingTimeStringById($product['ShippingTimeId']);
		$product['Manufacturer'] = $this->getManufacturerNameById($product['ManufacturerId']);

		// Loads Manufacturer Part Number to Product 'ManufacturerPartNumber' field
		$this->getManufacturerPartNumber($product);

		$this->completeTax($product);
		$this->getAllDataByMatching($product);
		$this->completeBasePrice($product);
		$this->completeImages($product);
		$this->completeAdditionalConfiguration($product);

		if (empty($product['DateAvailable'])) {
			$product['DateAvailable'] = '0000-00-00 00:00:00';
		}
		if (empty($product['LastModified'])) {
			$product['LastModified'] = $product['DateAdded'];
		}
		if ((float)$product['Weight'] > 0) {
			$product['Weight'] = array (
				'Unit' => 'kg',
				'Value' => $product['Weight'],
			);
		} else {
			$product['Weight'] = array ();
		}
		
		$product['ProductUrl'] = HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'product_info.php?products_id='.$product['ProductId'];
		
		$this->prepareParentPrices($product);
		
		$product['Variations'] = $this->fetchVariations($product, false);
		$product['VariationPictures'] = $this->fetchVariationImages($product);
		$this->completeParentOffer($product);
		
		$product = $this->postProcessVariations($product);
		
		$this->resetOptionsTmp();
		return $product;
	}
	
	/**
	 * Loads a complete product with full detail and its variations.
	 *
	 * @param int $pId
	 *    The id of the product
	 * @param array $optionsTmp
	 *    Affects the options only for this one call. They will be reseted to their
	 *    previous state. For the available options @see self::setOptions().
	 *
	 * @return array
	 *    The loaded product
	 */
	public function getProductOfferById($pId, array $optionsTmp = array()) {
		if (empty($this->languagesSelected['values'])) {
			throw new Exception('Please set a language first.');
		}
		
		$this->setOptionsTmp($optionsTmp);
		
		$product = MagnaDB::gi()->fetchRow(eecho('
		    SELECT '.$this->productOfferSelectFields.'
		      FROM '.TABLE_PRODUCTS.' p
		     WHERE p.products_id = '.(int)$pId.'
		', false));
		
		if (empty($product)) {
			$this->resetOptionsTmp();
			return $product;
		}
		
		$product['MarketplaceId'] = 'ML'.$product['MarketplaceId'];
		
		$this->completeTax($product);
		$this->prepareParentPrices($product);
		
		$product['Variations'] = $this->fetchVariations($product, true);
		$this->completeParentOffer($product);
		
		$product = $this->postProcessVariations($product);
		
		$this->resetOptionsTmp();
		return $product;
	}
	
	/**
	 * Reduces the quantity for a product or variation.
	 * 
	 * @param string $sku
	 *    The SKU of the product.
	 * @param int $quantityDifference
	 *    A positive int increases the quantity, a negative one decreases it.
	 *
	 * @return $this
	 */
	public function changeQuantity($sku, $quantityDifference) {
		// @todo: implement
		return $this;
	}
	
	/**
	 * @deprecated
	 * Fetches the product for one or multiple product ids.
	 * 
	 * @param mixed $pID
	 *    The products id or ids
	 * @param int $languages_id
	 *    The langauge that will be used for the title and description
	 * @param string $addQuery
	 *    An additional filter query that will appendet to the WHERE condition.
	 *
	 * @return array
	 *    The product(s)
	 */
	public function getProductByIdOld($pID, $languages_id = false, $addQuery = '') {
		$lIDs = MagnaDB::gi()->fetchArray('
			SELECT language_id FROM '.TABLE_PRODUCTS_DESCRIPTION.' WHERE products_id="'.$pID.'"
		', true);

		if ($languages_id === false) {
			$languages_id = $_SESSION['languages_id'];
		}
		
		if (!empty($lIDs) && !in_array($languages_id, $lIDs)) {
			$languages_id = array_shift($lIDs);
		}

		if (is_array($pID)) {
			$where = 'p.products_id IN ("'.implode('", "',  $pID).'")';
		} else {
			$where = 'p.products_id = "'.(int) $pID.'"';
		}

        $sqlCoalesce = 'COALESCE(p.products_id, pd.products_id) as products_id,';
		$sJoin = '';
        if( MagnaDB::gi()->tableExists('products_item_codes')){
            $sJoin = 'LEFT JOIN `products_item_codes` ic ON p.products_id = ic.products_id';
            $sqlCoalesce = 'COALESCE(p.products_id, pd.products_id, ic.products_id) as products_id,';
        }

        // COALESCE because it could be that in left join no entry will be found
		$products = MagnaDB::gi()->fetchArray(eecho('
            SELECT *, '.$sqlCoalesce.' date_format(p.products_date_available, "%Y-%m-%d") AS products_date_available 
			  FROM '.TABLE_PRODUCTS.' p
              LEFT JOIN `'.TABLE_PRODUCTS_DESCRIPTION.'` pd ON pd.products_id = p.products_id
			  '.$sJoin.'
			 WHERE '.$where.'
			       AND p.products_id = pd.products_id
			       AND pd.language_id = "'.$languages_id.'"
			   '.$addQuery.'
		', false));

		if (!is_array($products) || empty($products)) return false;

		$finalProducts = array();
		foreach ($products as &$product) {
			// Filter JNH Tab
			if (getDBConfigValue('gambio.tabs.display', 0, 'h1') == 'none') {
				if (strpos($product['products_description'], '[TAB:')) {
					$product['products_description'] = substr($product['products_description'], 0, strpos($product['products_description'], '[TAB:'));
				}
			} else {
				$product['products_description'] = preg_replace('/\[TAB:([^\]]*)\]/', '<h1>${1}</h1>', $product['products_description']);
			}
			
			if ($product['products_image']) {
				$product['products_allimages'] = array($product['products_image']);
			} else {
				$product['products_allimages'] = array();
			}

			$imagesArray = $this->getProductImagesByID($product['products_id']);
			if (!empty($imagesArray)) {
				$product['products_allimages'] = array_merge($product['products_allimages'], $imagesArray);
			}

			if (isset($product['products_head_keywords_tag'])) {
				$product['products_meta_keywords'] = $product['products_head_keywords_tag'];
				unset($product['products_head_keywords_tag']);
			}
			if (isset($product['products_head_desc_tag'])) {
				$product['products_meta_description'] = $product['products_head_desc_tag'];
				unset($product['products_head_desc_tag']);
			}
			if (isset($product['products_vpe'])
			    && isset($product['products_vpe_value'])
			    && MagnaDB::gi()->tableExists(TABLE_PRODUCTS_VPE)
			) {
				$product['products_vpe_name'] = stringToUTF8(MagnaDB::gi()->fetchOne('
				    SELECT products_vpe_name 
				      FROM '.TABLE_PRODUCTS_VPE.'
				     WHERE products_vpe_id = "'.$product['products_vpe'].'"
				           AND language_id = "'.$languages_id.'"
				  ORDER BY products_vpe_id, language_id 
				     LIMIT 1
				'));
			}
			$finalProducts[$product['products_id']] = $product;
		}
		if (!is_array($pID)) {
			return $products[0];
		}
		unset($products);
		return $finalProducts;
	}
	
	/**
	 * Fetches the category path for a product or category depending on the parameters
	 * 
	 * @param int $id
	 *    The id of the product or category
	 * @param string $for
	 *    Set this to 'product' to get the path for a product. For anything else 'category' is assumed.
	 * @param array &$cPath
	 *    Internally used for recursion. Do not pass an argument here.
	 *
	 * @return array
	 *    The category path
	 */
	public function getCategoryPath($id, $for = 'category', &$cPath = array()) {
		if ($for == 'product') {
			$cIDs = MagnaDB::gi()->fetchArray('
				SELECT categories_id FROM '.TABLE_PRODUCTS_TO_CATEGORIES.'
				 WHERE products_id="'.MagnaDB::gi()->escape($id).'"
			', true);
			if (empty($cIDs)) {
				return array();
			}
			$return = array();
			foreach ($cIDs as $cID) {
				if ((int)$cID == 0) {
					$return[] = array('0');
				} else {
					$cPath = $this->getCategoryPath($cID);
					array_unshift($cPath, $cID);
					$return[] = $cPath;
				}
			}
			return $return;
		} else {
			$meh = MagnaDB::gi()->fetchOne(
				'SELECT parent_id FROM '.TABLE_CATEGORIES.' WHERE categories_id="'.MagnaDB::gi()->escape($id).'"'
			);
			$cPath[] = (int)$meh;
			if ($meh != '0') {
				$this->getCategoryPath($meh, 'category', $cPath);
			}
			return $cPath;
		}
	}

	/**
	 * Fetches a category path in the language of the current shop interface.
	 * Copied from xt:commerce 3.
	 *
	 * @param int $id
	 *    The id of the product or category
	 * @param string $from
	 *    Set this to 'product' to get the path for a product. For anything else 'category' is assumed.
	 * @param array &$categories_array
	 *    Internally used for recursion. Do not pass an argument here.
	 * @param int &$index
	 *    Internally used for recursion. Do not pass an argument here.
	 * @param int &$callCount
	 *    Internally used for recursion. Do not pass an argument here.
	 * 
	 * @return array
	 *    The category path
	 */
	public function generateCategoryPath($id, $from = 'category', $categories_array = array(), $index = 0, $callCount = 0) {
		if ($from == 'product') {
			$categories_query = MagnaDB::gi()->query('
				SELECT categories_id FROM '.TABLE_PRODUCTS_TO_CATEGORIES.'
				 WHERE products_id = "'.$id.'"
			');
			while ($categories = MagnaDB::gi()->fetchNext($categories_query)) {
				if ($categories['categories_id'] == '0') {
					$categories_array[$index][] = array ('id' => '0', 'text' => ML_LABEL_CATEGORY_TOP);
				} else {
					$category_query = MagnaDB::gi()->query('
						SELECT cd.categories_name, c.parent_id 
						  FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd 
						 WHERE c.categories_id = "'.$categories['categories_id'].'" 
						       AND c.categories_id = cd.categories_id 
						       AND cd.language_id = "'.$_SESSION['languages_id'].'"
					');
					$category = MagnaDB::gi()->fetchNext($category_query);
					$categories_array[$index][] = array (
						'id' => $categories['categories_id'],
						'text' => $category['categories_name']
					);
					if (($category['parent_id'] != '') && ($category['parent_id'] != '0')) {
						$categories_array = $this->generateCategoryPath($category['parent_id'], 'category', $categories_array, $index);
					}
				}
				++$index;
			}
		} else if ($from == 'category') {
			$category_query = MagnaDB::gi()->query('
				SELECT cd.categories_name, c.parent_id 
				  FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd
				 WHERE c.categories_id = "'.$id.'" 
				       AND c.categories_id = cd.categories_id
				       AND cd.language_id = "'.$_SESSION['languages_id'].'"
			');
			$category = MagnaDB::gi()->fetchNext($category_query);
			$categories_array[$index][] = array (
				'id' => $id,
				'text' => $category['categories_name']
			);
			if (($category['parent_id'] != '') && ($category['parent_id'] != '0')) {
				$categories_array = $this->generateCategoryPath($category['parent_id'], 'category', $categories_array, $index, $callCount + 1);
			}
			if ($callCount == 0) {
				$categories_array[$index] = array_reverse($categories_array[$index]);
			}
		}
		return $categories_array;
	}
	
	/**
	 * Fetches a category path in the language of the current shop interface.
	 * Copied from xt:commerce 3.
	 *
	 * @param int $id
	 *    The id of the product or category
	 * @param string $from
	 *    Set this to 'product' to get the path for a product. For anything else 'category' is assumed.
	 * @param array &$categories_array
	 *    Internally used for recursion. Do not pass an argument here.
	 * @param int &$index
	 *    Internally used for recursion. Do not pass an argument here.
	 * @param int &$callCount
	 *    Internally used for recursion. Do not pass an argument here.
	 * 
	 * @return array
	 *    The category path
	 */
	private function generateMPCategoryPath($id, $from = 'category', $langID = 1, $categories_array = array(), $index = 0, $callCount = 0) {
		$descCol = '';
		if (MagnaDB::gi()->columnExistsInTable('categories_description', TABLE_CATEGORIES_DESCRIPTION)) {
			$descCol = 'categories_description';
		} else {
			$descCol = 'categories_name';
		}
		$trim = " \n\r\0\x0B\xa0\xc2"; # last 2 ones are utf8 &nbsp;
		if ($from == 'product') {
			$categoriesQuery = MagnaDB::gi()->query('
				SELECT categories_id AS Id
				  FROM '.TABLE_PRODUCTS_TO_CATEGORIES.'
				 WHERE products_id = "'.$id.'"
			');
			while ($categories = MagnaDB::gi()->fetchNext($categoriesQuery)) {
				if ($categories['Id'] != '0') {
					$category = MagnaDB::gi()->fetchRow('
						SELECT cd.categories_name AS `Name`, cd.'.$descCol.' AS `Description`, c.parent_id AS `ParentId`
						  FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd 
						 WHERE c.categories_id = "'.$categories['Id'].'" 
						       AND c.categories_id = cd.categories_id 
						       AND cd.language_id = "'.$langID.'"
					');
					$c = array (
						'Id' => $categories['Id'],
						'ParentId' => $category['ParentId'],
						'Name' => trim(html_entity_decode(strip_tags($category['Name']), ENT_QUOTES, 'UTF-8'), $trim),
						'Description' => $category['Description'],
					);
					if ($c['ParentId'] == '0') {
						unset($c['ParentId']);
					}
					if ($c['Description'] == '') {
						$c['Description'] = $c['Name'];
					}
					$categories_array[$index][] = $c;
					if (($category['ParentId'] != '') && ($category['ParentId'] != '0')) {
						$categories_array = $this->generateMPCategoryPath($category['ParentId'], 'category', $langID, $categories_array, $index);
					}
				}
				++$index;
			}
		} else if ($from == 'category') {
			$category = MagnaDB::gi()->fetchRow('
				SELECT c.categories_id AS `Id`, cd.categories_name AS `Name`, cd.'.$descCol.' AS `Description`, c.parent_id AS `ParentId`
				  FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd
				 WHERE c.categories_id = "'.$id.'" 
				       AND c.categories_id = cd.categories_id
				       AND cd.language_id = "'.$langID.'"
			');
			$c = array (
				'Id' => $category['Id'],
				'ParentId' => $category['ParentId'],
				'Name' => trim(html_entity_decode(strip_tags($category['Name']), ENT_QUOTES, 'UTF-8'), $trim),
				'Description' => $category['Description'],
			);
			if ($c['ParentId'] == '0') {
				unset($c['ParentId']);
			}
			if ($c['Description'] == '') {
				$c['Description'] = $c['Name'];
			}
			$categories_array[$index][] = $c;
			if (($category['ParentId'] != '') && ($category['ParentId'] != '0')) {
				$categories_array = $this->generateMPCategoryPath($category['ParentId'], 'category', $langID, $categories_array, $index, $callCount + 1);
			}
			if ($callCount == 0) {
				$categories_array[$index] = array_reverse($categories_array[$index]);
			}
		}
		
		return $categories_array;
	}
	
	/**
	 * 
	 * @param int $id id of product
	 */
	public function getProductPropertiesByProductid($id){
		$aProperties = array();
		if (MAGNA_GAMBIO_VARIATIONS && (getDBConfigValue('general.gambio.useproperties', '0', 'true') == 'true')) {
			$properties = MagnaDb::gi()->fetchArray('
				SELECT DISTINCT ppas.properties_id as id,  properties_name as name 
				FROM products_properties_admin_select ppas 
				JOIN properties_description pd on ppas.properties_id = pd.properties_id
				WHERE products_id = '.$id.' AND language_id = '.$_SESSION['languages_id'].'
			');
			if(!empty($properties)){
				foreach ($properties as $property) {
					$aProperties[$property['id']] = $property['name'];
				}
			}
		}
		return $aProperties;
	}

	public function getProductAttributesByProductId($id){
		$aAttributes = array();
		$attributes = MagnaDb::gi()->fetchArray('
			SELECT DISTINCT po.products_options_id as id, products_options_name as name
			  FROM '.TABLE_PRODUCTS_OPTIONS.' po
			  JOIN '.TABLE_PRODUCTS_ATTRIBUTES.' pa on po.products_options_id = pa.options_id
			 WHERE pa.products_id = '.$id.' AND po.language_id = '.$_SESSION['languages_id'].' 
		');
		if(!empty($attributes)){
			foreach ($attributes as $attribute) {
				$aAttributes[$attribute['id']] = $attribute['name'];
			}
		}
		return $aAttributes;
	}

    /**
     * These data will be displayed on Gambio product detail under "Erweiterte Konfiguration"
     * @param $product
     */
    private function completeAdditionalConfiguration(&$product) {
        if (MagnaDB::gi()->tableExists('products_item_codes')) {
            $aData = MagnaDb::gi()->fetchRow('
                SELECT *
                  FROM `products_item_codes` ic 
                 WHERE ic.`products_id` = '.$product['ProductId'].'
            ');

            // skip setting data if result is not an array
            if (!is_array($aData)) {
                return;
            }

            foreach ($aData as $sColumn => $sValue) {
                $product[$sColumn] = isset($product[$sColumn]) ? $product[$sColumn] : $sValue;
            }
        }
    }

}
