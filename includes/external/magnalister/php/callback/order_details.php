<?php
/*
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
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

function magnaHasOrderDetails($args) {
	return MagnaDB::gi()->fetchOne(
		'SELECT platform FROM '.TABLE_MAGNA_ORDERS.' WHERE orders_id=\''.MagnaDB::gi()->escape($args['oID']).'\' LIMIT 1'
	);
}

function magnaLoadOrder($oID, $mpID = false) {
	$order = MagnaDB::gi()->fetchRow('
	    SELECT mo.*, o.currency AS Currency, currency_value AS CurrencyValue 
	      FROM '.TABLE_MAGNA_ORDERS.' mo
	INNER JOIN '.TABLE_ORDERS.' o ON mo.orders_id = o.orders_id
	     WHERE mo.orders_id="'.$oID.'"
	           '.(($mpID != false) ? 'AND mo.mpID="'.$mpID.'"' : '').'
	');
	# Not ours.
	if ($order === false) {
		return false;
	}
	$order['data'] = @unserialize($order['data']);
	if (!is_array($order['data'])) {
		$order['data'] = array();
	}
	$order['internaldata'] = @unserialize($order['internaldata']);
	if (!is_array($order['internaldata'])) {
		$order['internaldata'] = array();
	}
	return $order;
}

function magnaSaveOrder($order) {
	$order['data'] = serialize($order['data']);
	$order['internaldata'] = serialize($order['internaldata']);
	foreach ($order as $ColumnName => $val) {
		if (!MagnaDB::gi()->columnExistsInTable($ColumnName, TABLE_MAGNA_ORDERS))
			unset($order["$ColumnName"]);
	}
	MagnaDB::gi()->update(TABLE_MAGNA_ORDERS, $order, array(
		'orders_id' => $order['orders_id']
	));
}

function magnaGetOrderPlatformIcon($order) {
	global $_modules;
	if (isset($_modules[$order['platform']]['logo'])) {
		$logo = $_modules[$order['platform']]['logo'];
	} else {
		$logo = $order['platform'];
	}
	$filename = '';
	if ($logo == 'amazon') {
		$fulfillment = $order['internaldata']['FulfillmentChannel'];

		if ($fulfillment !== 'MFN-Prime' && $fulfillment != 'MFN' && $fulfillment != 'Business') {
            $filename = 'amazon_fba';

            if (isset($order['internaldata']['IsBusinessOrder']) && $order['internaldata']['IsBusinessOrder'] == 'true') {
                $filename .= '_business';
            }

            $filename .= '_orderview';
		} else {
			$suffix = '';
			if ($fulfillment === 'MFN-Prime') {
				$suffix = '_prime';
                if (isset($order['internaldata']['ShipServiceLevel'])) {
                    $sShipServiceLevel = $order['internaldata']['ShipServiceLevel'];
                    if ($sShipServiceLevel === 'NextDay') {
                        $suffix .= '_nextday';
                    } else if ($sShipServiceLevel === 'SameDay') {
                        $suffix .= '_sameday';
                    } else if ($sShipServiceLevel === 'SecondDay') {
                        $suffix .= '_secondday';
                    }
                }
			} elseif ($fulfillment === 'Business') {
				$suffix = '_business';
			}

			$filename = 'amazon_orderview'.$suffix;
			if (    (isset($order['data']['ML_AMAZON_LABEL_BATCHID']) && !empty($order['data']['ML_AMAZON_LABEL_BATCHID']))
			     || (isset($order['data']['BatchID']) && !empty($order['data']['BatchID']))) {
				if (isset($order['data']['ML_ERROR_LABEL'])) {
					$filename = 'amazon_orderview_error';
				} else if (isset($order['data']['ML_LABEL_ORDER_CANCELLED'])) {
					$filename = 'amazon_orderview_cancelled'.$suffix;
				} else if (isset($order['data']['ML_LABEL_SHIPPING_DATE'])) {
					$filename = 'amazon_orderview_shipped'.$suffix;
				}
			}
		}
	}
	if ($logo == 'ebay') {
		if (array_key_exists('eBayPlus', $order['data'])) {
			$filename = 'ebay_plus_orderview';
		} else {
			$filename = 'ebay_orderview';
		}
	}
    if ('kaufland' == $logo) {
        $filename = 'kaufland_orderview';
        if (is_array($order['data']) && array_key_exists('FulfillmentType', $order['data']) &&
            'fulfilled_by_kaufland' == $order['data']['FulfillmentType']
        ) {
            $filename .= '_fbk';
        }
    }
	if (empty($filename)) {
		$filename = $logo.'_orderview';
	}
	return $filename;
}

function magnaRenderOrderPlatformIcon($args) {
	$order = magnaLoadOrder(MagnaDB::gi()->escape($args['oID']));
	if ($order === false) {
		return '';
	}
	$filename = magnaGetOrderPlatformIcon($order);

	if (defined('ML_GAMBIO_USE_IFRAME') && ML_GAMBIO_USE_IFRAME === true) {
		return '<img class="magnaOrderIcon magna_'.$order['platform'].'" src="'.DIR_MAGNALISTER_WS.'images/logos/'.$filename.'.png">';
	}

	return ' style="
		background-image: url('.DIR_MAGNALISTER_WS.'images/logos/'.$filename.'.png);
		background-repeat: no-repeat;
		background-position: 99% 60%;"';
}

function magnaRenderOrderDetails($args) {
	/* Description of Modules */
	include(DIR_MAGNALISTER_FS_INCLUDES.'modules.php');
	$details = magnaLoadOrder(MagnaDB::gi()->escape($args['oID']));
	if ($details === false) {
		return '';
	}
	$filename = magnaGetOrderPlatformIcon($details);

	$html = '
		<style type="text/css">
