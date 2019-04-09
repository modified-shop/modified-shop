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

class TradoriaCheckinSubmit extends MagnaCompatibleCheckinSubmit {
	
	protected $hasDbColumn = array();
    protected $ignoreErrors = true;

	public function __construct($settings = array()) {
		$settings = array_merge(array(
			'itemsPerBatch'   => 1,
			'keytype' => getDBConfigValue('general.keytype', '0'),
			'mlProductsUseLegacy' => false,
		), $settings);
		parent::__construct($settings);
		$this->summaryAddText = "<br /><br />\n" . ML_TRADORIA_UPLOAD_EXPLANATION;
		
		$this->hasDbColumn['pa.attributes_stock'] = MagnaDB::gi()->columnExistsInTable('attributes_stock', TABLE_PRODUCTS_ATTRIBUTES);
	}
	
	protected function setUpMLProduct()
	{
		parent::setUpMLProduct();

		MLProduct::gi()->setOptions(array(
			'sameVariationsToAttributes' => false,
			'purgeVariations' => true,
			'useGambioProperties' => (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties')
		));
	}
	
	protected function getItemTax($pID, $product, &$data) {
		$taxMatch = getDBConfigValue($this->marketplace.'.checkin.taxmatching', $this->mpID, array());
		if (is_array($taxMatch) && array_key_exists($product['TaxClass'], $taxMatch)) {
			return $taxMatch[$product['TaxClass']];
		}
		/* Fallback. This represents 19%. Should be make configureable in a datastructure. */
		return '1';
	}
	
	protected function prepareOwnShopCategories($pID, $product, &$data) {
		$cPath = $this->generateShopCategoryPath($pID, 'product', $this->settings['language']);
		if (empty($cPath)) {
			return;
		}
		$catIDs = array();
		$finalpaths = array();
		// merge all paths so that each category is only included once.
		foreach ($cPath as $subpath) {
			$subpath = array_values($subpath);
			// only the deepest element of the path is the category id of this product. not the entire path!
			// make it independent of sort-order.
			if (isset($subpath[0]['ParentID'])) {
				$catIDs[] = $subpath[0]['ID'];
			} else if (isset($subpath[0]['ID'])) {
				$catIDs[] = $subpath[count($subpath) - 1]['ID'];
			}
			foreach ($subpath as $c) {
				$finalpaths[$c['ID']] = $c;
			}
		}
		$finalpaths = array_values($finalpaths);
		
		$data['submit']['ShopCategory'] = $catIDs;
		$data['submit']['ShopCategoryStructure'] = $finalpaths;
	}
	
	protected function appendAdditionalData($pID, $product, &$data) {
		if ($data['Quantity'] < 0) {
			$data['Quantity'] = 0;
		}

		$data['submit']['SKU'] = magnaPID2SKU($pID);
		$data['submit']['ItemTitle'] = $product['Title'];
		$data['submit']['Price'] = $data['price'];
		$data['submit']['BasePrice'] = $product['BasePrice'];
		$data['submit']['Currency'] = $this->settings['currency'];
		$data['submit']['Quantity'] = $data['quantity'];

		if (getDBConfigValue('tradoria.strike.price.group', $this->mpID, -1) > -1) {
			$strikePriceKind = getDBConfigValue('tradoria.strike.price.kind', $this->mpID, 'OldPrice');
			$data['submit'][$strikePriceKind] = $this->simpleprice->setFinalPriceFromDB($pID, $this->mpID, 'strike')->getPrice();
		}

		if (defined('MAGNA_FIELD_PRODUCTS_EAN') && !empty($product[MAGNA_FIELD_PRODUCTS_EAN]) 
			&& getDBConfigValue(array($this->marketplace.'.checkin.ean', 'submit'), $this->mpID, true)
		) {
			$data['submit']['EAN'] = $product[MAGNA_FIELD_PRODUCTS_EAN];
		}
		$data['submit']['Description'] = str_replace('•', '&bull;', $product['Description']);// Rakuten show question mark instead of dot, solution is we change it to html code

		$manufacturerName = '';
		if ($product['ManufacturerId'] > 0) {
			$manufacturerName = (string)MagnaDB::gi()->fetchOne(
				'SELECT manufacturers_name FROM '.TABLE_MANUFACTURERS.' WHERE manufacturers_id=\''.$product['ManufacturerId'].'\''
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

		$data['submit']['ShippingTime'] = getDBConfigValue($this->marketplace.'.checkin.leadtimetoship', $this->mpID, 3);

		$imagePath = getDBConfigValue($this->marketplace.'.imagepath', $this->mpID, '');
		if (empty($imagePath)) {
			$imagePath = SHOP_URL_POPUP_IMAGES;
		}

		$images = array();
		if (!empty($product['Images'])) {
			foreach ($product['Images'] as $img) {
				$images[] = (preg_match('/http(s{0,1}):\/\//', $img) ? '' : $imagePath).$img;
			}
		}
		$data['submit']['Images'] = $images;

		if (!empty($product['products_weight'])) {
			$data['submit']['Weight'] = array(
				'Unit' => 'kg',
				'Value' => $product['products_weight'],
			);
		}

		if (isset($product['EAN'])) {
			$data['submit']['EAN'] = $product['EAN'];
		}

		if (isset($product['ShippingCost'])) {
			$data['submit']['ShippingCost'] = $product['ShippingCost'];
		}

		$data['submit']['ShippingGroup'] = getDBConfigValue($this->marketplace.'.checkin.shippinggroup', $this->mpID, '1');
		$data['submit']['IsSplit'] = 0;

		$prepare = MagnaDB::gi()->fetchRow('
			SELECT * FROM '.TABLE_MAGNA_TRADORIA_PREPARE.'
			 WHERE '.((getDBConfigValue('general.keytype', '0') == 'artNr')
				? 'products_model=\''.MagnaDB::gi()->escape($product['ProductsModel']).'\''
				: 'products_id=\''.$pID.'\''
			).' 
				   AND mpID = '.$this->_magnasession['mpID'].'
		');

		$allMatchedAttributes = json_decode($prepare['CategoryAttributes'], true);

		if (is_array($prepare)) {
			$categoryAttributes = '';
			if (!empty($prepare['CategoryAttributes'])) {
				$categoryAttributes = TradoriaHelper::gi()->convertMatchingToNameValue($allMatchedAttributes, $product);
			}

			$data['submit']['Attributes'] = $categoryAttributes;
		}

        if (!$this->getCategoryMatching($pID, $product, $data)) {
            return;
        } else {
            $data['submit']['MarketplaceCategory'] = $prepare['TopMarketplaceCategory'];
        }

        if (!$this->getTradoriaVariations($product, $data, $allMatchedAttributes)) {
            return;
        }
	}

	private function getTradoriaVariations($product, &$data, $allMatchedAttributes)
	{
		if ($this->checkinSettings['Variations'] != 'yes') {
			return true;
		}

		$matchedAttributesCodeValueId = $this->getMatchedVariationAttributesCodeValueId($allMatchedAttributes);
		$variations = array();

		// This flag is necessary for checking if master product should be sent at all and will be used in
		// pre-submit algorithm.
		$data['HasVariations'] = count($product['Variations']) > 0;

		foreach ($product['Variations'] as $v) {
			$this->simpleprice->setPrice($v['Price']);
			$price = $this->simpleprice->roundPrice()->makeSignalPrice(
				getDBConfigValue($this->marketplace.'.price.signal', $this->mpID, '')
			)->getPrice();

			$vi = array(
				'SKU' => ($this->settings['keytype'] == 'artNr') ? $v['MarketplaceSku'] : $v['MarketplaceId'],
				'Price' => $price,
				'Currency' => $this->settings['currency'],
				'Quantity' => $this->quantityLumb === false ? max(0, $v['Quantity'] - (int)$this->quantitySub) : $this->quantityLumb,
				'EAN' => $v['EAN']
			);

			$vi['ItemTitle'] = $data['submit']['ItemTitle'];
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

	protected function shouldSendShopData()
	{
		return true;
	}

	protected function setProductVariant(&$productVariant, $varAttribute, $rawAmConfiguration, $variations)
	{
		$fixCatAttributes = TradoriaHelper::gi()->convertMatchingToNameValue(
            $rawAmConfiguration,
            array("variant_{$varAttribute['NameId']}" => $varAttribute['ValueId']),
            true
        );

		if (empty($fixCatAttributes)) {
            $varAttribute['Name'] = stringToUTF8($varAttribute['Name']);
            $varAttribute['Value'] = stringToUTF8($varAttribute['Value']);
			$fixCatAttributes = array($varAttribute['Name'] => $varAttribute['Value']);
		}

		$productVariant['Variation'] = array_merge($productVariant['Variation'], $fixCatAttributes);
	}

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
	
	protected function processException($e) {
		parent::processException($e);
		
		// in case of an marketplace timeout
		if ($e->getFirstAPIErrorCode() == 'MARKETPLACE_TIMEOUT') {
			// ignore the exception
			$e->setCriticalStatus(false);
			
			// ignore the timeout...
			$this->ajaxReply['ignoreErrors'] = true;
			
			// and try again (with the same product)
			$this->ajaxReply['reprocessSelection'] = true;
			
			// and fix the counters
			$this->submitSession['state']['failed'] -= count($this->selection);
			$this->submitSession['state']['submitted'] -= count($this->selection);
			
		}
	}

	protected function generateRedirectURL($state) {
		return toURL(array(
			'mp' => $this->realUrl['mp'],
			'mode'   => ($state == 'fail') ? 'errorlog' : 'listings'
		), true);
	}
}
