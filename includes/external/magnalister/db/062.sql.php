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

function ml_db_update_replaceOldHitmeisterTableWithNew() {
	if (MagnaDB::gi()->columnExistsInTable('is_porn', TABLE_MAGNA_HITMEISTER_PREPARE)) {
		MagnaDB::gi()->query("DROP TABLE IF EXISTS ".TABLE_MAGNA_HITMEISTER_PREPARE);
	}
	MagnaDB::gi()->query("
		CREATE TABLE IF NOT EXISTS `".TABLE_MAGNA_HITMEISTER_PREPARE."` (
			`mpID` int(8) NOT NULL,
			`products_id` int(11) NOT NULL,
			`products_model` varchar(64) NOT NULL,
			`EAN` VARCHAR(30) DEFAULT NULL,
			`MarketplaceCategoriesName` TEXT DEFAULT NULL,
			`MarketplaceCategories` INT(11) DEFAULT NULL,
			`CategoryAttributes` text NOT NULL,
			`Title` varchar(40) DEFAULT NULL,
			`Subtitle` varchar(60) DEFAULT NULL,
			`Description` text DEFAULT NULL,
			`PictureUrl` text DEFAULT NULL,
			`ConditionType` VARCHAR(60) DEFAULT NULL,
			`ShippingTime` char(1) NOT NULL,
			`Location` char(2) NOT NULL,
			`Comment` text DEFAULT NULL,
			`PrepareType` enum('Apply', 'Match') NOT NULL,
			`Verified` enum('OK','ERROR','OPEN','EMPTY') NOT NULL DEFAULT 'OK',
			`PreparedTS` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY (`mpID`,`products_id`,`products_model`)
		) ENGINE=".DB_SERVER_ENGINE." DEFAULT CHARSET=".DB_SERVER_CHARSET."
	");
}
$functions[] = 'ml_db_update_replaceOldHitmeisterTableWithNew';

function md_db_update_47_1() {
	if (MagnaDB::gi()->columnExistsInTable('MarketplaceCategoriesID', TABLE_MAGNA_HITMEISTER_PREPARE)) {
		MagnaDB::gi()->query("ALTER TABLE `".TABLE_MAGNA_HITMEISTER_PREPARE."` CHANGE COLUMN `MarketplaceCategoriesID` `MarketplaceCategoriesName` TEXT DEFAULT NULL");
	}
}
$functions[] = 'md_db_update_47_1';

function md_db_update_47_2() {
	if (MagnaDB::gi()->columnExistsInTable('MarketplaceCategories', TABLE_MAGNA_HITMEISTER_PREPARE)) {
		MagnaDB::gi()->query("ALTER TABLE `".TABLE_MAGNA_HITMEISTER_PREPARE."` CHANGE COLUMN `MarketplaceCategories` `MarketplaceCategories` INT(11) DEFAULT NULL");
	}
}
$functions[] = 'md_db_update_47_2';

function md_db_update_47_3() {
	if (MagnaDB::gi()->columnExistsInTable('ConditionType', TABLE_MAGNA_HITMEISTER_PREPARE)) {
		MagnaDB::gi()->query("ALTER TABLE `".TABLE_MAGNA_HITMEISTER_PREPARE."` CHANGE COLUMN `ConditionType` `ConditionType` VARCHAR(60) DEFAULT NULL");
	}
}
$functions[] = 'md_db_update_47_3';

function md_db_update_47_4() {
	if (!MagnaDB::gi()->columnExistsInTable('EAN', TABLE_MAGNA_HITMEISTER_PREPARE)) {
		MagnaDB::gi()->query("ALTER TABLE `".TABLE_MAGNA_HITMEISTER_PREPARE."` ADD COLUMN `EAN` VARCHAR(30) DEFAULT NULL AFTER `products_model`");
	}
}
$functions[] = 'md_db_update_47_4';

function md_db_update_47_5() {
	if (!MagnaDB::gi()->columnExistsInTable('Verified', TABLE_MAGNA_HITMEISTER_PREPARE)) {
		MagnaDB::gi()->query("ALTER TABLE `".TABLE_MAGNA_HITMEISTER_PREPARE."` ADD COLUMN `Verified` enum('OK','ERROR','OPEN','EMPTY') NOT NULL DEFAULT 'OK' AFTER `PrepareType`");
	}
}
$functions[] = 'md_db_update_47_5';
