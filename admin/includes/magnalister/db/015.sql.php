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

function magna_addStatusPaymentReceivedToOrdersStatus() {
	if (MagnaDB::gi()->fetchOne('SELECT COUNT(*) FROM '.TABLE_ORDERS_STATUS.'
             WHERE orders_status_name IN (\'Bezahlt\',\'Payment received\')') > 0) {
		return;
	}
    $langArr = MagnaDB::gi()->fetchArray('SELECT  languages_id, code 
            FROM '.TABLE_LANGUAGES);
    $langIDs = array();
    foreach ($langArr as $langRow) {
            $currCode = $langRow['code']; 
            $langIDs[$currCode] = $langRow['languages_id'];
    }
    $newStatusId = (int)MagnaDB::gi()->fetchOne('SELECT MAX(orders_status_id) FROM '.TABLE_ORDERS_STATUS)+1;

    if (isset($langIDs['en']))
	    MagnaDB::gi()->query(
		'INSERT INTO '.TABLE_ORDERS_STATUS.' (orders_status_id,language_id,orders_status_name)
         VALUES ('.$newStatusId.', '.$langIDs['en'].',\'Payment received\')');

    if (isset($langIDs['de']))
	    MagnaDB::gi()->query(
		'INSERT INTO '.TABLE_ORDERS_STATUS.' (orders_status_id,language_id,orders_status_name)
         VALUES ('.$newStatusId.', '.$langIDs['de'].',\'Bezahlt\')');
}
$functions[] = 'magna_addStatusPaymentReceivedToOrdersStatus';
