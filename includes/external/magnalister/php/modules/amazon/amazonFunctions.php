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
 * $Id: amazonFunctions.php 6690 2016-05-06 13:51:57Z tim.neumann $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'generic/genericFunctions.php');

function magnaAmazonSKU2pID($sku, $asin = '') {
	$pID = magnaSKU2pID($sku);
	if (($pID <= 0) && !empty($asin)) {
		global $_MagnaSession;
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$query = '
				SELECT p.products_id 
				  FROM '.TABLE_PRODUCTS.' p, '.TABLE_MAGNA_AMAZON_PROPERTIES.' pa
				 WHERE p.products_model = pa.products_model
				       AND pa.asin=\''.$asin.'\' AND mpID=\''.$_MagnaSession['mpID'].'\'
				 LIMIT 1
			';
		} else {
			$query = '
				SELECT products_id 
				  FROM '.TABLE_MAGNA_AMAZON_PROPERTIES.' 
				 WHERE asin=\''.$asin.'\' AND mpID=\''.$_MagnaSession['mpID'].'\'
				 LIMIT 1';
		}
		$pID = (int)MagnaDB::gi()->fetchOne($query);
		unset($query);
	}
	return $pID;
}

function performItemSearch($asin, $ean, $productsName) {
	require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');

	$searchResults = array();
	if (!empty($asin)) {
		try {
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'ItemLookup',
				'ASIN' => $asin
			));
			if (!empty($result['DATA'])) {
				$searchResults = array_merge($searchResults, $result['DATA']);
			}
		} catch (MagnaException $e) {
			$e->setCriticalStatus(false);
		}
	}
	$ean = str_replace(array(' ', '-'), '', $ean);
	if (!empty($ean)) {
		try {
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'ItemLookup',
				'ASIN' => $ean
			));
			if (!empty($result['DATA'])) {
				$searchResults = array_merge($searchResults, $result['DATA']);
			}
		} catch (MagnaException $e) {
			$e->setCriticalStatus(false);
		}
		try {
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'ItemSearch',
				'NAME' => $ean
			));
			if (!empty($result['DATA'])) {
				$searchResults = array_merge($searchResults, $result['DATA']);
			}
		} catch (MagnaException $e) {
			$e->setCriticalStatus(false);
		}
	}

	if (!empty($productsName)) {
		try {	
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'ItemSearch',
				'NAME' => $productsName
			));
			if (!empty($result['DATA'])) {
				$searchResults = array_merge($searchResults, $result['DATA']);
			}
		} catch (MagnaException $e) {
			$e->setCriticalStatus(false);
		}
	}
	if (!empty($searchResults)) {
		$searchResults = array_map('unserialize', array_unique(array_map('serialize', $searchResults)));
		foreach ($searchResults as &$data) {
			if (!empty($data['Author'])) {
				$data['Title'] .= ' ('.$data['Author'].')';
			}
			$price = new SimplePrice($data['LowestPrice']['Amount'], $data['LowestPrice']['CurrencyCode']);
			$data['LowestPrice'] = $data['LowestPrice']['Amount'];
			$data['LowestPriceFormated'] = $price->format();
		}
	}
	return $searchResults;
}

function amazonGetPossibleOptions($kind, $mpID = false) {
	if ($mpID === false) {
		global $_MagnaSession;
		$mpID = $_MagnaSession['mpID'];
	}
	
	initArrayIfNecessary($_MagnaSession, array($mpID, $kind));
	
	if (empty($_MagnaSession[$mpID][$kind])) {
		try {
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'Get'.$kind,
				'SUBSYSTEM' => 'Amazon',
				'MARKETPLACEID' => $mpID,
			));
			$_MagnaSession[$mpID][$kind] = $result['DATA'];
		} catch (MagnaException $e) { }
	}
	return $_MagnaSession[$mpID][$kind];
}

function amazonGetMarketplaces() {
	global $_MagnaSession;
	
	initArrayIfNecessary($_MagnaSession, array($_MagnaSession['mpID'], 'Marketplaces', 'Sites'));
	initArrayIfNecessary($_MagnaSession, array($_MagnaSession['mpID'], 'Marketplaces', 'Currencies'));

	if (empty($_MagnaSession[$_MagnaSession['mpID']]['Marketplaces']['Sites']) || 
		empty($_MagnaSession[$_MagnaSession['mpID']]['Marketplaces']['Currencies'])
	) {
		try {
			$_MagnaSession[$_MagnaSession['mpID']]['Marketplaces'] = array();
			
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetMarketplaces',
				'SUBSYSTEM' => 'Amazon',
			));
			foreach ($result['DATA'] as $item) {
				$_MagnaSession[$_MagnaSession['mpID']]['Marketplaces']['Sites'][$item['Key']] = fixHTMLUTF8Entities($item['Label']);
				$_MagnaSession[$_MagnaSession['mpID']]['Marketplaces']['Currencies'][$item['Key']] = $item['Currency'];
			}
		} catch (MagnaException $e) { }
	}

	return $_MagnaSession[$_MagnaSession['mpID']]['Marketplaces'];
}

