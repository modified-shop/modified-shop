<?php
/*
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
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

$queries[] = '
    CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_OTTO_PREPARE.'` (
        `mpID` int(11) UNSIGNED NOT NULL, 
        `products_id` int(11) NOT NULL, 
        `products_model` varchar(64) NOT NULL,
        `PreparedTS` datetime NOT NULL, 
        `StartTime` datetime DEFAULT NULL,  
        `Description` longtext DEFAULT NULL, 
        `Images` text DEFAULT NULL, 
        `PrimaryCategory` varchar(50) DEFAULT NULL, 
        `PrimaryCategoryName` varchar(128) DEFAULT NULL, 
        `ShopVariation` mediumtext DEFAULT NULL, 
        `CategoryIndependentShopVariation` text DEFAULT NULL,
        `VariationThemeBlacklist` text DEFAULT NULL, 
        `DeliveryType` enum ("PARCEL", "FORWARDER_PREFERREDLOCATION", "FORWARDER_CURBSIDE") NOT NULL,
        `DeliveryTime` tinyint(4) NOT NULL,
        `noidentifierflag` varchar(10) NOT NULL, 
        `Verified` enum("OK", "ERROR", "OPEN") NOT NULL,
        `Transferred` int(1) NOT NULL, 
        `deletedBy` enum("empty", "Sync", "Button", "expired", "notML") NOT NULL,
        `topPrimaryCategory` varchar(64) NOT NULL,

        UNIQUE INDEX `UniqueEntry`(`mpID`, `products_id`) USING BTREE
    );
';

$queries[] = '
    CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_OTTO_CATEGORIES_MARKETPLACE.'` (
        `CategoryID` varchar(64) NOT NULL,
        `CategoryName` varchar(128) NOT NULL default "",
        `ParentID` varchar(64) NOT NULL,
        `LeafCategory` tinyint  NOT NULL default 0,
        `Expires` datetime DEFAULT NULL
    );
';

$queries[] = '
    CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_OTTO_VARIANTMATCHING.'` (
        `MpId` int(11) NOT NULL,
        `MpIdentifier` varchar(50) NOT NULL,
        `CustomIdentifier` varchar(64) NOT NULL DEFAULT \'\',
        `ShopVariation` text NOT NULL,
        `IsValid` bit(1) NOT NULL DEFAULT b\'1\',
        `ModificationDate` datetime NOT NULL,
        
        PRIMARY KEY (MpId, MpIdentifier, CustomIdentifier)
    );
';

