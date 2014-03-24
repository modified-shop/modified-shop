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
 * $Id: get_amazon_orders.php 3513 2014-02-12 22:14:53Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

/* Amazon Bestellungen importieren */ 
function magnaImportAmazonOrders($mpID) {
	global $magnaConfig, $_magnaLanguage, $_modules;

	$mp = 'amazon';

	require_once(DIR_MAGNALISTER_MODULES.'amazon/amazonFunctions.php');
	require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
    if (   ('oscommerce' == SHOPSYSTEM)
		&& (!function_exists('tep_encrypt_password'))
	    && ( file_exists(DIR_WS_FUNCTIONS.'password_funcs.php'))) {
        require_once(DIR_WS_FUNCTIONS.'password_funcs.php');
    }

	//*
	require_once(DIR_MAGNALISTER_INCLUDES . 'lib/MagnaTestDB.php');
	$MagnaDB = MagnaTestDB::gi();
	/*/
	$MagnaDB = MagnaDB::gi();
	//*/

	$character_set_client = MagnaDB::gi()->mysqlVariableValue('character_set_client');
    if (('utf8mb3' == $character_set_client) || ('utf8mb4' == $character_set_client)) {
	# means the same for us
		$character_set_client = 'utf8';
	}
	
	$verbose = (MAGNA_CALLBACK_MODE == 'STANDALONE') && (get_class($MagnaDB) == 'MagnaTestDB');

	$simplePrice = new SimplePrice();

	$ShopInfo = array(
		'Lang' => getDBConfigValue($mp.'.lang', $mpID),
		'CustomerGroup' => getDBConfigValue($mp.'.CustomerGroup', $mpID),
		'OrderStatusOpen' => getDBConfigValue($mp.'.orderstatus.open', $mpID),
		'OrderStatusShipped' => getDBConfigValue($mp.'.orderstatus.shipped', $mpID),
		'OrderStatusFba' => getDBConfigValue($mp.'.orderstatus.fba', $mpID),
	);
	$defaultShippingAndPaymentMethod = 'amazon';
	$ShopInfo['ShippingMethod'] = getDBConfigValue($mp.'.orderimport.shippingmethod', $mpID, 'textfield');
	if ($ShopInfo['ShippingMethod'] == 'textfield') {
		$ShopInfo['ShippingMethod'] = trim(getDBConfigValue($mp.'.orderimport.shippingmethod.name', $mpID, $defaultShippingAndPaymentMethod));
	}
	if (empty($ShopInfo['ShippingMethod'])) {
		$ShopInfo['ShippingMethod'] = $defaultShippingAndPaymentMethod;
	}
	$ShopInfo['PaymentMethod'] = getDBConfigValue($mp.'.orderimport.paymentmethod', $mpID, 'textfield');
	if ($ShopInfo['PaymentMethod'] == 'textfield') {
		$ShopInfo['PaymentMethod'] = trim(getDBConfigValue($mp.'.orderimport.paymentmethod.name', $mpID, $defaultShippingAndPaymentMethod));
	}
	if (empty($ShopInfo['PaymentMethod'])) {
		$ShopInfo['PaymentMethod'] = $defaultShippingAndPaymentMethod;
	}
	
	/* again, just FBA related. */
	$defaultShippingAndPaymentMethodFBA = 'amazon';
	$ShopInfo['ShippingMethodFBA'] = getDBConfigValue($mp.'.orderimport.fbashippingmethod', $mpID, 'textfield');
	if ($ShopInfo['ShippingMethodFBA'] == 'textfield') {
		$ShopInfo['ShippingMethodFBA'] = trim(getDBConfigValue($mp.'.orderimport.fbashippingmethod.name', $mpID, $defaultShippingAndPaymentMethodFBA));
	}
	if (empty($ShopInfo['ShippingMethodFBA'])) {
		$ShopInfo['ShippingMethodFBA'] = $defaultShippingAndPaymentMethodFBA;
	}
	$ShopInfo['PaymentMethodFBA'] = getDBConfigValue($mp.'.orderimport.fbapaymentmethod', $mpID, 'textfield');
	if ($ShopInfo['PaymentMethodFBA'] == 'textfield') {
		$ShopInfo['PaymentMethodFBA'] = trim(getDBConfigValue($mp.'.orderimport.fbapaymentmethod.name', $mpID, $defaultShippingAndPaymentMethodFBA));
	}
	if (empty($ShopInfo['PaymentMethodFBA'])) {
		$ShopInfo['PaymentMethodFBA'] = $defaultShippingAndPaymentMethodFBA;
	}
	
	$updateExchangeRate = getDBConfigValue(array($mp.'.exchangerate', 'update'), $mpID, false);
	
	$lastImport = getDBConfigValue($mp.'.orderimport.lastrun', $mpID, 0);
	if (preg_match('
		/^([1-2][0-9]{3})-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s'.
		'([0-1][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9])$/',
		$lastImport
	)) {
		# Since we only request non acknowledged orders, we go back in time by 7 days.
		$lastImport = strtotime($lastImport.' +0000') - 60 * 60 * 24 * 7;
	} else {
		$lastImport = 0;
	}

	$begin = strtotime(getDBConfigValue($mp.'.preimport.start', $mpID, '1970-01-01'));
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
	#$begin -= 60 * 60 * 24 * 30 * 12;

	# Bestelldaten abfragen.
	$break = false;
	$offset = array (
		'COUNT' => 200,
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
			'SUBSYSTEM' => 'Amazon',
			'MARKETPLACEID' => $mpID,
			'BEGIN' => gmdate('Y-m-d H:i:s', $begin),
			'IgnoreLastImport' => false,
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
		#if ($verbose) echo print_m($res, '$res');
		if (!array_key_exists('DATA', $res) || empty($res['DATA'])) {
			if ($verbose) echo "No Data.\n";
			return false;
		}
		
		$break = !$res['HASNEXT'];
		$offset['START'] += $offset['COUNT'];

		$orders = $res['DATA'];
		unset($res['DATA']);
		
		if ($verbose) echo print_m($res, '$res');
		
		# ggf. Zeichensatz korrigieren
		if ('utf8' != $character_set_client) {
			arrayEntitiesToLatin1($orders);
		}
		
		$syncBatch = array();
		
		# Bestelldaten durchgehen.
		foreach ($orders as $order) {
			$Continue = false;
			/* {Hook} "GetAmazonOrders_PreOrderImport": Is called before the amazon order in <code>$order</code> is imported.
				Variables that can be used:
				<ul><li>$order: The order that is going to be imported. The order is an 
				        associative array representing the structures of the order and customer related shop tables.</li>
				    <li>$mpID: The ID of the marketplace.</li>
				    <li>$MagnaDB: Instance of the magnalister database class. USE THIS for accessing the database during the
				        order import. DO NOT USE the shop functions for database access or MagnaDB::gi()!</li>
					<li>$Continue (bool): Set this to true to skip the processing of current order.</li>
				</ul>
			*/
			if (($hp = magnaContribVerify('GetAmazonOrders_PreOrderImport', 1)) !== false) {
				require($hp);
			}
			if ($Continue) {
				continue;
			}
			if ($verbose) echo "\n== Processing ".$order['magnaOrders']['AmazonOrderID'].". ==\n";
			if (!array_key_exists($order['order']['currency'], $allCurrencies)) {
				# Gibts die Waehrung auch im Shop?
				if (!$simplePrice->currencyExists($order['order']['currency'])) {
					if ($verbose) echo "Currency does not exist.\n";
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
	
			$countryISO = $order['adress']['entry_country_id'];
	
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
			
			$countryISO = $order['adress']['entry_country_id'];
	
			$billingCountry  = magnaGetCountryFromISOCode($order['adress']['entry_country_id']);
			$shippingCountry = magnaGetCountryFromISOCode($order['order']['delivery_country']);
	
			# Muss ein neuer Kunde angelegt werden?
			if ($customersId === false) {
				# Der Kunde muss angelegt werden.
				$customers_password = randomString(10);
				$order['customer']['customers_password'] = md5($customers_password);
				
				if (SHOPSYSTEM != 'oscommerce') {
					$order['customer']['customers_status'] = $ShopInfo['CustomerGroup'];
					$order['customer']['account_type'] = '0';
				} else if (function_exists('tep_encrypt_password')) {
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
					if (isset($customersCId)) {
						MagnaDB::gi()->update(TABLE_CUSTOMERS, array('customers_cid' => $customersCId), array('customers_id' => $customersId));
					}
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
				$country = magnaGetCountryFromISOCode($order['adress']['entry_country_id']);
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
			$orderUpdate = false;
			if (($ordersId = MagnaDB::gi()->fetchOne('
					SELECT orders_id 
					  FROM '.TABLE_MAGNA_ORDERS.'
					 WHERE special=\''.$order['orderInfo']['AmazonOrderID'].'\'
				')) !== false
			) {
				# Bestellung existiert bereits.
				if ($verbose) echo "Order already exists.\n";
				$orderUpdate = true;
				
				//*
				$processedOrders[] = array (
					'MOrderID' => $order['orderInfo']['AmazonOrderID'],
					'ShopOrderID' => $ordersId,
				);
				continue;
				//*/
			}
			
			# Bestellung anlegen.
			# Hauptdatensatz in Tabelle "orders".

			$finalMPTitle = $_modules['amazon']['title'].(
				($order['orderInfo']['FulfillmentChannel'] == 'AFN')
					? 'FBA'
					: ''
			);

			$order['order']['customers_id'] = $customersId;
			$order['order']['customers_address_format_id'] = $order['order']['billing_address_format_id'] = magnaGetAddressFormatID($billingCountry['countries_id']);
			$order['order']['delivery_address_format_id']  = magnaGetAddressFormatID($shippingCountry['countries_id']);
			if ($order['orderInfo']['FulfillmentChannel'] == 'AFN') {
				$order['order']['orders_status'] = $ShopInfo['OrderStatusFba'];
			} else {
				$order['order']['orders_status'] = $ShopInfo['OrderStatusOpen'];
			}
	
			$order['order']['customers_country'] = $billingCountry['countries_name'];
			$order['order']['delivery_country'] = $shippingCountry['countries_name'];
			$order['order']['billing_country'] = $billingCountry['countries_name'];

			if (SHOPSYSTEM != 'oscommerce') {
				if (isset($customersCId)) {
					$order['order']['customers_cid'] = $customersCId;
				}
				$order['order']['customers_status'] = $ShopInfo['CustomerGroup'];
				$order['order']['language'] = $_magnaLanguage;

				$order['order']['comments'] = str_replace('GiftMessageText', ML_AMAZON_LABEL_GIFT_MESSAGE, $order['order']['comments']);
				$order['order']['comments'] = trim(
					sprintf(ML_GENERIC_AUTOMATIC_ORDER_MP_SHORT, $finalMPTitle)."\n".
					'AmazonOrderID: '.$order['magnaOrders']['AmazonOrderID']."\n\n".
					$order['order']['comments']
				);
			}
			
			/* Change Shipping and Payment Methods */
			if ($order['orderInfo']['FulfillmentChannel'] == 'AFN') {
				$order['order']['payment_method'] = $ShopInfo['PaymentMethodFBA'];
			} else {
				$order['order']['payment_method'] = $ShopInfo['PaymentMethod'];
			}
			if (SHOPSYSTEM != 'oscommerce') {
				$order['order']['payment_class'] = $order['order']['payment_method'];
				if ($order['orderInfo']['FulfillmentChannel'] == 'AFN') {
					$order['order']['shipping_class'] = $order['order']['shipping_method'] = $ShopInfo['ShippingMethodFBA'];
				} else {
					$order['order']['shipping_class'] = $order['order']['shipping_method'] = $ShopInfo['ShippingMethod'];
				}
			}
			
			# $existsGmSendOrderStatus check for exsisting table colum gm_send_order_status
			if ($existsGmSendOrderStatus) {
				$order['order']['gm_send_order_status'] = 1;
			}
			if ($orderColumns['customers_status_discount']) {
				$order['order']['customers_status_discount'] = '0.0';
			}
			
			if ($orderUpdate) {
				/*
				$MagnaDB->update(TABLE_ORDERS, $order['order'], array (
					'orders_id' => $ordersId
				));
				*/
			} else {
				$MagnaDB->insert(TABLE_ORDERS, $order['order']);
				# OrderId merken
				$ordersId = $MagnaDB->getLastInsertID();
			}
			// echo 'DELETE FROM '.TABLE_ORDERS.' WHERE orders_id=\''.$ordersId.'\';'."\n\n";

			$internalData = array (
				'FulfillmentChannel' => $order['orderInfo']['FulfillmentChannel']
			);
			$magnaOrder = array(
				'mpID' => $mpID,
				'orders_id' => $ordersId,
				'orders_status' => $order['order']['orders_status'],
				'data' => serialize($order['magnaOrders']),
				'internaldata' => serialize($internalData),
				'special' => $order['magnaOrders']['AmazonOrderID'],
				'platform' => 'amazon'
			);
			
			/* Bestellung in unserer Tabelle registrieren */
			if (!$orderUpdate || !MagnaDB::gi()->recordExists(TABLE_MAGNA_ORDERS, array (
				'orders_id' => $ordersId,
			))) {
				$MagnaDB->insert(TABLE_MAGNA_ORDERS, $magnaOrder);
			} else {
				/*
				$MagnaDB->update(TABLE_MAGNA_ORDERS, $magnaOrder, array (
					'orders_id' => $ordersId
				));
				//*/
			}
			// echo 'DELETE FROM '.TABLE_MAGNA_ORDERS.' WHERE orders_id=\''.$ordersId.'\';'."\n\n";
			
			# Statuseintrag fuer Historie vornehmen.
			$order['orderStatus']['orders_id'] = $ordersId;
			$order['orderStatus']['orders_status_id'] = $order['order']['orders_status'];
			
			$order['orderStatus']['comments'] = str_replace('GiftMessageText', ML_AMAZON_LABEL_GIFT_MESSAGE, $order['orderStatus']['comments']);
			
			$order['orderStatus']['comments'] = trim(
				sprintf(ML_GENERIC_AUTOMATIC_ORDER_MP, $finalMPTitle)."\n".
				'AmazonOrderID: '.$order['magnaOrders']['AmazonOrderID']."\n\n".
				$order['orderStatus']['comments']
			);
			
			if (!$orderUpdate || !MagnaDB::gi()->recordExists(TABLE_ORDERS_STATUS_HISTORY, array (
				'orders_id' => $ordersId,
			))) {
				$MagnaDB->insert(TABLE_ORDERS_STATUS_HISTORY, $order['orderStatus']);
			}
			// echo 'DELETE FROM '.TABLE_ORDERS_STATUS_HISTORY.' WHERE orders_id=\''.$ordersId.'\';'."\n\n";
			
			$mailOrderSummary = array();
			$taxes = array();
			#echo print_m($order['products']);
			foreach ($order['products'] as &$prodOrderData) {
				$prodOrderData['products_name'] = str_replace('GiftWrapType', ML_AMAZON_LABEL_GIFT_PAPER, $prodOrderData['products_name']);
				
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
				/* Attribute Values ermitteln aus der SKU von Amazon, nicht aus dem Hauptprodukt.
				   Daher bevor products_id ermittelt wird. */
				$attrValues = magnaSKU2pOpt($prodOrderData['products_id'], $countryISO);
				$prodOrderData['products_id'] = magnaSKU2pID($prodOrderData['products_id']);
	
				if (!MagnaDB::gi()->recordExists(TABLE_PRODUCTS, array('products_id' => (int)$prodOrderData['products_id']))) {
					$prodOrderData['products_id'] = 0;
					$tax = (float)getDBConfigValue($mp.'.mwstfallback', $mpID);
				} else {
					/* Lagerbestand reduzieren */
					if ((getDBConfigValue($mp.'.stocksync.frommarketplace', $mpID) != 'no') && ($order['orderInfo']['FulfillmentChannel'] != 'AFN')
						|| (getDBConfigValue($mp.'.stocksync.frommarketplace', $mpID) == 'fba') && ($order['orderInfo']['FulfillmentChannel'] == 'AFN')
					) {
						$MagnaDB->query('
							UPDATE '.TABLE_PRODUCTS.' SET products_quantity = products_quantity - '.(int)$prodOrderData['products_quantity'].' 
							 WHERE products_id='.(int)$prodOrderData['products_id'].'
						');
						/* Varianten-Bestand reduzieren, falls Produkt mit Varianten (gibt es bei osCommerce nicht) */
						if (!empty($attrValues['options_name'])
						    && (MagnaDB::gi()->columnExistsInTable('attributes_stock',TABLE_PRODUCTS_ATTRIBUTES)) 
						) {
							$MagnaDB->query('
								UPDATE '.TABLE_PRODUCTS_ATTRIBUTES.' SET attributes_stock = attributes_stock - '.(int)$prodOrderData['products_quantity'].'
								 WHERE products_id='.(int)$prodOrderData['products_id'].' 
								       AND options_id='.(int)$attrValues['options_id'].' 
								       AND options_values_id='.(int)$attrValues['options_values_id'].'
							');
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
						$prodOrderData['products_model'] = $row['products_model'];
					} else {
						$tax = (float)getDBConfigValue($mp.'.mwstfallback', $mpID);
					}
				}
				$prodOrderData['products_tax'] = $tax;
	
				$priceWOTax = $simplePrice->setPrice($prodOrderData['products_price'])->removeTax($tax)->getPrice();
	
				if (!isset($taxes[$tax])) {
					$taxes[$tax] = 0.0;
				}
				$taxes[$tax] += $priceWOTax * (int)$prodOrderData['products_quantity'];
	
				if (SHOPSYSTEM != 'oscommerce') {
					$prodOrderData['allow_tax'] = 1;
				} else {
					$prodOrderData['products_price'] = $priceWOTax;
					$prodOrderData['final_price'] = $prodOrderData['products_price'];
				}
				
				# Produktdatensatz in Tabelle "orders_products".
				if (!$orderUpdate || !MagnaDB::gi()->recordExists(TABLE_ORDERS_PRODUCTS, array (
					'orders_id' => $ordersId,
					'products_model' => $prodOrderData['products_model']
				))) {
					$MagnaDB->insert(TABLE_ORDERS_PRODUCTS, $prodOrderData);
					$ordersProductsId = $MagnaDB->getLastInsertID();
					
					// orders_products_attributes:
					$prodOrderAttrData = array(
						'orders_id' => $prodOrderData['orders_id'],
						'orders_products_id' => $ordersProductsId,
						'products_options' => $attrValues['options_name'],
						'products_options_values' => $attrValues['options_values_name'],
						'options_values_price' => 0.0,
						'price_prefix' => ''
					);
					if (!empty($attrValues['options_name'])) {
						if (MagnaDB::gi()->columnExistsInTable('products_options_id', TABLE_ORDERS_PRODUCTS_ATTRIBUTES)) {
							$prodOrderAttrData['products_options_id'] = $attrValues['options_id'];
							$prodOrderAttrData['products_options_values_id'] = $attrValues['options_values_id'];
						}
						
						$MagnaDB->insert(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $prodOrderAttrData);
					}
				}

			}
			// echo 'DELETE FROM '.TABLE_ORDERS_PRODUCTS.' WHERE orders_id=\''.$ordersId.'\';'."\n\n";
	
			/* MwSt Versandkosten */
			if (array_key_exists('Shipping', $order['orderTotal'])) {
				$shippingTax = getDBConfigValue($mp.'.mwst.shipping', $mpID, 0);
				if ($shippingTax > 0) {
					if (!isset($taxes[$shippingTax])) {
						$taxes[$shippingTax] = 0.0;
					}
					$taxes[$shippingTax] += $simplePrice->setPrice($order['orderTotal']['Shipping']['value'])->removeTax($shippingTax)->getPrice();
				}
			}
			
			$saveOrdersTotal = (!$orderUpdate || !MagnaDB::gi()->recordExists(TABLE_ORDERS_TOTAL, array (
				'orders_id' => $ordersId,
			)));
			foreach ($order['orderTotal'] as $key => &$entry) {
				$entry['orders_id'] = $ordersId;
				if (defined($entry['title'])) {
					$entry['title'] = constant($entry['title']);
				}
				$entry['text'] = $simplePrice->setPrice($entry['value'])->format();
				if ($saveOrdersTotal) {
					$MagnaDB->insert(TABLE_ORDERS_TOTAL, $entry);
				}
			}
			// echo 'DELETE FROM '.TABLE_ORDERS_TOTAL.' WHERE orders_id=\''.$ordersId.'\';'."\n\n";
			
			/* MwSt speichern */
			if (defined('MODULE_ORDER_TOTAL_TAX_STATUS') && (MODULE_ORDER_TOTAL_TAX_STATUS == 'true')) {
				$otc = 60;
				foreach ($taxes as $key => $sumprice) {
					$taxEntry = array(
						'orders_id' => $ordersId,
						'title' => ML_LABEL_INCL.' '.round($key, 2).'% '.MAGNA_LABEL_ORDERS_TAX,
						'value' => $simplePrice->setPrice($sumprice)->getTaxValue($key),
						'class' => 'ot_tax',
						'sort_order' => $otc
					);
					$taxEntry['text'] = $simplePrice->setPrice($taxEntry['value'])->format();
					++$otc;
	
					if ($saveOrdersTotal) {
						$MagnaDB->insert(TABLE_ORDERS_TOTAL, $taxEntry);
					}
				}
			}
			
			if (getDBConfigValue($mp.'.mail.send', $mpID, 'false') == 'true') {
				sendSaleConfirmationMail(
					$mpID,
					$order['customer']['customers_email_address'],
					array(
						'#FIRSTNAME#' => $order['customer']['customers_firstname'],
						'#LASTNAME#' => $order['customer']['customers_lastname'],
						'#EMAIL#' => $order['customer']['customers_email_address'],
						'#PASSWORD#'  => $customers_password,
						'#ORDERSUMMARY#' => $mailOrderSummary,
						'#MARKETPLACE#' => $_modules[$mp]['title'],
						'#SHOPURL#' => '', /** @deprecated amazon doen't like this */
					)
				);
			}
			unset($customers_password); # nicht dass es versehentlich an weitere Kunden geht
			$lastOrder = $order['order']['date_purchased'];
			if ($verbose) echo "\n### Done.\n\n";
			#break;
			
			$processedOrders[] = array (
				'MOrderID' => $order['orderInfo']['AmazonOrderID'],
				'ShopOrderID' => $ordersId,
			);			
			/* {Hook} "GetAmazonOrders_PostOrderImport": Is called after the amazon order in <code>$order</code> is imported.
				Usefull to manipulate some of the data in the database
				Variables that can be used:
				<ul><li>$order: The order that is going to be imported. The order is an 
				        associative array representing the structures of the order and customer related shop tables.</li>
				    <li>$mpID: The ID of the marketplace.</li>
				    <li>$ordersId: The Order ID of the shop (<code>orders_id</code>).</li>
				    <li>$customersId: The Customers ID of the shop (<code>customers_id</code>).</li>
				    <li>$MagnaDB: Instance of the magnalister database class. USE THIS for accessing the database during the
				        order import. DO NOT USE the shop functions for database access or MagnaDB::gi()!</li>
				</ul>
			*/
			if (($hp = magnaContribVerify('GetAmazonOrders_PostOrderImport', 1)) !== false) {
				require($hp);
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
			'SUBSYSTEM' => 'Amazon',
			'MARKETPLACEID' => $mpID,
			'DATA' => $processedOrders,
		);
		if (get_class($MagnaDB) != 'MagnaTestDB') {
			try {
				$res = MagnaConnector::gi()->submitRequest($request);
				$processedOrderIDs = array();
			} catch (MagnaException $e) {
				if (MAGNA_CALLBACK_MODE == 'STANDALONE') {
					echo print_m($e->getErrorArray(), 'Error: '.$e->getMessage(), true);
				}
				if ($e->getCode() == MagnaException::TIMEOUT) {
					$e->saveRequest();
					$e->setCriticalStatus(false);
				}
			}
		} else {
			if ($verbose) echo print_m($request);
			$processedOrderIDs = array();
		}
	
	}
}
