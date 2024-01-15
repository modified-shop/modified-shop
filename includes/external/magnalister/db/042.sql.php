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

function mlDbUpdate_AddPreparedTsColumn_042 () {
	foreach ( array(
		TABLE_MAGNA_AMAZON_APPLY,
		TABLE_MAGNA_AMAZON_PROPERTIES,
		TABLE_MAGNA_DAWANDA_PROPERTIES,
		TABLE_MAGNA_EBAY_PROPERTIES,
		TABLE_MAGNA_HITMEISTER_PREPARE,
		TABLE_MAGNA_HOOD_PROPERTIES,
	) as $sTable) {
		if (MagnaDB::gi()->tableExists($sTable)) {
			$aFields = MagnaDB::gi()->getTableCols($sTable);
			if (!in_array('PreparedTS', $aFields)) {
				MagnaDB::gi()->query("ALTER TABLE `".$sTable."` ADD COLUMN `PreparedTS` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");
			}
		}
	}
}

$functions[] = 'mlDbUpdate_AddPreparedTsColumn_042';
