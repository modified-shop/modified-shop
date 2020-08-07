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
 * (c) 2010 - 2019 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

$queries[] = '
    CREATE TABLE IF NOT EXISTS `'. TABLE_MAGNA_METRO_PREPARE .'` (
    `mpID` int(11) UNSIGNED NOT NULL,
      `products_id` int(11) NOT NULL,
      `products_model` varchar(64) NOT NULL,
      `PreparedTS` datetime NOT NULL,
      `StartTime` datetime DEFAULT NULL,
      `Title` varchar(256) DEFAULT NULL,
      `Manufacturer` varchar(55) DEFAULT NULL,
      `ManufacturerPartNumber` varchar(55) DEFAULT NULL,
      `ShortDescription` text DEFAULT NULL,
      `GTIN` varchar(15) DEFAULT NULL,
      `Brand` varchar(255) DEFAULT NULL,
      `Feature` text DEFAULT NULL,
      `Description` longtext DEFAULT NULL,
      `Images` text DEFAULT NULL,
      `MSRP` decimal(15,4) DEFAULT NULL,
      `PrimaryCategory` varchar(50) DEFAULT NULL,
      `PrimaryCategoryName` varchar(128) DEFAULT NULL,
      `ShopVariation` mediumtext DEFAULT NULL,
      `VariationThemeBlacklist` text DEFAULT NULL,
      `Features` text NOT NULL,
      `ProcessingTime` int(4) NOT NULL,
      `FreightForwarding` enum("true", "false") NOT NULL,
      `ShippingProfile` int(4) NOT NULL,
      `BusinessModel` enum("ALL", "B2B", "B2C") NOT NULL,
      `noidentifierflag` varchar(10) NOT NULL,
      `Verified` enum("OK", "ERROR", "OPEN") NOT NULL,
      `Transferred` int(1) NOT NULL,
      `deletedBy` enum("empty", "Sync", "Button", "expired", "notML") NOT NULL,
      `topPrimaryCategory` varchar(64) NOT NULL,
      
      UNIQUE INDEX `UniqueEntry`(`mpID`, `products_id`) USING BTREE
    );
';

$queries[] = '
	CREATE TABLE IF NOT EXISTS '.TABLE_MAGNA_METRO_CATEGORIES.' (
		`mpID` int(11) NOT NULL,
		`platform` varchar(30) NOT NULL,
		`CategoryID` varchar(64) NOT NULL,
		`CategoryName` varchar(128) NOT NULL default "",
		`ParentID` varchar(64) NOT NULL,
		`LeafCategory` enum("0","1") NOT NULL default "0",
		`Selectable` enum("0","1") NOT NULL default "1",
		`InsertTimestamp` datetime NOT NULL,
		`Language` varchar(5) NOT NULL,
        `Fee` float,        
        `FeeCurrency` varchar(5),
		UNIQUE KEY UniqueEntry (CategoryID, CategoryName, ParentID)
	);
';

$queries[] = '
	CREATE TABLE IF NOT EXISTS '.TABLE_MAGNA_METRO_VARIANTMATCHING.' (
		  MpId int(11) NOT NULL,
		  MpIdentifier varchar(50) NOT NULL,
		  CustomIdentifier varchar(64) NOT NULL DEFAULT \'\',
		  ShopVariation text NOT NULL,
		  IsValid bit(1) NOT NULL DEFAULT b\'1\',
		  ModificationDate datetime NOT NULL,
		  PRIMARY KEY (MpId, MpIdentifier, CustomIdentifier)
	);
';

$queries[] = '
	CREATE TABLE IF NOT EXISTS '.TABLE_MAGNA_METRO_PROPERTIES.' (
		  mpID int(8) NOT NULL,
		  products_id int(11) NOT NULL,
		  products_model varchar(64) NOT NULL,
		  ShippingService int(16) NOT NULL,
		  MarketplaceCategories text NOT NULL,
		  topMarketplaceCategory int(16) NOT NULL,
		  StoreCategories text NOT NULL,
		  topStoreCategory int(16) NOT NULL,
		  ListingDuration tinyint(4) NOT NULL,
		  ProductType int(11) NOT NULL DEFAULT 0,
		  ReturnPolicy int(11) NOT NULL DEFAULT 0,
		  MpColors text NOT NULL,
		  Attributes longtext NOT NULL,
		  CategoryAttributes text NOT NULL,
		  Verified enum(\'OK\',\'ERROR\',\'OPEN\',\'EMPTY\') NOT NULL DEFAULT \'OPEN\',
		  UNIQUE INDEX U_PRODUCT_ID(mpID, products_id, products_model) USING BTREE,
		  INDEX mpID(mpID) USING BTREE,
		  INDEX products_id(products_id) USING BTREE,
		  INDEX products_model(products_model) USING BTREE
		);
';
