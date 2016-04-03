
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
 * $Id: 028.sql.php 3098 2013-08-07 19:10:52Z tim.neumann $
 *
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

/* Tabellenstruktur fuer Tabelle `magnalister_config` */
$queries[] = "
   CREATE TABLE IF NOT EXISTS `magnalister_hood_categories` (
  `CategoryID` bigint(11) NOT NULL DEFAULT '0',
  `CategoryName` varchar(128) NOT NULL,
  `ParentID` bigint(11) NOT NULL DEFAULT '0',
  `LeafCategory` enum('0','1') NOT NULL DEFAULT '1',
  `StoreCategory` enum('0','1') NOT NULL DEFAULT '0',
  `InsertTimestamp` int(11) NOT NULL DEFAULT '0',
  `Fee` float NOT NULL,
  `FeeCurrency` varchar(5) NOT NULL,
  `Mode` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`CategoryID`,`StoreCategory`)
  );
";

$queries[] = "
    CREATE TABLE IF NOT EXISTS  `magnalister_hood_properties` (
   `mpID` int(8) unsigned not null default '0',
   `products_id` int(11) not null,
   `products_model` varchar(64) not null,
   `ItemID` varchar(12),
   `PreparedTS` datetime not null default '0000-00-00 00:00:00',
   `StartTime` datetime,
   `Title` varchar(80) not null,
   `Subtitle` varchar(55) not null,
   `Description` longtext not null,
   `PictureURL` varchar(255) not null,
   `GalleryURL` varchar(255) not null,
   `ConditionType` enum('new','used') not null,
   `Price` decimal(15,4) not null default '0.0000',
   `BuyItNowPrice` decimal(15,4) not null,
   `PrimaryCategory` int(10) not null,
   `PrimaryCategoryName` varchar(128) not null,
   `SecondaryCategory` int(10) not null,
   `SecondaryCategoryName` varchar(128) not null,
   `StoreCategory` bigint(11) default '0',
   `ItemSpecifics` longtext not null,
   `ListingType` enum('classic','buyItNow','shopProduct') not null default 'buyItNow',
   `ListingDuration` varchar(10) default '1',
   `BestOfferEnabled` enum('0','1') default '0',
   `PaymentMethods` longtext not null,
   `ShippingServiceOptions` longtext not null,
   `Verified` enum('OK','ERROR','OPEN') not null default 'OPEN',
   `Transferred` tinyint(1) unsigned not null default '0',
   `deletedBy` enum('','empty','Sync','Button','expired','notML') not null,
   `topPrimaryCategory` varchar(64) not null,
   `topSecondaryCategory` varchar(64) not null,
   `StoreCategory2` bigint(11),
   `Manufacturer` varchar(200),
   `ManufacturerPartNumber` varchar(200),
   UNIQUE KEY (`mpID`,`products_id`,`products_model`)
   ) ;
";