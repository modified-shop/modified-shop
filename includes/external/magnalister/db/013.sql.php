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
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

$queries[] = '
	CREATE TABLE IF NOT EXISTS '.TABLE_MAGNA_COMPAT_CATEGORIES.' (
		mpID int(11) NOT NULL,
		CategoryID varchar(30) NOT NULL,
		CategoryName varchar(128) NOT NULL default \'\',
		ParentID varchar(30) NOT NULL,
		LeafCategory enum(\'0\',\'1\') NOT NULL default \'0\',
		Fee decimal(12,4) NOT NULL,
		FeeCurrency varchar(3) NOT NULL,
		InsertTimestamp datetime NOT NULL,
		UNIQUE KEY UniqueEntry (mpID,CategoryID)
	);
';
$queries[] = '
	CREATE TABLE IF NOT EXISTS '.TABLE_MAGNA_COMPAT_CATEGORYMATCHING.' (
		mpID int(11) NOT NULL,
		products_id int(11) NOT NULL,
		products_model varchar(64) NOT NULL,
		mp_category_id varchar(255) NOT NULL,
		store_category_id varchar(255) NOT NULL,
		UNIQUE KEY UniqueEntry (mpID,products_id,products_model)
	);
';	
$queries[] = '
	CREATE TABLE IF NOT EXISTS '.TABLE_MAGNA_COMPAT_DELETEDLOG.' (
		id int(10) unsigned NOT NULL auto_increment,
		products_id int(11) unsigned NOT NULL,
		products_model varchar(64) default NULL,
		mpID int(8) unsigned NOT NULL,
		old_price decimal(15,4) NOT NULL,
		`timestamp` datetime NOT NULL,
		PRIMARY KEY (id)
	);
';
$queries[] = '
	CREATE TABLE IF NOT EXISTS '.TABLE_MAGNA_COMPAT_ERRORLOG.' (
		id int(10) unsigned NOT NULL auto_increment,
		mpID int(8) unsigned NOT NULL,
		dateadded datetime NOT NULL,
		errormessage text NOT NULL,
		additionaldata longtext NOT NULL,
		PRIMARY KEY  (id),
		KEY mpID (mpID)
	);
';
