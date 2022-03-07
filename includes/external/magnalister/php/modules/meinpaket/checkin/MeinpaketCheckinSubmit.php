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
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/checkin/MagnaCompatibleCheckinSubmit.php');
require_once(DIR_MAGNALISTER_MODULES.'meinpaket/MeinpaketHelper.php');

class MeinpaketCheckinSubmit extends MagnaCompatibleCheckinSubmit
{
	
	protected $varMatchingCache = array();
	
	public function __construct($settings = array()) {
		global $_MagnaSession;
		/* Setzen der Currency nicht noetig, da Preisberechnungen bereits in 
		   der MeinpaketSummaryView Klasse gemacht wurden.
		 */
		$settings = array_merge(array(
			'language' => getDBConfigValue($settings['marketplace'].'.lang', $_MagnaSession['mpID']),
			'itemsPerBatch' => 25,
			'mlProductsUseLegacy' => false,
		), $settings);
		
		parent::__construct($settings);
		
		$this->settings['SyncInventory'] = array (
			'Price' => getDBConfigValue($this->settings['marketplace'].'.inventorysync.price', $this->mpID, '') == 'auto',
			'Quantity' => getDBConfigValue($this->settings['marketplace'].'.stocksync.tomarketplace', $this->mpID, '') == 'auto',
		);
	}
	
	protected function strreplace($str, array $repl) {
		$replace = array();
		if (!empty($repl)) {
			foreach ($repl as $key => $val) {
				$replace['{#'.$key.'#}'] = $val;
			}
		}
		return str_replace(array_keys($replace), array_values($replace), $str);
	}
	
