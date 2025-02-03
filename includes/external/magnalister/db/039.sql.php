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
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

$queries[] = 'CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_DAWANDA_PROPERTIES.'` (
  `mpID` int(8) NOT NULL,
  `products_id` int(11) NOT NULL,
  `products_model` varchar(64) NOT NULL,
  `ShippingService` int(16) NOT NULL,
  `MarketplaceCategories` text NOT NULL,
  `topMarketplaceCategory` int(16) NOT NULL,
  `StoreCategories` text NOT NULL,
  `topStoreCategory` int(16) NOT NULL,
  `ListingDuration` tinyint(4) NOT NULL,
  `ProductType` int(11) NOT NULL DEFAULT \'0\',
  `ReturnPolicy` int(11) NOT NULL DEFAULT \'0\',
  `MpColors` text NOT NULL,
  `Attributes` longtext NOT NULL,
  `Verified` enum(\'OK\',\'ERROR\',\'OPEN\',\'EMPTY\') NOT NULL DEFAULT \'OPEN\',
  UNIQUE KEY `U_PRODUCT_ID` (`mpID`,`products_id`,`products_model`),
  KEY `mpID` (`mpID`),
  KEY `products_id` (`products_id`),
  KEY `products_model` (`products_model`)
) ENGINE='.DB_SERVER_ENGINE;
