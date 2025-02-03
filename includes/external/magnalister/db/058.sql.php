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

$queries[] = "CREATE TABLE IF NOT EXISTS `" . TABLE_MAGNA_AYN24_CATEGORIES . "` (
   `mpID` int(11) NOT NULL,
   `platform` varchar(30) NOT NULL,
   `CategoryID` varchar(200) NOT NULL,
   `CategoryName` varchar(128) NOT NULL DEFAULT '',
   `ParentID` varchar(200) NOT NULL,
   `CategoryPath` text NOT NULL,
   `LeafCategory` enum('0','1') NOT NULL DEFAULT '0',
   `Selectable` enum('0','1') NOT NULL,
   `Fee` decimal(12,4) NOT NULL,
   `FeeCurrency` varchar(3) NOT NULL,
   `InsertTimestamp` datetime NOT NULL,
   UNIQUE KEY `UniqueEntry` (`mpID`,`platform`,`CategoryID`),
   KEY `mpID` (`mpID`)
 ) ENGINE=".DB_SERVER_ENGINE." DEFAULT CHARSET=".DB_SERVER_CHARSET." COLLATE=utf8_unicode_ci";
*/
