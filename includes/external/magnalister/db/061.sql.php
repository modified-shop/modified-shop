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
 * (c) 2010 - 2015 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

function md_db_update_61() {
	$aEBayMpIds = MagnaDB::gi()->fetchArray("
		SELECT DISTINCT mpID
		  FROM ".TABLE_MAGNA_CONFIG."
		 WHERE mkey = 'ebay.username'
		       AND value <> ''
	", true);

	foreach ($aEBayMpIds as $sMpId) {
		// MPN - Match exists as default in modified shops
		if (   !MagnaDB::gi()->recordExists(TABLE_MAGNA_CONFIG, array('mpID' => $sMpId, 'mkey' => 'ebay.listingdetails.mpn.dbmatching.table'))
			&& MagnaDB::gi()->columnExistsInTable('products_manufacturers_model', TABLE_PRODUCTS)
		) {
			$aData = array(
				'table' => TABLE_PRODUCTS,
				'column' => 'products_manufacturers_model',
			);
			MagnaDB::gi()->insert(TABLE_MAGNA_CONFIG, array(
				'mpID' => $sMpId,
				'mkey' => 'ebay.listingdetails.mpn.dbmatching.table',
				'value' => json_encode($aData),
			));
		}

		// EAN - Match
		if (   !MagnaDB::gi()->recordExists(TABLE_MAGNA_CONFIG, array('mpID' => $sMpId, 'mkey' => 'ebay.listingdetails.ean.dbmatching.table'))
			&& MagnaDB::gi()->columnExistsInTable('products_ean', TABLE_PRODUCTS)
		) {
			$aData = array(
				'table' => TABLE_PRODUCTS,
				'column' => 'products_ean',
			);
			MagnaDB::gi()->insert(TABLE_MAGNA_CONFIG, array(
				'mpID' => $sMpId,
				'mkey' => 'ebay.listingdetails.ean.dbmatching.table',
				'value' => json_encode($aData),
			));
		}
	}
}

$functions[] = 'md_db_update_61';
