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
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/listings/MagnaCompatibleInventoryView.php');

class CdiscountInventoryView extends MagnaCompatibleInventoryView {

	protected function getFields() {
		return array(
			'SKU' => array (
				'Label' => ML_LABEL_SKU,
				'Sorter' => 'sku',
				'Getter' => null,
				'Field' => 'SKU'
			),
            'Title' => array (
                'Label' => ML_LABEL_SHOP_TITLE,
				'Sorter' => null,
				'Getter' => 'getTitle',
				'Field' => null,
 			),
			'MarketplaceTitle' => array (
				'Label' => ML_CDISCOUNT_LABEL_TITLE,
				'Sorter' => 'title',
				'Getter' => 'getMarketplaceTitle',
				'Field' => null,
			),
            'EAN' => array (
                'Label' => ML_LABEL_EAN,
                'Sorter' => 'ean',
                'Getter' => 'getEANLink',
                'Field' => null,
            ),
 			'Price' => array (
 				'Label' => ML_CDISCOUNT_LABEL_PRICE,
 				'Sorter' => 'price',
 				'Getter' => 'getItemPrice',
 				'Field' => null
 			),
 			'Quantity' => array (
				'Label' => ML_STOCK_SHOP_STOCK_CDISCOUNT,
				'Sorter' => 'quantity',
				'Getter' => 'getQuantities',
				'Field' => null,
			),
 			'DateAdded' => array (
 				'Label' => ML_GENERIC_CHECKINDATE,
 				'Sorter' => 'dateadded',
 				'Getter' => 'getItemDateAdded',
 				'Field' => null
 			),
			'Status' => array(
				'Label' => ML_CDISCOUNT_INVENTORY_STATUS,
 				'Sorter' => 'status',
 				'Getter' => 'getStatus',
 				'Field' => null
			),
			'IsSplit' => array(
				'Label' => ML_GENERAL_INVENTORY_IS_SPLIT,
				'Sorter' => 'isSplit',
				'Getter' => 'isSplit',
				'Field' => null
			),
		);
	}

	protected function getEANLink($item) {
		if (empty($item['EAN'])) {
			return '<td>&mdash</td>';
		}

		if (empty($item['CdiscountSKU'])) {
			return '<td>' . $item['EAN'] . '</td>';
		} else {
            return '<td><a href="http://www.cdiscount.com/search/'.$item['CdiscountSKU'].'.html" target="_blank">'.$item['EAN'].'</a></td>';
		}
	}

	protected function getQuantities($item)
	{
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$where = 'sku';
		} else {
			$where = 'id';
		}

		$shopQuantity = (int)MagnaDB::gi()->fetchOne("
			SELECT variation_quantity
			FROM " . TABLE_PRODUCTS_VARIATIONS . "
			WHERE marketplace_" . $where . " = '" . $item['SKU'] . "'
		");

		if (!$shopQuantity) {
			$shopQuantity = (int)MagnaDB::gi()->fetchOne("
				SELECT products_quantity
				  FROM " . TABLE_PRODUCTS . "
				 WHERE products_id = '" . magnaSKU2pID($item['SKU']) . "'
			");
		}

		if (!$shopQuantity) {
			$shopQuantity = '-';
		}

		return '<td>' . $shopQuantity . ' / ' . $item['Quantity'] . '</td>';
	}
	
	protected function postDelete() {
		MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'UploadItems'
		));
	}

	protected function getStatus($item) {		
		if (isset($item['StatusProduct']) === false) {
			$status = '-';
		} else if ($item['StatusProduct'] === 'Active' && $item['StatusOffer'] === 'Active') {
			$status = ML_CDISCOUNT_INVENTORY_STATUS_ACTIVE;
		} else if ($item['StatusProduct'] === 'UpdateItem' || $item['StatusProduct'] === 'WaitingUpdateItem') {
			$status = ML_CDISCOUNT_INVENTORY_STATUS_PENDING_UPDATE;
		} else {
			$status = ML_CDISCOUNT_INVENTORY_STATUS_PENDING_NEW;
		}
		
		return '<td>' . $status . '</td>';
	}

	protected function isSplit($item)
	{
		return '<td>' . (empty($item['IsSplit']) ? ML_BUTTON_LABEL_NO : ML_BUTTON_LABEL_YES) . '</td>';
	}

}
