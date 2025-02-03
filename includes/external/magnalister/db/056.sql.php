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
 * (c) 2010 - 2015 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

/*
2024-01-08 module deleted

$queries[] = 'CREATE TABLE IF NOT EXISTS `' . TABLE_MAGNA_AYN24_VARIANTMATCHING . '` (
	`MpId` int(11) NOT NULL,
	`MpIdentifier` varchar(50) NOT NULL,
	`CustomIdentifier` varchar(64) NOT NULL DEFAULT "",
	`ShopVariation` text NOT NULL,
	PRIMARY KEY (`MpId`, `MpIdentifier`, `CustomIdentifier`)
) ENGINE='.DB_SERVER_ENGINE.' DEFAULT CHARSET='.DB_SERVER_CHARSET;

$queries[] = 'CREATE TABLE IF NOT EXISTS `' . TABLE_MAGNA_AYN24_PROPERTIES . '` (
	`mpID` int(11) NOT NULL,
	`products_id` int(11) NOT NULL,
	`products_model` varchar(64) NOT NULL,
	`MarketplaceCategory` varchar(30) NOT NULL,
	`StoreCategory` varchar(255) NOT NULL,
	`VariationConfiguration` varchar(255) NOT NULL,
	`ShippingDetails` tinytext NOT NULL,
	`PreparedTS` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
	PRIMARY KEY (`mpID`,`products_id`,`products_model`),
	KEY `mpID` (`mpID`),
	KEY `products_id` (`products_id`),
	KEY `products_model` (`products_model`)
)';

$queries[] = 'CREATE TABLE IF NOT EXISTS `' . TABLE_MAGNA_AYN24_ERRORLOG . '` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`mpID` int(8) unsigned NOT NULL,
	`dateadded` datetime NOT NULL,
	`errormessage` text NOT NULL,
	`additionaldata` longtext NOT NULL,
	PRIMARY KEY (`id`),
	KEY `mpID` (`mpID`)
)';
*/
