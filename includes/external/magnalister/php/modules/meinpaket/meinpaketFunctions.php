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
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'generic/genericFunctions.php');
$_Marketplace = 'meinpaket';

function magnaMeinpaketProcessCheckinResult($result, $mpID) {
	$fieldnames = array('UPLOADERRORS', 'CHECKINERRORS');
	$fieldname = '';
	foreach ($fieldnames as $fn) {
		if (array_key_exists($fn, $result)
			&& is_array($result[$fn])
			&& !empty($result[$fn])
		) {
			$fieldname = $fn;
			break;
		}
	}
	if ($fieldname == '') return;

	foreach ($result[$fieldname] as $err) {
		if (!isset($err['AdditionalData'])) {
			$err['AdditionalData'] = array();
		}
		$err = array (
			'mpID' => $mpID,
			'errormessage' => $err['ErrorMessage'],
			'dateadded' => $err['DateAdded'],
			'additionaldata' => serialize($err['AdditionalData']),
		);
		MagnaDB::gi()->insert(TABLE_MAGNA_MEINPAKET_ERRORLOG, $err);
	}
}

function magnaMeinpaketUpdateItems($mpID, $data) {
	magnaUpdateItems($mpID, $data, false, 'magnaMeinpaketProcessCheckinResult');
}

function updateMeinpaketInventoryByEdit($mpID, $updateData) {
	global $_Marketplace;
	if (in_array(getDBConfigValue($_Marketplace.'.stocksync.tomarketplace', $mpID), array('no', 'auto'))) {
		return;
	}
	$updateItem = genericInventoryUpdateByEdit($mpID, $updateData);	
	if (!is_array($updateItem)) {
		return false;
	}
	/* Beschreibung kann zz. nur aktualisiert werden fuer den Standard-Fall und einen weiteren Quasi-Standard-Fall. */
	$longdescField = getDBConfigValue($_Marketplace.'.checkin.longdesc.field', $mpID, '');
	$shortdescField = getDBConfigValue($_Marketplace.'.checkin.shortdesc.field', $mpID, '');
	if (($longdescField == '') && ($shortdescField == '')) { // Standard
		$updateItem['ShortDescription'] = $updateItem['Description'];
	} else if (($longdescField == 'products_description') && ($shortdescField == 'products_short_description')) {
		// Quasi-Standard-Fall... Alles korrekt, nichts machen
	} else {
		/* Eine zz nicht behandelbare Konfiguration. Die verwendeten Bezeichner in der Eingabemaske sind
		   unbekannt (auch wegen Multilanguage) und koennen daher nicht verarbeitet werden */
		unset($updateItem['Description']);
		unset($updateItem['ShortDescription']);
	}
	magnaMeinpaketUpdateItems($mpID, array($updateItem));
}

function updateMeinpaketInventoryByOrder($mpID, $boughtItems, $subRelQuant = true) {
	global $_Marketplace;
	if (in_array(getDBConfigValue($_Marketplace.'.stocksync.tomarketplace', $mpID), array('no', 'auto'))) {
		return;
	}
	$data = genericInventoryUpdateByOrder($mpID, $boughtItems, $subRelQuant);
	#echo print_m($data, '$data');
	magnaMeinpaketUpdateItems($mpID, $data);
}
