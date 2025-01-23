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
 * (c) 2010 - 2023 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleCronBase.php');

class MagnaCompatibleSyncOrderStatus extends MagnaCompatibleCronBase {
	/**
	 * Name of the confirmation field. May be DATA or CONFIRMATIONS.
	 * @var string
	 */
	protected $confirmationResponseField = 'DATA';

    /**
     * Name of the confirmation order id field. May be MOrderId or MOrderID.
     * @var string
     */
    protected $confirmationsOrderIdFieldName = 'MOrderId';

	/**
	 * List of all orders that have to be synced
	 * @var array
	 */
	protected $aOrders = array();

	/**
	 * The order that is currently processed
	 * @var array
	 */
	protected $oOrder = null;

	/**
	 * The index of the current order in the list of all orders.
	 * @var int
	 */
	protected $iOrderIndex = 0;
	
	/**
	 * A lookup table of the orders. Key is usually the MOrderID,
	 * value is the index of the order in the list of all orders.
	 * @var array
	 */
	protected $aMOrderID2Order = array();
	
	/**
	 * List of all orders where shipping will be confirmed.
	 * Will be the DATA element for the ConfirmShipment request.
	 * @var array
	 */
	protected $confirmations = array();
	
	/**
	 * List of all orders that will be cancelled.
	 * Will be the DATA element for the CancelShipment request.
	 * @var array
	 */
	protected $cancellations = array();
	
	/**
	 * List of all order ids that have a changed status that is not
	 * relevant for the marketplace.
	 * @var array
	 */
	protected $unprocessed = array();
	
	/**
	 * Size of request batch.
	 * @var int
	 */
	protected $sizeOfBatch = 0xffff;
	
	public function __construct($mpID, $marketplace) {
		parent::__construct($mpID, $marketplace);  
	}

	/*
	 * Display messages, if called in standalone mode
	 */
	protected function out($str) {
		if (    !defined('MAGNA_CALLBACK_MODE')
		     || MAGNA_CALLBACK_MODE != 'STANDALONE') {
			return;
		}
		echo $str;
		flush();
	}
	
	/**
	 * Specifies the settings and their default values for order status
	 * synchronisation. Assumes the order status synchronisation is 
	 * disabled.
	 * @return array
	 *   List of settings
	 */
	protected function getConfigKeys() {
		return array(
			'OrderStatusSync' => array (
				'key' => 'orderstatus.sync',
				'default' => 'no',
			),
			'StatusCancelled' => array (
				'key' => 'orderstatus.cancelled',
				'default' => false
			),
			'StatusShipped' => array (
				'key' => 'orderstatus.shipped',
				'default' => false,
			),
			'CarrierDefault' => array(
				'key' => 'orderstatus.carrier.default',
				'default' => false,
			),
			'CarrierMatchingTable' => array (
				'key' => 'orderstatus.carrier.dbmatching.table',
				'default' => false,
			),
			'CarrierMatchingAlias' => array (
				'key' => 'orderstatus.carrier.dbmatching.alias',
				'default' => false,
			),
			'TrackingCodeMatchingTable' => array (
				'key' => 'orderstatus.trackingcode.dbmatching.table',
				'default' => false,
			),
			'TrackingCodeMatchingAlias' => array (
				'key' => 'orderstatus.trackingcode.dbmatching.alias',
				'default' => false,
			),
		);
	}
	
	/**
	 * Builds the base API request for this marketplace.
	 * @return array
	 *   The base request
	 */
	protected function getBaseRequest() {
		return array (
			'SUBSYSTEM' => $this->marketplace,
			'MARKETPLACEID' => $this->mpID,
		);
	}
	
