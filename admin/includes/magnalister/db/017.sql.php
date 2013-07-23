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
 * $Id: 017.sql.php 650 2011-01-08 22:30:52Z MaW $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

function magnaUpdateAmazonTables_17() {
	$column = 'leadtimeToShip';
	$tablesToUpdate = array();
	if (!MagnaDB::gi()->columnExistsInTable($column, TABLE_MAGNA_AMAZON_APPLY)) {
		MagnaDB::gi()->query('
			ALTER TABLE '.TABLE_MAGNA_AMAZON_APPLY.' ADD COLUMN '.$column.' INT NOT NULL
		');
		$tablesToUpdate[] = TABLE_MAGNA_AMAZON_APPLY;
	}
	if (!MagnaDB::gi()->columnExistsInTable($column, TABLE_MAGNA_AMAZON_PROPERTIES)) {
		MagnaDB::gi()->query('
			ALTER TABLE '.TABLE_MAGNA_AMAZON_PROPERTIES.' ADD COLUMN '.$column.' INT NOT NULL
		');
		$tablesToUpdate[] = TABLE_MAGNA_AMAZON_PROPERTIES;
	}
	if (empty($tablesToUpdate)) return;
	
	$mpIDs = array_unique(array_merge(
		(array)MagnaDB::gi()->fetchArray('
			SELECT DISTINCT mpID FROM '.TABLE_MAGNA_AMAZON_APPLY.'
		', true),
		(array)MagnaDB::gi()->fetchArray('
			SELECT DISTINCT mpID FROM '.TABLE_MAGNA_AMAZON_PROPERTIES.'
		', true)
	));
	if (empty($mpIDs)) return;
	
	foreach ($mpIDs as $mpID) {
		$leadtime = (int)MagnaDB::gi()->fetchOne('
			SELECT value FROM '.TABLE_MAGNA_CONFIG.'
			 WHERE mkey=\'amazon.leadtimetoship\'
			       AND mpID='.$mpID.'
		');
		foreach ($tablesToUpdate as $tbl) {
			MagnaDB::gi()->update($tbl, array (
				$column => $leadtime,
			), array (
				'mpID' => $mpID
			));
		}
	}
}

$functions[] = 'magnaUpdateAmazonTables_17';