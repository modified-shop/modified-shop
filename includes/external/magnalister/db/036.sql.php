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

$queries = array();
$functions = array();
/*
2024-01-08 module deleted
$queries[] = 'CREATE TABLE IF NOT EXISTS '.TABLE_MAGNA_MEINPAKET_VARIANTMATCHING.' (
  `MpId` int(11) NOT NULL,
  `MpIdentifier` varchar(50) NOT NULL,
  `CustomIdentifier` varchar(64) NOT NULL DEFAULT "",
  `ShopVariation` text NOT NULL,
  PRIMARY KEY (`MpId`,`MpIdentifier`,`CustomIdentifier`)
) ENGINE='.DB_SERVER_ENGINE.' DEFAULT CHARSET='.DB_SERVER_CHARSET;

function magnaUpdateMeinpaketProperties() {
	MagnaDB::gi()->query('
		CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_MEINPAKET_PROPERTIES.'` (
			`mpID` int(11) NOT NULL,
			`products_id` int(11) NOT NULL,
			`products_model` varchar(64) NOT NULL,
			`MarketplaceCategory` varchar(30) NOT NULL DEFAULT "",
			`StoreCategory` varchar(255) NOT NULL DEFAULT "",
			`VariationConfiguration` varchar(255) NOT NULL DEFAULT "",
			`ShippingDetails` tinytext NOT NULL,
			PRIMARY KEY (`mpID`,`products_id`,`products_model`)
		)
	');
	
	if (MagnaDB::gi()->tableExists(TABLE_MAGNA_MEINPAKET_CATEGORYMATCHING)) {
		$data = MagnaDB::gi()->query('SELECT * FROM '.TABLE_MAGNA_MEINPAKET_CATEGORYMATCHING);
		$ok = true;
		while (($row = MagnaDB::gi()->fetchNext($data)) !== false) {
			if (MagnaDB::gi()->recordExists(TABLE_MAGNA_MEINPAKET_PROPERTIES, array (
				'mpID' => $row['mpID'],
				'products_id' => $row['products_id'],
				'products_model' => $row['products_model'],
			))) {
				continue;
			}
			$state = MagnaDB::gi()->insert(TABLE_MAGNA_MEINPAKET_PROPERTIES, array (
				'mpID' => $row['mpID'],
				'products_id' => $row['products_id'],
				'products_model' => $row['products_model'],
				'MarketplaceCategory' => $row['mp_category_id'],
				'StoreCategory' => $row['store_category_id'],
			));
			$ok = $ok && $state;
		}
		
		if ($ok) {
			// In case everything went fine drop the table. If not, keep it around so
			// we can try to migrate the data again.
			MagnaDB::gi()->query('DROP TABLE '.TABLE_MAGNA_MEINPAKET_CATEGORYMATCHING);
		}
	}
}

$functions[] = 'magnaUpdateMeinpaketProperties';
*/
