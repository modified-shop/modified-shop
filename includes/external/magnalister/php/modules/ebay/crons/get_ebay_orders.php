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
 * $Id: get_ebay_orders.php 889 2011-04-03 23:46:11Z MaW $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

/*
NOCH TODO bei zusammengefassten Bestellungen:
promotion mail verbessern

*/

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

/* Hilfsfunktion: Attributwerte anhand von variation_products_model ermitteln */
# SKU unter dem Namen products_id uebermitteln, variation_products_model unter products_model
function variation_products_model2pOpt($variation_products_model, $products_id, $mpID) {
	if (empty($variation_products_model)) return false;
	if (empty($products_id)) return false;
	$attrValues = array();
	$variation_products_model = MagnaDB::gi()->escape($variation_products_model);
	$variation_attributes_select = 'SELECT variation_attributes
	    FROM '.TABLE_MAGNA_VARIATIONS;
	if ('artNr' != getDBConfigValue('general.keytype', '0')) {
		$sVariantSKU = str_replace('ML', '', $variation_products_model);
		$variation_attributes_select .= " WHERE REPLACE(".mlGetVariationSkuField().", 'ML', '') = '".MagnaDB::gi()->escape($sVariantSKU)."'";
	} else {
		$variation_attributes_select .= " WHERE ".mlGetVariationSkuField()." = '".MagnaDB::gi()->escape($variation_products_model)."'";
	}
	$variation_attributes_select .=
	    ' AND products_id = '.$products_id.'
	    ORDER BY variation_id DESC LIMIT 1';
	$variation_attributes = MagnaDB::gi()->fetchOne($variation_attributes_select);
	if (!$variation_attributes) {
		/* Fall: Variantentabelle nach Upgrade im Dez '13 noch nicht neu gefuellt */
		$sVariantSKU = str_replace('ML', '', $variation_products_model);
		if (  ('auto' <> getDBConfigValue('ebay.stocksync.tomarketplace', $mpID, 'none'))
			&&(MagnaDB::gi()->columnExistsInTable('variation_products_model', TABLE_MAGNA_VARIATIONS))
			&&(MagnaDB::gi()->fetchOne("
			    SELECT COUNT(*)
			      FROM ".TABLE_MAGNA_VARIATIONS."
			     WHERE ".('artNr' == getDBConfigValue('general.keytype', '0')
			             ? "variation_products_model = '".MagnaDB::gi()->escape($variation_products_model)."'"
			             : "REPLACE(variation_products_model, 'ML', '') = '".MagnaDB::gi()->escape($sVariantSKU)."'"
			            ).'
			           AND products_id = '.$products_id.'
			  ORDER BY variation_id DESC LIMIT 1') > 0)
			) {
			if (false == setProductVariations($products_id, false, false)) {
				return false;
			}
			#require_once(DIR_MAGNALISTER_CALLBACK.'updateVariationsTable.php');
			$variation_attributes = MagnaDB::gi()->fetchOne($variation_attributes_select);
			if (!$variation_attributes) {
				return false;
			}
		} else {
			return false;
		}
	}

	$options =''; $values = '';
	$attributes = explode('|', trim($variation_attributes,'|'));
	foreach ($attributes as $k => $attribute) {
		if (empty($attribute)) continue;
		list($attrValues[$k]['options_id'],$attrValues[$k]['options_values_id']) = explode(',',$attribute);
		$options .= $attrValues[$k]['options_id'].', ';
		$values .= $attrValues[$k]['options_values_id'].', ';
	}
	$languageID = magnaGetDefaultLanguageID();
	$options = trim($options,', ');
	$values = trim($values,', ');
	$options_name_select = 'SELECT products_options_id, products_options_name
		FROM '.TABLE_PRODUCTS_OPTIONS.' WHERE language_id = '.$languageID.'
		AND products_options_id in ('.$options.')';
	$options_names_array = MagnaDB::gi()->fetchArray($options_name_select);
	$options_names = array();
	foreach ($options_names_array as $name) {
		$options_names[$name['products_options_id']] = $name['products_options_name'];
	}
	$options_values_name_select = 'SELECT products_options_values_id, products_options_values_name
		FROM '.TABLE_PRODUCTS_OPTIONS_VALUES.' WHERE language_id = '.$languageID.'
		AND products_options_values_id in ('.$values.')';
	$options_values_names_array = MagnaDB::gi()->fetchArray($options_values_name_select);
	foreach ($options_values_names_array as $name) {
		$options_values_names[$name['products_options_values_id']] = $name['products_options_values_name'];
	}
	$options_values_prices = array();
	$options_values_price_select = 'SELECT options_values_id, options_values_price
		FROM '.TABLE_PRODUCTS_ATTRIBUTES
		.' WHERE products_id = '.$products_id.'
		 AND options_id IN ('.$options.') AND options_values_id IN  ('.$values.')';
	$options_values_price_array = MagnaDB::gi()->fetchArray($options_values_price_select);
	foreach ($options_values_price_array as $price_row) {
		$options_values_prices[$price_row['options_values_id']] = $price_row['options_values_price'];
	}
	foreach ($attrValues as &$attr) {
		$attr['options_name'] = $options_names[$attr['options_id']];
		$attr['options_values_name'] = $options_values_names[$attr['options_values_id']];
		$attr['options_values_price'] = $options_values_prices[$attr['options_values_id']];
	}
	return $attrValues;
}

function isDomestic($countryISO) {
    if (defined('ML_GAMBIO_41_NEW_CONFIG_TABLE')) {
        $storeCountry = strtoupper(MagnaDB::gi()->fetchOne("
            SELECT ctr.`countries_iso_code_2`
              FROM ".TABLE_CONFIGURATION." config, ".TABLE_COUNTRIES." ctr
             WHERE     config.`key` = 'configuration/STORE_COUNTRY'
                   AND config.`value` = ctr.`countries_id`'
        "));
    } else {
        $storeCountry = strtoupper(MagnaDB::gi()->fetchOne('
            SELECT ctr.`countries_iso_code_2`
              FROM '.TABLE_CONFIGURATION.' config, '.TABLE_COUNTRIES.' ctr
             WHERE     config.`configuration_key` = \'STORE_COUNTRY\'
                   AND config.`configuration_value` = ctr.`countries_id`')
        );
    }

	if (strtoupper($countryISO) == $storeCountry) {	
		return true;
	} else {
		return false;
	}
}

/* Versandkosten bei zusammengefassten Bestellungen ermitteln */
function calculate_shipping_cost($existingShippingCost, $currItemShippingCost, $totalNumberOfItems, $totalPriceWOShipping, $currProductsCount, $countryISO, $mpID) {
	if ((0 == $existingShippingCost) && (0 == $currItemShippingCost)) return 0.0;
	# ShippingServiceAdditionalCost aus den Profilen nehmen
	$shippingProfiles         = getDBConfigValue('ebay.shippingprofiles', $mpID, null);
	$localProfileID           = getDBConfigValue('ebay.default.shippingprofile.local',$mpID, 0);
	$internationalProfileID   = getDBConfigValue('ebay.default.shippingprofile.international',$mpID, 0);
	$localUseDiscount         = getDBConfigValue(array('ebay.shippingdiscount.local', 'val'), $mpID, true);
	$internationalUseDiscount = getDBConfigValue(array('ebay.shippingdiscount.international', 'val'), $mpID, true);
	if (empty($shippingProfiles)) {
		$localAddCost         = 0.0;
		$internationalAddCost = 0.0;
	} else {
		if (empty($localProfileID)) {
			$localAddCost = 0.0;
		} else {
			$localAddCost = (float)$shippingProfiles['Profiles']["$localProfileID"]['EachAdditionalAmount'];
		}
		if (empty($internationalProfileID)) {
			$internationalAddCost = 0.0;
		} else {
			$internationalAddCost = (float)$shippingProfiles['Profiles']["$internationalProfileID"]['EachAdditionalAmount'];
		}
		if (array_key_exists('PromotionalShippingDiscount', $shippingProfiles)) {
			if (   array_key_exists('DiscountName',$shippingProfiles['PromotionalShippingDiscount'])
			    && array_key_exists('ShippingCost',$shippingProfiles['PromotionalShippingDiscount'])) {
				switch ($shippingProfiles['PromotionalShippingDiscount']['DiscountName']) {
					case ('MaximumShippingCostPerOrder'): {
						$MaximumShippingCostPerOrder = (float)$shippingProfiles['PromotionalShippingDiscount']['ShippingCost'];
						break;
					}
					case ('ShippingCostXForAmountY'): {
						if ($totalPriceWOShipping >= (float)$shippingProfiles['PromotionalShippingDiscount']['OrderAmount']) {
							$MaximumShippingCostPerOrder = (float)$shippingProfiles['PromotionalShippingDiscount']['ShippingCost'];
						}
						break;
					}
					default: break;
				} # switch
			}
		} # if (array_key_exists('PromotionalShippingDiscount', $shippingProfiles))
	}
	$domestic = isDomestic($countryISO);
	if ($domestic) {
		$addcost = $localAddCost;
		if (!$localUseDiscount && isset($MaximumShippingCostPerOrder)) {
			unset($MaximumShippingCostPerOrder);
		}
	} else {
		$addcost = $internationalAddCost;
		if (!$internationalUseDiscount && isset($MaximumShippingCostPerOrder)) {
			unset($MaximumShippingCostPerOrder);
		}
	}
	# existingAddCost: ausser dem ersten Item und aktueller Bestellung
	$existingAddCost = ($totalNumberOfItems - 1 - $currProductsCount) * $addcost;
	$firstItemShippingCost = $existingShippingCost - $existingAddCost;
	# currSingleItemShippingCost: erstes Stueck der aktuellen Bestellung
	$currSingleItemShippingCost = $currItemShippingCost - (($currProductsCount - 1) * $addcost);
	$totalAddCost = $existingAddCost + ($currProductsCount * $addcost);
	if ($firstItemShippingCost > $currSingleItemShippingCost) {
		$totalShippingCost = $firstItemShippingCost + $totalAddCost;
	} else {
		$totalShippingCost = $currSingleItemShippingCost + $totalAddCost;
	}
	if (isset($MaximumShippingCostPerOrder)) {
		$totalShippingCost = min($totalShippingCost, $MaximumShippingCostPerOrder);
	}
	return $totalShippingCost;
}

/* pruefe ob notwendige Konfig-Einstellungen vorhanden */
function settingsOK($mpID) {
	$mp = 'ebay';
	if (    (null == getDBConfigValue($mp.'.CustomerGroup', $mpID, null))
	     && (MagnaDB::gi()->tableExists(TABLE_CUSTOMERS_STATUS))) {
		return false;
	}
	if (null == getDBConfigValue($mp.'.orderstatus.open', $mpID, null)) {
		return false;
	} 
	return true;
}

/* eBay Bestellungen importieren */ 
function magnaImportEbayOrders($mpID) {
	global $magnaConfig, $_magnaLanguage, $_modules;

	$mp = 'ebay';
	$_magnaLanguageId = MagnaDB::gi()->fetchOne("SELECT languages_id FROM ".TABLE_LANGUAGES." WHERE directory = '$_magnaLanguage'");
	$useGambioProperties = (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties');

	settingsOK($mpID) or die ("\nOrder import aborted. Please configure the $mp module of the magnalister plugin.\n");

	require_once(DIR_MAGNALISTER_MODULES.'ebay/ebayFunctions.php');
	require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
	require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/MagnaRecalcOrdersTotal.php');
    if (   ('oscommerce' == SHOPSYSTEM)
		&& (!function_exists('tep_encrypt_password'))
	    && ( file_exists(DIR_WS_FUNCTIONS.'password_funcs.php'))) {
        require_once(DIR_WS_FUNCTIONS.'password_funcs.php');
    }

	if (isset($_GET['MLDEBUG']) && ($_GET['MLDEBUG'] == 'true')) {
		require_once(DIR_MAGNALISTER_INCLUDES . 'lib/MagnaTestDB.php');
		$MagnaDB = MagnaTestDB::gi();
	} else {
		$MagnaDB = MagnaDB::gi();
	}
	
	$character_set_client = MagnaDB::gi()->mysqlVariableValue('character_set_client');
    if (('utf8mb3' == $character_set_client) || ('utf8mb4' == $character_set_client)) {
	# means the same for us
		$character_set_client = 'utf8';
	}
	
	$verbose = (MAGNA_CALLBACK_MODE == 'STANDALONE') && (get_class($MagnaDB) == 'MagnaTestDB');
	$verbose = (get_class($MagnaDB) == 'MagnaTestDB'); // || true;

	$simplePrice = new SimplePrice();
	
	$ShopInfo = array(
		'CustomerGroup' => getDBConfigValue($mp.'.CustomerGroup', $mpID, null),
		'OrderStatusOpen' => getDBConfigValue($mp.'.orderstatus.open', $mpID),
        # OrderStatusClosed als kommagetrennte Liste fuer MySQL queries
		'OrderStatusClosed' => (is_array(getDBConfigValue($mp.'.orderstatus.closed', $mpID, array('99')))
            ? trim(implode(', ', getDBConfigValue($mp.'.orderstatus.closed', $mpID, array('99'))), ', ')
            : getDBConfigValue($mp.'.orderstatus.closed', $mpID, 99)
        )
	);
	$updateExchangeRate = getDBConfigValue(array($mp.'.exchangerate', 'update'), $mpID, false);


	# default shipping method
	if ('__ml_lump' == ($ShopInfo['DefaultShippingMethod'] = getDBConfigValue($mp.'.order.shippingmethod', $mpID, '__ml_lump'))) {
		$ShopInfo['DefaultShippingMethod'] = $ShopInfo['DefaultShippingMethodName']
		= getDBConfigValue($mp.'.order.shippingmethod.name', $mpID, 'ebay');
	} else {
		if (!class_exists('Shipping')) {
			require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/Shipping.php');
		}
		$shippingClass = new Shipping();
		$shippingMethods = $shippingClass->getShippingMethods();
		if (!empty($shippingMethods)) {
			foreach ($shippingMethods as $method) {
				if ($method['code'] == $ShopInfo['DefaultShippingMethod']) {
					$ShopInfo['DefaultShippingMethodName'] = $method['title'];
				}
			}
		}
	}

	# Display prices with tax included (true) or add the tax at the end (false)
	if ((SHOPSYSTEM == 'oscommerce') || (!MagnaDB::gi()->tableExists(TABLE_CUSTOMERS_STATUS))){
		$displayPriceWithTax = true;
	} else {
		# customers_status_show_price_tax = 0 Preise netto anzeigen
		# customers_status_add_tax_ot = 1 MwSt am Ende draufaddieren
		$displayPriceWithTax = ((int)(MagnaDB::gi()->fetchOne('
			SELECT count(*)
			  FROM '.TABLE_CUSTOMERS_STATUS.'
			 WHERE customers_status_id = '.$ShopInfo['CustomerGroup'].'
			       AND customers_status_show_price_tax = 0
			       AND customers_status_add_tax_ot = 1
		')) == 0);
	}
	if ($verbose) echo var_dump_pre($displayPriceWithTax, '$displayPriceWithTax');
	
	$dateRegexp = '/^([1-2][0-9]{3})-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])'.
		'(\s([0-1][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9]))?$/';
	
	$lastImport = getDBConfigValue($mp.'.orderimport.lastrun', $mpID, 0);
	if (preg_match($dateRegexp, $lastImport)) {
		# Since we only request non acknowledged orders, we go back in time by 7 days.
		$lastImport = strtotime($lastImport.' +0000') - 60 * 60 * 24 * 7;
	} else {
		$lastImport = 0;
	}

	$begin = strtotime(getDBConfigValue($mp.'.preimport.start', $mpID, '2011-05-01'));
	if ($begin > time()) {
		if ($verbose) echo "Date in the future --> no import\n";
		return;
	}
	if ($begin < MAGNA_ORDERS_DATERANGE_BEGIN) {
		if ($verbose) echo "Date in the past --> fix date\n";
		$begin = MAGNA_ORDERS_DATERANGE_BEGIN;
	}

	if ( ($lastImport > 0) && ($begin < $lastImport) ) {
		$begin = $lastImport;
	}
	
	if (isset($_GET['ForceBeginImportDate']) && preg_match($dateRegexp, $_GET['ForceBeginImportDate'])) {
		$begin = strtotime($_GET['ForceBeginImportDate']);
	}
	#$begin -= 60 * 60 * 24 * 30 * 12;

	# Bestelldaten abfragen.
	$break = false;
	$offset = array (
		'COUNT' => 100,
		'START' => 0,
	);
	
	$processedOrders = array();

	$lastOrder = '';
	$allCurrencies = array();

	#check for gambio gm_send_order_status
	$existsGmSendOrderStatus = MagnaDB::gi()->columnExistsInTable('gm_send_order_status', TABLE_ORDERS);
	$orderColumns = array (
		'customers_status_discount' => MagnaDB::gi()->columnExistsInTable('customers_status_discount', TABLE_ORDERS),
	);
	
	while (!$break) {
		@set_time_limit(60);
		$request = array(
			'ACTION' => 'GetOrdersForDateRange',
			'SUBSYSTEM' => 'eBay',
			'MARKETPLACEID' => $mpID,
			'BEGIN' => gmdate('Y-m-d H:i:s', $begin),
			'OFFSET' => $offset,
		);
		if ($verbose) echo print_m($request, '$request');
		try {
			$res = MagnaConnector::gi()->submitRequest($request);
		} catch (MagnaException $e) {
			$res = array();
			if (MAGNA_CALLBACK_MODE == 'STANDALONE') {
				echo print_m($e->getErrorArray(), 'Error: '.$e->getMessage(), true);
			}
			if (MAGNA_DEBUG && ($e->getMessage() == ML_INTERNAL_API_TIMEOUT)) {
				$e->setCriticalStatus(false);
			}
			$break = true;
		}
		if (!array_key_exists('DATA', $res) || empty($res['DATA'])) {
			if ($verbose) echo "No Data.\n";
			# delete surplus orders_total lines
    		delete_double_ot_lines();
			return false;
		}
		
		$break = !$res['HASNEXT'];
		$offset['START'] += $offset['COUNT'];

		$orders = $res['DATA'];
		#unset($res['DATA']);
		if ($verbose) echo print_m($res, '$res');
	
		# ggf. Zeichensatz korrigieren
		if ('utf8' != $character_set_client) {
			arrayEntitiesToLatin1($orders);
		}
		
		$syncBatch = array();
		
		# Bestelldaten durchgehen.
		foreach ($orders as $order) {
			/* {Hook} "GeteBayOrders_PreOrderImport": Is called before the eBay order in <code>$order</code> is imported.
				Variables that can be used:
				<ul><li>$order: The order that is going to be imported. The order is an 
				        associative array representing the structures of the order and customer related shop tables.</li>
				    <li>$mpID: The ID of the marketplace.</li>
				    <li>$MagnaDB: Instance of the magnalister database class. USE THIS for accessing the database during the
				        order import. DO NOT USE the shop functions for database access or MagnaDB::gi()!</li>
				</ul>
			*/
			if (($hp = magnaContribVerify('GeteBayOrders_PreOrderImport', 1)) !== false) {
				require($hp);
			}
			# eBay-OrderID == ItemID-TransactionID
			if ($verbose) echo "\n== Processing ".$order['orderInfo']['eBayOrderID'].". ==\n";
			if (!array_key_exists($order['order']['currency'], $allCurrencies)) {
				# Gibts die Waehrung auch im Shop?
				if (!$simplePrice->currencyExists($order['order']['currency'])) {
					if ($verbose) echo $order['order']['currency'] . ": Currency does not exist.\n";
					continue;
				}
				
				$simplePrice->setCurrency($order['order']['currency']);
				if ($updateExchangeRate) {
					$simplePrice->updateCurrencyByService();
				}
				$currencyValue = $simplePrice->getCurrencyValue();
				if ((float)$currencyValue <= 0.0) {
					if ($verbose) echo "CurrencyValue <= 0.\n";
					continue;
				}
				$allCurrencies[$order['order']['currency']] = $currencyValue;
			}

			# Zeitpunkte des ML Servers in lokale Zeit umrechnen:
			if (MagnaDB::gi()->columnExistsInTable('customers_last_modified', TABLE_CUSTOMERS)){
				$order['customer']['customers_last_modified'] = magnaTimeToLocalTime($order['customer']['customers_last_modified']);
			}
			if (MagnaDB::gi()->columnExistsInTable('customers_date_added', TABLE_CUSTOMERS)){
				$order['customer']['customers_date_added']    = magnaTimeToLocalTime($order['customer']['customers_date_added']);
			}
			if (MagnaDB::gi()->columnExistsInTable('address_last_modified', TABLE_ADDRESS_BOOK)){
			$order['adress']['address_last_modified'] = magnaTimeToLocalTime($order['adress']['address_last_modified']);
			}
			if (MagnaDB::gi()->columnExistsInTable('address_date_added', TABLE_ADDRESS_BOOK)){
			$order['adress']['address_date_added']    = magnaTimeToLocalTime($order['adress']['address_date_added']);
			}
			$order['order']['date_purchased'] = magnaTimeToLocalTime($order['order']['date_purchased']);
			$order['order']['last_modified']  = magnaTimeToLocalTime($order['order']['last_modified']);
			$order['orderStatus']['date_added'] = magnaTimeToLocalTime($order['orderStatus']['date_added']);
	
			$countryISO = $order['order']['billing_country_iso_code_2'];
	
			$simplePrice->setCurrency($order['order']['currency']);
			$order['order']['currency_value'] = $allCurrencies[$order['order']['currency']];
	
			if (MagnaDB::gi()->columnExistsInTable('customers_cid',TABLE_CUSTOMERS)) {
				$tmp_customer_id = MagnaDB::gi()->fetchRow('
					SELECT customers_id, customers_cid FROM '.TABLE_CUSTOMERS.' 
				 	WHERE customers_email_address=\''.$order['customer']['customers_email_address'].'\' 
				 	LIMIT 1
				');
				if (!$tmp_customer_id)
					$customersId = false;
				else {
					$customersId = $tmp_customer_id['customers_id'];
					$customersCId = $tmp_customer_id['customers_cid'];
				}
			} else {
				$customersId = MagnaDB::gi()->fetchOne('
					SELECT customers_id FROM '.TABLE_CUSTOMERS.'
					WHERE customers_email_address=\''.$order['customer']['customers_email_address'].'\' LIMIT 1
				');
			}
			
			$countryISO = $order['order']['billing_country_iso_code_2'];
	
			$billingCountry  = magnaGetCountryFromISOCode($order['order']['billing_country_iso_code_2']);
			$shippingCountry = magnaGetCountryFromISOCode($order['order']['delivery_country_iso_code_2']);
	
			# Muss ein neuer Kunde angelegt werden?
			if ($customersId === false) {
				# Der Kunde muss angelegt werden.
				$customers_password = randomString(10);
				$order['customer']['customers_password'] = md5($customers_password);
				
				if (    (MagnaDB::gi()->columnExistsInTable('customers_status', TABLE_CUSTOMERS))
				     && (null !== $ShopInfo['CustomerGroup'])
				) {
					$order['customer']['customers_status'] = $ShopInfo['CustomerGroup'];
				}
				if (MagnaDB::gi()->columnExistsInTable('account_type', TABLE_CUSTOMERS)) {
					$order['customer']['account_type'] = '0';
				}
				if (function_exists('tep_encrypt_password')) {
                    $order['customer']['customers_password'] = tep_encrypt_password($customers_password);
                }
				$MagnaDB->insert(TABLE_CUSTOMERS, $order['customer']);
				
				# Kunden-ID herausfinden
				$customersId = $MagnaDB->getLastInsertID();
				# customers_cid bestimmen
				if (MagnaDB::gi()->columnExistsInTable('customers_cid',TABLE_CUSTOMERS)) {
					switch (getDBConfigValue('customers_cid.assignment', '0', 'none')) {
						case 'sequential': 
							$customersCId = MagnaDB::gi()->fetchOne('
					  		SELECT MAX(CAST(IFNULL(customers_cid,0) AS SIGNED))+1
					  		FROM '.TABLE_CUSTOMERS);
							break;
						case 'customers_id':
							$customersCId = $customersId;
							break;
						case 'none':
						default:
							if (isset($customersCId)) unset($customersCId);
							break;
					}
					if (isset($customersCId)) 
						MagnaDB::gi()->update(TABLE_CUSTOMERS, array('customers_cid' => $customersCId), array('customers_id' => $customersId));
				}
				
				# Infodatensatz erzeugen
				$MagnaDB->insert(TABLE_CUSTOMERS_INFO, array(
					'customers_info_id' => $customersId,
					'customers_info_number_of_logons' => 0,
					'customers_info_date_account_created' => date('Y-m-d H:i:s', strtotime($order['order']['date_purchased']) - 1),
					'customers_info_date_account_last_modified' => date('Y-m-d H:i:s'),
				));
				// echo 'DELETE FROM '.TABLE_CUSTOMERS_INFO.' WHERE customers_info_id=\''.$customersId.'\';'."\n\n";
	
				# Adressbuchdatensatz ergaenzen.
				$country = magnaGetCountryFromISOCode($order['order']['billing_country_iso_code_2']);
				$order['adress']['customers_id'] = $customersId;
				$order['adress']['entry_country_id'] = $billingCountry['countries_id'];
	
				$MagnaDB->insert(TABLE_ADDRESS_BOOK, $order['adress']);
	
				# Adressbuchdatensatz-Id herausfinden.
				$abId = $MagnaDB->getLastInsertID();
				// echo 'DELETE FROM '.TABLE_ADDRESS_BOOK.' WHERE customers_id=\''.$customersId.'\';'."\n\n";
	
				# Kundendatensatz updaten.
				$MagnaDB->update(TABLE_CUSTOMERS, array(
					'customers_default_address_id' => $abId
				), array (
					'customers_id' => $customersId
				));
			} else {
				# Adressbuchdatensatz erneuern
					$customer = $order['customer'];
					unset($customer['customers_date_added']);
					unset($customer['account_type']);
					$MagnaDB->update(TABLE_CUSTOMERS, $customer, array (
						'customers_id' => $customersId,
					));
					
					if (isset($order['adress']['address_date_added'])) {
						unset($order['adress']['address_date_added']);
					}
					$order['adress']['entry_country_id'] = $billingCountry['countries_id'];
					$MagnaDB->update(TABLE_ADDRESS_BOOK, $order['adress'], array (
						'customers_id' => $customersId,
					));
				
			# Falls Altkunde, haben wir kein Password, brauchen geeigneten Platzhalter
				switch($countryISO) {
					case('AT'):
					case('DE'): $customers_password = '(wie bekannt)';
						break;
					default:    $customers_password = '(as known)';
						break;
				}
			}
	
			if (0 <> MagnaDB::gi()->fetchOne('SELECT COUNT(*) FROM '.TABLE_MAGNA_ORDERS.'
				WHERE platform = \'ebay\' AND special like \'%'.$order['orderInfo']['eBayOrderID'].'%\''
			 )) {
				# Bestellung mit dieser ItemID / TransactionID existiert bereits.
				if ($verbose) echo "Order already exists.\n";
				$ordersId = MagnaDB::gi()->fetchOne('
					SELECT orders_id
					  FROM '.TABLE_MAGNA_ORDERS.'
					 WHERE platform = \'ebay\'
					       AND special like \'%'.$order['orderInfo']['eBayOrderID'].'%\'
				  ORDER BY orders_id DESC
				     LIMIT 1
				');
				$processedOrders[] = array (
					'MOrderID' => $order['orderInfo']['eBayOrderID'],
					'ShopOrderID' => $ordersId
				);
				continue;
			}
	
			# Bestellung anlegen.
			# Hauptdatensatz in Tabelle "orders".

			$order['order']['customers_id'] = $customersId;
			if (isset($customersCId)) {
				$order['order']['customers_cid'] = $customersCId;
			}
			$order['order']['customers_address_format_id'] = $order['order']['billing_address_format_id'] = magnaGetAddressFormatID($billingCountry['countries_id']);
			$order['order']['delivery_address_format_id']  = magnaGetAddressFormatID($shippingCountry['countries_id']);
			$order['order']['orders_status'] = $ShopInfo['OrderStatusOpen'];
	
			$order['order']['customers_country'] = $billingCountry['countries_name'];
			$order['order']['delivery_country'] = $shippingCountry['countries_name'];
			$order['order']['billing_country'] = $billingCountry['countries_name'];

			if (isset ($ShopInfo['DefaultShippingMethodName'])
                && MagnaDB::gi()->columnExistsInTable('shipping_class',  TABLE_ORDERS)
                && MagnaDB::gi()->columnExistsInTable('shipping_method', TABLE_ORDERS)) {
				$order['order']['shipping_class']  = $ShopInfo['DefaultShippingMethod'];
				$order['order']['shipping_method'] = $ShopInfo['DefaultShippingMethodName'];
			}
	
			if (SHOPSYSTEM != 'oscommerce') {
				$order['order']['customers_status'] = $ShopInfo['CustomerGroup'];
				$order['order']['language'] = $_magnaLanguage;
				
				if (!empty($order['orderInfo']['eBayBuyerUsername']))
					$buyer="\n".'eBay User:   '.$order['orderInfo']['eBayBuyerUsername'];
				else
					$buyer='';
				if (0 != $order['orderInfo']['eBaySalesRecordNumber'])
					$salesRecordNo="\n".ML_LABEL_EBAY_SALES_RECORD_NUMBER.': '.$order['orderInfo']['eBaySalesRecordNumber'];
				else
					$salesRecordNo='';
				$order['order']['comments'] = trim(
					sprintf(ML_GENERIC_AUTOMATIC_ORDER_MP_SHORT, $_modules['ebay']['title'])."\n".
					'eBayOrderID: '.$order['orderInfo']['eBayOrderID'].$salesRecordNo.$buyer
				);
			} else {
				# Spalten die osCommerce nicht hat
				unset($order['order']['customers_cid']);
				unset($order['order']['billing_country_iso_code_2']);
				unset($order['order']['delivery_country_iso_code_2']);
			}

			if ($orderColumns['customers_status_discount']) {
				$order['order']['customers_status_discount'] = '0.0';
			}

            # Gibt es eine Bestellung, zu der man die aktuelle hinzufuegen kann?
            $existingOpenOrder = MagnaDB::gi()->fetchRow(eecho('
                SELECT o.orders_id orders_id, mo.special special, mo.data data 
                  FROM '.TABLE_ORDERS.' o, '.TABLE_MAGNA_ORDERS.' mo
                 WHERE o.customers_id = '.$order['order']['customers_id'].'
                       AND o.customers_email_address = \''.$order['order']['customers_email_address'].'\' 
                       AND o.orders_status NOT IN ('.$ShopInfo['OrderStatusClosed'].')
                       AND mo.mpID = '.$mpID.' AND o.orders_id = mo.orders_id 
              ORDER BY o.orders_id DESC LIMIT 1
            ', $verbose));
            # .' AND o.orders_status = '.$ShopInfo['OrderStatusOpen']
            if ($verbose) echo var_dump_pre($existingOpenOrder, '$existingOpenOrder');
            
            #$existingOrdersTotal = false;
            if (false != $existingOpenOrder) {
                $ordersId = (int)$existingOpenOrder['orders_id'];
                $magnaOrdersDataArr = unserialize($existingOpenOrder['data']);
                if (!is_array($magnaOrdersDataArr['eBayOrderID'])) {
                    $magnaOrdersDataArr['eBayOrderID'] = array($magnaOrdersDataArr['eBayOrderID'],
                                                            $order['magnaOrders']['eBayOrderID']);
                } else {
                    $magnaOrdersDataArr['eBayOrderID'][] = $order['magnaOrders']['eBayOrderID'];
                }
                $magnaOrdersData = serialize($magnaOrdersDataArr);
                $magnaOrdersSpecial = $existingOpenOrder['special']."\n".$order['orderInfo']['eBayOrderID'];
            } else {
				# $existsGmSendOrderStatus check for exsisting table colum gm_send_order_status
				if ($existsGmSendOrderStatus) {
					$order['order']['gm_send_order_status'] = 1;
				}
                # sonst neue anlegen
			    $MagnaDB->insert(TABLE_ORDERS, $order['order']);
			    $ordersId = $MagnaDB->getLastInsertID();
                $magnaOrdersData = serialize($order['magnaOrders']);
                $magnaOrdersSpecial = $order['orderInfo']['eBayOrderID'];
            }
			/* Bestellung in unserer Tabelle registrieren */
			$MagnaDB->insert(TABLE_MAGNA_ORDERS, array(
				'mpID' => $mpID,
				'orders_id' => $ordersId,
				'orders_status' => $order['order']['orders_status'],
				'data' => $magnaOrdersData,
				'internaldata' => '',
				'special' => $magnaOrdersSpecial,
				'platform' => 'ebay'
			), true);
			// echo 'DELETE FROM '.TABLE_MAGNA_ORDERS.' WHERE orders_id=\''.$ordersId.'\';'."\n\n";
	
			# Statuseintrag fuer Historie vornehmen.
			$order['orderStatus']['orders_id'] = $ordersId;
			$order['orderStatus']['orders_status_id'] = $order['order']['orders_status'];
			
			$order['orderStatus']['comments'] = trim(
				sprintf(ML_GENERIC_AUTOMATIC_ORDER_MP, $_modules['ebay']['title'])."\n".
				'eBayOrderID: '.$order['orderInfo']['eBayOrderID']
			);
	
			$MagnaDB->insert(TABLE_ORDERS_STATUS_HISTORY, $order['orderStatus']);
			// echo 'DELETE FROM '.TABLE_ORDERS_STATUS_HISTORY.' WHERE orders_id=\''.$ordersId.'\';'."\n\n";
	
			$mailOrderSummary = array();

			$currProductsCount = 0;
			foreach ($order['products'] as &$prodOrderData) {
				$sku = $prodOrderData['products_id'];
				$prodOrderData['products_price'] = $prodOrderData['final_price'] / $prodOrderData['products_quantity'];
				$mailOrderSummary[] = array(
					'quantity' => $prodOrderData['products_quantity'],
					'name' => $prodOrderData['products_name'],
					'price' => $simplePrice->setPrice($prodOrderData['products_price'])->format(),
					'finalprice' => $simplePrice->setPrice($prodOrderData['final_price'])->format(),
				);
	
				if (array_key_exists($prodOrderData['products_id'], $syncBatch)) {
					$syncBatch[$prodOrderData['products_id']]['NewQuantity']['Value'] += (int)$prodOrderData['products_quantity'];
				} else {
					$syncBatch[$prodOrderData['products_id']] = array (
						'SKU' => $prodOrderData['products_id'],
						'NewQuantity' => array (
							'Mode' => 'SUB',
							'Value' => (int)$prodOrderData['products_quantity']
						),
					);
				}
	
				$prodOrderData['orders_id'] = $ordersId;
				$prodOrderData['products_id'] = magnaSKU2pID($prodOrderData['products_id'], true);
				/* Attribute Values ermitteln aus der VariantenSKU von eBay */
				/* (bei Gambio Properties ist es einfacher, da wird nur die 1 Tabelle genommen) */
				if ($useGambioProperties) {
					$attrValues = false;
				} else {
					$attrValues = variation_products_model2pOpt($prodOrderData['products_model'], $prodOrderData['products_id'], $mpID);
				}
	
				if (!MagnaDB::gi()->recordExists(TABLE_PRODUCTS, array('products_id' => (int)$prodOrderData['products_id']))) {
					$prodOrderData['products_id'] = 0;
					$tax = (float)getDBConfigValue($mp.'.mwstfallback', $mpID);
				} else {
					/* Lagerbestand reduzieren */
					if (getDBConfigValue($mp.'.stocksync.frommarketplace', $mpID) != 'no') {
						$MagnaDB->query('
							UPDATE '.TABLE_PRODUCTS.' SET products_quantity = products_quantity - '.(int)$prodOrderData['products_quantity'].' 
							 WHERE products_id='.(int)$prodOrderData['products_id'].'
						');
						/* Varianten-Bestand reduzieren, falls Produkt mit Varianten (gibt es bei osCommerce nicht) */
						if ((false != $attrValues)
						    && (!empty($attrValues[0]['options_name']))
						    && (MagnaDB::gi()->columnExistsInTable('attributes_stock',TABLE_PRODUCTS_ATTRIBUTES)) 
						) {
							foreach($attrValues as $attrValue) {
								$MagnaDB->query('
								   UPDATE '.TABLE_PRODUCTS_ATTRIBUTES.' SET attributes_stock = attributes_stock - '.(int)$prodOrderData['products_quantity'].'
								    WHERE products_id='.(int)$prodOrderData['products_id'].' 
								          AND options_id='.(int)$attrValue['options_id'].' 
								          AND options_values_id='.(int)$attrValue['options_values_id'].'
								');
							}
							/* Auch in magnalister_variations */
							$MagnaDB->query('
								UPDATE '.TABLE_MAGNA_VARIATIONS. '
								   SET  variation_quantity = variation_quantity - '.(int)$prodOrderData['products_quantity'].'
								 WHERE '.mlGetVariationSkuField().' = \''.MagnaDB::gi()->escape($prodOrderData['products_model']).'\'
							');
						} else if ($useGambioProperties) {
							$combisId = magnaSKU2aID($prodOrderData['products_model'], $prodOrderData['products_id'], true);
							if (false != $combisId) {
								$MagnaDB->query(eecho('
									UPDATE products_properties_combis
										SET combi_quantity = combi_quantity - '.(int)$prodOrderData['products_quantity'].'
										WHERE products_properties_combis_id = '.$combisId, false));
							}
						}
					}
					/* Steuersatz und Model holen */
					$row = MagnaDB::gi()->fetchRow('
						SELECT products_tax_class_id, products_model 
						  FROM '.TABLE_PRODUCTS.' 
						 WHERE products_id=\''.(int)$prodOrderData['products_id'].'\'
					');
					if ($row !== false) {
						$tax = SimplePrice::getTaxByClassID((int)$row['products_tax_class_id'], (int)$shippingCountry['countries_id']);
						if (!$useGambioProperties) {
							$prodOrderData['products_model'] = $row['products_model'];
						}
					} else {
						$tax = 
							isDomestic($shippingCountry['countries_id'])
							? (float)getDBConfigValue($mp.'.mwstfallback', $mpID)
							: 0.0;
					}
				}
				$prodOrderData['products_tax'] = $tax;
	
				$priceWOTax = $simplePrice->setPrice($prodOrderData['products_price'])->removeTax($tax)->getPrice();
	
				if (SHOPSYSTEM != 'oscommerce') {
					if ($displayPriceWithTax) {
						$prodOrderData['allow_tax'] = 1;
					} else {
						$prodOrderData['allow_tax'] = 0;
						$prodOrderData['products_price'] = $priceWOTax;
						$prodOrderData['final_price'] = $priceWOTax * (int)$prodOrderData['products_quantity'];
					}
				} else {
					$prodOrderData['products_price'] = $priceWOTax;
					$prodOrderData['final_price'] = $prodOrderData['products_price'];
				}

				# Sonderzeichen im Produkt-Namen un-HTMLen (weil PDF-Werkzeuge es nicht anders vertragen)
				$prodOrderData['products_name'] = html_entity_decode($prodOrderData['products_name'], ENT_COMPAT, (('utf8' == $character_set_client)? 'UTF-8' : 'ISO-8859-1'));
	
				# Produktdatensatz in Tabelle "orders_products".					
				$MagnaDB->insert(TABLE_ORDERS_PRODUCTS, $prodOrderData);
				$ordersProductsId = $MagnaDB->getLastInsertID();
	
				// orders_products_attributes:
				if ($attrValues) {
					$bPAttrTableHasOptID = MagnaDB::gi()->columnExistsInTable('products_options_id', TABLE_ORDERS_PRODUCTS_ATTRIBUTES);
				    foreach ($attrValues as $attrValue) {
						$prodOrderAttrData = array(
						    'orders_id' => $prodOrderData['orders_id'],
						    'orders_products_id' => $ordersProductsId,
						    'products_options' => $attrValue['options_name'],
						    'products_options_values' => $attrValue['options_values_name'],
						    'options_values_price' => (float)$attrValue['options_values_price'],
						    'price_prefix' => ''
						);
						if (!empty($attrValue['options_name'])) {
							if ($bPAttrTableHasOptID) {
								$prodOrderAttrData['products_options_id'] = $attrValue['options_id'];
								$prodOrderAttrData['products_options_values_id'] = $attrValue['options_values_id'];
							}
							if (MagnaDB::gi()->columnExistsInTable('products_attributes_model', TABLE_ORDERS_PRODUCTS_ATTRIBUTES)) {
								$prodOrderAttrData['products_attributes_model'] = ('artNr' == getDBConfigValue('general.keytype', '0'))
									? $sku
									: MagnaDB::gi()->fetchOne('
											SELECT attributes_model
											  FROM '.TABLE_PRODUCTS_ATTRIBUTES.'
											 WHERE products_id='.(int)$prodOrderData['products_id'].'
											       AND options_id='.(int)$attrValue['options_id'].'
											       AND options_values_id='.(int)$attrValue['options_values_id'].'
									');
							}
						    $MagnaDB->insert(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $prodOrderAttrData);
						}
				    }
				}
				// orders_products_properties (bei Gambio)
				if (isset($combisId) && (false != $combisId)) {
					$MagnaDB->query(eecho("
						INSERT INTO orders_products_properties (orders_products_id, products_properties_combis_id, properties_name, values_name, properties_price_type, properties_price)
						 (SELECT '$ordersProductsId' AS orders_products_id,
							'$combisId' AS products_properties_combis_id,
							ppi.properties_name, ppi.values_name,
							ppc.combi_price_type AS properties_price_type,
							ppi.values_price AS properties_price
						   FROM products_properties_index ppi, products_properties_combis ppc
						  WHERE ppi.products_properties_combis_id = ppc.products_properties_combis_id
							AND ppi.language_id=$_magnaLanguageId
							AND ppc.products_properties_combis_id=$combisId)", false));
				}
				# Anzahl Produkte merken
				$currProductsCount += (int)$prodOrderData['products_quantity'];
			}

			/* Versandkosten (maximal, bei zusammengefassten) */
			/*$shippingCost = max((float)MagnaDB::gi()->fetchOne('
				SELECT value 
				  FROM '.TABLE_ORDERS_TOTAL.'
				 WHERE orders_id = '.$ordersId.'
				       AND class = \'ot_shipping\'
			  ORDER BY value DESC 
			     LIMIT 1
			'), (array_key_exists('Shipping', $order['orderTotal']))
				? $order['orderTotal']['Shipping']['value']
				: 0
			);*/
/* 
	-schau wie die konfigurierten Kosten aussehen,
	-schau wieviele Artikel drin sind,
	-daraus die Kosten fuer den ersten + je weiteren
	-schau ob uebermittelte Kosten > Kosten fuer den ersten
	-wenn ja, neue Kosten = uebermittelte Kosten + bisherige Anzahl * addcost fuer uebermittelte Versandart (anhand Kosten)
	-wenn nein, neue Kosten = alte Kosten + addcost fuer alte Versandart
*/
			$existingShippingCost = (float)MagnaDB::gi()->fetchOne(eecho('
				SELECT value
					FROM '.TABLE_ORDERS_TOTAL.'
				 WHERE orders_id = '.$ordersId.'
				       AND class = \'ot_shipping\'
			  ORDER BY value DESC 
			     LIMIT 1
			', $verbose));
			$productsCount = (int)MagnaDB::gi()->fetchOne(eecho('
				SELECT SUM(products_quantity) 
					FROM '.TABLE_ORDERS_PRODUCTS.'
				WHERE orders_id = '.$ordersId.'
			', $verbose));
			$shippingCost = calculate_shipping_cost($existingShippingCost,
				array_key_exists('Shipping', $order['orderTotal'])
					? $order['orderTotal']['Shipping']['value']
					: 0,
				$productsCount,
				$prodOrderData['final_price'], # CHECKEN ist das auch der Gesamtpreis?
				$currProductsCount,
				$countryISO,
				$mpID);
			if ($verbose) { echo "shippingCost == $shippingCost\n"; }

			$mfot = new MagnaRecalcOrdersTotal();
			$ordersTotal = $mfot->recalcExistingOrder($ordersId, $shippingCost, (get_class($MagnaDB) != 'MagnaTestDB'));
			
			if (getDBConfigValue($mp.'.mail.send', $mpID, 'false') == 'true') {
				sendSaleConfirmationMail(
					$mpID,
					$order['customer']['customers_email_address'],
					array(
						'#SHOPORDERID#' => $ordersId,
						'#FIRSTNAME#' => $order['customer']['customers_firstname'],
						'#LASTNAME#' => $order['customer']['customers_lastname'],
						'#EMAIL#' => $order['customer']['customers_email_address'],
						'#PASSWORD#' => $customers_password,
						'#ORDERSUMMARY#' => $mailOrderSummary,
						'#MARKETPLACE#' => $_modules['ebay']['title'],
						'#SHOPURL#' => '',
					)
				);
			}
			unset($customers_password); # nicht dass es versehentlich an weitere Kunden geht
			$lastOrder = $order['order']['date_purchased'];
			if ($verbose) echo "\n### Done.\n\n";
			
			$processedOrders[] = array (
				'MOrderID' => $order['orderInfo']['eBayOrderID'],
				'ShopOrderID' => $ordersId
			);
			/* {Hook} "GeteBayOrders_PostOrderImport": Is called after the eBay order in <code>$order</code> is imported.
				Variables that can be used: Same as for GeteBayOrders_PreOrderImport.
			*/
			if (($hp = magnaContribVerify('GeteBayOrders_PostOrderImport', 1)) !== false) {
				require($hp);
			}
			/* debug */
			$ot = MagnaDB::gi()->fetchArray('SELECT * FROM '.TABLE_ORDERS_TOTAL.' WHERE orders_id=\''.$ordersId.'\'');
			if ((get_class($MagnaDB) != 'MagnaTestDB') && ((count($order['orderTotal']) < 3) || (count($ot) < 3))) {
				$supportMessage = 'ACHTUNG: Der eBay Bestellimport hat eine Bestellung ohne orders_total angelegt.'."\n\n";
				$supportMessage .= print_m($orders, 'Daten die die Schnittstelle uebergeben hat', true)."\n\n";
				$supportMessage .= print_m($order, 'Daten der aktuellen Bestellung', true)."\n\n";
				$supportMessage .= print_m($ot, 'Daten der aus DB', true)."\n\n";
				#echo $supportMessage;
				try {
					$res = MagnaConnector::gi()->submitRequest(array (
						'ACTION' => 'SendMessage',
						'SUBSYSTEM' => 'core',
						'DATA' => $supportMessage,
					));
				} catch (MagnaException $e) {
					#echo print_m($e);
					$e->setCriticalStatus(false);
				}
			}
		}
		
		if (get_class($MagnaDB) != 'MagnaTestDB') {
			require_once(DIR_MAGNALISTER_CALLBACK.'inventoryUpdate.php');
			magnaInventoryUpdateByOrderImport(array_values($syncBatch), $mpID);
			if ($lastOrder !== '') {
				setDBConfigValue($mp.'.orderimport.lastrun', $mpID, $lastOrder, true);
			}
		}

		/* Acknowledge imported orders */
		$request = array(
			'ACTION' => 'AcknowledgeImportedOrders',
			'SUBSYSTEM' => 'eBay',
			'MARKETPLACEID' => $mpID,
			'DATA' => $processedOrders,
		);
		if ($verbose) echo print_m($request);
		if (get_class($MagnaDB) != 'MagnaTestDB') {
			try {
				$res = MagnaConnector::gi()->submitRequest($request);
				$processedOrders = array();
			} catch (MagnaException $e) {
				/* don't show these errors. */
				if (MAGNA_CALLBACK_MODE == 'STANDALONE') {
					echo print_m($e->getErrorArray(), 'Error: '.$e->getMessage(), true);
				}
				if ($e->getCode() == MagnaException::TIMEOUT) {
					$e->saveRequest();
					$e->setCriticalStatus(false);
				}
			}
		} else {
			$processedOrders = array();
		}
		
		#break;
	}
	# delete surplus orders_total lines
    delete_double_ot_lines();
}
