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
    CREATE TABLE IF NOT EXISTS `'. TABLE_MAGNA_GOOGLESHOPPING_PREPARE .'` (
    `mpID` int(11) UNSIGNED NOT NULL,
      `products_id` int(11) NOT NULL,
      `products_model` varchar(64) NOT NULL,
      `PreparedTS` datetime NOT NULL,
      `channel` varchar(6) NOT NULL DEFAULT \'online\',
      `title` varchar(60) NULL DEFAULT NULL,
      `contentLanguage` varchar(3) NOT NULL DEFAULT \'en\',
      `offerId` varchar(255) NOT NULL,
      `targetCountry` varchar(255) NOT NULL DEFAULT \'DE\',
      `brand` varchar(255) NULL DEFAULT NULL,
      `condition` varchar(30) NOT NULL,
      `Price` text NOT NULL,
      `currency` varchar(3) NOT NULL,
      `link` text NOT NULL,
      `description` text NOT NULL,
      `availability` varchar(50) NOT NULL,
      `adult` tinyint(1),
      `Verified` enum(\'OK\',\'ERROR\',\'OPEN\',\'EMPTY\') NULL DEFAULT \'OPEN\',
      `PrepareType` enum(\'manual\',\'auto\',\'apply\') NOT NULL,
      `Primarycategory` text NOT NULL,
      `PrimaryCategoryName` text NOT NULL,
      `Image` text NOT NULL,
      `ShopVariation` text NOT NULL,
      `CustomAttributes` text NOT NULL,
      `CategoryAttributes` text NOT NULL,
      `MarketplaceCategories` varchar(255) NOT NULL,
      UNIQUE INDEX `UniqueEntry`(`mpID`, `products_id`) USING BTREE
    );
';

$queries[] = '
	CREATE TABLE IF NOT EXISTS '.TABLE_MAGNA_GOOGLESHOPPING_CATEGORIES.' (
		mpID int(11) NOT NULL,
		platform varchar(30) NOT NULL,
		CategoryID varchar(30) NOT NULL,
		CategoryName varchar(128) NOT NULL default "",
		ParentID varchar(30) NOT NULL,
		LeafCategory enum("0","1") NOT NULL default "0",
		Selectable enum("0","1") NOT NULL default "1",
		InsertTimestamp datetime NOT NULL,
		Language varchar(5) NOT NULL,
		UNIQUE KEY UniqueEntry (CategoryID, CategoryName, ParentID)
	);
';

$queries[] = '
	CREATE TABLE IF NOT EXISTS '.TABLE_MAGNA_GOOGLESHOPPING_VARIANTMATCHING.' (
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
	CREATE TABLE IF NOT EXISTS '.TABLE_MAGNA_GOOGLESHOPPING_PROPERTIES.' (
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