function amazonGetLeadtimeToShip($mpID, $pID) {
	$w = (getDBConfigValue('general.keytype', '0') == 'artNr')
		? 'products_model = p.products_model'
		: 'products_id = p.products_id';

    // If you checked to prefer matching
    if (getDBConfigValue(array('amazon.leadtimetoshipmatching.prefer', 'val'), $mpID, false)) {
        $products_shippingtime = MagnaDB::gi()->fetchOne("
            SELECT products_shippingtime
              FROM ".TABLE_PRODUCTS." p
             WHERE p.products_id = '".$pID."'
        ");

        $iTime = getDBConfigValue(
            array('amazon.leadtimetoshipmatching.values', $products_shippingtime),
            $mpID,
            getDBConfigValue('amazon.leadtimetoship', $mpID, '-')
        );
        if ($iTime == '-') {
            $iTime = ''; // empty string means use default setting of in amazon seller central of client
        }

        return $iTime;
    }
	
	$leadtime = MagnaDB::gi()->fetchOne(eecho('
	    SELECT IF(ap.leadtimeToShip IS NULL, aa.leadtimeToShip, ap.leadtimeToShip)
	      FROM '.TABLE_PRODUCTS.' p
	 LEFT JOIN '.TABLE_MAGNA_AMAZON_PROPERTIES.' ap ON (
					ap.mpID=\''.$mpID.'\' 
					AND ap.'.$w.'
				)
	 LEFT JOIN '.TABLE_MAGNA_AMAZON_APPLY.' aa ON (
	                aa.mpID=\''.$mpID.'\' 
	                AND aa.'.$w.'
	            )
	     WHERE p.products_id=\''.$pID.'\'
	', false));

	if (($leadtime === false) || ($leadtime === null)) {
		$leadtime = getDBConfigValue('amazon.leadtimetoship', $mpID, '-');

        if ($leadtime == '-') {
            $leadtime = ''; // empty string means use default setting of in amazon seller central of client
        }
	}
	return $leadtime;
}

function updateAmazonInventoryByEdit($mpID, $updateData) {
	if (in_array(getDBConfigValue('amazon.stocksync.tomarketplace', $mpID), array('no', 'auto'))) {
		return;
	}
	$updateItem = genericInventoryUpdateByEdit($mpID, $updateData);
	if (!is_array($updateItem)) {
		return;
	}
	$timeToShip = getDBConfigValue('amazon.leadtimetoship', $mpID, '');
	if (!empty($timeToShip)) {
		$updateItem['LeadtimeToShip'] = (int)$timeToShip;
	}
	#echo print_m($updateItem, '$updateItem');
	magnaUpdateItems($mpID, array($updateItem), true);
}

function updateAmazonInventoryByOrder($mpID, $boughtItems, $subRelQuant = true) {
	if (in_array(getDBConfigValue('amazon.stocksync.tomarketplace', $mpID), array('no', 'auto'))) {
		return;
	}
	$data = genericInventoryUpdateByOrder($mpID, $boughtItems, $subRelQuant);
	$timeToShip = getDBConfigValue('amazon.leadtimetoship', $mpID, '');
	if (!empty($timeToShip)) {
		foreach ($data as &$item) {
			$item['LeadtimeToShip'] = (int)$timeToShip;
		}
	}
	#echo print_m($data, '$data');
	magnaUpdateItems($mpID, $data, true);
}

function loadCarrierCodes($mpID = false) {
	if ($mpID === false) {
		global $_MagnaSession;
		$mpID = $_MagnaSession['mpID'];
	}
	$carrier = amazonGetPossibleOptions('CarrierCodes', $mpID);

	# Amazon Config Form
	if (array_key_exists('conf', $_POST) && array_key_exists('amazon.orderstatus.carrier.additional', $_POST['conf'])) {
		setDBConfigValue(
			'amazon.orderstatus.carrier.additional',
			$mpID,
			$_POST['conf']['amazon.orderstatus.carrier.additional']
		);
	}

	$addCarrier = explode(',', getDBConfigValue('amazon.orderstatus.carrier.additional', $mpID, ''));
	if (!empty($addCarrier)) {
		foreach ($addCarrier as $val) {
			$val = trim($val);
			if (empty($val)) continue;
			$carrier[$val] = $val;
		}
	}
	$carrierValues = array('null' => ML_LABEL_CARRIER_NONE);
	if (!empty($carrier)) {
		foreach ($carrier as $val) {
			if ($val == 'Other') continue;
			$carrierValues[$val] = $val;
		}
	}
	return $carrierValues;
}

/* returns a list with extra options (show as optgroups) */
function loadCarrierCodesExtended($mpID = false) {
	$carrierCodes = loadCarrierCodes($mpID);
	array_shift($carrierCodes); // remove the 'none' entry)
	$carrierSelection = array (
		ML_LABEL_CHOOSE,
		ML_SELECT_AMAZON_SUGGESTED_CARRIER => $carrierCodes,
		ML_ADDITIONAL_OPTIONS => array (
			'shipmodulematch' => ML_MATCH_AMAZON_CARRIER_TO_SHIPPING_MODULE,
			'dbmatch' => ML_MATCH_CARRIER_TO_DB,
			'textfield' => ML_CARRIER_TEXTFIELD
		),
	);
	return $carrierSelection;
}

/* returns a list with extra options (show as optgroups) */
function loadShipMethods($mpID = false) {
	$carrierSelection = array (
		ML_LABEL_CHOOSE,
		'shipmodulematch' => ML_MATCH_TEXT_TO_SHIPPING_MODULE,
		ML_ADDITIONAL_OPTIONS => array (
			'dbmatch' => ML_MATCH_CARRIER_TO_DB,
			'textfield' => ML_SHIPMETHOD_TEXTFIELD
		),
	);
	return $carrierSelection;
}

function amazonDoOrderStatusSyncByTigger($mpID) {
	return false;
}

function amazonRenderOrderStatusSync($args) {
	$html = '';
	$order = $args['order'];
	if (array_key_exists('ML_LABEL_SHIPPING_DATE', $order['data'])) {
		return '';
	}
	if (isset($order['internaldata']['Request'])) {
		return '';
	}
	$carrierCodes = loadCarrierCodes($order['mpID']);
	$defaultcarrier = getDBConfigValue(
		'amazon.orderstatus.carrier.default',
		$order['mpID']
	);

	$replace = array (
		'{#TRACKING_CODE#}' => magnaAmazonFetchTrackingCode3rdParty($order['orders_id'], $order['mpID']),
		'{#CARRIERS_OPTIONS#}' => '',
	);
	foreach ($carrierCodes as $key => $val) {
		$replace['{#CARRIERS_OPTIONS#}'] .= '
			<option '.(($key == $defaultcarrier) ? 'selected="selected"' : '').' value="'.$key.'">'.$val.'</option>';
	}
	
	$htmlTmpl = '
		<tr id="amazonSending"><td class="main" colspan="2" style="padding-left: 0;">
			<table><tbody>
				<tr><td class="main"><b>'.ML_LABEL_TRACKINGCODE.':</b></td>
					<td><input type="text" name="magna[trackingcode]" value="{#TRACKING_CODE#}"/></td></tr>
				<tr><td class="main"><b>'.ML_LABEL_CARRIER.':</b></td>
					<td><select name="magna[carriercode]">{#CARRIERS_OPTIONS#}</select></td></tr>
			</tbody></table>
		</td></tr>';

	if (SHOPSYSTEM == 'gambio') {
		if ($args['view'] == 'orderDetailMulti') {
			global $content_multi_order_status;
			$content_multi_order_status[] = array (
				'text' => ML_LABEL_TRACKINGCODE.': <input type="text" name="magna[trackingcode]" value="'.$replace['{#TRACKING_CODE#}'].'"/>',
			);
			$content_multi_order_status[] = array (
				'text' => ML_LABEL_CARRIER.': <select name="magna[carriercode]">'.$replace['{#CARRIERS_OPTIONS#}'].'</select>',
			);
			return;
		} else {
			$htmlTmpl = '
				<tr><td class="main">'.ML_LABEL_TRACKINGCODE.':</td>
					<td class="main"><input type="text" name="magna[trackingcode]" value="{#TRACKING_CODE#}"/></td></tr>
				<tr><td class="main">'.ML_LABEL_CARRIER.':</td>
					<td><select name="magna[carriercode]">{#CARRIERS_OPTIONS#}</select></td></tr>';
		}
	}

	$html = str_replace(array_keys($replace), array_values($replace), $htmlTmpl);
	return $html;
}

function amazonProcessSingleOrderStatus($args) {
	$order = $args['order'];
	$mp = $order['platform'];
	$mpID = $order['mpID'];
	
	$cancelledState = getDBConfigValue($mp.'.orderstatus.cancelled', $mpID, false);
	$shippedState = getDBConfigValue($mp.'.orderstatus.shipped', $mpID, false);

	$newState = $args['status'];
	if ($newState == 'cancel') {
		$newState = $cancelledState;
	} else if ($newState == 'order') {
		$newState = $shippedState;
		$_POST['magna']['trackingcode'] = magnaAmazonFetchTrackingCode3rdParty($order['orders_id'], $order['mpID']);
		$_POST['magna']['carriercode']  = magnaAmazonFetchCarrier3rdParty($order['orders_id'], $order['mpID']);
		if (empty($_POST['magna']['carriercode'])) {
			$_POST['magna']['carriercode']  = getDBConfigValue('amazon.orderstatus.carrier.default', $order['mpID']);
		}
	}
	
	$oStatAutoSync = getDBConfigValue($mp.'.orderstatus.sync', $mpID, 'auto') == 'auto';
	
	$request = false;
	if ($newState == $shippedState) {
		$trackercode = (
			array_key_exists('magna', $_POST) 
			&& array_key_exists('trackingcode', $_POST['magna']) 
			&& !eempty(trim($_POST['magna']['trackingcode']))
				? trim($_POST['magna']['trackingcode'])
				: ''
		);
		$carrier = (
			array_key_exists('magna', $_POST) 
			&& array_key_exists('carriercode', $_POST['magna']) 
			&& !eempty(trim($_POST['magna']['carriercode']))
			&& !($_POST['magna']['carriercode'] == 'null')
				? trim($_POST['magna']['carriercode'])
				: ''
		);
		$request = array (
			'ACTION' => 'ConfirmShipment',
			'SUBSYSTEM' => 'Amazon',
			'MARKETPLACEID' => $order['mpID'],
			'DATA' => array (
				array (
					'AmazonOrderID' => $order['data']['AmazonOrderID'],
					'ShippingDate' => gmdate('Y-m-d'),
					'Carrier' => $carrier,
					'TrackingCode' => $trackercode
				)
			)
		);
	} else if ($newState == $cancelledState) {
		$request = array (
			'ACTION' => 'CancelShipment',
			'SUBSYSTEM' => 'Amazon',
			'MARKETPLACEID' => $order['mpID'],
			'DATA' => array (
				array (
					'AmazonOrderID' => $order['data']['AmazonOrderID'],
				)
			)
		);
	}
	/*/
	else {
		echo 'Do nothing';
		echo var_dump_pre($cancelledState, '$cancelledState');
		echo var_dump_pre($shippedState, 'shippedState');
	}
	//*/

	if ($request === false) return '';
	#echo print_m($request);

	if ($order['internaldata']['FulfillmentChannel'] == 'AFN') {
		$order['orders_status'] = $newState;
		magnaSaveOrder($order);
		return '';
	}

	if ($oStatAutoSync) {
		if ($newState == $shippedState) {
			unset($request['DATA'][0]['AmazonOrderID']);
			$order['internaldata']['Request'] = array (
				'Action' => $request['ACTION'],
				'Data' => $request['DATA'][0],
			);
		} else {
			$order['internaldata']['Request'] = array (
				'Action' => $request['ACTION']
			);
		}
		if (isset($trackercode)) {
			empty($trackercode) or magnaAmazonSaveTrackingCode3rdParty($order['orders_id'], $trackercode, $mpID);
		}
		if (isset($carrier)) {
			empty($carrier) or magnaAmazonSaveCarrier3rdParty($order['orders_id'], $carrier, $mpID);
		}
		magnaSaveOrder($order);
		return '';
	}

	try {
		$result = MagnaConnector::gi()->submitRequest($request);
		# $result['BatchIDs'] = array('1234768');
	} catch (MagnaException $e) {
		$result = array();
	}

	if (!isset($result['DATA'][0])) {
		return '';
	}

	foreach ($result['DATA'] as $cData) {
		if ($order['data']['AmazonOrderID'] != $cData['AmazonOrderID']) continue;
		if ($newState == $shippedState) {
			$order['data']['ML_LABEL_SHIPPING_DATE'] = date('Y-m-d H:i:s');
			$order['data']['ML_LABEL_TRACKINGCODE'] = $trackercode;
			$order['data']['ML_LABEL_CARRIER'] = $carrier;
			magnaAmazonSaveTrackingCode3rdParty($order['orders_id'], $trackercode, $order['mpID']);
			magnaAmazonSaveCarrier3rdParty($order['orders_id'], $carrier, $order['mpID']);
		} else if ($newState == $cancelledState) {
			$order['data']['ML_LABEL_ORDER_CANCELLED'] = date('Y-m-d H:i:s');
		}
		$order['data']['ML_AMAZON_LABEL_BATCHID'] = $cData['BatchID'];
		$order['orders_status'] = $newState;
		magnaSaveOrder($order);
	}
}

function amazonProcessMultiOrderStatus($args) {
/*
	echo var_export_pre($args, '$args');
	echo var_export_pre($_GET, '$_GET');
	echo var_export_pre($_POST, '$_POST');
*/
	$args['orders'] = array_values($args['orders']);
	$mpID = $args['orders'][0]['mpID'];
	$mp = $args['orders'][0]['platform'];

	$cancelledState = getDBConfigValue($mp.'.orderstatus.cancelled', $mpID, false);
	$shippedState = getDBConfigValue($mp.'.orderstatus.shipped', $mpID, false);
/*
	echo var_dump_pre($cancelledState, '$cancelledState');
	echo var_dump_pre($shippedState, '$shippedState');
	echo var_dump_pre($args['status'], '$args[status]');
*/
#	$args['status'] = '99';

	$oStatAutoSync = getDBConfigValue($mp.'.orderstatus.sync', $mpID, 'auto') == 'auto';

	$request = false;
	$preparedOrders = array();
	if ($args['status'] == $shippedState) {
		$trackercode = (
			array_key_exists('magna', $_POST) 
			&& array_key_exists('trackingcode', $_POST['magna']) 
			&& !eempty(trim($_POST['magna']['trackingcode']))
				? trim($_POST['magna']['trackingcode'])
				: ''
		);
		$carrier = (
			array_key_exists('magna', $_POST) 
			&& array_key_exists('carriercode', $_POST['magna']) 
			&& !eempty(trim($_POST['magna']['carriercode']))
			&& !($_POST['magna']['carriercode'] == 'null')
				? trim($_POST['magna']['carriercode'])
				: ''
		);
		$request = array (
			'ACTION' => 'ConfirmShipment',
			'SUBSYSTEM' => 'Amazon',
			'MARKETPLACEID' => $mpID,
			'DATA' => array (),
		);
		foreach ($args['orders'] as $o) {
			if ($o['internaldata']['FulfillmentChannel'] == 'AFN') {
				$o['orders_status'] = $args['status'];
				magnaSaveOrder($o);
				continue;
			}
			if (empty($trackercode)) {
				$trackercode = magnaAmazonFetchTrackingCode3rdParty($o['orders_id'], $mpID);
			}
			if (empty($carrier)) {
				$carrier     = magnaAmazonFetchCarrier3rdParty($o['orders_id'], $mpID);
			}
			$r = array (
				'ShippingDate' => gmdate('Y-m-d'),
				'Carrier' => $carrier,
				'TrackingCode' => $trackercode
			);
			if ($oStatAutoSync) {
				$o['internaldata']['Request'] = array (
					'Action' => $request['ACTION'],
					'Data' => $r
				);
				magnaSaveOrder($o);
				empty($trackercode) or magnaAmazonSaveTrackingCode3rdParty($o['orders_id'], $trackercode, $mpID);
				empty($carrier) or magnaAmazonSaveCarrier3rdParty($o['orders_id'], $carrier, $mpID);
			} else {
				$r['AmazonOrderID'] = $o['data']['AmazonOrderID'];
				$request['DATA'][] = $r;
				$preparedOrders[$o['data']['AmazonOrderID']] = &$o;
			}
		}

	} else if ($args['status'] == $cancelledState) {
		$request = array (
			'ACTION' => 'CancelShipment',
			'SUBSYSTEM' => 'Amazon',
			'MARKETPLACEID' => $mpID,
			'DATA' => array (),
		);
		foreach ($args['orders'] as $o) {
			if ($o['internaldata']['FulfillmentChannel'] == 'AFN') {
				$o['orders_status'] = $args['status'];
				magnaSaveOrder($o);
				continue;
			}
			if ($oStatAutoSync) {
				$o['internaldata']['Request'] = array (
					'Action' => $request['ACTION']
				);
				magnaSaveOrder($o);
			} else {
				$request['DATA'][] = array (
					'AmazonOrderID' => $o['data']['AmazonOrderID'],
				);
				$preparedOrders[$o['data']['AmazonOrderID']] = &$o;	
			}
		}
	}

	if (!is_array($request) || !isset($request['DATA']) || empty($request['DATA'])) {
		return '';
	}
	#echo print_m($request);

	if ($oStatAutoSync) {
		return '';
	}

	/*
	$result = array('BatchIDs' => '');
	/*/
	try {
		$result = MagnaConnector::gi()->submitRequest($request);
		# $result['BatchIDs'] = array('1234768');
	} catch (MagnaException $e) {
		$result = array();
	}
	//*/

	if (!isset($result['DATA'][0])) {
		return '';
	}

	foreach ($result['DATA'] as $cData) {
		if (!isset($preparedOrders[$cData['AmazonOrderID']])) continue;
		$o = &$preparedOrders[$cData['AmazonOrderID']];
		if ($args['status'] == $shippedState) {
			$o['data']['ML_LABEL_SHIPPING_DATE'] = date('Y-m-d H:i:s');
			$o['data']['ML_LABEL_TRACKINGCODE'] = $trackercode;
			$o['data']['ML_LABEL_CARRIER'] = $carrier;
			empty($trackercode) or magnaAmazonSaveTrackingCode3rdParty($o['orders_id'], $trackercode, $o['mpID']);
			empty($carrier) or magnaAmazonSaveCarrier3rdParty($o['orders_id'], $carrier, $o['mpID']);
		} else if ($args['status'] == $cancelledState) {
			$o['data']['ML_LABEL_ORDER_CANCELLED'] = date('Y-m-d H:i:s');
		}
		$o['data']['ML_AMAZON_LABEL_BATCHID'] = $cData['BatchID'];
		$o['orders_status'] = $args['status'];
		#echo print_m($o, '$o');
		magnaSaveOrder($o);
	}
	#die();
}

function magnaAmazonRunDbMatching($tableSettings, $defaultAlias, $where) {
    if (   !isset($tableSettings['Table']['table'])
        || empty($tableSettings['Table']['table'])
        || empty($tableSettings['Table']['column'])
    ) {
        return false;
    }
    if (empty($tableSettings['Alias'])) {
        $tableSettings['Alias'] = $defaultAlias;
    }

    return (string)MagnaDB::gi()->fetchOne('
        SELECT `'.$tableSettings['Table']['column'].'` 
          FROM `'.$tableSettings['Table']['table'].'` 
         WHERE `'.$tableSettings['Alias'].'` = "'.MagnaDB::gi()->escape($where).'"
               AND `'.$tableSettings['Table']['column'].'` <> \'\'
         LIMIT 1
    ');
}

function magnaAmazonFetchTrackingCode3rdParty($oID, $mpID) {
    $mTrackingCode = magnaAmazonRunDbMatching(array (
        'Table' => getDBConfigValue('amazon.orderstatus.carrier.trackingcode.table', $mpID, false),
        'Alias' => getDBConfigValue('amazon.orderstatus.carrier.trackingcode.alias', $mpID, false),
    ), 'orders_id', $oID);

    // for Gambio 2.3 > if table "orders_parcel_tracking_codes" exists
    if (false == $mTrackingCode && MagnaDB::gi()->tableExists('orders_parcel_tracking_codes')) {
        $mTrackingCode = MagnaDB::gi()->fetchOne("
            SELECT tracking_code
              FROM orders_parcel_tracking_codes
             WHERE order_id = '".MagnaDB::gi()->escape($oID)."'
             LIMIT 1
        ");
    }

    // for modified 2.0 > if table "orders_tracking" exists
    if (false == $mTrackingCode && MagnaDB::gi()->tableExists('orders_tracking')) {
        $mTrackingCode = MagnaDB::gi()->fetchOne("
            SELECT parcel_id
              FROM orders_tracking
             WHERE orders_id = '".MagnaDB::gi()->escape($oID)."'
             LIMIT 1
        ");
    }

    return $mTrackingCode;
}

function magnaAmazonSaveTrackingCode3rdParty($oID, $tc, $mpID) {
	$table = getDBConfigValue('amazon.orderstatus.carrier.trackingcode.table', $mpID, false);
	if ($table === false || empty($table['column']) || empty($table['table'])) return;
	$cIDAlias = getDBConfigValue('amazon.orderstatus.carrier.trackingcode.alias', $mpID);
	if (empty($cIDAlias)) {
		$cIDAlias = 'orders_id';
	}
	MagnaDB::gi()->update($table['table'], array (
		$table['column'] => trim($tc),
	), array (
		$cIDAlias => $oID,
	));
}

function magnaAmazonFetchCarrier3rdParty($oID, $mpID) {
    $mCarrier = magnaAmazonRunDbMatching(array (
        'Table' => getDBConfigValue('amazon.orderstatus.carrier.carrierDBMatching.table', $mpID, false),
        'Alias' => getDBConfigValue('amazon.orderstatus.carrier.carrierDBMatching.alias', $mpID, false),
    ), 'orders_id', $oID);

    // for Gambio 2.3 > if table "orders_parcel_tracking_codes" exists
    if (false == $mCarrier && MagnaDB::gi()->tableExists('orders_parcel_tracking_codes')) {
        $mCarrier = MagnaDB::gi()->fetchOne("
            SELECT parcel_service_name
              FROM orders_parcel_tracking_codes
             WHERE order_id = '".MagnaDB::gi()->escape($oID)."'
             LIMIT 1
        ");
    }

    // for modified 2.0+ > if table "orders_tracking" exists
    if (false == $mCarrier && MagnaDB::gi()->tableExists('orders_tracking')) {
        $sCarrierId = MagnaDB::gi()->fetchOne("
            SELECT carrier_id
              FROM orders_tracking
             WHERE orders_id = '".MagnaDB::gi()->escape($oID)."'
             LIMIT 1
        ");
        if (!empty($sCarrierId)) {
            $mCarrier = MagnaDB::gi()->fetchOne("
                SELECT carrier_name
                  FROM carriers
                 WHERE carrier_id = '".MagnaDB::gi()->escape($sCarrierId)."'
                 LIMIT 1
            ");
        }
    }

    return $mCarrier;
}

function magnaAmazonSaveCarrier3rdParty($oID, $carrier, $mpID) {
	$table = getDBConfigValue('amazon.orderstatus.carrier.carrierDBMatching.table', $mpID, false);
	if ($table === false || empty($table['column']) || empty($table['table'])) return;
	$cIDAlias = getDBConfigValue('amazon.orderstatus.carrier.carrierDBMatching.alias', $mpID);
	if (empty($cIDAlias)) {
		$cIDAlias = 'orders_id';
	}
	MagnaDB::gi()->update($table['table'], array (
		$table['column'] => trim($carrier),
	), array (
		$cIDAlias => $oID,
	));
}

function autoupdateAmazonOrdersStatus($mpID) {
	$mp = 'amazon';
	if (getDBConfigValue($mp.'.orderstatus.sync', $mpID, 'no') != 'auto') {
		return false;
	}
	$orders = MagnaDB::gi()->fetchArray(eecho('
	    SELECT mo.orders_id, mo.orders_status, mo.data, mo.internaldata, 
	           o.orders_status AS orders_status_shop
	      FROM `'.TABLE_MAGNA_ORDERS.'` mo, `'.TABLE_ORDERS.'` o
	     WHERE mo.orders_id=o.orders_id
	           AND mo.mpID=\''.$mpID.'\'
	           AND mo.orders_status<>o.orders_status
	', function_exists('ml_debug_out')));
	if (function_exists('ml_debug_out')) ml_debug_out(print_m($orders, '$orders'));
	if (empty($orders)) return true;

	$mp = 'amazon';
	$cancelledState = getDBConfigValue($mp.'.orderstatus.cancelled', $mpID, false);
	$shippedState = getDBConfigValue($mp.'.orderstatus.shipped', $mpID, false);
	
	$carrierDefault = getDBConfigValue($mp.'.orderstatus.carrier.default', $mpID, '');

	$confirmations = array();
	$cancellations = array();
	$unprocessed = array();

	$preparedOrders = array();

	$iCounter = 0;
	foreach ($orders as $key => &$order) {
		$order['data'] = @unserialize($order['data']);
		if (!is_array($order['data'])) {
			$order['data'] = array();
		}
		$order['internaldata'] = @unserialize($order['internaldata']);
		if (!is_array($order['internaldata'])) {
			$order['internaldata'] = array();
		}
		
		$status = $order['orders_status_shop'];
		unset($order['orders_status_shop']);

		if (   (($status != $shippedState) && ($status != $cancelledState))
			|| ($order['internaldata']['FulfillmentChannel'] == 'AFN')
		) {
			$unprocessed[$order['orders_id']] = $order['orders_status_shop'];
			unset($orders[$key]);
			continue;
		}
		$iCounter++;

		if ($status == $shippedState) {
			$sSortOrder = 'ASC';
		} else {
			$sSortOrder = 'DESC';
		}
		$date = MagnaDB::gi()->fetchOne('
		    SELECT date_added FROM `'.TABLE_ORDERS_STATUS_HISTORY.'`
		     WHERE orders_id='.$order['orders_id'].'
		           AND orders_status_id='.$status.'
		  ORDER BY date_added '.$sSortOrder.'
		     LIMIT 1
		');

		if ($date === false) {
			$date = date('Y-m-d');
		} else {
			$date = date('Y-m-d', strtotime($date));
		}

		if ($status == $shippedState) {
			$trackercode = magnaAmazonFetchTrackingCode3rdParty($order['orders_id'], $mpID);
			$carrier = magnaAmazonFetchCarrier3rdParty($order['orders_id'], $mpID);
			if (empty($carrier)) {
				$carrier = $carrierDefault;
			}
			if (isset($order['internaldata']['Request']['Data'])) {
				$cfirm = $order['internaldata']['Request']['Data'];
                // if we send tracking again don't forget the carrier and tracking code
                if (empty($cfirm['Carrier'])) {
                    $cfirm['Carrier'] = $carrier;
                }
                if (empty($cfirm['TrackingCode'])) {
                    $cfirm['TrackingCode'] = $trackercode;
                }
				if (   array_key_exists('ShippingDate', $cfirm)
				    && ($cfirm['ShippingDate'] > $date)
                ) {
					// use the first found ShippingDate for Shipping confirmations
					$cfirm['ShippingDate'] = $date;
				}
				$cfirm['AmazonOrderID'] = $order['data']['AmazonOrderID'];
				unset($order['internaldata']['Request']);
			} else {
				$cfirm = array (
					'AmazonOrderID' => $order['data']['AmazonOrderID'],
					'ShippingDate' => $date,
					'Carrier' => $carrier,
					'TrackingCode' => $trackercode
				);
			}
			$confirmations[] = $cfirm;
			$order['data']['ML_LABEL_SHIPPING_DATE'] = $cfirm['ShippingDate'];
			if (!empty($cfirm['TrackingCode'])) {
				$order['data']['ML_LABEL_TRACKINGCODE'] = $cfirm['TrackingCode'];
			}
			$order['data']['ML_LABEL_CARRIER'] = $carrier;
		} else if ($status == $cancelledState) {
			if (isset($order['internaldata']['Request'])) {
				unset($order['internaldata']['Request']);
			}
			$cancellations[] = array (
				'AmazonOrderID' => $order['data']['AmazonOrderID'],
			);
			$order['data']['ML_LABEL_ORDER_CANCELLED'] = $date;
		}
		$order['orders_tmp_status'] = $status;
		//$order['internaldata'] = serialize($order['internaldata']);
		if (isset($preparedOrders[$order['data']['AmazonOrderID']]) && ($status != $shippedState)) {
			/* This is a lie, but meh... the result will be correct. */
			$unprocessed[$preparedOrders[$order['data']['AmazonOrderID']]['orders_id']] = $status;
		}
		$preparedOrders[$order['data']['AmazonOrderID']] = &$order;
		if ($iCounter > 99) {
			break;
		}
	}
	$confirmedOrders = array();
	$cancelledOrders = array();

	$successfullySubmittedOrders = array();
	if (!empty($confirmations)) {
		$request = array (
			'ACTION' => 'ConfirmShipment',
			'SUBSYSTEM' => 'Amazon',
			'MARKETPLACEID' => $mpID,
			'DATA' => $confirmations,
		);
		if (defined('MAGNA_ECHO_UPDATE') && MAGNA_ECHO_UPDATE) {
			ml_debug_out(print_m($request, 'confirmations'));
		} else {
			//echo var_export_pre($request, '$requestConfirm');
			try {
				$result = MagnaConnector::gi()->submitRequest($request);
			} catch (MagnaException $e) {
				$result = array();
			}
			//echo var_export_pre($result, '$resultConfirm');
			if (isset($result['DATA'][0])) {
				foreach ($result['DATA'] as $cData) {
					if (!isset($preparedOrders[$cData['AmazonOrderID']])) continue;
					$tO = &$preparedOrders[$cData['AmazonOrderID']];
					if (!isset($tO['orders_tmp_status'])) {
						$unprocessed[$tO['orders_id']] = $tO['orders_status_shop'];
						continue;
					}
					$tO['orders_status'] = $tO['orders_tmp_status'];
					unset($tO['orders_tmp_status']);
					$tO['data']['ML_AMAZON_LABEL_BATCHID'] = $cData['BatchID'];
					$successfullySubmittedOrders[$cData['AmazonOrderID']] = &$tO;
				}
			}
		}
	}

	if (!empty($cancellations)) {
		$request = array (
			'ACTION' => 'CancelShipment',
			'SUBSYSTEM' => 'Amazon',
			'MARKETPLACEID' => $mpID,
			'DATA' => $cancellations,
		);
		if (defined('MAGNA_ECHO_UPDATE') && MAGNA_ECHO_UPDATE) {
			ml_debug_out(print_m($request, 'cancellations'));
		} else {
			//echo var_export_pre($request, '$requestCancel');
			try {
				$result = MagnaConnector::gi()->submitRequest($request);
			} catch (MagnaException $e) {
				$result = array();
			}
			//echo var_export_pre($result, '$resultCancel');
			if (isset($result['DATA'][0])) {
				foreach ($result['DATA'] as $cData) {
					if (!isset($preparedOrders[$cData['AmazonOrderID']])) continue;
					$tO = &$preparedOrders[$cData['AmazonOrderID']];
					if (!isset($tO['orders_tmp_status'])) {
                        $unprocessed[$tO['orders_id']] = $tO['orders_status_shop'];
						continue;
					}
					$tO['orders_status'] = $tO['orders_tmp_status'];
					unset($tO['orders_tmp_status']);
					$tO['data']['ML_AMAZON_LABEL_BATCHID'] = $cData['BatchID'];
					$successfullySubmittedOrders[$cData['AmazonOrderID']] = &$tO;
				}
			}
		}
	}
	
	if (!empty($unprocessed)) {
	    // just set the order status for an order that will not be processed to the value that it have at the beginning of the process
        //  not that is in the database because while processing it can be changed
        foreach ($unprocessed as $sOrderId => $sOrderStatus) {
            MagnaDB::gi()->update(TABLE_MAGNA_ORDERS, array(
                'orders_status' => $sOrderStatus
            ), array(
                'orders_id' => $sOrderId,
                'mpID' => $mpID,
            ));
        }
		if (function_exists('ml_debug_out')) ml_debug_out(print_m($unprocessed, '$unprocessed'));
	}
	if (empty($successfullySubmittedOrders)) return true;
	foreach ($successfullySubmittedOrders as $o) {
		#echo print_m($o);
		$o['data'] = serialize($o['data']);
		$o['internaldata'] = serialize($o['internaldata']);
		//*
		MagnaDB::gi()->update(TABLE_MAGNA_ORDERS, $o, array(
			'orders_id' => $o['orders_id']
		));
		//*/
	}
	return true;
}

function getAmazonOfferLink($sAsin, $sTitle) {
	if (empty($sAsin)) {
		return '&mdash;';
	} else {
		global $_MagnaSession;
		$sAmazonSite = strtolower(getDBConfigValue('amazon.site', $_MagnaSession['mpID'], 'de'));
        switch ($sAmazonSite) {
            case 'jp': {
                $sAmazonSite = 'co.jp';
                break;
            }
            case 'us': {
                $sAmazonSite = 'com';
                break;
            }
            case 'uk': {
                $sAmazonSite = 'co.uk';
                break;
            }
            case 'tr': {
                $sAmazonSite = 'com.tr';
                break;
            }
            case 'au': {
                $sAmazonSite = 'com.au';
                break;
            }
            default: {//fr, in, it, ca, es, cn
            }
        }
        return '<a href="https://www.amazon.' . $sAmazonSite . '/dp/' . $sAsin . '" title="' . $sTitle . '" target="_blank">' . $sAsin . '</a>';
    }
}

function amazonMfsGetConfigurationValues($sType = null) {
	global $cacheMFSGetConfigurationValues;
	if(!isset($cacheMFSGetConfigurationValues)){
		try {
		    $aResponse = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'MFS_GetConfigurationValues',
		    ));
		    if (array_key_exists('DATA', $aResponse)) {
			$cacheMFSGetConfigurationValues = $aResponse['DATA'];
		    } else {
			$cacheMFSGetConfigurationValues =  array();
		    }
		} catch (Exception $oEx) {
		    return array();
		}
	}
	if ($sType === null) {
		$return = $cacheMFSGetConfigurationValues;
	} elseif (array_key_exists($sType, $cacheMFSGetConfigurationValues)) {
		$return = $cacheMFSGetConfigurationValues[$sType];
	} else {
		$return = $sType;
	}
	return $return;
}

/*
 Helper function: Add id to carrier db matching + mp to shop matching
 so that its visibility can be controlled via js
 and the same for shipping method
*/
function extendCarrierConfig(&$sConfigForm) {
	// add id to carrier db matching
	$sCarrierDBMatching_table_label_pos = strpos($sConfigForm, 'config_amazon_orderstatus_carrier_carrierDBMatching_table');
	$iTrPos = strrpos(substr($sConfigForm, 0, $sCarrierDBMatching_table_label_pos), '<tr');
	$sConfigForm = substr($sConfigForm, 0, $iTrPos + 3)
		.' id="config_amazon_orderstatus_carrier_carrierDBMatching_table" '
		.substr($sConfigForm,$iTrPos + 4);
	// add id to mp carrier to shop shippingmodule matching
	$sCarrierAmazonToShopMatching_table_label_pos = strpos($sConfigForm, 'config_amazon_orderstatus_carrier_carrierAmazonToShopMatching');
	$iTrPos = strrpos(substr($sConfigForm, 0, $sCarrierAmazonToShopMatching_table_label_pos), '<tr');
	$sConfigForm = substr($sConfigForm, 0, $iTrPos + 3)
		.' id="config_amazon_orderstatus_carrier_carrierAmazonToShopMatching" '
		.substr($sConfigForm,$iTrPos + 4);

	// add id to shipping method db matching
	$sCarrierDBMatching_table_label_pos = strpos($sConfigForm, 'config_amazon_orderstatus_shipmethod_shipmethodDBMatching_table');
	$iTrPos = strrpos(substr($sConfigForm, 0, $sCarrierDBMatching_table_label_pos), '<tr');
	$sConfigForm = substr($sConfigForm, 0, $iTrPos + 3)
		.' id="config_amazon_orderstatus_shipmethod_shipmethodDBMatching_table" '
		.substr($sConfigForm,$iTrPos + 4);
	// add id to textfield to shop shippingmodule matching
	$sCarrierAmazonToShopMatching_table_label_pos = strpos($sConfigForm, 'config_amazon_orderstatus_shipmethod_shipmethodAmazonToShopMatching');
	$iTrPos = strrpos(substr($sConfigForm, 0, $sCarrierAmazonToShopMatching_table_label_pos), '<tr');
	$sConfigForm = substr($sConfigForm, 0, $iTrPos + 3)
		.' id="config_amazon_orderstatus_shipmethod_shipmethodAmazonToShopMatching" '
		.substr($sConfigForm,$iTrPos + 4);

	return;
}

/*
 Helper function: upgrade order sync setting
*/
function upgradeOrderSyncSettings() {
	global $_MagnaSession;
	if (getDBConfigValue('amazon.orderstatus.shipped.shipped', $_MagnaSession['mpID'], false) !== false) {
		return;
	}
	setDBConfigValue('amazon.orderstatus.shipped.shipped', $_MagnaSession['mpID'], array (
		getDBConfigValue('amazon.orderstatus.shipped', $_MagnaSession['mpID'], false)
	), true);
	setDBConfigValue('amazon.orderstatus.shipped', $_MagnaSession['mpID'], array (
		'defaults' => array ('1')
	), true);
	if (getDBConfigValue('amazon.orderstatus.carrier.default', $_MagnaSession['mpID'], '') != '') {
		setDBConfigValue('amazon.orderstatus.carrier.textfield', $_MagnaSession['mpID'], getDBConfigValue('amazon.orderstatus.carrier.default', $_MagnaSession['mpID'], ''), true);
		setDBConfigValue('amazon.orderstatus.carrier.default', $_MagnaSession['mpID'], 'textfield', true);
	} else {
		$aCarrierDBMatchingTable = getDBConfigValue('amazon.orderstatus.carrier.carrierDBMatching.table', $_MagnaSession['mpID'], false);
		if (!empty($aCarrierDBMatchingTable['table'])) {
			setDBConfigValue('amazon.orderstatus.carrier.default', $_MagnaSession['mpID'], 'dbmatch', true);
		}
        }
}
