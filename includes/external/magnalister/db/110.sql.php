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
 * (c) 2018 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

function ml_db_update_110() {
    MagnaDB::gi()->query("
        CREATE TABLE IF NOT EXISTS `".TABLE_MAGNA_ETSY_PREPARE."` (
            `mpID` int(8) NOT NULL,
            `products_id` int(11) NOT NULL,
            `products_model` varchar(64) NOT NULL,
            `PreparedTS` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `Verified` enum('OK','ERROR','OPEN','EMPTY') NOT NULL DEFAULT 'OK',
            `Title` varchar(255) DEFAULT NULL,
            `Description` text DEFAULT NULL,
            `Primarycategory` varchar(31) NOT NULL,
            `ShopVariation` text NOT NULL,
            `ShippingTemplate` varchar(127) NOT NULL,
            `Whomade` varchar(15) NOT NULL,
            `Whenmade` varchar(15) NOT NULL,
            `IsSupply` enum('false', 'true') NOT NULL DEFAULT 'false',
            `Image` text DEFAULT NULL,
            PRIMARY KEY (`mpID`,`products_id`,`products_model`)
        ) ENGINE=".DB_SERVER_ENGINE." DEFAULT CHARSET=".DB_SERVER_CHARSET."
    ");

    MagnaDB::gi()->query('
        CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_ETSY_VARIANTMATCHING.'` (
            `MpId` int(11) NOT NULL,
            `MpIdentifier` varchar(50) NOT NULL,
            `CustomIdentifier` varchar(64) NOT NULL DEFAULT "",
            `ShopVariation` text NOT NULL,
            `IsValid` bit NOT NULL DEFAULT 1,
            `ModificationDate` DATETIME NOT NULL DEFAULT \'0000-00-00 00:00:00\',
        PRIMARY KEY (`MpId`, `MpIdentifier`, `CustomIdentifier`))
    ');

    MagnaDB::gi()->query('
        CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_ETSY_CATEGORIES.'` (
            `CategoryID` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'0\',
            `Language` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'\',
            `CategoryName` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'\',
            `ParentID` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT \'0\',
            `LeafCategory` tinyint(4) NOT NULL DEFAULT \'1\',
            `InsertTimestamp` datetime NOT NULL,
            PRIMARY KEY (`CategoryID`, `Language`),
            KEY `CategoryID` (`CategoryID`),
            KEY `ParentID` (`ParentID`),
            KEY `Language` (`Language`)
        ) ENGINE='.DB_SERVER_ENGINE.' DEFAULT CHARSET='.DB_SERVER_CHARSET.' COLLATE=utf8_unicode_ci
    ');
}

$functions[] = 'ml_db_update_110';
