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

class HitmeisterCheckinSubmit extends MagnaCompatibleCheckinSubmit {

	protected $useShippingtimeMatching = false;
	protected $defaultShippingtime = '';
	protected $shippingtimeMatching = array();
	protected $ignoreErrors = true;

	public function __construct($settings = array()) {
		parent::__construct($settings);
		$this->summaryAddText = "<br /><br />\n".ML_HITMEISTER_UPLOAD_EXPLANATION;
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
		
		$this->defaultShippingtime  = getDBConfigValue($this->marketplace.'.shippingtime', $this->mpID, 0); 
		$this->shippingtimeMatching = getDBConfigValue($this->marketplace.'.shippingtimematching.values', $this->mpID, array()); 
		$this->useShippingtimeMatching = getDBConfigValue(array($this->marketplace.'.shippingtimematching.prefer', 'val'), $this->mpID, false); 
		
		if (!is_array($this->shippingtimeMatching) || empty($this->shippingtimeMatching)) {
			$this->useShippingtimeMatching = false;
		}
	}
	
	protected function appendAdditionalData($pID, $product, &$data) {
		if (defined('MAGNA_FIELD_PRODUCTS_EAN') && array_key_exists(MAGNA_FIELD_PRODUCTS_EAN, $product)) {
			$ean = $product[MAGNA_FIELD_PRODUCTS_EAN];
		}
		
		$defaultLocation = getDBConfigValue($this->settings['marketplace'].'.itemcountry', $this->_magnasession['mpID']);
		$defaultTitle = isset($product['products_name']) ? $product['products_name'] : '';
		$defaultSubtitle = isset($product['products_short_description']) ? $product['products_short_description'] : '';
		$defaultDescription = isset($product['products_description']) ? $product['products_description'] : '';
		
		$prepare = MagnaDB::gi()->fetchRow('
			SELECT * FROM '.TABLE_MAGNA_HITMEISTER_PREPARE.'
			 WHERE '.((getDBConfigValue('general.keytype', '0') == 'artNr')
					     ? 'products_model=\''.MagnaDB::gi()->escape($product['products_model']).'\''
					     : 'products_id=\''.$pID.'\''
					).' 
				   AND mpID = '.$this->_magnasession['mpID'].'
		');
		
		if (is_array($prepare)) {
			$categoryAttributes = (!empty($prepare['CategoryAttributes'])) ? $this->fixCategoryAttributes($prepare['CategoryAttributes'], $pID) : '';
			$data['submit']['SKU'] = magnaPID2SKU($pID);
			$data['submit']['ParentSKU'] = magnaPID2SKU($pID);
			$data['submit']['EAN'] = isset($prepare['EAN']) ? $prepare['EAN'] : $ean;
			$data['submit']['MarketplaceCategory'] = isset($prepare['MarketplaceCategories']) ? $prepare['MarketplaceCategories'] : '';
			$data['submit']['MarketplaceCategoryName'] = isset($prepare['MarketplaceCategoriesName']) ? $prepare['MarketplaceCategoriesName'] : '';
			$data['submit']['CategoryAttributes'] = $categoryAttributes;
			$data['submit']['Title'] = isset($prepare['Title']) ? $prepare['Title'] : $defaultTitle;
			$data['submit']['Subtitle'] = isset($prepare['Subtitle']) ? $prepare['Subtitle'] : $defaultSubtitle;
			$data['submit']['Description'] = isset($prepare['Description']) ? $prepare['Description'] : $defaultDescription;
			
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
			} else if (isset($product['products_allimages'])) {
				foreach($product['products_allimages'] as $image) {
					$data['submit']['Images'][] = array(
							'URL' => $imagePath . $image
						);
				}
			}
			
			$data['submit']['ShippingTime'] = isset($data['shippingtime']) && !empty($data['shippingtime'])
				? $data['shippingtime']
				: $prepare['ShippingTime'];
			if ($data['submit']['ShippingTime'] == 'm') { //fallback if old data stored
				$data['submit']['ShippingTime'] = (($this->useShippingtimeMatching)
					? $this->shippingtimeMatching[$product['products_shippingtime']]
					: isset($data['shippingtime']) && !empty($data['shippingtime'])
						? $data['shippingtime']
						: $this->defaultShippingtime
				);
			}
			$data['submit']['ConditionType'] = $prepare['ConditionType'];
			$data['submit']['Location'] = isset($prepare['Location']) ? $prepare['Location'] : $defaultLocation;
			$data['submit']['Comment'] = isset($prepare['Comment']) ? $prepare['Comment'] : '';
			$data['submit']['Matched'] = $prepare['PrepareType'] === 'Match' ? true : false;
		} else {
			$data['submit']['ShippingTime']  = isset($data['shippingtime']) && !empty($data['shippingtime'])
				? $data['shippingtime']
				: (($this->useShippingtimeMatching)
					? $this->shippingtimeMatching[$product['products_shippingtime']]
					: $this->defaultShippingtime
				);
			$data['submit']['ConditionType'] = getDBConfigValue($this->settings['marketplace'].'.itemcondition', $this->_magnasession['mpID']);
		}
		
		$data['submit']['Price'] = $data['price'];
		$data['submit']['Currency'] = $this->settings['currency'];
		$data['submit']['Quantity'] = $data['quantity'] < 0 ? 0 : $data['quantity'];
		
		$manufacturerName = '';
		if ($product['manufacturers_id'] > 0) {
			$manufacturerName = (string)MagnaDB::gi()->fetchOne(
				'SELECT manufacturers_name FROM '.TABLE_MANUFACTURERS.' WHERE manufacturers_id=\''.$product['manufacturers_id'].'\''
			);
		}
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
		
		$data['submit']['ItemTax'] = $this->getItemTax($pID, $product, $data);
		
		if (!$this->getCategoryMatching($pID, $product, $data)) {
			return;
		}
		
		if (!$this->getVariations($pID, $product, $data)) {
			return;
		}
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
	
	private function fixCategoryAttributes($categoryAttributes, $itemID) {
		$categoryAttributes = json_decode($categoryAttributes, true);
		foreach ($categoryAttributes as $key => &$categoryAttribute) {
			if ($key === 'additional_categories') {
				foreach ($categoryAttribute as $k => &$value) {
					$value = utf8_decode(urldecode($value));
					$values = explode('>', $value);
					$value = end($values);
					$values = explode(';', $value);
					$value = end($values);
					if (empty($value)) {
						unset($categoryAttribute[$k]);
					}
				}
			} elseif ($key === 'weight' && empty($categoryAttribute)) {
				$categoryAttribute = HitmeisterHelper::GetWeightFromShop($itemID);
			} elseif ($key === 'content_volume' && empty($categoryAttribute)) {
				$categoryAttribute = HitmeisterHelper::GetContentVolumeFromShop($itemID);
			}

			if (empty($categoryAttribute)) {
				unset($categoryAttributes[$key]);
			}
		}
		
		return $categoryAttributes;
	}

}
