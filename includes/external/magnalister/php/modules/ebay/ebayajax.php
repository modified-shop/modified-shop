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
 * $Id: ebayajax.php 3347 2013-12-02 15:42:17Z tim.neumann $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
//require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');

if (isset($_POST['request'])) {
	$r = $_POST['request'];

	if ($r == 'ItemSearch') {
		include_once(DIR_MAGNALISTER_MODULES.'ebay/matching/matchingViews.php');
		if (isset($_POST['search']) && !empty($_POST['search']) &&
			isset($_POST['productID']) && !empty($_POST['productID'])) {
			$search = $_POST['search'];
			$productID = $_POST['productID'];
# DEBUG
#die(print_m(array(
#                                        'ACTION' => 'ItemSearch',
#                                        'NAME'   => $search
#                                ), 'search');

			try {
				$result = MagnaConnector::gi()->submitRequest(array(
					'ACTION' => 'ItemSearch',
					'NAME'   => $search
				));
			} catch (MagnaException $e) {
				$result = array('DATA' => array());
			}
			/*if (!empty($result['DATA'])) {
				foreach ($result['DATA'] as &$data) {
					if (!empty($data['Author'])) {
						$data['Title'] .= ' ('.$data['Author'].')';
					}
				}
			}*/

			// if variarion, extract productID
			if (    (strpos($productID, 'ML') !== false)
			     && (strpos($productID, '_') !== false)) {
				$pID = ltrim(substr($productID, 0, strpos($productID, '_')), 'ML');
			} else {
				$pID = $productID;
			}
			$dbProd = MLProduct::gi()->getProductByIdOld($pID);
			header('Content-Type: text/html; charset=ISO-8859-1');
			renderMatchingResultTr($productID, $search, '', $result['DATA']);
		}
	}

	if ($r == 'ItemLookup') {
		include_once(DIR_MAGNALISTER_MODULES.'ebay/matching/matchingViews.php');
		if (isset($_POST['epid']) && !empty($_POST['epid']) &&
			isset($_POST['productID']) && !empty($_POST['productID'])) {
			$epid = $_POST['epid'];
			$productID = $_POST['productID'];

			try {
				$result = MagnaConnector::gi()->submitRequest(array(
					'ACTION' => 'ItemLookup',
					'EPID' => $epid
				));
			} catch (MagnaException $e) {
				$result = array('DATA' => array());
			}
			// if variarion, extract productID
			if (    (strpos($productID, 'ML') !== false)
			     && (strpos($productID, '_') !== false)) {
				$pID = ltrim(substr($productID, 0, strpos($productID, '_')), 'ML');
			} else {
				$pID = $productID;
			}
			$dbProd = MLProduct::gi()->getProductByIdOld($productID);

			/*if (!empty($result['DATA'])) {
				foreach ($result['DATA'] as &$data) {
					if (array_key_exists('Author', $data) && !empty($data['Author'])) {
						$data['Title'] .= ' ('.$data['Author'].')';
					}
				}
			}*/
			header('Content-Type: text/html; charset=ISO-8859-1');
			renderMatchingResultTr($productID, $dbProd['products_name'], '', $result['DATA']);
		}
	}
}
