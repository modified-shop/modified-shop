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
 * $Id: listings.php 2332 2013-04-04 16:12:19Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/listingsBox.php');
require_once(DIR_MAGNALISTER_MODULES.'amazon/classes/InventoryView.php');
require_once(DIR_MAGNALISTER_MODULES.'amazon/classes/DeletedView.php');

//unset($_MagnaShopSession['amazon']);

$_url['mode'] = 'listings';

echo generateListingsBox();

if ($_magnaQuery['view'] == 'inventory') {
	$iV = new InventoryView();
	echo $iV->renderView();
	
} else {
	$dV = new DeletedView();
	echo $dV->renderView();
	
}

if (array_key_exists('GetErrorLog', $_GET) && preg_match('/^[0-9]*$/', $_GET['GetErrorLog'])) {
	$request = array();
	$request['ACTION'] = 'GetErrorLog';
	$request['BATCHID'] = $_GET['GetErrorLog'];

	try {
		$result = MagnaConnector::gi()->submitRequest($request);
		echo print_m($result, 'GetErrorLog');
	} catch (MagnaException $e) {

	}
}
