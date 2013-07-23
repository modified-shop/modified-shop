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
 * $Id: 021.sql.php 650 2011-01-08 22:30:52Z MaW $
 *
 * (c) 2012 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

# eBay-Modul: 
function fixEBayPropertiesDeletedByEnum_22() {
	$aEnum = MagnaDB::gi()->fetchArray('
		SHOW COLUMNS FROM '.TABLE_MAGNA_EBAY_PROPERTIES
	);
	foreach ($aEnum as $aEnumRow) {
		if ($aEnumRow['Field'] == 'deletedBy') {
			$aEnum = $aEnumRow;
			break;
		}
	}
	if (!isset($aEnum['Field'])) {
		return;
	}
	$aEnum = explode(',', substr($aEnum['Type'], 5, -1));
	if (in_array("''", $aEnum)) {
		return;
	}
    
    $aEnum = "'',".implode(',', $aEnum);
    
    $q = 'ALTER TABLE '.TABLE_MAGNA_EBAY_PROPERTIES.' CHANGE deletedBy deletedBy ENUM('.$aEnum.') DEFAULT \'\' NOT NULL';
   	MagnaDB::gi()->query($q);
   	MagnaDB::gi()->update(TABLE_MAGNA_EBAY_PROPERTIES, array (
   		'deletedBy' => '',
   	), array (
   		'deletedBy' => 'empty',
   	));
}

$functions[] = 'fixEBayPropertiesDeletedByEnum_22';
