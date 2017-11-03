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

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/ComparisonShopping/ComparisonShoppingCheckinSubmit.php');

class YategoCheckinSubmit extends ComparisonShoppingCheckinSubmit {
	protected function appendAdditionalData($pID, $product, &$data) {
		parent::appendAdditionalData($pID, $product, $data);
		#echo sprintf("<pre>%4d [cp: %5.2f;  up: %5.2f]</pre>", $pID, $data['submit']['Price'], $data['price']);
		if (getDBConfigValue('yatego.submit.shopurl', $this->_magnasession['mpID'], 'true') == 'true') {
			/* Unveraenderter Preis != Eingegebener Preis */
			if ($data['submit']['Price'] != $data['price']) {
				unset($data['submit']['ItemUrl']);
			}
		} else {
			unset($data['submit']['ItemUrl']);
		}
		$data['submit']['Price'] = $data['price'];
		
		unset($data['submit']['Image1']);
		$imageFSPath = SHOP_FS_POPUP_IMAGES;
		$imageWSPath = SHOP_URL_POPUP_IMAGES;
		$images = array();
		
		if (!empty($product['Images'])) {
			foreach($product['Images'] as $img) {
				if (file_exists($imageFSPath.$img)) {
					$images[] = $imageWSPath.$img;
				}
			}
		}
		$data['submit']['Images'] = $images;

		$cPaths = MLProduct::gi()->getCategoryPath($pID, 'product');
		$data['submit']['YategoCategories'] = array();
		if (!empty($cPaths)) {
			foreach ($cPaths as $cPath) {
				foreach ($cPath as $cID) {
					$yIDs = MagnaDB::gi()->fetchArray('
						SELECT yatego_category_id 
						  FROM '.TABLE_MAGNA_YATEGO_CATEGORYMATCHING.'
						 WHERE category_id=\''.$cID.'\' AND mpID=\''.$this->_magnasession['mpID'].'\'', true
					);
					if (!empty($yIDs)) {
						$data['submit']['YategoCategories'] = $yIDs;
						break;
					}
				}
				if (!empty($data['submit']['YategoCategories'])) {
					break;
				}
			}
		}
		
		$data['submit']['ShortDescription']	= short_str(
			str_replace(
				array("\r\n", "\r", "\n"), "\\n", 
				sanitizeProductDescription($product['ShortDescription'])
			), 130
		);
		$data['submit']['Description']	= str_replace(
			array("\r\n", "\r", "\n"), "\\n", 
			sanitizeProductDescription(
				$product['Description'],
				'<a><b><i><u><p><br><hr><h1><h2><h3><h4><h5><h6><ul><ol><li><span><font>'.
				'<table><thead><tbody><tfoot><tr><td><th><colgroup><col>',
				'_keep_all_'
			)
		);

		$tax = $this->simpleprice->getTaxByClassID($product['TaxClass']);

		if(($tax - (int)$tax) > 0) {
			$decimalPlaces = 2;
		} else {
			$decimalPlaces = 0;
		}
		$taxStr = number_format($tax, $decimalPlaces, '.', '');

		$data['submit']['ItemTax']  = ($taxStr != '0') ? $taxStr : '0';
		$data['submit']['Quantity'] = $data['quantity'];
		
		if (empty($data['submit']['Manufacturer'])) {
			$data['submit']['Manufacturer'] = getDBConfigValue(
				'yatego.checkin.manufacturerfallback',
				$this->_magnasession['mpID'],
				''
			);
		}
		$mfrmd = getDBConfigValue('yatego.checkin.manufacturerpartnumber.table', $this->_magnasession['mpID'], false);
		if (is_array($mfrmd) && !empty($mfrmd['column']) && !empty($mfrmd['table'])) {
			$pIDAlias = getDBConfigValue('yatego.checkin.manufacturerpartnumber.alias', $this->_magnasession['mpID']);
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
		 if (isset($product['BasePrice']) && !empty($product['BasePrice'])) {
			$data['submit']['BasePrice'] = $product['BasePrice'];
		}
	}

	protected function generateErrorSaveArray(&$data) {
		return array_merge(
			array (
				'quantity' => $data['submit']['Quantity'],
				'price' => $data['submit']['Price'],
				'currency' => $data['submit']['Currency'],
			),
			parent::generateErrorSaveArray($data)
		);
	}

	protected function postSubmit() {
		parent::postSubmit();
		
		try {
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'UploadItems',
			));

		} catch (MagnaException $e) {
			$this->submitSession['api']['exception'] = $e;
			$this->submitSession['api']['html'] = MagnaError::gi()->exceptionsToHTML();			
		}
	}
	
	protected function filterSelection() {
		foreach ($this->selection as $pID => &$data) {
			if ((int)$data['submit']['Quantity'] == 0) {
				unset($this->selection[$pID]);
				$this->disabledItems[] = $pID;
			}
		}
		return parent::filterSelection();
	}

	protected function getFinalDialogs() {
		return array();
	}
}
