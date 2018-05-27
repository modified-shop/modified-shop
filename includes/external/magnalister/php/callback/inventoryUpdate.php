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
 *	 Released under the GNU General Public License v2 or later
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
define('ML_LOG_INVENTORY_CHANGE', false);
define('ML_DISABLE_UPDATE', false);

function calcUpdatePrice($mpID, $pID, $prices, $tax) {
	$mp = magnaGetMarketplaceByID($mpID);
	$sp = new SimplePrice($prices['0'], getCurrencyFromMarketplace($mpID));
	
	/* Update Currency */
	if (getDBConfigValue(array($mp.'.exchangerate', 'update'), $mpID, false)) {
		$sp->updateCurrencyByService();
	}
	
	if (getDBConfigValue($mp.'.submit.shopurl', $mpID, 'false') != 'true') {
		# Customer Group Price
		$customerGroup = (int)getDBConfigValue($mp.'.price.group', $mpID, 0);
		if (($customerGroup > 0) && (array_key_exists($customerGroup, $prices))
			&& ((float)$prices[$customerGroup] > 0)
		) {
			$sp->setPrice((float)$prices[$customerGroup]);
		}
	}

	# Special Price
	$specialPrice = 0;
	if (getDBConfigValue(array($mp.'.price.usespecialoffer', 'val'), $mpID, false)) {
		$specialPrice = $sp->getSpecialOffer($pID);
	}
	if ($specialPrice > 0) {
		$sp->setPrice($specialPrice);
	}
	
	$sp->addTax($tax)->calculateCurr();
	
	if (getDBConfigValue($mp.'.submit.shopurl', $mpID, 'true') != 'true') {
		# LUMP
		if (getDBConfigValue($mp.'.price.addkind', $mpID) == 'percent') {
			$sp->addTax((float)getDBConfigValue($mp.'.price.factor', $mpID, 0));
		} else if (getDBConfigValue($mp.'.price.addkind', $mp) == 'addition') {
			$sp->addLump((float)getDBConfigValue($mp.'.price.factor', $mpID, 0));
		}
	}

	# Signal Price
	return $sp->roundPrice()->makeSignalPrice(
				getDBConfigValue($mp.'.price.signal', $mpID, '')
			)->getPrice();
}

function genericInventoryUpdateByEdit($mpID, $updateData) {
	$mp = magnaGetMarketplaceByID($mpID);
	$lang = (string)getDBConfigValue($mp.'.lang', $mpID, $_SESSION['languages_id']);
	$pID = array_first(array_keys($updateData));

	$updateItem = array();

	if (getDBConfigValue(array($mp.'.inventorysync', 'title'), $mpID, false)
		&& array_key_exists($lang, $updateData[$pID]['ItemTitle']) 
		&& !empty($updateData[$pID]['ItemTitle'][$lang])
	) {
		$updateItem['ItemTitle'] = $updateData[$pID]['ItemTitle'][$lang];
	}

	if (getDBConfigValue(array($mp.'.inventorysync', 'desc'), $mpID, false)) {
		if (array_key_exists($lang, $updateData[$pID]['Description']) 
			&& !empty($updateData[$pID]['Description'][$lang])
		) {
			$updateItem['Description'] = $updateData[$pID]['Description'][$lang];
		}
		if (array_key_exists($lang, $updateData[$pID]['ShortDescription']) 
			&& !empty($updateData[$pID]['ShortDescription'][$lang])
		) {
			$updateItem['ShortDescription'] = $updateData[$pID]['ShortDescription'][$lang];
		}
	}
	/* If inventorysync.price exists in the config system the setting in the else branch is
	   irrelevant. */
	$priceSync = getDBConfigValue($mp.'.inventorysync.price', $mpID, null);
	if ($priceSync !== null) {
		$priceSync = $priceSync != 'no';
	} else {
		$priceSync = getDBConfigValue(array($mp.'.inventorysync', 'price'), $mpID, false);
	}

	if ($priceSync) {
		$updateItem['Price'] = calcUpdatePrice($mpID, $pID, $updateData[$pID]['Prices'], $updateData[$pID]['Tax']);
	}
	if ( ( ($mode = getDBConfigValue($mp.'.stocksync.tomarketplace', $mpID, 'no')) != 'no') 
		&& (array_key_exists('NewQuantity', $updateData[$pID]) && array_key_exists('Quantity', $updateData[$pID]))
	) {
		if (in_array($mode, array('abs', 'auto'))) {
			$updateData[$pID]['NewQuantity']['Value'] = $updateData[$pID]['Quantity'];
			$updateData[$pID]['NewQuantity']['Mode'] = 'SET';

			$quantityType = getDBConfigValue($mp.'.quantity.type', $mpID, '');
			$sub = 0;
			if ($quantityType == 'stocksub') {
				$sub = getDBConfigValue($mp.'.quantity.value', $mpID, 0);
			}
			$updateData[$pID]['NewQuantity']['Value'] -= $sub;
			if ($updateData[$pID]['NewQuantity']['Value'] < 0) {
				$updateData[$pID]['NewQuantity']['Value'] = 0;
			}
		}
		if (array_key_exists('NewQuantity', $updateData[$pID])) {
			$updateItem['NewQuantity'] = $updateData[$pID]['NewQuantity'];
		}
	}
	if (empty($updateItem)) {
		return false;
	}
	$updateItem['SKU'] = magnaPID2SKU($pID);
	arrayEntitiesToUTF8($updateItem);
	return $updateItem;
}

