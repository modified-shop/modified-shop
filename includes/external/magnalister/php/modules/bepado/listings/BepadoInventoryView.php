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

class BepadoInventoryView extends MagnaCompatibleInventoryView {
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
				'Sorter' => 'title',
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
				'Label' => ML_LABEL_QUANTITY,
				'Sorter' => 'quantity',
				'Getter' => null,
				'Field' => 'Quantity',
			),
			'DateAdded' => array (
				'Label' => ML_GENERIC_CHECKINDATE,
				'Sorter' => 'dateadded',
				'Getter' => 'getDateAdded',
				'Field' => null
			),
			'LastModified' => array (
				'Label' => 'Letzte Synchronisation',
				'Sorter' => 'lastmodified',
				'Getter' => 'getLastModified',
				'Field' => null
			),
		);
	}
	
	protected function getDateAdded($item) {
		$item['DateAdded'] = strtotime($item['DateAdded']);
		return '<td>'.date("d.m.Y", $item['DateAdded']).' &nbsp;&nbsp;<span class="small">'.date("H:i", $item['DateAdded']).'</span>'.'</td>';
	}

	protected function getLastModified($item) {
		$item['LastModified'] = strtotime($item['LastModified']);
		if ($item['LastModified'] < 0) {
			return '<td>-</td>';
		}
		return '<td>'.date("d.m.Y", $item['LastModified']).' &nbsp;&nbsp;<span class="small">'.date("H:i", $item['LastModified']).'</span>'.'</td>';
	}
}
