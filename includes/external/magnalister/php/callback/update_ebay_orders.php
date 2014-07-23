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
 * $Id: update_ebay_orders.php 889 2011-04-03 23:46:11Z MaW $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */


defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

/* Zahlungsarten eBay:
-AmEx
CashInPerson
-CashOnPickup
-CCAccepted
-COD
CODPrePayDelivery # reserved for future use
CustomCode        # reserved for future use
-Diners           # CC 
-Discover         # CC
-ELV              # Lastschrift 
Escrow            # reserved for future use 
-IntegratedMerchantCreditCard # CC
LoanCheck
-MOCC             # Money order/cashiers check 
-Moneybookers
-MoneyXferAccepted# Direct transfer of money
-MoneyXferAcceptedInCheckout
None
Other
OtherOnlinePayments
PaisaPayAccepted  # India only
PaisaPayEscrow    # India only
PaisaPayEscrowEMI # India only
Paymate           # US only
PaymentSeeDescription
-PayOnPickup
-PayPal
-PersonalCheck
PostalTransfer    # reserved for future use
PrePayDelivery    # reserved for future use
ProPay            # US only
StandardPayment
-VisaMC
*/
function getPaymentClassForEbayPaymentMethod($paymentMethod) {
	$PaymentModules = explode(';', MODULE_PAYMENT_INSTALLED);
    $class = 'ebay';

    if (  ('MOCC' == $paymentMethod) || ('PersonalCheck' == $paymentMethod)
        ||('MoneyXferAccepted' == $paymentMethod)
        ||('MoneyXferAcceptedInCheckout' == $paymentMethod)) {
        # money order / Zahlungsanweisung / Vorkasse
        if (in_array('heidelpaypp.php', $PaymentModules))
            $class = 'heidelpaypp';
        else if (in_array('moneyorder.php', $PaymentModules))
            $class = 'moneyorder';
        else if (in_array('uos_vorkasse_modul.php', $PaymentModules))
            $class = 'uos_vorkasse_modul';
        
    } else if ('Moneybookers' == $paymentMethod) {
        # Moneybookers
        if (in_array('monebookers.php', $PaymentModules))
            $class = 'monebookers';

    } else if ('COD' == $paymentMethod) {
        # Nachnahme
        if (in_array('cod.php', $PaymentModules))
            $class = 'cod';
        
    } else if (  ('AmEx' == $paymentMethod) 
               ||('CCAccepted' == $paymentMethod)
               ||('Diners' == $paymentMethod)
               ||('Discover' == $paymentMethod)
               ||('IntegratedMerchantCreditCard' == $paymentMethod)
               ||('VisaMC' == $paymentMethod)) {
        # Kreditkarte
        if (in_array('cc.php', $PaymentModules))
            $class = 'cc';
        else if (in_array('heidelpaycc.php', $PaymentModules))
            $class = 'heidelpaycc';
        else if (in_array('moneybookers_cc.php', $PaymentModules))
            $class = 'moneybookers_cc';
        else if (in_array('uos_kreditkarte_modul.php', $PaymentModules))
            $class = 'uos_kreditkarte_modul';

    } else if ('ELV' == $paymentMethod) {
        # Lastschrift
        # if (in_array('banktransfer.php', $PaymentModules))
        #     $class = 'banktransfer';
        # if (in_array('heidelpaydd.php', $PaymentModules))
        #     $class = 'heidelpaydd';
        # if (in_array('ipaymentelv.php', $PaymentModules))
        #     $class = 'ipaymentelv';
        if (in_array('moneybookers_elv.php', $PaymentModules))
            $class = 'moneybookers_elv';
        else if (in_array('uos_lastschrift_de_modul.php', $PaymentModules))
            $class = 'uos_lastschrift_de_modul';

    } else if ('PayPal' == $paymentMethod) {
        # PayPal
        if (in_array('paypal.php', $PaymentModules))
            $class = 'paypal';
        else if (in_array('paypalng.php', $PaymentModules))
            $class = 'paypalng';
        
    } else if (stripos($paymentMethod, 'Rechnung') !== false) {
        # Auf Rechnung
        if (in_array('invoice.php', $PaymentModules))
            $class = 'invoice';

    } else if (  ('CashOnPickup' == $paymentMethod)
               ||('PayOnPickup'  == $paymentMethod)) {
        # Barzahlung
        if (in_array('cash.php', $PaymentModules))
            $class = 'cash';
    }

    return $class;
}


