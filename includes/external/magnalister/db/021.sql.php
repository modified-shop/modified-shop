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
 * $Id: 021.sql.php 650 2011-01-08 22:30:52Z MaW $
 *
 * (c) 2012 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

# eBay-Modul: 
function extend_ebay_properties_table_21() {
	if (! MagnaDB::gi()->columnExistsInTable('ItemID', TABLE_MAGNA_EBAY_PROPERTIES))
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` ADD COLUMN `ItemID` varchar(12) AFTER `products_model`');
	if (! MagnaDB::gi()->columnExistsInTable('PreparedTS', TABLE_MAGNA_EBAY_PROPERTIES))
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` ADD COLUMN `PreparedTS` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\' AFTER `ItemID`');
	if (! MagnaDB::gi()->columnExistsInTable('deletedBy', TABLE_MAGNA_EBAY_PROPERTIES))
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` ADD COLUMN `deletedBy` ENUM(\'empty\', \'Sync\', \'Button\',  \'expired\', \'notML\') DEFAULT \'empty\' AFTER `Transferred`');
	if (! MagnaDB::gi()->columnExistsInTable('StartTime', TABLE_MAGNA_EBAY_PROPERTIES))
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` ADD COLUMN `StartTime` DATETIME DEFAULT NULL AFTER `PreparedTS`');
	if (! MagnaDB::gi()->columnExistsInTable('HitCounter', TABLE_MAGNA_EBAY_PROPERTIES))
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` ADD COLUMN `HitCounter` ENUM(\'NoHitCounter\', \'BasicStyle\', \'RetroStyle\', \'HiddenStyle\') NOT NULL DEFAULT \'NoHitCounter\'AFTER `BestOfferEnabled`');
	return;
}

$functions[] = 'extend_ebay_properties_table_21';

# eBay-Modul - KType-Tabelle fuer KFZ-Haendler
$queries[] = '
    CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_TECDOC.'` (
        `products_id` int(11) NOT NULL,
        `products_model` varchar(64) NOT NULL,
        `KType` int(11) unsigned NOT NULL DEFAULT 0,
        `CompatibilityNotes` varchar(255) NOT NULL DEFAULT \'\',
        PRIMARY KEY (`products_id`, `products_model`, `KType`)
    ) ENGINE='.DB_SERVER_ENGINE.';
';
