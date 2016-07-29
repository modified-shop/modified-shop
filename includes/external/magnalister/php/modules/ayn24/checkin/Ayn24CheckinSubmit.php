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
 * $Id: Ayn24CheckinSubmit.php 4984 2014-12-15 12:15:49Z tim.neumann $
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/CheckinSubmit.php');
require_once(DIR_MAGNALISTER_MODULES.'ayn24/Ayn24Helper.php');

class Ayn24CheckinSubmit extends CheckinSubmit {
	private $checkinDetails = array();
	
	protected $varMatchingCache = array();
	
	public function __construct($settings = array()) {
		global $_MagnaSession;
		/* Setzen der Currency nicht noetig, da Preisberechnungen bereits in 
		   der Ayn24SummaryView Klasse gemacht wurden.
		 */
		$settings = array_merge(array(
			'language' => getDBConfigValue($settings['marketplace'].'.lang', $_MagnaSession['mpID']),
			'itemsPerBatch' => 10,
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
			TABLE_MAGNA_AYN24_ERRORLOG,
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
		MLProduct::gi()->setOptions(array(
			'purgeVariations' => true,
		));
		
		// Set Price and Quantity settings
		MLProduct::gi()->setPriceConfig(Ayn24Helper::loadPriceSettings($this->mpID));
		MLProduct::gi()->setQuantityConfig(Ayn24Helper::loadQuantitySettings($this->mpID));
	}
	
	protected function loadVariationMatching($variationConfiguration) {
		$key = md5($variationConfiguration['MpIdentifier'].$variationConfiguration['CustomIdentifier']);
		
		if (isset($this->varMatchingCache[$key])) {
			return $this->varMatchingCache[$key];
		}
		
		// Load variation configuration
		$varConfig = @json_decode(MagnaDB::gi()->fetchOne('
			SELECT ShopVariation
			 FROM '.TABLE_MAGNA_AYN24_VARIANTMATCHING.'
			 WHERE MpId = '.$this->mpID.'
			       AND MpIdentifier="'.$variationConfiguration['MpIdentifier'].'"
			       AND CustomIdentifier="'.$variationConfiguration['CustomIdentifier'].'"
		'), true);
		
		if (empty($varConfig)) {
			return false;
		}
		
		$keys = array_keys($varConfig);
		foreach ($keys as $key) {
			$mpname = base64_decode($key);
			$newkey = $varConfig[$key]['Code'];
			$varConfig[$key]['MPName'] = $mpname;
			
			$varConfig[$newkey] = $varConfig[$key];
			unset($varConfig[$key]);
			
			// translate free text matching
			if ($varConfig[$newkey]['Kind'] == 'FreeText') {
				$trans = MagnaDB::gi()->fetchArray('
				    SELECT DISTINCT products_options_values_id AS Id, products_options_values_name As Name
				      FROM '.TABLE_PRODUCTS_OPTIONS_VALUES.'
				     WHERE language_id = "'.$this->settings['language'].'"
				           AND products_options_values_id IN ("'.implode('", "', $varConfig[$newkey]['Values']).'")
				');
				if (!empty($trans)) {
					foreach ($trans as $row) {
						$varConfig[$newkey]['Values'][$row['Id']] = ($varConfig[$newkey]['Values'][$row['Id']] == 'null')
							? 'null'
							: $row['Name'];
					}
				}
			}
		}
		
		arrayEntitiesToUTF8($varConfig);
		
		$this->varMatchingCache[$key] = $varConfig;
		
		return $this->varMatchingCache[$key];
	}
	
	protected function processVariations($propertiesRow, &$data) {
		if (empty($data['submit']['Variations'])) {
			unset($data['submit']['Variations']);
			return true;
		}
		
		if (empty($propertiesRow['VariationConfiguration'])) {
			// means submit no variations
			unset($data['submit']['Variations']);
			return true;
		}
		
		$varConfig = $this->loadVariationMatching($propertiesRow['VariationConfiguration']);
		if (empty($varConfig)) {
			$this->addToErrorLog($data['submit']['SKU'], ML_AYN24_ERROR_CHECKIN_VARIATION_CONFIG_EMPTY);
			$this->markAsFailed($propertiesRow['products_id']);
			$this->ajaxReply['ignoreErrors'] = true;
			return false;
		}
		
		/*
[0] => Array
    (
        [VariationId] => 1757
        [VariationModel] => CoolPantsS:LC:Blu
        [MarketplaceId] => ML4641_1.3_2.12
        [MarketplaceSku] => CoolPantsS:LC:Blu
        [Variation] => Array
            (
                [0] => Array
                    (
                        [NameId] => 1
                        [Name] => Größe
                        [ValueId] => 3
                        [Value] => L
                    )

                [1] => Array
                    (
                        [NameId] => 2
                        [Name] => Farbe
                        [ValueId] => 12
                        [Value] => blau
                    )

            )

        [Price] => 109.9
        [Quantity] => 1
        [Status] => 1
        [ShippingTimeId] => 1
        [ShippingTime] => ca. 3-4 Tage
        [EAN] => 
    )
-------------------------------------------
$varConfig :: Array
(
    [2] => Array
        (
            [Code] => 2
            [Kind] => FreeText
            [Values] => Array
                (
                    [10] => 10
                    [11] => 11
                    [12] => 12
                    ...
                    [49] => 49
                )

            [MPName] => Farbe
        )
-------------------------------------------
{
    "Name": "Gr\u00f6\u00dfe",
    "MPName": "Gr\u00f6\u00dfe",
    "Value": "L",
    "MPValue:" "190",
},
-------------------------------------------
*/
		
		foreach ($propertiesRow['VariationConfiguration'] as &$sBaseItem) {
			$sBaseItem = base64_decode($sBaseItem);
		}
		$data['submit']['MPVariationConfiguration'] = $propertiesRow['VariationConfiguration'];
		
		foreach ($data['submit']['Variations'] as $key => &$vItem) {
			foreach ($vItem['Variation'] as &$vSet) {
				if (!isset($varConfig[$vSet['NameId']])) {
					$msg = $this->strreplace(ML_AYN24_ERROR_CHECKIN_VARIATION_CONFIG_MISSING_NAMEID, array (
						'Attribute' => $vSet['Name'],
						'SKU' => ((getDBConfigValue('general.keytype', '0') == 'artNr')
							? $vItem['MarketplaceSku']
							: $vItem['MarketplaceId']
						),
						'MpIdentifier' => fixHTMLUTF8Entities($propertiesRow['VariationConfiguration']['MpIdentifier']),
					));
					$this->addToErrorLog($data['submit']['SKU'], $msg);
					$this->markAsFailed($propertiesRow['products_id']);
					$this->ajaxReply['ignoreErrors'] = true;
					return false;
				}
				$matching = $varConfig[$vSet['NameId']];
				
				$vSet['MPName'] = $matching['MPName'];
				
				if (!isset($matching['Values'][$vSet['ValueId']]) || ($matching['Values'][$vSet['ValueId']] == 'null')) {
					unset($data['submit']['Variations'][$key]);
					break;
				}
				$vSet['MPValue'] = $matching['Values'][$vSet['ValueId']];
			}
			
			$vItem['SKU'] = (getDBConfigValue('general.keytype', '0') == 'artNr')
				? $vItem['MarketplaceSku']
				: $vItem['MarketplaceId'];
			
			$vItem['ShippingTime'] = getDBConfigValue($this->settings['marketplace'].'.checkin.leadtimetoship', $this->mpID, 3);
			
			// if the reduced price is available here it has been enabled in the module configuration and should be used.
			if (isset($vItem['PriceReduced'])) {
				$vItem['Price'] = $vItem['PriceReduced'];
			}
			
			// remove stuff we do not want.
			foreach (array('ShippingTimeId') as $key) {
				unset($vItem[$key]);
			}
		}
		
		if (empty($data['submit']['Variations'])) {
			$this->addToErrorLog($data['submit']['SKU'], ML_AYN24_ERROR_CHECKIN_VARIATION_CONFIG_CANNOT_CALC_VARIATIONS);
			$this->markAsFailed($propertiesRow['products_id']);
			$this->ajaxReply['ignoreErrors'] = true;
			return false;
		}
		
		#echo print_m($varConfig, '$varConfig');
		#echo print_m($data['submit'], 'Submit');
		
		return true;
	}
	
	protected function appendAdditionalData($pID, $product, &$data) {
		$propertiesRow = MagnaDB::gi()->fetchRow(eecho('
			SELECT *
			  FROM ' . TABLE_MAGNA_AYN24_PROPERTIES . '
			 WHERE ' . ((getDBConfigValue('general.keytype', '0') == 'artNr') 
						? 'products_model="' . MagnaDB::gi()->escape($product['ProductsModel']) . '"'
						: 'products_id="' . $pID . '"'
					) . ' 
			       AND mpID = ' . $this->_magnasession['mpID']
		));
		#echo print_m($propertiesRow, '$propertiesRow');
		
		// Will not happen in sumbit cycle but can happen in loadProductByPId.
		if (empty($propertiesRow)) {
			$data['submit'] = array();
			$this->addToErrorLog(magnaPID2SKU($pID));
			$this->markAsFailed($pID);
			return;
		}
		$propertiesRow['VariationConfiguration'] = @json_decode($propertiesRow['VariationConfiguration'], true);
		$propertiesRow['ShippingDetails'] = @json_decode($propertiesRow['ShippingDetails'], true);
		
		if ($data['quantity'] < 0) {
			$data['quantity'] = 0;
		}
		
		// remove stuff we do not want.
		foreach (array('ProductId', 'ProductsModel', 'ManufacturerId', 'ShippingTimeId', 'DateAdded', 'LastModified') as $key) {
			unset($product[$key]);
		}
		
		// if the reduced price is available here it has been enabled in the module configuration and should be used.
		if (isset($product['PriceReduced'])) {
			$product['Price'] = $product['PriceReduced'];
		}
		
		$data['submit'] = $product;
		
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
		
		/* Falls Langbeschreibung leer, Kurzbeschreibung ebenfalls fuer Langbeschreibung verwenden. Ansonsten entfernt Ayn24
		   zu viele HTML-Tags */
		if (!isset($data['submit']['Description']) || empty($data['submit']['Description'])) {
			$data['submit']['Description'] = $data['submit']['ShortDescription'];
		}
		
		$data['submit']['ShippingTime'] = getDBConfigValue($this->settings['marketplace'].'.checkin.leadtimetoship', $this->mpID, 3);
		$data['submit']['ShippingDetails'] = $propertiesRow['ShippingDetails'];
		
		$imageWSPath = getDBConfigValue($this->settings['marketplace'].'.checkin.imagepath', $this->mpID, SHOP_URL_POPUP_IMAGES);
		$images = array();
		
		if (!empty($product['Images'])) {
			foreach($product['Images'] as $img) {
				$images[] = array('URL' => $imageWSPath.$img);
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
		
		if (!$this->processVariations($propertiesRow, $data)) {
			$data['submit'] = array();
			//$this->addToErrorLog(magnaPID2SKU($pID));
			//$this->markAsFailed($pID);
			return;
		}
		
		//echo print_m($product, '$product');
		//echo print_m($propertiesRow, '$propertiesRow');
		
		return;
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
				MagnaDB::gi()->insert(TABLE_MAGNA_AYN24_ERRORLOG, $err);
			}
		}
		
		magnaAyn24ProcessCheckinResult($result, $this->mpID);
	}

	protected function filterSelection() { }

	protected function preSubmit(&$request) {
		MagnaConnector::gi()->setTimeOutInSeconds(600);
	}

	protected function postSubmit() {		
		if ((array_key_exists('CHECKINERRORS', $this->lastResponse)
			&& is_array($this->lastResponse['CHECKINERRORS'])
			&& !empty($this->lastResponse['CHECKINERRORS']))
			|| 
			(array_key_exists('UPLOADERRORS', $this->lastResponse)
			&& is_array($this->lastResponse['UPLOADERRORS'])
			&& !empty($this->lastResponse['UPLOADERRORS']))
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
