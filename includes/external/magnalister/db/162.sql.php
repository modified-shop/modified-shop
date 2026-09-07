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
 * (c) 2010 - 2026 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

# Temu: Create prepare table for product preparation data
$queries[] = '
CREATE TABLE IF NOT EXISTS `magnalister_temu_prepare` (
    `mpID` int(11) NOT NULL,
    `products_id` int(11) NOT NULL,
    `products_model` varchar(255) NOT NULL DEFAULT \'\',
    `PrepareType` varchar(16) NOT NULL DEFAULT \'Apply\',
    `Title` varchar(255) NOT NULL DEFAULT \'\',
    `Description` text,
    `BulletPoints` text,
    `PrimaryCategory` varchar(64) NOT NULL DEFAULT \'\',
    `TopPrimaryCategory` varchar(64) NOT NULL DEFAULT \'\',
    `Price` decimal(15,4) DEFAULT NULL,
    `MsrpPrice` decimal(15,4) DEFAULT NULL,
    `MainImage` varchar(512) NOT NULL DEFAULT \'\',
    `Images` text,
    `SKU` varchar(255) NOT NULL DEFAULT \'\',
    `EAN` varchar(64) NOT NULL DEFAULT \'\',
    `ProcessingTime` int(11) DEFAULT NULL,
    `ShippingType` varchar(32) NOT NULL DEFAULT \'PARCEL\',
    `CostTemplateId` varchar(64) NOT NULL DEFAULT \'\',
    `ShipmentLimitDay` int(11) DEFAULT NULL,
    `variation_theme` text,
    `VariationThemeBlacklist` text,
    `Verified` varchar(16) NOT NULL DEFAULT \'\',
    `PrepareError` text,
    `Transferred` int(1) NOT NULL DEFAULT 0,
    `PreparedTS` datetime DEFAULT NULL,
    PRIMARY KEY (`mpID`, `products_id`, `PrepareType`),
    KEY `idx_products_model` (`products_model`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
';

# Temu: Create longtext table for large variation JSON data
$queries[] = '
CREATE TABLE IF NOT EXISTS `magnalister_temu_prepare_longtext` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `mpID` int(11) NOT NULL,
    `products_id` int(11) NOT NULL,
    `ShopVariationId` mediumtext,
    `CategoryIndependentShopVariationId` mediumtext,
    PRIMARY KEY (`id`),
    KEY `idx_mp_product` (`mpID`, `products_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
';

# Temu: Create variant matching table
$queries[] = '
CREATE TABLE IF NOT EXISTS `magnalister_temu_variantmatching` (
    `mpID` int(11) NOT NULL,
    `MpIdentifier` varchar(255) NOT NULL DEFAULT \'\',
    `ShopVariation` mediumtext NOT NULL,
    `MarketplaceVariation` text NOT NULL,
    `CustomIdentifier` varchar(255) NOT NULL DEFAULT \'\',
    PRIMARY KEY (`mpID`, `MpIdentifier`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
';

# Temu: Create categories marketplace table for category tree cache
# Schema follows v3 structure with columns expected by MarketplaceCategoryMatching base class
$queries[] = '
CREATE TABLE IF NOT EXISTS `magnalister_temu_categories_marketplace` (
    `CategoryID` varchar(50) NOT NULL DEFAULT \'0\',
    `SiteID` int(3) NOT NULL DEFAULT 0,
    `CategoryName` varchar(128) NOT NULL DEFAULT \'\',
    `CategoryPath` varchar(500) DEFAULT \'\',
    `CategoryLevel` int(3) NOT NULL DEFAULT 1,
    `ParentID` varchar(50) NOT NULL DEFAULT \'0\',
    `LeafCategory` tinyint(4) NOT NULL DEFAULT 1,
    `Selectable` tinyint(4) NOT NULL DEFAULT 0,
    `CatType` int(3) DEFAULT NULL,
    `AvailableStatus` int(3) DEFAULT NULL,
    `mpID` int(11) NOT NULL DEFAULT 0,
    `InsertTimestamp` datetime DEFAULT NULL,
    PRIMARY KEY (`CategoryID`),
    KEY `idx_parent` (`ParentID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
';
