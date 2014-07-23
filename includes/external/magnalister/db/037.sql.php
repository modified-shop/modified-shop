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

# eBay-annd hood prepare: adding verified = EMPTY
function fixPropertiesVerifiedEnum_36() {
	foreach ( array(TABLE_MAGNA_EBAY_PROPERTIES, TABLE_MAGNA_HOOD_PROPERTIES) as $sTable) {
		$aEnum = MagnaDB::gi()->fetchArray('
			SHOW COLUMNS FROM '.$sTable
		);
		foreach ($aEnum as $aEnumRow) {
			if ($aEnumRow['Field'] == 'Verified') {
				$aEnum = $aEnumRow;
				break;
			}
		}
		if (!isset($aEnum['Field'])) {
			continue;
		}
		$aEnum = explode(',', substr($aEnum['Type'], 5, -1));
		if (in_array("'EMPTY'", $aEnum)) {
			continue;
		}
		$aEnum = implode(',', $aEnum).",'EMPTY'";
		$q = 'ALTER TABLE '.$sTable.' CHANGE Verified Verified ENUM('.$aEnum.') DEFAULT \'OPEN\' NOT NULL';
		MagnaDB::gi()->query($q);
	}
}

$functions[] = 'fixPropertiesVerifiedEnum_36';