div.magnaOrderHeadline {
	color: #000000;
	font: 14px sans-serif;
	font-weight: bold;
}
div.magnaOrderHeadline span {
	color: #DC043D;
}
div.magnaOrderHeadline img {
	vertical-align: middle;
}
table.magnaOrderDetails {
    width: 100%;
    font: 11px sans-serif;
    border: 1px solid #999;
    margin: 0.5em 0 2em 0;
    border-collapse: separate;
    border-spacing: 1px;
}
table.magnaOrderDetails.onlyForGambio {
    width: 100%;
    font: 11px sans-serif;
    border: 0px;
    margin: 0;
    border-collapse: unset;
    border-spacing: 0px;
}

table.magnaOrderDetails tbody tr td {
	padding: 4px 6px;
}
table.magnaOrderDetails tbody tr.odd td {
	background: #eaeaea;
	border-top: 1px solid #e0e0e0;
	border-bottom: 1px solid #e0e0e0;
}
table.magnaOrderDetails tbody tr.even td {
	background: #fff;
	border-top: 1px solid #fafafa;
	border-bottom: 1px solid #fafafa;
}
table.magnaOrderDetails tbody tr td.key {
	white-space: nowrap;
	width: 15em;
} 
div.removePadding {
    padding: 0px !important;
}
</style>
		<script>
		$( "div:has(table.onlyForGambio)" ).addClass( "removePadding" );
		</script> 
';
    if (isset($args['separate']) && $args['separate'] === true) {
        $html .= '<table class="magnaOrderDetails onlyForGambio"><tbody>';

    } else {
        $html .= '<table class="magnaOrderDetails"><tbody>';
    }

	$hedaline = '		<div class="magnaOrderHeadline">
			<span>m</span>agnalister Details
			<img src="'.DIR_MAGNALISTER_WS.'images/logos/'.$filename.'.png" alt="'.$filename.'">
		</div>';
	$isOdd = true;
	foreach ($details['data'] as $key => $value) {
		if (defined($key)) {
			$key = constant($key);
		}
		if (!is_array($value) && defined($value)) {
			if ($value == 'ML_GENERIC_ORDER_THROUGH_COMPARISON_SHOPPING') {
				if (array_key_exists($details['platform'], $_modules)) {
					$title = $_modules[$details['platform']]['title'];
				} else {
					$title = ML_LABEL_UNKNOWN;
				}
				$value = sprintf(constant($value), $title);
			} else {
				$value = constant($value);
			}
		} else if (is_array($value)) {
			$value = implode("<br>\n", $value);
		} 
		
		$html .= '
		<tr class="'.(($isOdd = !$isOdd) ? 'odd' : 'even').'">
			<td class="key">'.$key.'</td>
			<td class="value">'.$value.'</td>
		</tr>';
	}
	
	if (defined('DEFAULT_CURRENCY') && (DEFAULT_CURRENCY != $details['Currency'])) {
		$total = (float)MagnaDB::gi()->fetchOne('
			SELECT `value` FROM orders_total WHERE orders_id='.$args['oID'].' AND `class`="ot_total"
		');
		if ($total > 0) {
			$total = round($total * 1 / $details['CurrencyValue'], 2);
			$text = str_replace(array (
				'{#Currency#}', '{#CurrencyValue#}', '{#DefaultCurrency#}', '{#Value#}'
			), array (
				$details['Currency'], (float)$details['CurrencyValue'], DEFAULT_CURRENCY, $total
			), ML_GENERIC_ORDER_DETAILS_CURRENCY_CONVERSION_TEXT);
			$html .= '
		<tr class="'.(($isOdd = !$isOdd) ? 'odd' : 'even').'">
			<td class="key">'.ML_GENERIC_ORDER_DETAILS_CURRENCY_CONVERSION_LABEL.'</td>
			<td class="value">'.$text.'</td>
		</tr>';
		}
	}
	
	$html .= '
		</tbody></table>';

	if (isset($args['separate']) && $args['separate'] === true) {
        $html .= '';
	    $result = array (
	        'headline' => $hedaline,
            'body' => $html
        );
    } else {
	    $result = $hedaline . $html;
    }

	
	return $result;
}

