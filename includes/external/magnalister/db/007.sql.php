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
 * $Id: 001.sql.php 650 2011-01-08 22:30:52Z MaW $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

# Tabellen fuer das eBay-Modul - Korrektur

$queries = array();
$functions = array();

function correct_ebay_properties_table() {
	if (! MagnaDB::gi()->columnExistsInTable('Price', TABLE_MAGNA_EBAY_PROPERTIES))
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` CHANGE COLUMN `StartPrice` `Price` decimal(15,4) NOT NULL DEFAULT 0');
	if (! MagnaDB::gi()->columnExistsInTable('StoreCategory', TABLE_MAGNA_EBAY_PROPERTIES))
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` ADD COLUMN `StoreCategory` bigint(11)  default NULL AFTER `SecondaryCategoryName`');
	if ('bigint(11)' != MagnaDB::gi()->columnType('StoreCategory', TABLE_MAGNA_EBAY_PROPERTIES)) 
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` CHANGE COLUMN `StoreCategory` `StoreCategory` bigint(11) default NULL');
	if (! MagnaDB::gi()->columnExistsInTable('StoreCategory2', TABLE_MAGNA_EBAY_PROPERTIES))
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` ADD COLUMN `StoreCategory2` bigint(11) default NULL AFTER `StoreCategory`');
	if (! MagnaDB::gi()->columnExistsInTable('Description', TABLE_MAGNA_EBAY_PROPERTIES))
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` ADD COLUMN `Description` longtext NOT NULL  AFTER `Subtitle`');
	if (! MagnaDB::gi()->columnExistsInTable('ListingType', TABLE_MAGNA_EBAY_PROPERTIES))
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` ADD COLUMN `ListingType` enum(\'Chinese\',\'FixedPriceItem\',\'StoresFixedPrice\') NOT NULL DEFAULT \'FixedPriceItem\' AFTER `Attributes`');
	if (! MagnaDB::gi()->columnExistsInTable('ListingDuration', TABLE_MAGNA_EBAY_PROPERTIES))
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` ADD COLUMN `ListingDuration` varchar(10) NOT NULL  AFTER `ListingType`');
	if (! MagnaDB::gi()->columnExistsInTable('ShippingDetails', TABLE_MAGNA_EBAY_PROPERTIES))
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` CHANGE COLUMN `ShippingServices` `ShippingDetails` longtext NOT NULL');
	if ( MagnaDB::gi()->columnExistsInTable('SKU', TABLE_MAGNA_EBAY_PROPERTIES)) {
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` DROP KEY `UC_SKU_mpID`');
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` DROP COLUMN `SKU`');
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` ADD UNIQUE KEY `UniqueEntry` (`mpID`,`products_id`,`products_model`)');
	if (! MagnaDB::gi()->columnExistsInTable('GalleryURL', TABLE_MAGNA_EBAY_PROPERTIES))
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` ADD COLUMN `GalleryURL` varchar(255) NOT NULL  AFTER `PictureURL`');
	}
	if ('varchar(80)' != MagnaDB::gi()->columnType('Title',TABLE_MAGNA_EBAY_PROPERTIES)) {
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` CHANGE COLUMN `Title` `Title` varchar(80) NOT NULL DEFAULT \'\'');
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` CHANGE COLUMN `Subtitle` `Subtitle` varchar(55) NOT NULL DEFAULT \'\'');
	}
	return;
}

$functions[] = 'correct_ebay_properties_table';

function correct_ebay_errorlog_table() {
	if (MagnaDB::gi()->columnExistsInTable('Price', TABLE_MAGNA_EBAY_ERRORLOG))
		return;
	MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_ERRORLOG.'` CHANGE COLUMN `StartPrice` `Price` decimal(15,4) NOT NULL DEFAULT 0');
	return;
}

$functions[] = 'correct_ebay_errorlog_table';

function correct_ebay_categories_table() {
	if (! MagnaDB::gi()->columnExistsInTable('StoreCategory', TABLE_MAGNA_EBAY_CATEGORIES))
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_CATEGORIES.'` ADD COLUMN `StoreCategory` enum(\'0\',\'1\') DEFAULT \'0\' AFTER `LeafCategory`');
	if ('bigint(11)' != MagnaDB::gi()->columnType('CategoryID', TABLE_MAGNA_EBAY_CATEGORIES)) {
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_CATEGORIES.'` CHANGE COLUMN `CategoryID` `CategoryID` bigint(11) NOT NULL default \'0\'');
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_CATEGORIES.'` CHANGE COLUMN `ParentID` `ParentID` bigint(11) NOT NULL default \'0\'');
	}
	return;
}

$functions[] = 'correct_ebay_categories_table';
