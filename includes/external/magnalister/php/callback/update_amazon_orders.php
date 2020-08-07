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
 * $Id: update_amazon_orders.php 889 2019-07-31 23:46:11Z MaW $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');


/* Amazon Bestellungen updaten (Adressdaten) */ 
function magnaUpdateAmazonOrders($mpID) {
	global $magnaConfig, $_magnaLanguage, $_modules;

	$mp = 'amazon';

	require_once(DIR_MAGNALISTER_MODULES.'amazon/amazonFunctions.php');

	/*
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
	
    # Bestelldaten abfragen.
    $break = false;
    $offset = array (
        'COUNT' => 200,
        'START' => 0,
    );

    $processedOrders = array();
    $lastOrder = '';

    while (!$break) {
        @set_time_limit(60);
        # Startzeitpunkt wird vom Server bestimmt
        # Hole nur Adressen für Bestellungen
        # die schon importiert sind
        $request = array(
            'ACTION' => 'GetOrdersUpdates',
            'SUBSYSTEM' => 'Amazon',
            'MARKETPLACEID' => $mpID,
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
	
	$processedOrders = array();
	$changedDataKeys = array();
	foreach ($orders as $nr => &$row) {
	# Bestelldaten durchgehen.
	$customer  = $row['customer'];
	$adress    = $row['adress'];
	$order     = $row['order'];
	$orderInfo = $row['orderInfo'];
	# Amazon-OrderID
	echo "\n== Processing ".$orderInfo['AmazonOrderID'].". ($nr) ==\n";
            # TABLE_ORDERS, TABLE_CUSTOMERS, TABLE_ADDERSS_BOOK updaten. Nur adressen.
            # Vorher schauen dass man keine Felder dabei hat die nicht drin sind.
            if (!MagnaDB::gi()->recordExists(TABLE_MAGNA_ORDERS, array (
	            	'special' => $orderInfo['AmazonOrderID']
	            ))) {
	            	$processedOrders[] = array('MOrderID' => $orderInfo['AmazonOrderID']);
	            	echo $orderInfo['AmazonOrderID'].". not found\n";
					unset($customer);
					unset($adress);
					unset($order);
					unset($orderInfo);
					unset($orders[$nr]);
	            	                continue;
            }
       # schau ob schon vervollständigt
       $row['order']['orders_id'] = MagnaDB::gi()->fetchOne('SELECT orders_id
         FROM '.TABLE_MAGNA_ORDERS.' WHERE platform=\'amazon\' AND special = \''.$orderInfo['AmazonOrderID'].'\'');
       $aOrderInDB = MagnaDB::gi()->fetchRow('SELECT *
         FROM '.TABLE_ORDERS.' WHERE orders_id = '.$row['order']['orders_id']);
       if (array_key_exists('delivery_firstname', $aOrderInDB)) {
           if (    (!empty($aOrderInDB['customers_name']))
                && (!empty($aOrderInDB['customers_firstname']))
                && (!empty($aOrderInDB['customers_lastname']))
                && (!empty($aOrderInDB['customers_street_address']))
                && (!empty($aOrderInDB['delivery_name']))
                && (!empty($aOrderInDB['delivery_firstname']))
                && (!empty($aOrderInDB['delivery_lastname']))
                && (!empty($aOrderInDB['delivery_street_address']))
                && (!empty($aOrderInDB['billing_name']))
                && (!empty($aOrderInDB['billing_firstname']))
                && (!empty($aOrderInDB['billing_lastname']))
                && (!empty($aOrderInDB['billing_street_address']))) {
	       echo $orderInfo['AmazonOrderID']." already complete.\n";
               $processedOrders[] = array('MOrderID' => $orderInfo['AmazonOrderID'],
                                          'ShopOrderID' => $row['order']['orders_id']);
               unset($customer);
               unset($adress);
               unset($order);
               unset($orderInfo);
               unset($aOrderInDB);
               unset($orders[$nr]);
               continue;
           }
       } else {
           if (    (!empty($aOrderInDB['customers_name']))
                && (!empty($aOrderInDB['customers_street_address']))
                && (!empty($aOrderInDB['delivery_name']))
                && (!empty($aOrderInDB['delivery_street_address']))
                && (!empty($aOrderInDB['billing_name']))
                && (!empty($aOrderInDB['billing_street_address']))) {
	       echo $orderInfo['AmazonOrderID']." already complete.\n";
               $processedOrders[] = array('MOrderID' => $orderInfo['AmazonOrderID'],
                                          'ShopOrderID' => $row['order']['orders_id']);
               unset($customer);
               unset($adress);
               unset($order);
               unset($orderInfo);
               unset($aOrderInDB);
               unset($orders[$nr]);
               continue;
           }
       }
       # vervollständige
       if (isset($order['billing_country_iso_code_2'])) {
           $shippingCountry = magnaGetCountryFromISOCode($order['billing_country_iso_code_2']);
           $order['billing_country'] = $billingCountry['countries_name'];
        }
       if (isset($order['delivery_country_iso_code_2'])) {
           $shippingCountry = magnaGetCountryFromISOCode($order['delivery_country_iso_code_2']);
           $order['delivery_country'] = $shippingCountry['countries_name'];
        }
        $order = array_filter_keys($order, MagnaDB::gi()->getTableColumns(TABLE_ORDERS)); 
        # leere Felder weglassen
        foreach ($order as $sOrderKey => $sOrderField) {
            if (empty($sOrderField)) unset($order[$sOrderKey]);
        }
        $MagnaDB->update(TABLE_ORDERS, $order, array('orders_id' => $row['order']['orders_id']));
        # customer
        $customer = array_filter_keys($customer, MagnaDB::gi()->getTableColumns(TABLE_CUSTOMERS));
        if (MagnaDB::gi()->recordExists(TABLE_CUSTOMERS, array ('customers_id' => $aOrderInDB['customers_id']))) {
            # leere Felder weglassen
            foreach ($customer as $sCustomerKey => $sCustomerField) {
                if (empty($sCustomerField)) unset($customer[$sCustomerKey]);
            }
            $MagnaDB->update(TABLE_CUSTOMERS, $customer, array('customers_id' => $aOrderInDB['customers_id']));
        } else {
            $MagnaDB->insert(TABLE_CUSTOMERS, $customer);
            $aOrderInDB['customers_id'] = $MagnaDB->getLastInsertID();
            $MagnaDB->update(TABLE_ORDERS, array ('customers_id' => $aOrderInDB['customers_id']), array('orders_id' => $row['order']['orders_id']));
        }
        #address_book
        $adress = array_filter_keys($adress, MagnaDB::gi()->getTableColumns(TABLE_ADDRESS_BOOK));
        if (MagnaDB::gi()->recordExists(TABLE_ADDRESS_BOOK, array ('customers_id' => $aOrderInDB['customers_id']))) {
            # leere Felder weglassen
            foreach ($adress as $sAddressKey => $sAddressField) {
                if (empty($sAddressField)) unset($adress[$sAddressKey]);
            }
            $MagnaDB->update(TABLE_ADDRESS_BOOK, $adress, array('customers_id' => $aOrderInDB['customers_id']));
        } else {
            $MagnaDB->insert(TABLE_ADDRESS_BOOK, $adress);
            $customers_default_address_id = $MagnaDB->getLastInsertID();
            $MagnaDB->update(TABLE_CUSTOMERS, array ('customers_default_address_id' => $customers_default_address_id), array ('customers_id' => $aOrderInDB['customers_id']));
        }
       $processedOrders[] = array('MOrderID' => $orderInfo['AmazonOrderID'],
                                  'ShopOrderID' => $row['order']['orders_id'],
                                  'Updated' => '1');
        unset($customer);
        unset($adress);
        unset($order);
        unset($orderInfo);
        unset($orders[$nr]);
    } #foreach ($orders as $nr => &$row)

    # acknowledge the update to server
    $request = array(
        'ACTION' => 'AcknowledgeUpdatedOrders',
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
	} else  {
		if ($verbose) echo print_m($request);
		$processedOrders = array();
	}
    } # while(!$break)
}