	private function generateMPCategoryPath($id, $from = 'category', $langID, $categories_array = array(), $index = 0, $callCount = 0) {
		$descCol = '';
		if (MagnaDB::gi()->columnExistsInTable('categories_description', TABLE_CATEGORIES_DESCRIPTION)) {
			$descCol = 'categories_description';
		} else {
			$descCol = 'categories_name';
		}
		$trim = " \n\r\0\x0B\xa0\xc2"; # last 2 ones are utf8 &nbsp;
		if ($from == 'product') {
			$categoryIds = MagnaDB::gi()->fetchArray('
				SELECT categories_id AS code
				  FROM '.TABLE_PRODUCTS_TO_CATEGORIES.'
				 WHERE products_id = "'.$id.'"
			', true);
			foreach ($categoryIds as $cId) {
				if ($cId != '0') {
					$category = MagnaDB::gi()->fetchRow('
						SELECT cd.categories_name AS `name`, cd.'.$descCol.' AS `desc`, c.parent_id AS `parent`
						  FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd 
						 WHERE c.categories_id = "'.$cId.'" 
						       AND c.categories_id = cd.categories_id 
						       AND cd.language_id = "'.$langID.'"
						 LIMIT 1
					');
					if (empty($category)) {
						continue;
					}
					$c = array (
						'code' => $cId,
						'name' => trim(html_entity_decode(strip_tags($category['name']), ENT_QUOTES, 'UTF-8'), $trim),
						'desc' => $category['desc'],
						'parent' => $category['parent'],
					);
					if ($c['parent'] == '0') {
						unset($c['parent']);
					}
					if ($c['desc'] == '') {
						$c['desc'] = $c['name'];
					}
					$categories_array[$index][] = $c;
					if (($category['parent'] != '') && ($category['parent'] != '0')) {
						$categories_array = $this->generateMPCategoryPath($category['parent'], 'category', $langID, $categories_array, $index);
					}
				}
				++$index;
			}
		} else if ($from == 'category') {
			$category = MagnaDB::gi()->fetchRow('
				SELECT c.categories_id AS code, cd.categories_name AS `name`, cd.'.$descCol.' AS `desc`, c.parent_id AS `parent`
				  FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd
				 WHERE c.categories_id = "'.$id.'" 
				       AND c.categories_id = cd.categories_id
				       AND cd.language_id = "'.$langID.'"
				 LIMIT 1
			');
			if (empty($category)) {
				return $categories_array;
			}
			$c = array (
				'code' => $category['code'],
				'name' => trim(html_entity_decode(strip_tags($category['name']), ENT_QUOTES, 'UTF-8'), $trim),
				'desc' => $category['desc'],
				'parent' => $category['parent'],
			);
			if ($c['parent'] == '0') {
				unset($c['parent']);
			}
			if ($c['desc'] == '') {
				$c['desc'] = $c['name'];
			}
			$categories_array[$index][] = $c;
			if (($category['parent'] != '') && ($category['parent'] != '0')) {
				$categories_array = $this->generateMPCategoryPath($category['parent'], 'category', $langID, $categories_array, $index, $callCount + 1);
			}
			if ($callCount == 0) {
				$categories_array[$index] = array_reverse($categories_array[$index]);
			}
		}
		
		return $categories_array;
	}
	
	public function makeSelectionFromErrorLog() {}
	
	protected function generateRequestHeader() {
		return array(
			'ACTION' => 'AddItems',
			'MODE' => $this->submitSession['mode']
		);
	}
	
	protected function addToErrorLog($sku, $error = '') {
		if (empty($error)) {
			$error = ML_GENERIC_ERROR_UNABLE_TO_LOAD_PREPARE_DATA;
		}
		MagnaDB::gi()->insert(
			TABLE_MAGNA_MEINPAKET_ERRORLOG,
			array (
				'mpID' => $this->mpID,
				'errormessage' => $error,
				'dateadded' => gmdate('Y-m-d H:i:s'),
				'additionaldata' => serialize(array(
					'SKU' => $sku
				))
			)
		);
	}
	
	protected function markAsFailed($pId) {
		$this->badItems[] = $pId;
		unset($this->selection[$pId]);
	}
	
	protected function setUpMLProduct() {
		parent::setUpMLProduct();
		
		// Set a db matching (e.g. 'ManufacturerPartNumber')
		$mfrmd = getDBConfigValue($this->settings['marketplace'].'.checkin.manufacturerpartnumber.table', $this->mpID, false);
		if (is_array($mfrmd) && !empty($mfrmd['column']) && !empty($mfrmd['table'])) {
			$pIDAlias = getDBConfigValue($this->settings['marketplace'].'.checkin.manufacturerpartnumber.alias', $this->mpID);
			if (empty($pIDAlias)) {
				$pIDAlias = 'products_id';
			}
			MLProduct::gi()->setDbMatching('ManufacturerPartNumber', array (
				'Table'  => $mfrmd['table'],
				'Column' => $mfrmd['column'],
				'Alias'  => $pIDAlias,
			));
		}
		
		// Use multi dimensional variations
		MLProduct::gi()->useMultiDimensionalVariations(true);
		
		// Set Price and Quantity settings
		MLProduct::gi()->setPriceConfig(MeinpaketHelper::loadPriceSettings($this->mpID));
		MLProduct::gi()->setQuantityConfig(MeinpaketHelper::loadQuantitySettings($this->mpID));
		MLProduct::gi()->setOptions(array(
			'sameVariationsToAttributes' => false,
			'purgeVariations' => true,
			'useGambioProperties' => (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties'),
		));
	}
	
	protected function appendAdditionalData($pID, $product, &$data) {
		$propertiesRow = MagnaDB::gi()->fetchRow('
			SELECT *
			  FROM ' . TABLE_MAGNA_MEINPAKET_PROPERTIES . '
			 WHERE ' . ((getDBConfigValue('general.keytype', '0') == 'artNr') 
						? 'products_model="' . MagnaDB::gi()->escape($product['ProductsModel']) . '"'
						: 'products_id="' . $pID . '"'
					) . ' 
			       AND mpID = ' . $this->_magnasession['mpID']
		);
		
		// Will not happen in sumbit cycle but can happen in loadProductByPId.
		if (empty($propertiesRow)) {
			$data['submit'] = array();
			$this->addToErrorLog(magnaPID2SKU($pID));
			$this->markAsFailed($pID);
			return;
		}

		$categoryAttributes = '';
		if (!empty($propertiesRow['CategoryAttributes'])) {
			$categoryAttributes = MeinpaketHelper::gi()->convertMatchingToNameValue(
				json_decode($propertiesRow['CategoryAttributes'], true),
				$product
			);
		}

		$variationTheme = array();
		if (isset($propertiesRow['variation_theme'])) {
			$variationTheme = json_decode($propertiesRow['variation_theme'], true);

			// Unset all variation theme attributes from category attributes
			$variationThemeKey = key($variationTheme);
			if (is_array($variationTheme[$variationThemeKey])) {
				foreach ($variationTheme[$variationThemeKey] as $variationThemeAttribute) {
					unset($categoryAttributes[$variationThemeAttribute]);
				}
			}
		}

		$product['variation_theme'] = $variationTheme;

		$propertiesRow['ShippingDetails'] = @json_decode($propertiesRow['ShippingDetails'], true);
		
		if ($data['quantity'] < 0) {
			$data['quantity'] = 0;
		}

		// if the reduced price is available here it has been enabled in the module configuration and should be used.
		if (isset($product['PriceReduced'])) {
			$product['Price'] = $product['PriceReduced'];
		}
		
		$data['submit'] = $product;

		// remove stuff we do not want.
		$productFieldsToUnset = array(
			'ProductId',
			'ProductsModel',
			'ManufacturerId',
			'ShippingTimeId',
			'DateAdded',
			'LastModified',
			'VariationPictures',
		);

		foreach ($productFieldsToUnset as $key) {
			unset($data['submit'][$key]);
		}

		$data['submit']['SKU'] = magnaPID2SKU($pID);
		
		$data['submit']['ItemTitle'] = $product['Title'];
		unset($data['submit']['Title']);
		if (!$this->settings['SyncInventory']['Price']) {
			$data['submit']['Price'] = $data['price'];
		}
		if (!$this->settings['SyncInventory']['Quantity']) {
			$data['submit']['Quantity'] = (int)$data['quantity'];
		}
		
		if (
			(!empty($data['submit']['EAN']) && !getDBConfigValue(array($this->settings['marketplace'].'.checkin.ean', 'submit'), $this->mpID, true))
			|| empty($data['submit']['EAN'])
		) {
			unset($data['submit']['EAN']);
		}
		
		$shortdescField = getDBConfigValue($this->settings['marketplace'].'.checkin.shortdesc.field', $this->mpID, '');
		if (!empty($shortdescField) && array_key_exists($shortdescField, $product)) {
			$data['submit']['ShortDescription'] = $product[$shortdescField];
		} else {
			$data['submit']['ShortDescription'] = $product['Description'];
		}
		
		$longdescField = getDBConfigValue($this->settings['marketplace'].'.checkin.longdesc.field', $this->mpID, '');
		if (!empty($longdescField) && array_key_exists($longdescField, $product)) {
			$data['submit']['Description'] = $product[$longdescField];
		} else {
			unset($data['submit']['Description']);
		}
		/* Short-Desc ist leer, vielleicht ist die Lang-Desc ja nicht leer. */
		$longDesc = $product['Description'];
		if (empty($data['submit']['ShortDescription']) && !empty($longDesc)) {
			$data['submit']['ShortDescription'] = $longDesc;
		}
		
		/* Falls Langbeschreibung leer, Kurzbeschreibung ebenfalls fuer Langbeschreibung verwenden. Ansonsten entfernt Meinpaket
		   zu viele HTML-Tags */
		if (!isset($data['submit']['Description']) || empty($data['submit']['Description'])) {
			$data['submit']['Description'] = $data['submit']['ShortDescription'];
		}
		
		$taxMatch = getDBConfigValue($this->settings['marketplace'].'.checkin.taxmatching', $this->mpID, array());
		if (is_array($taxMatch) && array_key_exists($product['TaxClass'], $taxMatch)) {
			$data['submit']['ItemTax'] = $taxMatch[$product['TaxClass']];
		} else {
			$data['submit']['ItemTax'] = 'Standard';
		}
		
		$data['submit']['ShippingTime'] = getDBConfigValue($this->settings['marketplace'].'.checkin.leadtimetoship', $this->mpID, 3);
		$data['submit']['ShippingDetails'] = $propertiesRow['ShippingDetails'];
		$data['submit']['CategoryAttributes'] = $categoryAttributes;
		if (!empty($propertiesRow['VariationConfiguration'])) {
			$data['submit']['MPVariationConfiguration'] = array(
				'MpIdentifier' => $propertiesRow['VariationConfiguration'],
				'CustomIdentifier' => '',
			);
		}

        $imageWSPath = getDBConfigValue($this->settings['marketplace'].'.checkin.imagepath', $this->mpID, '');
        if (empty($imageWSPath)) {
            $imageWSPath = SHOP_URL_POPUP_IMAGES;
            $imageWSPath = trim($imageWSPath, '/ ').'/';
        }

		$images = array();
		if (!empty($product['Images'])) {
			foreach($product['Images'] as $img) {
				$images[] = array('URL' => (preg_match('/http(s{0,1}):\/\//', $img) ? '' : $imageWSPath).$img);
			}
		}
		$data['submit']['Images'] = $images;
		
		$data['submit']['MarketplaceCategory'] = $propertiesRow['MarketplaceCategory'];
		
		if (getDBConfigValue(array($this->settings['marketplace'].'.catmatch.mpshopcats', 'val'), $this->mpID, false)) {
			$cPath = $this->generateMPCategoryPath($pID, 'product', $this->settings['language']);
			if (empty($cPath)) {
				$data['submit']['MarketplaceShopCategory'] = '';
				$data['submit']['MarketplaceShopCategoryStructure'] = array();
			} else {
				$cPath = array_shift($cPath);
				$data['submit']['MarketplaceShopCategory'] = $cPath[count($cPath)-1]['code'];
				$data['submit']['MarketplaceShopCategoryStructure'] = $cPath;
			}
		} else if (!empty($catMatching['StoreCategory'])) {
			$data['submit']['MarketplaceShopCategory'] = $propertiesRow['StoreCategory'];
		}

		$data['submit']['IsSplit'] = false;

		if (!empty($product['variation_theme'])) {
			$data['submit']['variation_theme'] = $product['variation_theme'];
		}
		
		$allMatchedAttributes = json_decode($propertiesRow['CategoryAttributes'], true);

		$this->getMeinpaketVariations($product, $data, $allMatchedAttributes);

		//echo print_m($product, '$product');
		//echo print_m($propertiesRow, '$propertiesRow');
		
		return;
	}
	
	protected function getMeinpaketVariations($product, &$data, $allMatchedAttributes)
	{
		$matchedAttributesCodeValueId = $this->getMatchedVariationAttributesCodeValueId($allMatchedAttributes, $product['variation_theme']);
		$variations = array();

		// This flag is necessary for checking if master product should be sent at all and will be used in
		// pre-submit algorithm.
		$data['HasVariations'] = count($product['Variations']) > 0;

		foreach ($product['Variations'] as $v) {
			$this->simpleprice->setPrice($v['Price']);
			$price = $this->simpleprice->roundPrice()->makeSignalPrice(
				getDBConfigValue($this->marketplace . '.price.signal', $this->mpID, '')
			)->getPrice();

			$vi = array(
				'SKU' => (getDBConfigValue('general.keytype', '0') == 'artNr') ? $v['MarketplaceSku'] : $v['MarketplaceId'],
				'Price' => $price,
				'Currency' => $this->settings['currency'],
				'Quantity' => ($this->quantityLumb === false)
					? max(0, $v['Quantity'] - (int)$this->quantitySub)
					: $this->quantityLumb,
				'EAN' => $v['EAN']
			);

			$vi['Title'] = $product['Title'];
			$vi['VariantTitle'] = $product['Title'];

			// Only those attributes that are matched and all their values are matched will be in $convertedShopToMpAttributes.
			// This will be used for checking if all values are matched for matched attribute.
			$vi['CategoryAttributes'] = $this->fixVariationCategoryAttributes($allMatchedAttributes, $product, $v);


			$vi['Variation'] = array();
			$masterProductSku = $data['submit']['SKU'];
			$this->setAllVariationsDataAndMasterProductsSKUs(
				$v,
				$vi,
				$variations,
				$matchedAttributesCodeValueId,
				$allMatchedAttributes,
				$masterProductSku,
				$data,
				$product
			);
		}

		$this->prepareVariationDataForSubmitRequest($variations, $data);

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


		$fixCatAttributes = MeinpaketHelper::gi()->convertMatchingToNameValue($aCatAttributes, $productDataForMatching);

		// Unset all variation theme attributes from category attributes
		if (isset($product['variation_theme'])) {
			$variationThemeKey = key($product['variation_theme']);
			foreach ($product['variation_theme'][$variationThemeKey] as $variationThemeAttribute) {
				unset($fixCatAttributes[$variationThemeAttribute]);
			}
		}

		return $fixCatAttributes;
	}

	protected function setProductVariant(&$productVariant, $varAttribute, $rawAmConfiguration, $variations)
	{
		$fixCatAttributes = MeinpaketHelper::gi()->convertMatchingToNameValue($rawAmConfiguration, array(
			"variant_{$varAttribute['NameId']}" => $varAttribute['ValueId']
		), true);

		if (!empty($fixCatAttributes)) {
            $arrayKeys = array_keys($fixCatAttributes);
			$fixCatAttributes = array(
				'MPName' => array_pop($arrayKeys),
				'MPValue' => array_pop($fixCatAttributes)
			);
		}

		$productVariant['Variation'][] = $fixCatAttributes;
	}

	protected function processSubmitResult($result) {
		if (array_key_exists('ERRORS', $result)
			&& is_array($result['ERRORS'])
			&& !empty($result['ERRORS'])
		) {
			foreach ($result['ERRORS'] as $err) {
				$ad = array ();
				if (isset($err['DETAILS']['SKU'])) {
					$ad['SKU'] = $err['DETAILS']['SKU'];
				}
				$err = array (
					'mpID' => $this->mpID,
					'errormessage' => $err['ERRORMESSAGE'],
					'dateadded' => gmdate('Y-m-d H:i:s'),
					'additionaldata' => serialize($ad),
				);
				MagnaDB::gi()->insert(TABLE_MAGNA_MEINPAKET_ERRORLOG, $err);
			}
		}
		magnaMeinpaketProcessCheckinResult($result, $this->mpID);
	}

	protected function filterSelection() { }

	protected function preSubmit(&$request) {
		$request['DATA'] = array();

		if (count($this->additionalSplitProducts) > 0) {
			foreach ($this->additionalSplitProducts as $additionalSplitProduct) {
				$request['DATA'][] = $additionalSplitProduct;
			}
		}

		foreach ($this->selection as $iProductId => &$aProduct) {
			// If product has variations, but all variations are skipped because none of the values
			// is matched, master product should not be sent at all.
			if (empty($aProduct['submit']['Variations']) && !empty($aProduct['HasVariations'])) {
				continue;
			}

			$request['DATA'][] = $aProduct['submit'];
		}

		arrayEntitiesToUTF8($request['DATA']);
	}

	protected function postSubmit() {
		if ((array_key_exists('CHECKINERRORS', $this->lastResponse)
			&& is_array($this->lastResponse['CHECKINERRORS'])
			&& !empty($this->lastResponse['CHECKINERRORS']))
			|| 
			(array_key_exists('UPLOADERRORS', $this->lastResponse)
			&& is_array($this->lastResponse['UPLOADERRORS'])
			&& !empty($this->lastResponse['UPLOADERRORS']))
			||
			(array_key_exists('ERRORS', $this->lastResponse)
			&& is_array($this->lastResponse['ERRORS'])
			&& !empty($this->lastResponse['ERRORS']))
		) {
			$this->ajaxReply['redirect'] = $this->generateRedirectURL('fail');
		}
		
		MagnaConnector::gi()->resetTimeOut();
	}

	protected function generateRedirectURL($state) {
		return toURL(array(
			'mp' => $this->realUrl['mp'],
			'mode' => ($state == 'fail') ? 'errorlog' : 'listings',
		), true);
	}

}