/* eBay Bestellungen updaten (Versandadresse und Zahlart) */ 
function magnaUpdateEbayOrders($mpID) {
	global $magnaConfig, $_magnaLanguage, $_modules;

	$mp = 'ebay';

	require_once(DIR_MAGNALISTER_MODULES.'ebay/ebayFunctions.php');
	require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
	require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/MagnaRecalcOrdersTotal.php');

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

	$simplePrice = new SimplePrice();
	
    # Bestelldaten abfragen.
    $break = false;
    $offset = array (
        'COUNT' => 200,
        'START' => 0,
    );

    $processedOrders = array();
    $lastOrder = '';
    
    $defaultPaymentMethod = 'ebay';
	$paymentMethod = getDBConfigValue($mp.'.orderimport.paymentmethod', $mpID, 'matching');
	if ($paymentMethod == 'textfield') {
		$paymentMethod = trim(getDBConfigValue($mp.'.orderimport.paymentmethod.name', $mpID, $defaultPaymentMethod));
	}
	if (empty($paymentMethod)) {
		$paymentMethod = $defaultPaymentMethod;
	}

    while (!$break) {
        @set_time_limit(60);
        # Startzeitpunkt wird vom Server bestimmt
        # Hole nur Versandadressen und Zahlungsarten fuer Bestellungen
        # die schon importiert sind und sich geaendert haben
        $request = array(
            'ACTION' => 'GetOrdersUpdates',
            'SUBSYSTEM' => 'eBay',
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

        $updateOrdersStatus = getDBConfigValue(array('ebay.update.orderstatus', 'val'), $mpID, true);

        if ($updateOrdersStatus) {
            $paidOrders = array();
            $unpaidOrders = array();
            foreach ($orders as &$row) {
                if (('Complete' == $row['order']['CheckoutStatus'])) {
                    $paidOrders[] = $row['order']['orders_id'];
                } else {
                    $unpaidOrders[] = $row['order']['orders_id'];
                }
                unset($row['order']['CheckoutStatus']);
            }
            array_unique($paidOrders);
            array_unique($unpaidOrders);
    
            # Wenn eine Teil-Bestellung unbezahlt ist,
            # darf die Gesamtbestellung nicht auf bezahlt gesetzt werden
            foreach ($paidOrders as $nr => $order) {
                if (in_array($order, $unpaidOrders)) {
                    unset($paidOrders[$nr]);
                }
            }
            unset($unpaidOrders);
            if ($verbose) echo print_m($paidOrders, '$paidOrders');
    
            $openStatus = getDBConfigValue('ebay.orderstatus.open', $mpID, false);
            $paidStatus = getDBConfigValue('ebay.orderstatus.paid', $mpID, false);
            $updateableStatusArray = getDBConfigValue('ebay.updateable.orderstatus', $mpID, array($openStatus));
            if (false === $paidStatus) {
                $paidStatus = (int)MagnaDB::gi()->fetchOne('
                	SELECT orders_status_id
                	  FROM '.TABLE_ORDERS_STATUS.'
                     WHERE orders_status_name IN (\'Bezahlt\',\'Payment received\')
                  ORDER BY language_id
                     LIMIT 1
                ');
            }
			$updateableStatusArray = array_diff($updateableStatusArray, array($paidStatus));
            if ($verbose) echo print_m($updateableStatusArray,'$updateableStatusArray');
        } else {
			# wenn kein Status-Update, sollten trotzdem Adressen upgedatet werden.
			# dazu miss CheckoutStatus weg, um DB-Fehler zu vermeiden
			foreach ($orders as &$row) {
				unset($row['order']['CheckoutStatus']);
			}
		}

	
        $processedOrderIDs = array();
        $changedDataKeys = array();
		foreach ($orders as $row) {
		# Bestelldaten durchgehen.
            $order = $row['order'];
			# eBay-OrderID == ItemID-TransactionID
			if ($verbose) echo "\n== Processing ".$order['orders_id'].". ==\n";
			/* {Hook} "UpdateeBayOrders_PreOrderUpdate": Is called before the eBay order in <code>$order</code> is updated.
				Variables that can be used:
				<ul><li>$order: The order that is going to be imported. The order is an 
				        associative array representing the structures of the order and customer related shop tables.</li>
				    <li>$mpID: The ID of the marketplace.</li>
				    <li>$MagnaDB: Instance of the magnalister database class. USE THIS for accessing the database during the
				        order import. DO NOT USE the shop functions for database access or MagnaDB::gi()!</li>
				</ul>
			*/
			if (($hp = magnaContribVerify('UpdateeBayOrders_PreOrderUpdate', 1)) !== false) {
				require($hp);
			}
            # einfach nur TABLE_ORDERS updaten. Vorher schauen
            # dass man keine Felder dabei hat die nicht drin sind.
            # Und die payment method zuordnen.
            if (!MagnaDB::gi()->recordExists(TABLE_ORDERS, array (
            	'orders_id' => $order['orders_id']
            ))) {
            	$processedOrderIDs[] = $order['orders_id'];
            	if ($verbose) echo $order['orders_id'].". not found\n";
            	continue;
            }
            
            if (isset($order['delivery_country_iso_code_2'])) {
                $shippingCountry = magnaGetCountryFromISOCode($order['delivery_country_iso_code_2']);
                $order['delivery_country'] = $shippingCountry['countries_name'];
            }
            if (!MagnaDB::gi()->columnExistsInTable('delivery_country_iso_code_2', TABLE_ORDERS)) {
                unset($order['delivery_country_iso_code_2']);
            }
            if (!MagnaDB::gi()->columnExistsInTable('delivery_firstname', TABLE_ORDERS)) {
                unset($order['delivery_firstname']);
                unset($order['delivery_lastname']);
            }
            if ($paymentMethod == 'matching') {
            	$order['payment_method'] = getPaymentClassForEbayPaymentMethod($order['PaymentMethod']);
            } else {
            	$order['payment_method'] = $paymentMethod;
            }
            if (MagnaDB::gi()->columnExistsInTable('payment_class', TABLE_ORDERS)) {
                $order['payment_class'] = $order['payment_method'];
            }
            unset ($order['PaymentMethod']);
            if ($updateOrdersStatus && in_array($order['orders_id'], $paidOrders)) {
                # Status aendern aktiv, Bestellung bei eBay bezahlt
                # und hat im Shop einen Status der geaendert werden darf 
                if (in_array(
	                MagnaDB::gi()->fetchOne('
	                	SELECT orders_status
	                	  FROM '.TABLE_ORDERS.'
	                	 WHERE orders_id = '.$order['orders_id']
	                ),
	                $updateableStatusArray
                )) {
                	$order['orders_status'] = $paidStatus;
                    $MagnaDB->insert(TABLE_ORDERS_STATUS_HISTORY, array (
                        'orders_id' => $order['orders_id'],
                        'orders_status_id' => $paidStatus,
                        'date_added' => date('Y-m-d H:i:s'),
                        'customer_notified' => '0',
                        'comments' => ML_EBAY_ORDER_PAID
                    ));
                }
            }
            $currentOrderID = $order['orders_id'];
            unset($order['orders_id']);
            # keine leeren Werte, damit man nichts plattmacht
            foreach ($order as $key=>$val) {
                if (empty($val)) unset($order[$key]);
            }

			# ShippingService
			if (array_key_exists('OrderTotal', $order)) {
				if (array_key_exists('Shipping', $order['OrderTotal']))
					$orderTotalShipping = $order['OrderTotal']['Shipping'];
				unset($order['OrderTotal']);
				# Fallunterscheidung:
				# - Einzel-Bestellung: OrderTotal muss neu berechnet werden, wenn unterschiedlich
				# - Mehrfach-Bestellung:
				#	- Wenn neue Kosten groesser, berechne neu,
				#	- Sonst nicht (genauer waere: schaue ob neue Kosten die hoechsten ersetzen,
				# 					wir haben aber die Daten hier nicht)
				$isSingleOrder = (1 == MagnaDB::gi()->fetchOne('
					SELECT COUNT(*) FROM '.TABLE_ORDERS_PRODUCTS.'
            	 	WHERE orders_id = '.$currentOrderID.'
					'));
				if ($isSingleOrder) {
					if ($verbose) echo "\nisSingleOrder($currentOrderID) == true\n";
					$mfot = new MagnaRecalcOrdersTotal();
					$ordersTotal = $mfot->recalcExistingOrder($currentOrderID, $orderTotalShipping['value'], (get_class($MagnaDB) != 'MagnaTestDB'));
				} else {
					if ($verbose) echo "\nisSingleOrder($currentOrderID) == false\n";
					$oldShippingCost = (float)MagnaDB::gi()->fetchOne('
						SELECT value FROM '.TABLE_ORDERS_TOTAL.'
						WHERE orders_id = '.$currentOrderID.'
						 AND class = \'ot_shipping\'
						ORDER BY value DESC
						LIMIT 1');
					if ($orderTotalShipping['value'] > $oldShippingCost) {
					if ($verbose) echo "\n$currentOrderID: newShippingCost ==".$orderTotalShipping['value']." > oldShippingCost == $oldShippingCost\n";
						$mfot = new MagnaRecalcOrdersTotal();
						$ordersTotal = $mfot->recalcExistingOrder($currentOrderID, $orderTotalShipping['value'], (get_class($MagnaDB) != 'MagnaTestDB'));
					}
				}
				if (isset($ordersTotal)) unset($ordersTotal);
			}

			
            ## Werte aus der Tabelle holen fuer die Info-mail was sich geaendert hat
			## Mail noch zu bauen
			$order = array_filter_keys($order, MagnaDB::gi()->getTableColumns(TABLE_ORDERS));
            $keys = implode(', ',array_keys($order));
            $oldValues = MagnaDB::gi()->fetchRow(eecho('
            	SELECT '.$keys.' FROM '.TABLE_ORDERS.' 
            	 WHERE orders_id = '.$currentOrderID.'
           	', $verbose));
           	if ($verbose) echo print_m($oldValues, '$oldValues');
            $updatedValues = array_diff_assoc($order, $oldValues);
            $MagnaDB->update(TABLE_ORDERS, $order, array('orders_id' => $currentOrderID));
            $processedOrderIDs[] = $currentOrderID;

			/* {Hook} "UpdateeBayOrders_PostOrderUpdate": Is called after the eBay order in <code>$order</code> is updated.
				Variables that can be used: Same as for UpdateeBayOrders_PreOrderUpdate.
			*/
			if (($hp = magnaContribVerify('UpdateeBayOrders_PostOrderUpdate', 1)) !== false) {
				require($hp);
			}

            unset($currentOrderID);
        }
	
        # acknowledge the update to server
        $request = array(
            'ACTION' => 'AcknowledgeUpdatedOrders',
            'SUBSYSTEM' => 'eBay',
            'MARKETPLACEID' => $mpID,
            'DATA' => $processedOrderIDs,
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
	# delete surplus orders_total lines
    delete_double_ot_lines();
}
