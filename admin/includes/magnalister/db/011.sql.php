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
 *     Released under the GNU General Public License v2 or later
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

function magna_addMagnaOrdersStatus() {
	$cols = array();
	$colsQuery = MagnaDB::gi()->query('SHOW COLUMNS FROM `'.TABLE_MAGNA_ORDERS.'`');
	while ($row = MagnaDB::gi()->fetchNext($colsQuery))	{
		$cols[] = $row['Field'];
	}
	MagnaDB::gi()->freeResult($colsQuery);
	if (in_array('orders_status', $cols)) {
		return;
	}
	
	MagnaDB::gi()->query('
		ALTER TABLE `'.TABLE_MAGNA_ORDERS.'` 
			ADD `orders_status` INT( 11 ) NOT NULL AFTER `orders_id`
	');
	MagnaDB::gi()->query("
		UPDATE `".TABLE_MAGNA_ORDERS."` mo, `".TABLE_ORDERS."` o 
		   SET mo.orders_status=o.orders_status
	     WHERE mo.orders_id=o.orders_id
	");
	MagnaDB::gi()->delete(TABLE_MAGNA_CONFIG, array (
		'mkey' => 'amazon.orderstatus.sync'
	));
}
$functions[] = 'magna_addMagnaOrdersStatus';