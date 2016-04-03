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
 * $Id: 004.sql.php 320 2010-11-30 00:42:26Z maw $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

function getProductsIDColname($a) {
	if (empty($a)) {
		return '';
	}
	$prevCol = '';
	foreach ($a as $col) {
		if (($col == 'products_id') || ($col == 'pID')) {
			return $col;
		}
	}
	return '';
}

function addProductsModelToMagnaTables() {
	$tables = array (
		'magnalister_amazon_apply_tmp',
		TABLE_MAGNA_AMAZON_PROPERTIES,
		// diese tabellen muessen nachgereicht werden.
		#TABLE_MAGNA_CS_DELETEDLOG,
		#TABLE_MAGNA_CS_ERRORLOG,
		TABLE_MAGNA_SELECTION_TEMPLATE_ENTRIES,
	);
	foreach ($tables as $table) {
		if (!MagnaDB::gi()->tableExists($table)) {
			continue;
		}
		$cols = array();
		$colsQuery = MagnaDB::gi()->query('SHOW COLUMNS FROM `'.$table.'`');
		while ($row = MagnaDB::gi()->fetchNext($colsQuery))	{
			$cols[] = $row['Field'];
		}
		MagnaDB::gi()->freeResult($colsQuery);
		if (in_array('products_model', $cols)) {
			continue;
		}
		$pIDCol = getProductsIDColname($cols);
		MagnaDB::gi()->query('ALTER TABLE `'.$table.'` ADD `products_model` VARCHAR(64) NOT NULL AFTER `'.$pIDCol.'`');
		MagnaDB::gi()->query('
			UPDATE `'.$table.'` ml, '.TABLE_PRODUCTS.' p
	           SET ml.products_model = p.products_model
	         WHERE ml.`'.$pIDCol.'`=p.products_id
		');
	}
}
$functions[] = 'addProductsModelToMagnaTables';

function extendPimaryKeys() {
	global $localBuild;
	
	if (($localBuild <= 615) || (isset($_GET['dbupdate']) && ($_GET['dbupdate'] == 'true'))) {
		if (MagnaDB::gi()->tableExists(TABLE_MAGNA_AMAZON_PROPERTIES) &&
			!in_array('mpID', MagnaDB::gi()->getTableCols(TABLE_MAGNA_AMAZON_PROPERTIES))
		) {
			MagnaDB::gi()->query('ALTER TABLE '.TABLE_MAGNA_AMAZON_PROPERTIES.' DROP INDEX UC_products_id');
			MagnaDB::gi()->query('ALTER TABLE '.TABLE_MAGNA_AMAZON_PROPERTIES.' ADD unique INDEX `UC_products_id` (`products_id`,`products_model`)');
		}
		if (MagnaDB::gi()->tableExists(TABLE_MAGNA_SELECTION_TEMPLATE_ENTRIES)) {
			MagnaDB::gi()->query('ALTER TABLE '.TABLE_MAGNA_SELECTION_TEMPLATE_ENTRIES.' DROP INDEX `entry`');
			MagnaDB::gi()->query('ALTER TABLE '.TABLE_MAGNA_SELECTION_TEMPLATE_ENTRIES.' ADD unique INDEX  `entry` (`tID`,`pID`,`products_model`)');
		}
		if (MagnaDB::gi()->tableExists('magnalister_amazon_apply_tmp')) {
			MagnaDB::gi()->query('ALTER TABLE magnalister_amazon_apply_tmp DROP PRIMARY KEY');
			MagnaDB::gi()->query('ALTER TABLE magnalister_amazon_apply_tmp ADD PRIMARY KEY (`pID`,`products_model`)');
		}
	}
}
$functions[] = 'extendPimaryKeys';

function extendProductsModelLength() {
	$columnType = MagnaDB::gi()->columnType('products_model', TABLE_PRODUCTS);
	
	if ($columnType) {
		preg_match("/\d+/", $columnType, $laengen);
		$columnLength = (int)$laengen[0];
		if ($columnLength >= 64) return;
	}
	MagnaDB::gi()->query('
		ALTER TABLE '.TABLE_PRODUCTS.' 
		  CHANGE COLUMN products_model products_model VARCHAR(64)
	');
}
$functions[] = 'extendProductsModelLength';
