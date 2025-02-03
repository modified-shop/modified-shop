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
 * (c) 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

function md_db_update_45_1() {
	if (!MagnaDB::gi()->columnExistsInTable('Selectable', TABLE_MAGNA_COMPAT_CATEGORIES)) {
		MagnaDB::gi()->query("TRUNCATE TABLE `".TABLE_MAGNA_COMPAT_CATEGORIES."`");
		MagnaDB::gi()->query("ALTER TABLE `".TABLE_MAGNA_COMPAT_CATEGORIES."` ADD `Selectable` ENUM( '0', '1' ) NOT NULL AFTER `LeafCategory`");
	}
	if (!MagnaDB::gi()->columnExistsInTable('CategoryPath', TABLE_MAGNA_COMPAT_CATEGORIES)) {
		MagnaDB::gi()->query("TRUNCATE TABLE `".TABLE_MAGNA_COMPAT_CATEGORIES."`");
		MagnaDB::gi()->query("ALTER TABLE `".TABLE_MAGNA_COMPAT_CATEGORIES."` ADD `CategoryPath` TEXT NOT NULL AFTER `ParentID`");
	}
	return;
}

$functions[] = 'md_db_update_45_1';

$queries[] = "CREATE TABLE IF NOT EXISTS `".TABLE_MAGNA_BEPADO_PROPERTIES."` (
  `mpID` int(8) NOT NULL,
  `products_id` int(11) NOT NULL,
  `products_model` varchar(64) NOT NULL,
  `MarketplaceCategories` text NOT NULL,
  `TopMarketplaceCategory` varchar(20) NOT NULL,
  `ShippingServiceOptions` mediumtext NOT NULL,
  `ShippingTime` int(11) NOT NULL,
  `SubmitPurchasePrice` enum('true','false') NOT NULL DEFAULT 'false',
  `Verified` enum('OK','ERROR','OPEN','EMPTY') NOT NULL DEFAULT 'OPEN',
  `PreparedTS` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  UNIQUE KEY `U_PRODUCT_ID` (`mpID`,`products_id`,`products_model`),
  KEY `mpID` (`mpID`),
  KEY `products_id` (`products_id`),
  KEY `products_model` (`products_model`)
) ENGINE=".DB_SERVER_ENGINE;

$queries[] = "DROP TABLE IF EXISTS `magnalister_meinpaket_categories`";
