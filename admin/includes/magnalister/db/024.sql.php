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
 * $Id: 024.sql.php 650 2011-01-08 22:30:52Z MaW $
 *
 * (c) 2012 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

# eBay-Modul: 
# falls "Bestellzusammefassung beenden" nicht multiselect ausgewaehlt (nicht angefasst seit es single select war)
# waehle alles ausser Anfangsstatus aus
function fixEBayClosedOrderStatus24() {
	$allStatusesArray = MagnaDB::gi()->fetchArray('
		SELECT DISTINCT orders_status_id
	      FROM '.TABLE_ORDERS_STATUS.'
	  ORDER BY orders_status_id
	', true);

	$mpIDs = MagnaDB::gi()->fetchArray('
		SELECT DISTINCT mpID
		  FROM '.TABLE_MAGNA_CONFIG.'
		 WHERE mkey LIKE \'ebay%\'
	', true);
    
	foreach ($mpIDs as $mpID) {
		$openStatus   = MagnaDB::gi()->fetchOne('
			SELECT value FROM '.TABLE_MAGNA_CONFIG.'
			 WHERE mpID = '.$mpID.'
			       AND mkey = \'ebay.orderstatus.open\'
		');
		$closedStatus = MagnaDB::gi()->fetchOne('
			SELECT value FROM '.TABLE_MAGNA_CONFIG.'
			 WHERE mpID = '.$mpID.'
				   AND mkey = \'ebay.orderstatus.closed\'
		');
		if (empty($openStatus) && empty($closedStatus)) {
			continue;
		}
		
		$closedStatus = @json_decode($closedStatus);
		if (!is_array($closedStatus) && ($closedStatus !== null)) {
			$closedStatus = array($closedStatus);
		} else if (!is_array($closedStatus)) {
			$closedStatus = array();
		}
		$closedStatus = array_values($closedStatus);
		
		$newClosedStatus = false;
		# nichts ausgewaehlt (falsch) => alles auswaehlen
		if (empty($closedStatus)) {
			$newClosedStatus = array_values($allStatusesArray);
		} else if (count($closedStatus) == 1) {
			# derselbe Status wie openStatus ausgewaehlt heisst, Kunde wuenscht keine Zusammenfassung => alles auswaehlen
			if (ctype_digit($openStatus) && ($closedStatus[0] == (int)$openStatus)) {
				$newClosedStatus = array_values($allStatusesArray);
			} else {
			# eins ausgewaehlt (falsch) => alles auswaehlen
				$newClosedStatus = array_values(array_diff($allStatusesArray, array($openStatus)));
			}
		}
		
		if (is_array($newClosedStatus)) {
			MagnaDB::gi()->update(TABLE_MAGNA_CONFIG, array(
				'value' => json_encode($newClosedStatus),
			), array (
				'mpID' => $mpID,
				'mkey' => 'ebay.orderstatus.closed',
			));
		}
	}
}

$functions[] = 'fixEBayClosedOrderStatus24';