function genericInventoryUpdateByOrder($mpID, $boughtItems, $subRelQuant = true) {
	$mp = magnaGetMarketplaceByID($mpID);

	if (!in_array(getDBConfigValue($mp.'.stocksync.tomarketplace', $mpID), array('abs', 'auto'))) {
		# if == 'rel' oder 'no'
		return $boughtItems;
	}

	foreach ($boughtItems as $i => &$item) {
		$newQuantity = false;
		if (($aID = magnaSKU2aID($item['SKU'])) !== false && (MagnaDB::gi()->columnExistsInTable('attributes_stock',TABLE_PRODUCTS_ATTRIBUTES))) {
			$newQuantity = MagnaDB::gi()->fetchOne('
				SELECT attributes_stock FROM '.TABLE_PRODUCTS_ATTRIBUTES.' 
				 WHERE products_attributes_id = \''.$aID.'\'
			');
		} else if (($pID = magnaSKU2pID($item['SKU'])) !== 0) {
			$newQuantity = MagnaDB::gi()->fetchOne('
				SELECT products_quantity FROM '.TABLE_PRODUCTS.'
				 WHERE products_id=\''.$pID.'\'
			');
		}
		if ($newQuantity === false) {
			# Dann halt nur relativ synchronisieren.
			continue;
		}

		$quantityType = getDBConfigValue($mp.'.quantity.type', $mpID, '');
		$sub = 0;
		if ($quantityType == 'stocksub') {
			$sub = getDBConfigValue($mp.'.quantity.value', $mpID, 0);
		}

		$item['NewQuantity']['Mode'] = 'SET';
		$item['NewQuantity']['Value'] = (int)$newQuantity - ($subRelQuant ? $item['NewQuantity']['Value'] : 0) - $sub;
		
		if ($quantityType == 'lump') {
			$item['NewQuantity']['Value'] = getDBConfigValue($mp.'.quantity.value', $mpID, 0);
		}
		
		if ($item['NewQuantity']['Value'] < 0) {
			$item['NewQuantity']['Value'] = 0;
		}

	}
	return $boughtItems;
}

function magnaGetCartContents() {
	if (!isset($_SESSION['cart']) || !is_object($_SESSION['cart']) || !($cartContents = $_SESSION['cart']->contents) || empty($cartContents)) {
		if (defined('ML_LOG_INVENTORY_CHANGE') && ML_LOG_INVENTORY_CHANGE) {
			file_put_contents(DIR_MAGNALISTER_CALLBACK.'inventoryUpdate.dat', "Can't access shopping cart contents\n", FILE_APPEND);
		}
		return array();
	}
	if (defined('ML_LOG_INVENTORY_CHANGE') && ML_LOG_INVENTORY_CHANGE) {
		file_put_contents(DIR_MAGNALISTER_CALLBACK.'inventoryUpdate.dat', print_m($cartContents, '$cartContents', true)."\n", FILE_APPEND);
	}
	
	$boughtItems = array();
	foreach ($cartContents as $ident => $attr) {
		if (defined('ML_LOG_INVENTORY_CHANGE') && ML_LOG_INVENTORY_CHANGE) {
			file_put_contents(DIR_MAGNALISTER_CALLBACK.'inventoryUpdate.dat', 'CurrentPosition :: '.print_m($attr, $ident, true)."\n", FILE_APPEND);
		}
		$attributes = array();
		if (strpos($ident, '{') !== false) {
			$pID = substr($ident, 0, strpos($ident, '{'));
			$attributes = $attr['attributes'];
		} else {
			$pID = $ident;
		}
		if (!isset($attr['qty']) || !ctype_digit((string)$pID)) {
			if (defined('ML_LOG_INVENTORY_CHANGE') && ML_LOG_INVENTORY_CHANGE) {
				file_put_contents(
					DIR_MAGNALISTER_CALLBACK.'inventoryUpdate.dat', 
					'CurrentPosition invalid.
					'.var_dump_pre(isset($attr['qty']), 'isset($attr[qty])', true).'
					'.var_dump_pre(ctype_digit((string)$pID), 'ctype_digit((string)$pID)', true).'
					', 
					FILE_APPEND
				);
			}
			continue;
		}
		$price = MagnaDB::gi()->fetchRow('
		    SELECT `products_price` AS price, MAX( tax_rate ) AS tax
		      FROM `'.TABLE_PRODUCTS.'`, '.TABLE_TAX_RATES.'
		     WHERE `products_tax_class_id` = tax_class_id
		            AND `products_id`=\''.$pID.'\'
		  GROUP BY products_tax_class_id
		     LIMIT 1
		');
		if (!is_array($price)) {
			$price = array(
				'price' => 0.0,
				'tax' => 0.0
			);
		}
		$price['price'] = round($price['price'] + $price['price'] / 100 * $price['tax'], 2);
		if (!empty($attributes)) {
			foreach ($attributes as $oID => $ovID) {
				$aPrice = MagnaDB::gi()->fetchRow('
					SELECT options_values_price AS price, price_prefix AS prefix
					  FROM '.TABLE_PRODUCTS_ATTRIBUTES.'
					 WHERE options_id=\''.$oID.'\' 
					       AND options_values_id=\''.$ovID.'\'
				');
				if (!is_array($aPrice)) continue;
				$aPrice['price'] = round($aPrice['price'] + $aPrice['price'] / 100 * $price['tax'], 2);
				switch($aPrice['prefix']) {
					case '+': {
						$price['price'] += $aPrice['price'];
						break;
					}
					case '-': {
						$price['price'] -= $aPrice['price'];
						break;
					}
					case '=': {
						$price['price'] = $aPrice['price'];
					}
					case '%': {
						$price['price'] = $price['price'] * (100 + $aPrice['price']) / 100.0;
					}
				}
			}
		}
		
		$boughtItems[$pID] = array (
			'Quantity' => (int)$attr['qty'],
			'Attributes' => $attributes,
			'Price' => round($price['price'], 2),
			'Currency' => DEFAULT_CURRENCY,
		);
	}
	if (empty($boughtItems)) {
		return array();
	}
	if (defined('ML_LOG_INVENTORY_CHANGE') && ML_LOG_INVENTORY_CHANGE) {
		file_put_contents(DIR_MAGNALISTER_CALLBACK.'inventoryUpdate.dat', print_m($boughtItems, '$boughtItems', true)."\n", FILE_APPEND);
	}
	return $boughtItems;
}

function magnaInventoryUpdateByOrder() {
	// don't use this any more 01.09.2015 Problems with magnalister (Max execution time -> MagnaConnector)
	return;
	global $magnaConfig, $_modules;
	
	if (getDBConfigValue(array('general.trigger.checkoutprocess.inventoryupdate', 'val'), 0, 'true') != 'true') {
		return;
	}
	
	$boughtItems = magnaGetCartContents();
	if (empty($boughtItems)) {
		return;
	}
	$tmp = array ();
	foreach ($boughtItems as $pID => $detail) {
		$tmp[] = array (
			'SKU' => magnaPID2SKU($pID),
			'NewQuantity' => array (
				'Mode' => 'SUB',
				'Value' => $detail['Quantity'],
			),
			'Attributes' => $detail['Attributes'],
		);
	}
	$boughtItems = $tmp;

	foreach ($_modules as $marketplace => $mod) {
		$mpIDs = magnaGetIDsByMarketplace($marketplace);
		if (empty($mpIDs)) continue;
		foreach ($mpIDs as $mpID) {
			if (!file_exists(DIR_MAGNALISTER_MODULES.$marketplace.'/'.$marketplace.'Functions.php')) {
				continue;
			}
			require_once(DIR_MAGNALISTER_MODULES.$marketplace.'/'.$marketplace.'Functions.php');
			$funcName = 'update'.ucfirst($marketplace).'InventoryByOrder';
			if (!function_exists($funcName)) {
				continue;
			}

            # Attribute nur fuer eBay
            #$myBoughtItems = $boughtItems;
            #if ('ebay' <> $marketplace) {
            #    foreach ($myBoughtItems as &$item) {
            #        unset($item['Attributes']);
            #    }
            #}

			if (!array_key_exists('db', $magnaConfig) || 
			    !array_key_exists($mpID, $magnaConfig['db'])
			) {
				loadDBConfig($mpID);
			}
			if (defined('ML_LOG_INVENTORY_CHANGE') && ML_LOG_INVENTORY_CHANGE) {
				file_put_contents(DIR_MAGNALISTER_CALLBACK.'inventoryUpdate.dat', "call: $funcName($mpID, $boughtItems);\n", FILE_APPEND);
			}
			#echo "$funcName($mpID, $boughtItems);\n";
			#if (strpos($funcName, 'Amazon') !== false)
			$funcName($mpID, $boughtItems);
		}
	}
}

function magnaInventoryUpdateByEdit() {
	// don't use this any more 01.09.2015 Problems with magnalister (Max execution time -> MagnaConnector)
	return;
	global $magnaConfig, $_modules;
	/*
	echo var_export_pre($_POST, '$_POST');
	echo var_export_pre($_GET, '$_GET');
	die();
	*/
	if (getDBConfigValue(array('general.trigger.editproduct.inventoryupdate', 'val'), 0, 'true') != 'true') {
		return;
	}
	if (defined('ML_LOG_INVENTORY_CHANGE') && ML_LOG_INVENTORY_CHANGE) {
		file_put_contents(DIR_MAGNALISTER_CALLBACK.'inventoryUpdate.dat', "\n========= inventoryUpdate :: ".date('Y-m-d H:i:s')." ==========\n", FILE_APPEND);
	}
	if (empty($_POST)) return;
	if ((SHOPSYSTEM == 'oscommerce') && ($_GET['action'] == 'new_product_preview')) return;

	if (defined('ML_LOG_INVENTORY_CHANGE') && ML_LOG_INVENTORY_CHANGE) {
		file_put_contents(DIR_MAGNALISTER_CALLBACK.'inventoryUpdate.dat', print_m($_POST, '$_POST', true), FILE_APPEND);
	}
	if (array_key_exists('multi_products', $_POST)) return;
	if (!array_key_exists('products_price', $_POST)) return;
	
	if (array_key_exists('products_id', $_POST)) {
		$pID = $_POST['products_id'];
	} else if (array_key_exists('pID', $_GET)) {
		$pID = $_GET['pID'];
	}
	if (!isset($pID) || !ctype_digit($pID)) {
		if (defined('ML_LOG_INVENTORY_CHANGE') && ML_LOG_INVENTORY_CHANGE) {
			file_put_contents(DIR_MAGNALISTER_CALLBACK.'inventoryUpdate.dat', 'Return '.__LINE__.': (!isset($pID) || !ctype_digit($pID));'."\n\n", FILE_APPEND);
		}
		return;
	}

	$productsData = MLProduct::gi()->getProductByIdOld($pID);
	if ($productsData === false) {
		if (defined('ML_LOG_INVENTORY_CHANGE') && ML_LOG_INVENTORY_CHANGE) {
			file_put_contents(DIR_MAGNALISTER_CALLBACK.'inventoryUpdate.dat', 'Return '.__LINE__.': ($productsData === false); $pID: '.$pID.';'."\n\n", FILE_APPEND);
		}
		return false;
	}

	$updateData = array ( 
		$pID => array()
	);

	if (array_key_exists('products_quantity', $_POST)) {
		$quantity = (int)$_POST['products_quantity'];
		if ($productsData['products_quantity'] != $quantity) {
			$diff = (int)($quantity - $productsData['products_quantity']);
			$mode = ($diff < 0) ? 'SUB' : 'ADD';
			$diff = abs($diff);
			$updateData[$pID]['NewQuantity'] = array (
				'Mode' => $mode,
				'Value' => $diff
			);
		}
		$updateData[$pID]['Quantity'] = $quantity;
	}
	require_once(DIR_MAGNALISTER_INCLUDES . 'lib/classes/SimplePrice.php');
	
	$updateData[$pID]['ItemTitle'] = $_POST['products_name'];
	$updateData[$pID]['Tax'] = SimplePrice::getTaxByClassID($_POST['products_tax_class_id']);
	$updateData[$pID]['Prices']['0'] = $_POST['products_price'];
	
	/* products_price ist bei osC immer Netto und es kennt PRICE_IS_BRUTTO nicht */
	$isBrutto = false;
	if (defined('PRICE_IS_BRUTTO') && (PRICE_IS_BRUTTO == 'true')) {
		$isBrutto = true;
		# NettoPreis herstellen.
		$updateData[$pID]['Prices']['0'] = ($updateData[$pID]['Prices']['0'] / (($updateData[$pID]['Tax'] + 100) / 100));
	}
	$updateData[$pID]['GroupPrices'] = array();
	foreach ($_POST as $key => $ble) {
		if (preg_match('/^products_price_([0-9]+)/', $key, $match)) {
			$gPrice = $_POST['products_price_'.$match[1]];
			if ($isBrutto) {
				$updateData[$pID]['Prices'][$match[1]] = ($gPrice / (($updateData[$pID]['Tax'] + 100) / 100));
			} else {
				$updateData[$pID]['Prices'][$match[1]] = $gPrice;
			}
		}
	}

	if (SHOPSYSTEM == 'oscommerce') {
		$updateData[$pID]['Description'] = $_POST['products_description'];
	} else {
		$updateData[$pID]['Description'] = array();
		$updateData[$pID]['ShortDescription'] = array();
		foreach ($_POST as $key => $ble) {
			if (preg_match('/^products_description_([0-9]+)/', $key, $match)) {
				$updateData[$pID]['Description'][$match[1]] = $_POST[$key];
				$updateData[$pID]['ShortDescription'][$match[1]] = $_POST['products_short_description_'.$match[1]];
			}
		}
	}
	if (!empty($updateData[$pID]['Description'])) {
		foreach ($updateData[$pID]['Description'] as $lang => $text) {
			$updateData[$pID]['Description'][$lang] = smartstripslashes($text);
			$updateData[$pID]['ShortDescription'][$lang] = smartstripslashes($updateData[$pID]['ShortDescription'][$lang]);
		}
	}
	#echo print_m($updateData);
	foreach ($_modules as $marketplace => $mod) {
		$mpIDs = magnaGetIDsByMarketplace($marketplace);
		if (empty($mpIDs)) continue;
		foreach ($mpIDs as $mpID) {
			if (!file_exists(DIR_MAGNALISTER_MODULES.$marketplace.'/'.$marketplace.'Functions.php')) {
				continue;
			}
			require_once(DIR_MAGNALISTER_MODULES.$marketplace.'/'.$marketplace.'Functions.php');
			$funcName = 'update'.ucfirst($marketplace).'InventoryByEdit';
			
			if (!function_exists($funcName)) {
				continue;
			}
			if (!array_key_exists('db', $magnaConfig) || !array_key_exists($mpID, $magnaConfig['db'])) {
				loadDBConfig($mpID);
			}
			if (defined('ML_LOG_INVENTORY_CHANGE') && ML_LOG_INVENTORY_CHANGE) {
				file_put_contents(DIR_MAGNALISTER_CALLBACK.'inventoryUpdate.dat', "call: $funcName($mpID, $boughtItems);\n", FILE_APPEND);
			}
			#echo "$funcName($mpID, $updateData)\n";
			#if (strpos($funcName, 'Preissuchmaschine') !== false)
			$funcName($mpID, $updateData);
		}
	}
}

function magnaInventoryUpdate($args) {
	if ($args['action'] == 'inventoryUpdateOrder') {
		magnaInventoryUpdateByOrder();
	} else if ($args['action'] == 'inventoryUpdate') {
		magnaInventoryUpdateByEdit();
	}
}

function magnaInventoryUpdateByOrderImport($boughtItems, $exclMpID) {
	global $magnaConfig, $_modules;

	if (empty($boughtItems)) {
		return $boughtItems;
	}

	foreach ($_modules as $marketplace => $mod) {
		$mpIDs = magnaGetIDsByMarketplace($marketplace);
		if (empty($mpIDs)) continue;
		foreach ($mpIDs as $mpID) {
			if (!file_exists(DIR_MAGNALISTER_MODULES.$marketplace.'/'.$marketplace.'Functions.php')) {
				continue;
			}
			require_once(DIR_MAGNALISTER_MODULES.$marketplace.'/'.$marketplace.'Functions.php');
			$funcName = 'update'.ucfirst($marketplace).'InventoryByOrder';
			
			if (!function_exists($funcName)) {
				continue;
			}
			if ($mpID == $exclMpID) {
				continue;
			}
			if (!array_key_exists('db', $magnaConfig) || 
			    !array_key_exists($mpID, $magnaConfig['db'])
			) {
				loadDBConfig($mpID);
			}
			if (getDBConfigValue($marketplace.'.stocksync.tomarketplace', $mpID, null) == 'no') {
				continue;
			}
			if (defined('ML_LOG_INVENTORY_CHANGE') && ML_LOG_INVENTORY_CHANGE) {
				file_put_contents(DIR_MAGNALISTER_CALLBACK.'inventoryUpdate.dat', "call: $funcName($mpID, $boughtItems);\n", FILE_APPEND);
			}
			#echo "$funcName($mpID, $boughtItems, false));\n";
			#print_r(genericInventoryUpdateByOrder($mpID, $boughtItems, $subRelQuant));
			#if (strpos($funcName, 'Amazon') !== false)
			$funcName($mpID, $boughtItems, false);
		}
	}
}