	/**
	 * Helper method to execute a db matching query.
	 * @return mixed
	 *   A string or false if the matching config is empty.
	 */
	protected function runDbMatching($tableSettings, $defaultAlias, $where) {
		if (!isset($tableSettings['Table']['table'])
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
	
	/**
	 * Fetches a tracking code if supported by the marketplace.
	 * @return string
	 *   The tracking code
	 */
	protected function getTrackingCode($orderId) {
        // if shop has column is_return_delivery (Gambio 4.5+) and matched to default tracking codes database table
        // skip usage of runDbMatching() - see below
        $mTrackingCode = false;
        if (   is_array($this->config['TrackingCodeMatchingTable'])
            && array_key_exists('table', $this->config['TrackingCodeMatchingTable'])
            && $this->config['TrackingCodeMatchingTable']['table'] == 'orders_parcel_tracking_codes'
            && $this->config['TrackingCodeMatchingTable']['column'] == 'tracking_code'
            && MagnaDB::gi()->columnExistsInTable('is_return_delivery','orders_parcel_tracking_codes')
        ) {
            $mTrackingCode = false;
        } else if (is_array($this->config['TrackingCodeMatchingTable'])){
            $mTrackingCode = $this->runDbMatching(array(
                'Table' => $this->config['TrackingCodeMatchingTable'],
                'Alias' => $this->config['TrackingCodeMatchingAlias']
            ), 'orders_id', $orderId);
        }

		// for Gambio 2.3 > if table "orders_parcel_tracking_codes" exists
		if (false == $mTrackingCode && MagnaDB::gi()->tableExists('orders_parcel_tracking_codes')) {
            $isReturnFlag = '';
            if (MagnaDB::gi()->columnExistsInTable('is_return_delivery','orders_parcel_tracking_codes')) {
                $isReturnFlag = ' AND is_return_delivery = 0';
            }

			$mTrackingCode = MagnaDB::gi()->fetchOne("
				SELECT tracking_code
				  FROM orders_parcel_tracking_codes
				 WHERE order_id = '".MagnaDB::gi()->escape($orderId)."' $isReturnFlag
				 LIMIT 1
			");
		}

        // for modified 2.0 > if table "orders_tracking" exists
        if (false == $mTrackingCode && MagnaDB::gi()->tableExists('orders_tracking')) {
            $mTrackingCode = MagnaDB::gi()->fetchOne("
                SELECT parcel_id
                  FROM orders_tracking
                 WHERE orders_id = '".MagnaDB::gi()->escape($orderId)."'
                 LIMIT 1
            ");
        }

		return $mTrackingCode;
	}
	
	/**
	 * Fetches a carrier if supported by the marketplace.
	 *   more priority on matching
	 * @return string
	 *   The carrier
	 */
	protected function getCarrier($orderId) {
		$mCarrier = $this->runDbMatching(array (
			'Table' => $this->config['CarrierMatchingTable'],
			'Alias' => $this->config['CarrierMatchingAlias']
		), 'orders_id', $orderId);

		// matching set to "don't use", but default carrier set
		if (    false == $mCarrier
		     && !empty($this->config['CarrierDefault'])
		     && (MagnaDB::gi()->fetchOne('SELECT `value`
			     FROM '.TABLE_MAGNA_CONFIG.'
			    WHERE mpID = '.$this->mpID.'
			      AND mkey = \''.$this->marketplace.'.orderstatus.carrier.dbmatching.table'.'\'') == '{"table":"","column":""}')) {
			$mCarrier = $this->config['CarrierDefault'];
		}

		// for Gambio 2.3 > if table "orders_parcel_tracking_codes" exists
		if (false == $mCarrier && MagnaDB::gi()->tableExists('orders_parcel_tracking_codes')) {
			$mCarrier = MagnaDB::gi()->fetchOne("
				SELECT parcel_service_name
				  FROM orders_parcel_tracking_codes
				 WHERE order_id = '".MagnaDB::gi()->escape($orderId)."'
				 LIMIT 1
			");
		}

        // for modified 2.0+ > if table "orders_tracking" exists
        if (false == $mCarrier && MagnaDB::gi()->tableExists('orders_tracking')) {
            $sCarrierId = MagnaDB::gi()->fetchOne("
                SELECT carrier_id
                  FROM orders_tracking
                 WHERE orders_id = '".MagnaDB::gi()->escape($orderId)."'
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
		// carrier should not be empty
		if (false == $mCarrier && !empty($this->config['CarrierDefault'])) {
			$mCarrier = $this->config['CarrierDefault'];
		}

		return $mCarrier;
	}
	
	/**
	 * Checks whether the status of the current order should be synchronized with
	 * the marketplace.
	 * @return bool
	 */
	protected function isProcessable() {
		return ($this->oOrder['orders_status_shop'] == $this->config['StatusShipped'])
			|| ($this->oOrder['orders_status_shop'] == $this->config['StatusCancelled']);
	}
	
	/**
	 * Builds an element for the ConfirmShipment request.
	 * @return array
	 */
	protected function confirmShipment($date) {
		$cfirm = array (
			'MOrderID' => $this->oOrder['special'],
			'ShippingDate' => localTimeToMagnaTime($date),
		);
		$this->oOrder['data']['ML_LABEL_SHIPPING_DATE'] = $cfirm['ShippingDate'];
		
		$trackercode = $this->getTrackingCode($this->oOrder['orders_id']);
		$carrier = $this->getCarrier($this->oOrder['orders_id']);
		if (false != $carrier) {
			$this->oOrder['data']['ML_LABEL_CARRIER'] = $cfirm['Carrier'] = $carrier;
		}
		if (false != $trackercode) {
			$this->oOrder['data']['ML_LABEL_TRACKINGCODE'] = $cfirm['TrackingCode'] = $trackercode;
		}
		
		// flag order as dirty, meaning that it has to be saved.
		$this->oOrder['__dirty'] = true;
		$this->out($this->marketplace.' ('.$this->mpID.') sent shipping confirmation request for order '.$this->oOrder['special'] .' ('.$this->oOrder['orders_id'].')'."\n");
		return $cfirm;
	}

	/**
	 * Builds an element for the CancelShipment request
	 * @return array
	 */
	protected function cancelOrder($date) {
		$cncl = array (
			'MOrderID' => $this->oOrder['special']
		);
		
		$this->oOrder['data']['ML_LABEL_ORDER_CANCELLED'] = $date;
		// flag order as dirty, meaning that it has to be saved.
		$this->oOrder['__dirty'] = true;
		$this->out($this->marketplace.' ('.$this->mpID.') sent cancel order request for order '.$this->oOrder['special'] .' ('.$this->oOrder['orders_id'].')'."\n");
		return $cncl;
	}
	
	/**
	 * Decodes the orders serialized attributes.
	 * @return void
	 */
	protected function decodeData() {
		try {
			$this->oOrder['data'] = @unserialize($this->oOrder['data']);
		} catch (Exception $ex) {}
		if (!is_array($this->oOrder['data'])) {
			$this->oOrder['data'] = array();
		}
		try {
			$this->oOrder['internaldata'] = @unserialize($this->oOrder['internaldata']);
		} catch (Exception $ex) {}
		if (!is_array($this->oOrder['internaldata'])) {
			$this->oOrder['internaldata'] = array();
		}
	}
	
	/**
	 * Tries to get the timestamp of the first status change for Shipped status, else last status change.
	 * Returns now if it can not be determined.
	 * @return string
	 *   A mysql datetime
	 */
	protected function getStatusChangeTimestamp() {
		if ($this->oOrder['orders_status_shop'] == $this->config['StatusShipped']) {
			$sSortOrder = 'ASC';
		} else {
			$sSortOrder = 'DESC';
		}
		$date = MagnaDB::gi()->fetchOne('
		    SELECT date_added
		      FROM `'.TABLE_ORDERS_STATUS_HISTORY.'`
		     WHERE orders_id='.$this->oOrder['orders_id'].'
		           AND orders_status_id = '.$this->oOrder['orders_status_shop'].'
		  ORDER BY date_added '.$sSortOrder.'
		     LIMIT 1
		');
		if ($date === false) {
			$date = date('Y-m-d H:i:s');
		}
		return $date;
	}
	
	/**
	 * Processes the current order.
	 * @return void
	 */
	protected function prepareSingleOrder($date) {
		if ($this->oOrder['orders_status_shop'] == $this->config['StatusShipped']) {
			$this->confirmations[] = $this->confirmShipment($date);
		} else if ($this->oOrder['orders_status_shop'] == $this->config['StatusCancelled']) {
			$this->cancellations[] = $this->cancelOrder($date);
		}
	}
	
	/**
	 * Adds the current order's index to a lookup table where the key is
	 * usually the MOrderID. May be different for extending subclasses.
	 * @return void
	 */
	protected function addToLookupTable() {
		$this->aMOrderID2Order[$this->oOrder['special']] = $this->iOrderIndex;
	}
	
	/**
	 * Gets an order item (reference) from the lookup table.
	 * @param string $mOrderId
	 *   The marketplace order id
	 * @return array|null
	 *   Reference to the order item or null if not found.
	 */
	protected function &getFromLookupTable($mOrderId) {
		if (isset($this->aMOrderID2Order[$mOrderId])
			&& isset($this->aOrders[$this->aMOrderID2Order[$mOrderId]])
		) {
			return $this->aOrders[$this->aMOrderID2Order[$mOrderId]];
		}

        // Only variables can be returned by reference
        $default = null;
		return $default;
	}
	
	/**
	 * Adds confirmation information to the order item
	 * @param array &$oOrder
	 *   The order item
	 * @param array $cData
	 *   The confirmation element specific to this order
	 * @return void
	 */
	protected function storeConfirmation(&$oOrder, $cData) {
		// Flag as dirty. Probably is already flagged as dirty.
		// Other marketplaces may do more, eg amazon, which stores the batch id.
		$oOrder['__dirty'] = true;
	}
	
	/**
	 * Processes the confirmations send from the API.
	 * Can be overwritten from subclasses if required.
	 * 
	 * @param array $result
	 *   The entire API result.
	 * @return void
	 */
	protected function processResponseConfirmations($result) {
		if (!isset($result[$this->confirmationResponseField][0])) {
			return;
		}
		foreach ($result[$this->confirmationResponseField] as $cData) {
			if (!isset($cData[$this->confirmationsOrderIdFieldName])) {
				continue;
			}
			$oOrder = &$this->getFromLookupTable($cData[$this->confirmationsOrderIdFieldName]);
			if ($oOrder !== null) {
				$this->out($this->marketplace.' ('.$this->mpID.') success response received for order '.$oOrder['special'] .' ('.$oOrder['orders_id'].')'."\n");
				$this->storeConfirmation($oOrder, $cData);
			}
		}
		/*
		    [DATA] => Array
		        (
		            [0] => Array
		                (
		                    [BatchID] => 5885100948
		                    [AmazonOrderID] => 303-9828726-5714706
		                    [RecordNumber] => 1
		                    [State] => CONFIRM
		                )
		
		        )
		*/
	}
	
	/**
	 * Adds an error to the error log. Empty method that can be extended by subclasses.
	 * 
	 * @param array $error
	 *   The entry for the error log.
	 * @return void
	 */
	protected function addToErrorLog($error) {
		
	}
	
	/**
	 * Processes the API errors. If the error element contains a
	 * DETAILS field with an MOrderID it can be added to an error log.
	 * 
	 * @param array $result
	 *   The entire API result.
	 * @return void
	 */
	protected function processResponseErrors($result) {
		if (!isset($result['ERRORS'][0])) {
			return;
		}
		foreach ($result['ERRORS'] as $eData) {
			if (!isset($eData['DETAILS']['MOrderID'])) {
				continue;
			}
			$this->addToErrorLog($eData);
			
			$oOrder = &$this->getFromLookupTable($eData['DETAILS']['MOrderID']);
			if ($oOrder !== null) {
				$this->out($this->marketplace.' ('.$this->mpID.') error response received for order '.$this->oOrder['special'] .' ('.$this->oOrder['orders_id'].')'."\n");
				$oOrder['__dirty'] = true;
			}
		}
	}
	
	/**
	 * Submits the status update for ConfirmShipment and CancelShipment.
	 * @param string $action
	 *   The API action: Either ConfirmShipment or CancelShipment.
	 * @param array $data
	 *   The data for the DATA element of the API request
	 * @return void
	 */
	protected function submitStatusUpdate($action, $data) {
		if (empty($data)) {
			return;
		}
		$request = $this->getBaseRequest();
		$request['ACTION'] = $action;
		$request['DATA'] = $data; 
		if ($this->_debugLevel >= self::DBGLV_MED) {
			$this->log(print_m(json_indent(json_encode($request)), $action.' Request')."\n\n");
		}
		
		if ($this->_debugDryRun) return;
		
		try {
			$result = MagnaConnector::gi()->submitRequest($request);
		} catch (MagnaException $e) {
			$result = array();
		}
		if ($this->_debugLevel >= self::DBGLV_MED) {
			$this->log(print_m($result, $action.' Response'));
		}
		$this->processResponseConfirmations($result);
		$this->processResponseErrors($result);

		$this->storeLogging('Request', $request);
		$this->storeLogging('Result', $result);
	}
	
	/**
	 * Sets magnalister_orders.orders_status to the one of the shop for all
	 * unprocessed orders.
     *
     * Hint: Set the order to the status in magnalister_orders when the order was loaded,
     *       because it could be that while processing the status has been changed
     *
	 * @return void
	 */
	protected function updateUnprocessed() {
		if (empty($this->unprocessed)) {
			return;
		}
		if ($this->_debugLevel >= self::DBGLV_MED) {
			$this->log(print_m($this->unprocessed, '$this->unprocessed'));
		}
		$this->out($this->marketplace.' ('.$this->mpID.') SyncOrderStatus: Unprocessed = '. implode('", "', array_keys($this->unprocessed))."\n");
		if ($this->_debugDryRun) {
			return;
		}

        $unprocessedOrdersWithStatus = array();

        /** @var int $orderId */
        /** @var string $orderStatus */
        foreach ($this->unprocessed as $orderId => $orderStatus) {
            // log the status of unprocessed orders
            $unprocessedOrdersWithStatus[] = array(
                'orders_id' => $orderId,
                'orders_status' => $orderStatus,
            );
            MagnaDB::gi()->update(TABLE_MAGNA_ORDERS, array(
                'orders_status' => $orderStatus
            ), array(
                'orders_id' => $orderId,
                'mpID' => $this->mpID,
            ));
        }

        $this->storeLogging('UnprocessedWithStatus', $unprocessedOrdersWithStatus);
	}
	
	/**
	 * Gets a list of all orders for this marketplace where the
	 * magnalister_orders.orders_status differs from the one of the shop.
	 * @return array
	 */
	protected function getOrdersToSync() {
		/*
		 * For Amazon and eBay, no more than 92 days backwards
		 * (they don't accept order updates older than 3 months)
		 */
		$sTimeConstraint = "";
		if (   ($this->marketplace == 'amazon')
		    || ($this->marketplace == 'ebay')) {
			$iMinOrderId = MagnaDB::gi()->fetchOne('SELECT min(orders_id)
				 FROM `'.TABLE_ORDERS.'`
				WHERE UNIX_TIMESTAMP(date_purchased) > (UNIX_TIMESTAMP() - 92 * 86400)');
			if ($iMinOrderId !== false) {
				$sTimeConstraint = "
		           AND o.orders_id >= $iMinOrderId";
			}
		}
		if ($this->_debugDryRun && ($this->_debugLevel >= self::DBGLV_HIGH)
			&& isset($_GET['Test']) && ($_GET['Test'] == 'ConfirmShipment')
		) {
			return MagnaDB::gi()->fetchArray(eecho('
			    SELECT mo.orders_id, 
			           "'.($this->config['StatusShipped'] + 1).'" AS orders_status,
			           mo.data, mo.internaldata, mo.special,
			           "'.$this->config['StatusShipped'].'" AS orders_status_shop
			      FROM `'.TABLE_MAGNA_ORDERS.'` mo,
			           `'.TABLE_ORDERS.'` o
			     WHERE mo.orders_id=o.orders_id
			           AND mo.mpID = "'.$this->mpID.'"
			  ORDER BY mo.orders_id DESC
			     LIMIT 5
			', $this->_debugLevel >= self::DBGLV_HIGH));
		}
		
		return MagnaDB::gi()->fetchArray(eecho('
		    SELECT mo.orders_id, mo.orders_status, mo.data,
		           mo.internaldata, mo.special,
		           o.orders_status AS orders_status_shop
		      FROM `'.TABLE_MAGNA_ORDERS.'` mo,
		           `'.TABLE_ORDERS.'` o
		     WHERE mo.orders_id=o.orders_id
		           AND mo.mpID = "'.$this->mpID.'"
		           AND mo.orders_status <> o.orders_status'.$sTimeConstraint.'
		  ORDER BY mo.orders_id DESC
		     LIMIT 500
		', $this->_debugLevel >= self::DBGLV_HIGH));
	}
	
	/**
	 * Saves all orders that are marked with the dirty flag.
	 * @return void
	 */
	protected function saveDirtyOrders() {
		foreach ($this->aOrders as $key => $oOrder) {
			if (!isset($oOrder['__dirty']) || ($oOrder['__dirty'] !== true)) {
				continue;
			}
			
			unset($oOrder['__dirty']);
			unset($this->aOrders[$key]['__dirty']);
			
			// Store the new order status. 
			$oOrder['orders_status'] = $oOrder['orders_status_shop'];
			unset($oOrder['orders_status_shop']);
			$oOrder['internaldata'] = serialize($oOrder['internaldata']);
			$oOrder['data'] = serialize($oOrder['data']);
			
			if ($this->_debugLevel >= self::DBGLV_HIGH) {
				echo print_m($oOrder, 'update');
			}
			$this->storeLogging('saveDirtyOrders', $oOrder);
			if ($this->_debugDryRun) {
				continue;
			}
			
			MagnaDB::gi()->update(TABLE_MAGNA_ORDERS, $oOrder, array (
				'orders_id' => $oOrder['orders_id']
			));
		}
	}
	
	/**
	 * Main method of the class that manages the order status update.
	 * @return bool
	 *   false if the orderstatus sync has been disabled, true otherwise.
	 */
	public function process() {
		#echo print_m($this->config, '$this->config');
		$this->storeLogging('Config', $this->config);
		
		if ($this->config['OrderStatusSync'] != 'auto') {
			return false;
		}
		$this->aOrders = $this->getOrdersToSync();

		$this->log(print_m($this->aOrders, "\n".'$this->aOrders'));
		
		if (empty($this->aOrders)) {
			$this->out($this->marketplace.' ('.$this->mpID.") SyncOrderStatus: No orders to sync.\n");
			return true;
		}
		
		#return true;
		$this->confirmations = array();
		$this->cancellations = array();
        /** array ('order id' => 'current order status id') */
		$this->unprocessed = array();
		
		foreach ($this->aOrders as $key => &$oOrder) {
			$this->oOrder = &$oOrder;
			$this->iOrderIndex = $key;
			
			if (!$this->isProcessable()) {
				$this->unprocessed[$oOrder['orders_id']] = $oOrder['orders_status_shop'];
				unset($this->aOrders[$key]);
				continue;
			}
			$this->decodeData();
			// add order to lookup table
			$this->addToLookupTable();
			$this->prepareSingleOrder($this->getStatusChangeTimestamp());
			
			$requestSend = false;
			if (count($this->confirmations) >= $this->sizeOfBatch) {
				$this->submitStatusUpdate('ConfirmShipment', $this->confirmations);
				$this->confirmations = array();
				$requestSend = true;
			}
			if (count($this->cancellations) >= $this->sizeOfBatch) {
				$this->submitStatusUpdate('CancelShipment', $this->cancellations);
				$this->cancellations = array();
				$requestSend = true;
			}
			if ($requestSend) {
				$this->saveDirtyOrders();
			}
		}
		//*
		$this->submitStatusUpdate('ConfirmShipment', $this->confirmations);
		$this->submitStatusUpdate('CancelShipment',  $this->cancellations);
		
		$this->saveDirtyOrders();

		$this->storeLogging('Unprocessed', $this->unprocessed);
		$this->updateUnprocessed();
		//*/
		return true;
	}
}
