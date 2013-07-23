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

# Tabellen fuer das eBay-Modul - Erweiterung

$queries = array();
$functions = array();

function extend_ebay_properties_table() {
	if (! MagnaDB::gi()->columnExistsInTable('StoreCategory', TABLE_MAGNA_EBAY_PROPERTIES))
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` ADD COLUMN `StoreCategory` bigint(11)  default NULL AFTER `SecondaryCategoryName`');
	if ('bigint(11)' != MagnaDB::gi()->columnType('StoreCategory', TABLE_MAGNA_EBAY_PROPERTIES)) 
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` CHANGE COLUMN `StoreCategory` `StoreCategory` bigint(11) default NULL');
	if (! MagnaDB::gi()->columnExistsInTable('StoreCategory2', TABLE_MAGNA_EBAY_PROPERTIES))
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` ADD COLUMN `StoreCategory2` bigint(11) default NULL AFTER `StoreCategory`');
	if ('varchar(80)' != MagnaDB::gi()->columnType('Title',TABLE_MAGNA_EBAY_PROPERTIES)) {
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` CHANGE COLUMN `Title` `Title` varchar(80) NOT NULL DEFAULT \'\'');
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` CHANGE COLUMN `Subtitle` `Subtitle` varchar(55) NOT NULL DEFAULT \'\'');
	}
	return;
}

$functions[] = 'extend_ebay_properties_table';
