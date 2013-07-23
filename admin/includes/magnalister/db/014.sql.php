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
 * (c) 2010 - 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

function magna_alterMagnaCompatCategories() {
	$cols = array();
	if (MagnaDB::gi()->columnExistsInTable('platform', TABLE_MAGNA_COMPAT_CATEGORIES)) {
		return;
	}
	MagnaDB::gi()->query('
		DELETE FROM `'.TABLE_MAGNA_COMPAT_CATEGORIES.'`
	');
	MagnaDB::gi()->query('
		ALTER TABLE `'.TABLE_MAGNA_COMPAT_CATEGORIES.'`
			CHANGE `CategoryID` `CategoryID` VARCHAR( 200 ) NOT NULL ,
			CHANGE `ParentID` `ParentID` VARCHAR( 200 ) NOT NULL 
	');
	MagnaDB::gi()->query('
		ALTER TABLE `'.TABLE_MAGNA_COMPAT_CATEGORIES.'`
			DROP INDEX `UniqueEntry` 
	');
	MagnaDB::gi()->query('
		ALTER TABLE `'.TABLE_MAGNA_COMPAT_CATEGORIES.'`
			ADD `platform` VARCHAR( 30 ) NOT NULL AFTER `mpID` 
	');
	MagnaDB::gi()->query('
		ALTER TABLE `'.TABLE_MAGNA_COMPAT_CATEGORIES.'`
			ADD UNIQUE `UniqueEntry` ( `mpID` , `platform` , `CategoryID` ) 
	');
}
$functions[] = 'magna_alterMagnaCompatCategories';