function magnaInsertOrderDetails($args) {
	global $magnaConfig;
	
	$modules = array_unique(array_values($magnaConfig['maranon']['Marketplaces']));
	if (!array_key_exists('magnalister', $_SESSION) || !is_array($_SESSION['magnalister'])
		|| !array_key_exists('camefrom', $_SESSION['magnalister']) || !is_array($_SESSION['magnalister']['camefrom'])
	) {
		return;
	}
	$info = $_SESSION['magnalister']['camefrom'];
	$boughtItems = magnaExecute('magnaGetCartContents', array(), array('inventoryUpdate.php'));

	MagnaDB::gi()->insert(TABLE_MAGNA_ORDERS, array(
		'orders_id' => $args['oID'],
		'data' => serialize(array(
			'ML_LABEL_NOTE' => 'ML_GENERIC_ORDER_THROUGH_COMPARISON_SHOPPING',
		)),
		'platform' => $_SESSION['magnalister']['camefrom']['Marketplace'],
		'mpID' => $_SESSION['magnalister']['camefrom']['MarketplaceID']
	), true);
	
	$tmpBI = array();
	if (!empty($boughtItems)) {
		foreach ($boughtItems as $pID => $detail) {
			$tmpBI[] = array(
				'SKU' => magnaPID2SKU($pID),
				'Quantity' => $detail['Quantity'],
				'Price' => $detail['Price'],
				'Currency' => $detail['Currency'],
			);
		}
	}

	$data = array (
		'OrderID' => $args['oID'],
		'Marketplace' => $info['Marketplace'],
		'MarketplaceID' => $info['MarketplaceID'],
		'IP' => $info['IP'],
		'BoughtItems' => $tmpBI,
		'DateTime' => $info['DateTime'],
	);
	#echo print_m($data);
	try {
		$res = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'CollectOrderStats',
			'SUBSYSTEM' => 'Core',
			'DATA' => $data
		));
	} catch (MagnaException $e) {
		if ($e->getCode() == MagnaException::TIMEOUT) {
			$e->saveRequest();
			$e->setCriticalStatus(false);
		}
		$res = $e->getErrorArray();
	}
	if (MAGNA_CALLBACK_MODE == 'STANDALONE') {
	#	echo print_m($res);
	}

}

