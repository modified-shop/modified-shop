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
 * $Id: InventoryView.php 1224 2011-09-06 00:28:04Z derpapst $
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/listings/MagnaCompatibleInventoryView.php');

class HitmeisterInventoryView extends MagnaCompatibleInventoryView {

	protected function getFields() {
		return array(
			'SKU' => array (
				'Label' => ML_LABEL_SKU,
				'Sorter' => 'sku',
				'Getter' => null,
				'Field' => 'SKU'
			),
			'EAN' => array (
				'Label' => ML_LABEL_EAN,
				'Sorter' => null,
				'Getter' => 'getEANLink',
				'Field' => null,
			),
			'Title' => array (
				'Label' => ML_LABEL_SHOP_TITLE,
				'Sorter' => null,
				'Getter' => 'getTitle',
				'Field' => null,
 			),
 			'Price' => array (
 				'Label' => ML_GENERIC_PRICE,
 				'Sorter' => 'price',
 				'Getter' => 'getItemPrice',
 				'Field' => null
 			),
 			'Quantity' => array (
				'Label' => ML_STOCK_SHOP_STOCK_HITMEISTER,
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
		);
	}

	protected function getEANLink($item) {
		return '<td><a href="http://www.hitmeister.de/item/search/?search_value='.$item['EAN'].'" target="_blank">'.$item['EAN'].'</a></td>';
	}

	protected function getQuantities($item) {
		$shopQuantity = (int)MagnaDB::gi()->fetchOne('SELECT products_quantity
			FROM '.TABLE_PRODUCTS.'
			 WHERE products_id = '.magnaSKU2pID($item['SKU']));
		return '<td>'.$shopQuantity.' / '.$item['Quantity'].'</td>';
	}

}
