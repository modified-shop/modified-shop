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
 * $Id: 043.sql.php 650 2011-01-08 22:30:52Z MaW $
 *
 * (c) 2012 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

# eBay-Modul: 
function extend_ebay_properties_table_43() {
	if (! MagnaDB::gi()->columnExistsInTable('DispatchTimeMax', TABLE_MAGNA_EBAY_PROPERTIES)) {
		MagnaDB::gi()->query('ALTER TABLE `'.TABLE_MAGNA_EBAY_PROPERTIES.'` ADD COLUMN `DispatchTimeMax` tinyint(2) NOT NULL DEFAULT 63 AFTER `ShippingDetails`');
		MagnaDB::gi()->query('UPDATE '.TABLE_MAGNA_CONFIG.' mc, '.TABLE_MAGNA_EBAY_PROPERTIES.' mep SET mep.DispatchTimeMax = mc.value WHERE mep.mpID=mc.mpID AND mc.mkey=\'ebay.DispatchTimeMax\''); 
	}
	return;
}


$functions[] = 'extend_ebay_properties_table_43';