function magnaSubmitOrderStatus($args) {
/* 
Bei Statusaenderung in Detailansicht:
$_POST = array (
    'comments' => '',
    'status' => '3',
    'notify' => 'on',
	'magna' => array (
		'trackingcode' => '',
		'carriercode' => 'Postbote',
	),
);
$_GET = array (
    'page' => '1',
    'oID' => '400289',
    'action' => 'update_order',
);

# Bei $args['action'] == 'gm_send_order'
$_POST = array();
$_GET = array (
    'oID' => '400289',
    'type' => 'order',
);

# Bei $argsv == array()
# InfoBox: "Bestellstatus für mehrere Bestellungen gleichzeitig ändern"
$args = array ();
$_GET = array (
  'page' => '1',
  'oID' => '400289',
  'action' => 'gm_multi_status',
);
$_POST = array (
  'gm_multi_status' => array (
    0 => '400247',
    1 => '400246',
    2 => '400253',
    3 => '400262',
    4 => '400213',
  ),
  'XTCsid' => '87i0ivoimfm7ivt05n07p6jla6',
  'action' => 'gm_multi_status',
  'page' => '2',
  'gm_status' => '3',
  'magna' => array (
    'trackingcode' => '',
    'carriercode' => 'Postbote',
  ),
  'gm_comments' => '',
);
*/

/*
	echo var_export_pre($args, '$args');
	echo var_export_pre($_GET, '$_GET');
	echo var_export_pre($_POST, '$_POST');
*/
	if (isset($_POST['action']) && ($_POST['action'] == 'gm_multi_status') 
		&& isset($_GET['oID'])
	) {
		# MultiConfirm
		if (!array_key_exists('gm_multi_status', $_POST) 
			|| !is_array($_POST['gm_multi_status']) 
			|| empty($_POST['gm_multi_status'])
		) return '';

		if (!ctype_digit($_POST['gm_status'])) return '';

		$oID = MagnaDB::gi()->escape($_GET['oID']);
		$order = magnaLoadOrder($oID);
		if ($order === false) return '';

		$orderIDs = $_POST['gm_multi_status'];
		
		$incl = DIR_MAGNALISTER_FS_MODULES.$order['platform'].'/'.$order['platform'].'Functions.php';
		if (!file_exists($incl)) return '';
		require_once($incl);

		$func = $order['platform'].'ProcessMultiOrderStatus';
		if (!function_exists($func)) return '';
		$doFunc = $order['platform'].'DoOrderStatusSyncByTigger';
		if (!function_exists($doFunc)) return '';

		loadDBConfig($order['mpID']);

		if (!$doFunc($order['mpID'])) {
			return '';
		}

		$orders = array($order['orders_id'] => $order);
		foreach ($orderIDs as $oID) {
			$oID = MagnaDB::gi()->escape($oID);
			$tO = magnaLoadOrder($oID, $order['mpID']);
			if ($tO === false) continue;
			$orders[$oID] = $tO;
		}

		$nuArgs = array(
			'orders' => $orders,
			'status' => $_POST['gm_status'],
		);
		return $func($nuArgs);
		#echo var_export_pre($nuArgs, $func.'($nuArgs)');
		#die();

	} else if (isset($_GET['oID']) && (isset($_POST['status']) || isset($_GET['type']))) {
		# SingleConfirm
		$oID = MagnaDB::gi()->escape($_GET['oID']);
		$order = magnaLoadOrder($oID);
		if ($order === false) return '';

		$incl = DIR_MAGNALISTER_FS_MODULES.$order['platform'].'/'.$order['platform'].'Functions.php';
		if (!file_exists($incl)) return '';
		require_once($incl);

		$func = $order['platform'].'ProcessSingleOrderStatus';
		if (!function_exists($func)) return '';
		$doFunc = $order['platform'].'DoOrderStatusSyncByTigger';
		if (!function_exists($doFunc)) return '';

		loadDBConfig($order['mpID']);

		if (!$doFunc($order['mpID'])) {
			return '';
		}

		$status = false;
		if (isset($_POST['status'])) {
			$status = $_POST['status'];
		} else if (isset($_GET['type']) && (($_GET['type'] == 'cancel') || ($_GET['type'] == 'order'))) {
			$status = $_GET['type'];
		}
		if ($status === false) return '';

		$nuArgs = array(
			'order' => $order,
			'status' => $status,
		);
		return $func($nuArgs);
		#echo var_export_pre($nuArgs, $func.'($nuArgs)');
		#die();

	}

}

function magnaRenderOrderStatusSync($args) {
	$html = '';
	if (isset($_GET['oID'])) {
		$oID = MagnaDB::gi()->escape($_GET['oID']);
		$order = magnaLoadOrder($oID);
		#echo print_m($order, '$order');
		if ($order === false) return '';

		$incl = DIR_MAGNALISTER_FS_MODULES.$order['platform'].'/'.$order['platform'].'Functions.php';
		if (!file_exists($incl)) return '';
		require_once($incl);

		$func = $order['platform'].'RenderOrderStatusSync';
		if (!function_exists($func)) return '';
		$doFunc = $order['platform'].'DoOrderStatusSyncByTigger';
		if (!function_exists($doFunc)) return '';

		loadDBConfig($order['mpID']);
		if (!$doFunc($order['mpID'])) {
			return '';
		}

		$view = 'orderDetail';
		if (array_key_exists('multi', $args) && (bool)$args['multi']) {
			$view = 'orderDetailMulti';
		}
		$html .= $func(array(
			'order' => $order,
			'view' => $view,
		));
	}
	echo $html.'';
}
