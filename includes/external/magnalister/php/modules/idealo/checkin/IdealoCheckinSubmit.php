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
 * $Id: IdealoCheckinSubmit.php 2179 2013-01-29 11:48:23Z michael.garbs $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/ComparisonShopping/ComparisonShoppingCheckinSubmit.php');

class IdealoCheckinSubmit extends ComparisonShoppingCheckinSubmit {

	protected $quantitySub = false;
	protected $quantityLumb = false;

	public function __construct($settings = array()) {
		parent::__construct($settings);

		$stockSetting = getDBConfigValue($this->marketplace.'.quantity.type', $this->mpID);
		if ($stockSetting == 'stocksub') {
			$this->quantitySub = -getDBConfigValue(
				$this->marketplace.'.quantity.value',
				$this->mpID,
				0
			);
			$this->quantityLumb = false;
		} else if ($stockSetting == 'lump') {
			$this->quantitySub = false;
			$this->quantityLumb = getDBConfigValue(
				$this->marketplace.'.quantity.value',
				$this->mpID,
				0
			);
		}
	}
	
	public function getcategoriesname($pID) {
		$catnames = array();
		$i = 0;
		
		// Maximale Kategorientiefe, bis zu der der Name der Ueberkategorie geholt wird. Kein von Idealo vorgegebener Wert, kann nach
		// persoenlichem Ermessen geaendert werden (aber nicht weglassen wg. moeglichem infinite loop!)
		$maxcatlevel = 4;

		$lang = (string)getDBConfigValue('idealo.lang', $this->mpID, 2);
		
		$catdata = MagnaDB::gi()->fetchRow('
			SELECT p.categories_id, c.parent_id
			  FROM '.TABLE_PRODUCTS_TO_CATEGORIES.' p
			  JOIN categories c ON p.categories_id = c.categories_id
			 WHERE products_id = '.$pID.'
			 LIMIT 1
		');
		$parentid = $catdata['parent_id'];
		$catnames[] = $catdata['categories_id'];

		while (($parentid != 0) && ($i < $maxcatlevel)) {
			$catdata = MagnaDB::gi()->fetchRow('
				SELECT categories_id, parent_id
				  FROM categories
				 WHERE categories_id = '.$parentid.'
				 LIMIT 1
			');
			$catnames[] = $catdata['categories_id'];
			$parentid = $catdata['parent_id'];
			++$i;
		}
		$catstring = '';
		$catnames = array_reverse($catnames);
		foreach ($catnames as $value) {
			if (!empty($value)) {
				$cName = MagnaDB::gi()->fetchOne('
					SELECT categories_name 
					  FROM categories_description
					 WHERE categories_id = '.$value.'
					AND language_id = "'.$lang.'"
					 LIMIT 1
				');
				if (empty($catstring)) {
					$catstring = $cName;
				} else {
					$catstring .= ' > '.$cName;
				}
			}
		}
		return $catstring;
	}
		
	protected function appendAdditionalData($pID, $product, &$data) {
		parent::appendAdditionalData($pID, $product, $data);

		$aPropertiesRow = MagnaDB::gi()->fetchRow('
				SELECT * FROM ' . TABLE_MAGNA_IDEALO_PROPERTIES . '
				 WHERE ' . (getDBConfigValue('general.keytype', '0') == 'artNr'
				? 'products_model = "' . MagnaDB::gi()->escape($product['products_model']) . '"'
				: 'products_id = "' . $pID . '"'
			) . '
					   AND mpID = ' . $this->_magnasession['mpID']
		);

		if (!empty($aPropertiesRow['Title'])) {
			$data['submit']['ItemTitle'] = $aPropertiesRow['Title'];
		}

		if (!empty($aPropertiesRow['Description'])) {
			$data['submit']['Description'] = $aPropertiesRow['Description'];
		}

		if (!empty($aPropertiesRow['PictureUrl'])) {
			$imagePath = getDBConfigValue($this->marketplace . '.imagepath', $this->_magnasession['mpID'], SHOP_URL_POPUP_IMAGES);
			$imagePath = trim($imagePath, '/ ').'/';
			$data['submit']['Image'] = array();
			$pictureUrls = json_decode($aPropertiesRow['PictureUrl']);
			foreach ($pictureUrls as $image => $use) {
				if ($use == 'true') {
					$data['submit']['Image'][] = array(
						'URL' => $imagePath . $image
					);
				}
			}
		} else {
			$data['submit']['Image'][] = array(
				'URL' => $data['submit']['Image']
			);
		}

		if (!empty($aPropertiesRow['Checkout'])) {
			$checkout = json_decode($aPropertiesRow['Checkout'], true);
			$data['submit']['Checkout'] = $checkout['val'];
		} else {
			$data['submit']['Checkout'] = false;
		}

		if ($data['submit']['Checkout']) {
			if (!empty($aPropertiesRow['PaymentMethod']) && $aPropertiesRow['PaymentMethod'] !== 'noselection') {
				$data['submit']['PaymentMethod'] = $aPropertiesRow['PaymentMethod'];
			}

			if (!empty($aPropertiesRow['ShippingMethod']) && $aPropertiesRow['ShippingMethod'] !== 'noselection') {
				$data['submit']['ShippingMethod'] = $aPropertiesRow['ShippingMethod'];
			}

			if (!empty($aPropertiesRow['ShippingCountry'])) {
				$country = MagnaDB::gi()->fetchOne('
					SELECT countries_iso_code_2 FROM ' . TABLE_COUNTRIES . '
					WHERE countries_id = ' . $aPropertiesRow['ShippingCountry'] . ' 
				');

				$data['submit']['ShippingCountry'] = $country;
			}
		}

		if (!empty($aPropertiesRow['ShippingCostMethod'])) {
			if (!empty($aPropertiesRow['ShippingCost']) && (float)$aPropertiesRow['ShippingCost'] > 0
				&& $aPropertiesRow['ShippingCostMethod'] === '__ml_lump') {
				$data['submit']['ShippingCost'] = $aPropertiesRow['ShippingCost'];
			} else if ($aPropertiesRow['ShippingCostMethod'] === '__ml_weight') {
				$data['submit']['ShippingCost'] = $data['submit']['ItemWeight'];
			}
		}
		
		$data['submit']['Quantity'] = $product['products_quantity'];
		$catname = $this->getcategoriesname($product['products_id']);
		if (!empty($catname)) {
			$data['submit']['MerchantCategory'] = $catname;
		}

		if (!$this->getVariations($pID, $product, $data)) {
			return;
		}
	}

	protected function getVariations($pID, $product, &$data) {
		require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/VariationsCalculator.php');
		$vc = new VariationsCalculator();
		$d = $vc->getVariationsByPIDFromDB($pID, true, $this->settings['language']);
		if (empty($d)) {
			return true;
		}
		arrayEntitiesToUTF8($d);

		$variations = array();
		foreach ($d as $v) {
			$vi = array (
				'SKU' => $v[mlGetVariationSkuField()],
				'Price' => $this->calcVariationPrice($data['submit']['Price'], $v['variation_price'], 19),
				'Quantity' => ($this->quantityLumb === false)
					? max(0, $v['variation_quantity'] - (int)$this->quantitySub)
					: $this->quantityLumb,
				'EAN' => $v['variation_ean'],
			);

			if (!empty($v['variation_unit_of_measure']) && !empty($v['variation_volume'])
				&& (!isset($product['products_vpe_status']) || ($product['products_vpe_status'] == '1'))
			) {
				$vi['BasePrice'] = array (
					'Unit' => $v['variation_unit_of_measure'],
					'Value' => $v['variation_volume'],
				);
			}

			$vi['ItemWeight'] = (int)$data['submit']['ItemWeight'] + (int)$v['variation_weight'] ;
			$vi['ItemTitle'] = $data['submit']['ItemTitle'];
			foreach ($v['variation_attributes_text'] as $attribute) {
				$vi['ItemTitle'] .= ', ' . $attribute['Group'] . ': ' . $attribute['Value'];
			}

			$variations[] = $vi;
		}

		if (!empty($variations)) {
			$data['submit']['Variations'] = $variations;
		}

		return true;
	}

    protected function preSubmit(&$request) {
        $request['DATA'] = array();

        foreach ($this->selection as $iProductId => &$aProduct) {
            if (isset($aProduct['submit']['Variations']) === false || empty($aProduct['submit']['Variations'])) {
                $request['DATA'][] = $aProduct['submit'];
                continue;
            }

            foreach ($aProduct['submit']['Variations'] as $aVariation) {
                $aVariationData = $aProduct;
                unset($aVariationData['submit']['Variations']);

                foreach ($aVariation as $sParameter => $mParameterValue) {
                    $aVariationData['submit'][$sParameter] = $mParameterValue;
                }

                $request['DATA'][] = $aVariationData['submit'];
            }
        }

        arrayEntitiesToUTF8($request['DATA']);
    }

	protected function calcVariationPrice($price, $offset, $tax) {
		if ($offset == 0) {
			return $price;
		}

		$this->simpleprice->setPrice($price);

		$offset = $offset + $offset / 100 * $tax;
		$this->simpleprice->addLump($offset);

		/*
		if (getDBConfigValue($this->marketplace.'.price.addkind', $this->mpID) == 'percent') {
			$this->simpleprice->addTax((float)getDBConfigValue(
				$this->marketplace.'.price.factor', $this->mpID
			));
		} else if (getDBConfigValue($this->marketplace.'.price.addkind', $this->mpID) == 'addition') {
			$this->simpleprice->addLump((float)getDBConfigValue(
				$this->marketplace.'.price.factor', $this->mpID
			));
		}
		*/
		return $this->simpleprice->roundPrice()->makeSignalPrice(
			getDBConfigValue($this->marketplace.'.price.signal', $this->mpID, '')
		)->getPrice();
	}
}
