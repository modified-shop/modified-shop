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
 *     Released under the GNU General Public License v2 or later
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

function magnaGetBaseRequest($mpID) {
	global $_modules;
	$marketplace = magnaGetMarketplaceByID($mpID);
	$subsystem = $_modules[$marketplace]['settings']['subsystem'];
	$request = array (
		'SUBSYSTEM' => $subsystem,
		'MARKETPLACEID' => $mpID,
	);
	if ($subsystem == 'ComparisonShopping') {
		$request['SEARCHENGINE'] = $marketplace;
	}
	return $request;
}

function magnaUpdateItems($mpID, $data, $upload = false, $responseCallback = '') {
	global $_modules;
	if (!is_array($data) || empty($data)) {
		return false;
	}
	$request = magnaGetBaseRequest($mpID);
	$request['ACTION'] = 'UpdateItems';
	$request['DATA'] = $data;
	if (defined('MAGNA_ECHO_UPDATE') && MAGNA_ECHO_UPDATE) {
		if (function_exists('ml_debug_out')) {
			ml_debug_out("\n".'$responseCallback: '.$responseCallback."\n");
			ml_debug_out(print_m($request));
		}
		return true;
	}
	$blSuccess = false;
	try {
		$r = MagnaConnector::gi()->submitRequest($request);
		if (!empty($responseCallback) && function_exists($responseCallback)) {
			$responseCallback($r, $mpID);
		}
		if (defined('ML_SHOW_INVENTORY_CHANGE') && ML_SHOW_INVENTORY_CHANGE) {
			echo var_export_pre($r, '$r', true)."\n\n";
		}
		if (defined('ML_LOG_INVENTORY_CHANGE') && ML_LOG_INVENTORY_CHANGE) {
			file_put_contents(DIR_MAGNALISTER_CALLBACK.'inventoryUpdate.dat', '$r = '.var_export_pre($r, true)."\n", FILE_APPEND);
		}
		$blSuccess = true;
	} catch (MagnaException $e) {
		if (defined('ML_LOG_INVENTORY_CHANGE') && ML_LOG_INVENTORY_CHANGE) {
			file_put_contents(
				DIR_MAGNALISTER_CALLBACK.'inventoryUpdate.dat', 
				'EXCEPTION :: '.$e->getMessage()."\n".'$error = '.var_export_pre($e->getErrorArray(), true).";\n",
				FILE_APPEND
			);
		}
		if ($e->getCode() == MagnaException::TIMEOUT) {
			$e->saveRequest();
			$e->setCriticalStatus(false);
		}
		return false;
	}
	
	if ($blSuccess && $upload) {
		magnaUploadItems($mpID);
	}
	
	return true;
}

function magnaUploadItems($mpID) {
	$request = magnaGetBaseRequest($mpID);
	$request['ACTION'] = 'UploadItems';
	if (defined('MAGNA_ECHO_UPDATE') && MAGNA_ECHO_UPDATE) {
		if (function_exists('ml_debug_out')) ml_debug_out("\n".print_m($request));
		return true;
	}
	try {
		//*
		$r = MagnaConnector::gi()->submitRequest($request);
		//*/
	} catch (MagnaException $e) {
		if (defined('ML_LOG_INVENTORY_CHANGE') && ML_LOG_INVENTORY_CHANGE) {
			file_put_contents(
				DIR_MAGNALISTER_CALLBACK.'inventoryUpdate.dat', 
				'EXCEPTION :: '.$e->getMessage()."\n".'$error = '.var_export_pre($e->getErrorArray(), true).";\n",
				FILE_APPEND
			);
		}
		if ($e->getCode() == MagnaException::TIMEOUT) {
			$e->saveRequest();
			$e->setCriticalStatus(false);
		}
		return false;
	}
	return true;
}

function genericCalcNewQuantity($pID, $aID, $sub = 0) {
	$curQty = false;
	if ($aID !== false) {
		$curQty = MagnaDB::gi()->fetchOne('
			SELECT attributes_stock FROM '.TABLE_PRODUCTS_ATTRIBUTES.' 
			 WHERE products_attributes_id = \''.$aID.'\'
		');
	}
	if ($curQty === false) {
		$curQty = MagnaDB::gi()->fetchOne('
			SELECT products_quantity FROM '.TABLE_PRODUCTS.'
			 WHERE products_id = \''.$pID.'\'
		');
	}
	if ($curQty === false) {
		return false;
	}

	$curQty -= $sub;
	if ($curQty < 0) {
		$curQty = 0;
	}
	return $curQty;
}
