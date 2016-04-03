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
 * (c) 2010 - 2012 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

function magna_fix_tradoria_orders() {
    $orders_query = 'SELECT o.orders_id, o.payment_method, ';
    if (MagnaDB::gi()->columnExistsInTable('comments',TABLE_ORDERS)) {
        $orders_query .= 'o.comments, ';
    }
    $orders_query .= 'mo.data
		  FROM '.TABLE_MAGNA_ORDERS.' mo, '.TABLE_ORDERS.' o
		 WHERE mo.orders_id=o.orders_id
		       AND mo.platform=\'tradoria\'
		       AND o.payment_method<>\'marketplace\'';
	$orders = MagnaDB::gi()->fetchArray($orders_query);
	
	$orderFix = array (
		'payment_method' => 'marketplace',
	);
	if (MagnaDB::gi()->columnExistsInTable('payment_class', TABLE_ORDERS)) {
		$orderFix['payment_class'] = $orderFix['payment_method'];
	}
	if (MagnaDB::gi()->columnExistsInTable('shipping_class', TABLE_ORDERS)) {
		$orderFix['shipping_class'] = $orderFix['shipping_method'] = $orderFix['payment_method'];
	}
	$langFix = 'Marketplace Bestellnummer';
	if (isset($_SESSION['language']) && !empty($_SESSION['language']) && ($_SESSION['language'] != 'german')) {
		$langFix = 'Marketplace Order ID';
	}
	foreach ($orders as $o) {
		$data = @unserialize($o['data']);
		if (!is_array($data)) {
			$data = array();
		}
		$data['ML_LABEL_MARKETPLACE_PAYMENT_METHOD'] = $o['payment_method'];
		
		MagnaDB::gi()->update(TABLE_MAGNA_ORDERS, array(
			'data' => serialize($data)
		), array (
			'orders_id' => $o['orders_id']
		));

        if (isset($o['comments'])) {
		    $orderFix['comments'] = str_replace('ML_GENERIC_LABEL_ORDER_ID', $langFix, $o['comments']);
        }
		
		MagnaDB::gi()->update(TABLE_ORDERS, $orderFix, array (
			'orders_id' => $o['orders_id']
		));
		
		$oshid = MagnaDB::gi()->fetchOne('
			SELECT orders_status_history_id FROM '.TABLE_ORDERS_STATUS_HISTORY.' 
			 WHERE orders_id='.$o['orders_id'].' 
	      ORDER BY orders_status_history_id ASC
	         LIMIT 1
	    ');
	    if ($oshid !== false) {
            MagnaDB::gi()->query('UPDATE '.TABLE_ORDERS_STATUS_HISTORY.'
                SET comments = REPLACE(comments, \'ML_GENERIC_LABEL_ORDER_ID\',\''.$langFix.'\')
                WHERE orders_status_history_id = '.$oshid);
		}
	}
}
$functions[] = 'magna_fix_tradoria_orders';
