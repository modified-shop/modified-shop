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
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

/* Tabellenstruktur fuer Tabelle `magnalister_config` */
$queries[] = '
	CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_CONFIG.'` (
		`mpID` int(8) UNSIGNED NOT NULL default \'0\',
		`mkey` varchar(100) NOT NULL,
		`value` longtext NOT NULL,
		UNIQUE KEY `UniqueKey` (`mpID`,`mkey`)
	);
';

/* Tabellenstruktur fuer Tabelle `magnalister_session` */
$queries[] = '
	CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_SESSION.'` (
		`session_id` varchar(32) NOT NULL,
		`data` longtext NOT NULL,
		`expire` int(11) unsigned NOT NULL,
		PRIMARY KEY  (`session_id`)
	);
';

/* Tabellenstruktur fuer Tablle `magnalister_selection` */
$queries[] = '
	CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_SELECTION.'` (
		`pID` int(10) UNSIGNED NOT NULL,
		`mpID` int(8) UNSIGNED NOT NULL,
		`data` text NOT NULL,
		`selectionname` varchar(50) NOT NULL,
		`session_id` varchar(32) NOT NULL,
		`expires` datetime NOT NULL,
		UNIQUE KEY `selection` (`pID`,`mpID`,`selectionname`,`session_id`),
		KEY `expires` (`expires`)
	);
';

/* Tabellenstruktur fuer Tabelle `magnalister_selection_templates` */
$queries[] = '
	CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_SELECTION_TEMPLATES.'` (
		`tID` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`title` VARCHAR( 200 ) NOT NULL ,
		`mpID` int( 8 ) UNSIGNED NOT NULL ,
		`data` LONGTEXT NOT NULL ,
		INDEX ( `title` , `mpID` )
	);
';

/* Tabellenstruktur fuer Tabelle `magnalister_selection_template_entries` */
$queries[] = '
	CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_SELECTION_TEMPLATE_ENTRIES.'` (
		`tID` int(11) unsigned NOT NULL,
		`pID` int(10) unsigned NOT NULL,
		`products_model` VARCHAR(64) NOT NULL,
		`data` text NOT NULL,
		UNIQUE KEY `entry` (`tID`,`pID`,`products_model`)
	);
';

/* Tabellenstruktur fuer Tabelle `magnalister_amazon_properties` */
$queries[] = '
	CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_AMAZON_PROPERTIES.'` (
		`products_id` int(11) NOT NULL,
		`products_model` VARCHAR(64) NOT NULL,
		`asin` varchar(16) NOT NULL,
		`asin_type` int(2) NOT NULL,
		`item_condition` int(2) NOT NULL,
		`amazon_price` decimal(15,4) NOT NULL,
		`image_url` text NOT NULL,
		`item_note` text NOT NULL,
		`will_ship_internationally` int(2) NOT NULL,
		`category_id` varchar(10) NOT NULL,
		`category_name` varchar(200) NOT NULL,
		`lowestprice` decimal(15,2) NOT NULL,
		UNIQUE KEY `UC_products_id` (`products_id`,`products_model`)
	);
';

/* Tabellenstruktur fuer amazon errorlog */
$queries[] = '
	CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_AMAZON_ERRORLOG.'` (
		`id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`products_id` INT UNSIGNED NOT NULL ,
		`products_model` VARCHAR( 64 ) NOT NULL ,
		`batchid` VARCHAR( 50 ) NOT NULL DEFAULT \'\',
		`asin` VARCHAR( 50 ) NOT NULL DEFAULT \'\',
		`commissiondate` DATETIME NOT NULL ,
		`errormessage` TINYTEXT NOT NULL,
		INDEX ( `products_id` )
	);
';

/* Tabellenstruktur fuer Tabelle `magnalister_cs_deletedlog` */
$queries[] = '
	CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_CS_DELETEDLOG.'` (
		`id` int(10) UNSIGNED NOT NULL auto_increment,
		`mpID` int(8) UNSIGNED NOT NULL,
		`products_id` int(11) unsigned NOT NULL,
		`products_model` VARCHAR( 64 ) NOT NULL,
		`old_price` decimal(15,4) NOT NULL,
		`timestamp` datetime NOT NULL,
		PRIMARY KEY  (`id`)
	);
';

/* Tabellenstruktur fuer Tablle `magnalister_cs_errorlog` */
$queries[] = '
	CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_CS_ERRORLOG.'` (
		`id` int(10) UNSIGNED NOT NULL auto_increment,
		`mpID` int(8) UNSIGNED NOT NULL,
		`products_id` int(11) unsigned NOT NULL,
		`products_model` VARCHAR( 64 ) NOT NULL,
		`product_details` text NOT NULL,
		`errormessage` text NOT NULL,
		`timestamp` datetime NOT NULL,
		PRIMARY KEY  (`id`)
	);
';

/* Tabellenstruktur fuer Tablle `magnalister_yatego_categories` */
$queries[] = '
	CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_YATEGO_CATEGORIES.'` (
		`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`title_h` VARCHAR( 50 ) NOT NULL ,
		`title_m` VARCHAR( 50 ) NOT NULL ,
		`title_l` VARCHAR( 50 ) NOT NULL ,
		`object_id` VARCHAR( 20 ) NOT NULL
	);
';

/* Tabellenstruktur fuer Tablle `magnalister_yatego_categorymatching` */
$queries[] = '
	CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_YATEGO_CATEGORYMATCHING.'` (
		`category_id` INT( 11 ) NOT NULL ,
		`yatego_category_id` VARCHAR( 20 ) NOT NULL ,
		INDEX ( `category_id` , `yatego_category_id` )
	);
';

/* Tabellenstruktur fuer Tablle `magnalister_orders` */
$queries[] = '
	CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_ORDERS.'` (
		`orders_id` INT( 11 ) NOT NULL ,
		`mpID` INT( 8 ) UNSIGNED NOT NULL ,
		`platform` VARCHAR( 20 ) NOT NULL ,
		`data` TEXT NOT NULL ,
		`special` VARCHAR( 100 ) default NULL ,
		PRIMARY KEY ( `orders_id` ) ,
		INDEX ( `platform` )
	);
';

/* Tabellenstruktur fuer Tablle `magnalister_api_requests` */
$queries[] = '
	CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_API_REQUESTS.'` (
		`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`data` TEXT NOT NULL ,
		`date` DATETIME NOT NULL ,
		INDEX ( `date` )
	);
';
