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
<<<<<<< HEAD
 * $Id$
 *
 * (c) 2010 - 2016 RedGecko GmbH -- http://www.redgecko.de
=======
 * $Id: 074.sql.php $
 *
 * (c) 2016 RedGecko GmbH -- http://www.redgecko.de
>>>>>>> master
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

function ml_db_update_96() {

    MagnaDB::gi()->query("DROP TABLE IF EXISTS " . TABLE_MAGNA_CROWDFOX_PREPARE);

    MagnaDB::gi()->query("
		CREATE TABLE IF NOT EXISTS `" . TABLE_MAGNA_CROWDFOX_PREPARE . "` (
			`mpID` int(8) NOT NULL,
			`products_id` int(11) NOT NULL,
			`products_model` varchar(64) NOT NULL,
			`PreparedTS` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`ItemTitle` varchar(255) DEFAULT NULL,
			`Description` varchar(5000) DEFAULT NULL,
			`Images` text DEFAULT NULL,
			`Verified` enum('OK','ERROR','OPEN','EMPTY') NOT NULL DEFAULT 'OK',
			`PrepareType` enum('Apply') NOT NULL,
			`GTIN` VARCHAR(13) DEFAULT NULL,
			`Brand` VARCHAR(255) DEFAULT NULL,
			`MPN` VARCHAR(50) DEFAULT NULL,
			`DeliveryTime` VARCHAR(50) DEFAULT NULL,
			`DeliveryCost` decimal(12,2) DEFAULT NULL,
			`ShippingMethod` enum('0','1') DEFAULT NULL,
			`ShopVariation` text NOT NULL,
			PRIMARY KEY (`mpID`,`products_id`,`products_model`)
		) ENGINE=".DB_SERVER_ENGINE." DEFAULT CHARSET=".DB_SERVER_CHARSET."
	");

    MagnaDB::gi()->query('CREATE TABLE IF NOT EXISTS `' . TABLE_MAGNA_CROWDFOX_VARIANTMATCHING . '` (
	`MpId` int(11) NOT NULL,
	`MpIdentifier` varchar(50) NOT NULL,
	`CustomIdentifier` varchar(64) NOT NULL DEFAULT "",
	`ShopVariation` text NOT NULL,
	`IsValid` bit NOT NULL DEFAULT 1,
	`ModificationDate` DATETIME NOT NULL DEFAULT \'0000-00-00 00:00:00\',
	PRIMARY KEY (`MpId`, `MpIdentifier`, `CustomIdentifier`)) ENGINE='.DB_SERVER_ENGINE.' DEFAULT CHARSET='.DB_SERVER_CHARSET);
}

$functions[] = 'ml_db_update_96';
