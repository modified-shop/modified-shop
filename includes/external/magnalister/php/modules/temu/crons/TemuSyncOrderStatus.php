<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleSyncOrderStatus.php');
require_once(DIR_MAGNALISTER_MODULES.'temu/TemuHelper.php');

class TemuSyncOrderStatus extends MagnaCompatibleSyncOrderStatus {

	protected $confirmationResponseField = 'DATA';
	protected $confirmationsOrderIdFieldName = 'MOrderID';

	/** Sentinel: the Buy-Shipping label status could not be fetched from the API this run. */
	const LABEL_STATUS_FETCH_FAILED = '__fetch_failed__';

	/** Config key holding the order ids deferred by earlier runs of the current sweep. */
	const DEFERRED_CONFIG_KEY = 'temu.orderstatus.deferredorders';

	/**
	 * Upper bound for that list. Reaching it restarts the sweep instead of growing the config
	 * row without limit — a shop whose whole backlog is undeliverable then simply retries from
	 * the newest order again.
	 */
	const DEFERRED_MAX = 5000;

	/**
	 * Set by process() when the order-independent fulfillment config is incomplete. The run is NOT
	 * aborted — cancellations need none of that config and must keep flowing; only shipped orders
	 * are deferred (kept out-of-sync) in prepareSingleOrder() until the merchant completes it.
	 */
	protected $blFulfillmentConfigIncomplete = false;

	/** Order ids carried over from earlier runs; excluded from this run's selection. */
	protected $aDeferredCarried = array();

	/** Order ids deferred by THIS run; merged into the carried list when the run ends. */
	protected $aDeferredThisRun = array();

	public function __construct($mpID, $marketplace) {
		parent::__construct($mpID, $marketplace);
	}

	/**
	 * Extend the base config with the Temu-specific keys. It is essential to keep the base keys
	 * (OrderStatusSync, StatusShipped, StatusCancelled) so that process() runs and
	 * isProcessable()/prepareSingleOrder()/getStatusChangeTimestamp() (which read StatusShipped /
	 * StatusCancelled) work. Replacing the array instead of extending it disabled the whole sync
	 * (OrderStatusSync was absent → process() returned early).
	 */
	protected function getConfigKeys() {
		$parent = parent::getConfigKeys();
		$parent['CarrierSelect'] = array('key' => 'orderstatus.sendcarrier.select', 'default' => '');
		$parent['UseBuyShipping'] = array('key' => 'orderstatus.temu.usebuyshipping', 'default' => 'false');
		$parent['WarehouseId'] = array('key' => 'orderstatus.temu.warehouseid', 'default' => '');
		// FEAT-026: per-order "Carrier Shop-Matching" — maps the order's shop shipping method
		// (`orders.shipping_method`) to a Temu carrier, mirroring Otto's `shipmodulematch`
		// (OttoSyncOrderStatus::getCarrierValue). The shop side stores the value produced by
		// mlGetShippingModules() (a shop carrier_id or a shipping-module code) and the merchant maps
		// it to a Temu CarrierId. DB-Matching + auto-fallback still come from base getCarrier(); only
		// these matching arrays are Temu additions. `.shop` = shop shipping value, `.temu` = Temu
		// CarrierId (parallel arrays). Portable across shops (uses `orders.shipping_method`, not the
		// Gambio-only parcel-tracking table).
		$parent['SendCarrierMatchingMarketplace'] = array('key' => 'orderstatus.sendcarrier.tosShopMatching.temu', 'default' => '');
		$parent['SendCarrierMatchingShop']        = array('key' => 'orderstatus.sendcarrier.tosShopMatching.shop', 'default' => '');
		return $parent;
	}

	/**
	 * Build the Temu shipping-confirmation payload for the current order. The base
	 * prepareSingleOrder() routes shipped orders here (and cancelled ones to cancelOrder()) and
	 * pushes the return value into $this->confirmations. $date is the status-change timestamp
	 * (a datetime string) — the order data comes from $this->oOrder.
	 */
	/**
	 * FEAT-026: resolve the Temu carrier for ONE order. Mirrors Otto's mode-selector
	 * (OttoSyncOrderStatus::getCarrierValue): the "Carrier" field (`sendcarrier.select`) picks exactly
	 * ONE mechanism — no cascade. Its value is:
	 *   - `shopmatch` — map the order's shop shipping method (`orders.shipping_method`) to a Temu
	 *     CarrierId via the Carrier Shop-Matching rows (`sendcarrier.tosShopMatching.shop`/`.temu`).
	 *   - `dbmatch`   — base getCarrier(): DB-Matching (`orderstatus.carrier.dbmatching`, the shared
	 *     "Spediteur Matching" field) + auto `orders_parcel_tracking_codes.parcel_service_name`.
	 *     Returns a carrier NAME → reverse-map to a Temu CarrierId via getCarriers().
	 *   - `` (empty) — nothing chosen; no carrier.
	 *   - otherwise   — a fixed Temu CarrierId picked from the dropdown.
	 *
	 * @param int $orderId shop orders_id
	 * @return array array('CarrierId' => string, 'Carrier' => string) — either may be empty.
	 */
	protected function resolveCarrier($orderId) {
		$aCarriers = TemuHelper::getCarriers($this->mpID); // CarrierId => CarrierName (cached 30 min)
		$sSel = $this->config['CarrierSelect'];

		switch ($sSel) {
			case '':
				return array('CarrierId' => '', 'Carrier' => '');

			case 'shopmatch':
				// Map the order's shop shipping method (`orders.shipping_method`) to a Temu CarrierId,
				// exactly like Otto's `shipmodulematch`. `.shop` = the shop value from mlGetShippingModules()
				// (carrier_id or shipping-module code), `.temu` = Temu CarrierId (parallel arrays).
				$aShop = $this->config['SendCarrierMatchingShop'];
				$aMp   = $this->config['SendCarrierMatchingMarketplace'];
				if (is_array($aShop) && is_array($aMp) && !empty($aShop)) {
					$sShipMethod = MagnaDB::gi()->fetchOne("
						SELECT shipping_method FROM ".TABLE_ORDERS."
						 WHERE orders_id = '".MagnaDB::gi()->escape($orderId)."'");
					if ($sShipMethod !== false && (string)$sShipMethod !== '') {
						foreach ($aShop as $k => $sVal) {
							if ($sVal !== '' && (string)$sVal === (string)$sShipMethod && !empty($aMp[$k])) {
								$sId = $aMp[$k];
								return array(
									'CarrierId' => $sId,
									'Carrier'   => isset($aCarriers[$sId]) ? $aCarriers[$sId] : '',
								);
							}
						}
					}
				}
				return array('CarrierId' => '', 'Carrier' => '');

			case 'dbmatch':
				// base getCarrier(): DB-Matching + auto (orders_parcel_tracking_codes.parcel_service_name).
				$mCarrier = $this->getCarrier($orderId);
				if (!empty($mCarrier)) {
					if (isset($aCarriers[$mCarrier])) {          // already a Temu CarrierId
						return array('CarrierId' => $mCarrier, 'Carrier' => $aCarriers[$mCarrier]);
					}
					$sId = array_search($mCarrier, $aCarriers, true); // reverse lookup: name -> CarrierId
					return array(
						'CarrierId' => ($sId !== false) ? $sId : '',
						'Carrier'   => $mCarrier,                     // send the name; API resolves via findCarrierByName
					);
				}
				return array('CarrierId' => '', 'Carrier' => '');

			default:
				// a fixed Temu CarrierId picked from the dropdown.
				return array(
					'CarrierId' => $sSel,
					'Carrier'   => isset($aCarriers[$sSel]) ? $aCarriers[$sSel] : '',
				);
		}
	}

	/**
	 * Config pre-check (parity with v3 SyncOrderStatus::execute): a ConfirmShipment without the
	 * fulfillment config the API needs fails on the API side without any merchant-visible
	 * feedback. The configuration is validated ONCE, before any out-of-sync order is even looked
	 * up: an incomplete config writes a merchant-visible Error Log entry (one record per day)
	 * and defers only the shipped orders, which stay out-of-sync so they are picked up
	 * automatically once the merchant completes the configuration.
	 */
	public function process() {
		// Only enforce the fulfillment config when shipping confirmations can actually be
		// produced: without a configured "shipped" status the merchant only syncs
		// cancellations, which need none of this config - the run must not be blocked.
		if ($this->config['OrderStatusSync'] == 'auto' && !empty($this->config['StatusShipped'])) {
			$aProblems = $this->getFulfillmentConfigProblems();
			if (!empty($aProblems)) {
				foreach ($aProblems as $sMessage) {
					$this->addConfigErrorOncePerDay($sMessage, array('Marketplace' => 'Temu'));
					$this->out($this->marketplace.' ('.$this->mpID.') '.$sMessage."\n");
				}
				// Do NOT abort the whole run: cancellations need none of the fulfillment config.
				// Flag it so only shipped orders are deferred per-order below; cancellations run.
				$this->blFulfillmentConfigIncomplete = true;
			}
		}
		$mResult = parent::process();
		$this->persistDeferredOrderIds();

		return $mResult;
	}

	/**
	 * Selects the orders to sync, skipping the ones this sweep already deferred.
	 *
	 * The base implementation always takes the newest 500 mismatched orders. Deferred orders stay
	 * mismatched, so 500 undeliverable ones would occupy that window on every run and older
	 * shipments and cancellations would never be reached. Excluding them lets the window advance
	 * through the backlog; once nothing else is left the exclusion is dropped, which both retries
	 * the deferred orders and starts the next sweep.
	 *
	 * An order is only excluded while it still sits on the shipped status, so a cancellation
	 * entered after the deferral is picked up on the very next run.
	 *
	 * @return array
	 */
	protected function getOrdersToSync() {
		$this->aDeferredCarried = $this->loadDeferredOrderIds();

		if (empty($this->aDeferredCarried) || empty($this->config['StatusShipped']) || $this->_debugDryRun) {
			return parent::getOrdersToSync();
		}

		$aOrders = $this->fetchOrdersToSyncExcludingDeferred($this->aDeferredCarried);
		if (!empty($aOrders)) {
			return $aOrders;
		}

		// Nothing left that was not already deferred: the sweep is complete. Drop the exclusion
		// so the deferred orders are retried now instead of being skipped forever.
		$this->aDeferredCarried = array();
		$this->storeDeferredOrderIds(array());

		return parent::getOrdersToSync();
	}

	/**
	 * The base selection plus an exclusion for the deferred orders. Kept marketplace-local: the
	 * base query also carries the Amazon/eBay 92-day constraint and a debug branch, neither of
	 * which applies to Temu.
	 *
	 * @param array $aOrderIds Order ids to skip while they remain on the shipped status
	 * @return array
	 */
	protected function fetchOrdersToSyncExcludingDeferred($aOrderIds) {
		$aEscaped = array();
		foreach ($aOrderIds as $mOrderId) {
			$aEscaped[] = (int)$mOrderId;
		}

		return MagnaDB::gi()->fetchArray("
		    SELECT mo.orders_id, mo.orders_status, mo.data,
		           mo.internaldata, mo.special,
		           o.orders_status AS orders_status_shop
		      FROM `".TABLE_MAGNA_ORDERS."` mo,
		           `".TABLE_ORDERS."` o
		     WHERE mo.orders_id = o.orders_id
		           AND mo.mpID = '".MagnaDB::gi()->escape($this->mpID)."'
		           AND mo.orders_status <> o.orders_status
		           AND NOT (    mo.orders_id IN (".implode(',', $aEscaped).")
		                    AND o.orders_status = '".MagnaDB::gi()->escape($this->config['StatusShipped'])."')
		  ORDER BY mo.orders_id DESC
		     LIMIT 500
		");
	}

	/**
	 * Records one order as deferred by this run, so the next run can look past it.
	 *
	 * @param int $orderId shop orders_id
	 * @return void
	 */
	protected function markOrderDeferred($orderId) {
		$this->aDeferredThisRun[(int)$orderId] = (int)$orderId;
	}

	/**
	 * @return array Order ids deferred by earlier runs of the current sweep
	 */
	protected function loadDeferredOrderIds() {
		$sStored = getDBConfigValue(self::DEFERRED_CONFIG_KEY, $this->mpID, '');
		if (!is_string($sStored) || $sStored === '') {
			return array();
		}
		$aIds = array();
		foreach (explode(',', $sStored) as $sId) {
			$sId = trim($sId);
			if ($sId !== '' && ctype_digit($sId)) {
				$aIds[(int)$sId] = (int)$sId;
			}
		}

		return $aIds;
	}

	/**
	 * @param array $aOrderIds Order ids to carry into the next run
	 * @return void
	 */
	protected function storeDeferredOrderIds($aOrderIds) {
		setDBConfigValue(self::DEFERRED_CONFIG_KEY, $this->mpID, implode(',', $aOrderIds), true);
	}

	/**
	 * Merges the orders deferred by this run into the carried list. Passing DEFERRED_MAX restarts
	 * the sweep rather than letting the stored list grow without bound.
	 *
	 * @return void
	 */
	protected function persistDeferredOrderIds() {
		if (empty($this->aDeferredThisRun) && empty($this->aDeferredCarried)) {
			return;
		}

		$aIds = $this->aDeferredCarried + $this->aDeferredThisRun;
		if (count($aIds) > self::DEFERRED_MAX) {
			$this->out($this->marketplace.' ('.$this->mpID.') deferred-order list exceeded '.self::DEFERRED_MAX.' entries — restarting the retry sweep.'."\n");
			$aIds = array();
		}

		$this->storeDeferredOrderIds($aIds);
	}

	/**
	 * Returns the list of messages for every order-independent fulfillment config requirement
	 * that is not met. Empty array = sync may run.
	 *
	 * Always: carrier selection + warehouse (Buy Shipping still routes self-fulfilled orders
	 * per Temu's per-order assignment, so a carrier is mandatory in every mode).
	 * Buy-Shipping additionally: logistics type.
	 */
	protected function getFulfillmentConfigProblems() {
		$aProblems = array();
		$blBuyShipping = ($this->config['UseBuyShipping'] === 'true');

		if (empty($this->config['CarrierSelect'])) {
			$aProblems[] = 'Temu order status sync was not started: no shipping carrier is configured. Please select a carrier in the Temu order configuration (self-fulfillment section).';
		}

		if (empty($this->config['WarehouseId'])) {
			$aProblems[] = 'Temu order status sync was not started: no warehouse is configured. Please select a warehouse in the Temu order configuration.';
		}

		if ($blBuyShipping && ('' == getDBConfigValue('temu.orderstatus.temu.buyshipping.logisticstype', $this->mpID, ''))) {
			$aProblems[] = 'Temu order status sync was not started: no Buy-Shipping logistics type is configured. Please select a logistics type in the Temu order configuration.';
		}

		return $aProblems;
	}

	/**
	 * Per-order guard for requirements the global pre-check cannot cover because they depend on
	 * the single order (self-fulfilled only): a tracking code must exist, and with the carrier
	 * shop-/DB-matching modes the carrier must resolve for THIS order. Such orders are skipped
	 * (kept out-of-sync for automatic retry) with a merchant-visible Error Log entry.
	 */
	protected function prepareSingleOrder($date) {
		if ($this->oOrder['orders_status_shop'] == $this->config['StatusShipped']) {
			$sOrderId = $this->oOrder['special'];

			// Order-independent fulfillment config incomplete (flagged in process()): a shipped
			// order cannot be confirmed → defer (kept out-of-sync for retry). No per-order Error
			// Log here — process() already wrote the merchant-visible daily config message.
			if ($this->blFulfillmentConfigIncomplete) {
				$this->out($this->marketplace.' ('.$this->mpID.') shipping confirmation deferred for order '.$sOrderId.': fulfillment configuration incomplete.'."\n");
				$this->markOrderDeferred($this->oOrder['orders_id']);
				return;
			}

			// trim() so a whitespace-only tracking ("   ") counts as missing — Temu rejects it.
			$sTracking = trim((string)$this->getTrackingCode($this->oOrder['orders_id']));
			$blBuyShipping = ($this->config['UseBuyShipping'] === 'true');
			$aProblems = array();

			if ($sTracking !== '') {
				// A tracking code exists → the order can be confirmed. Self-fulfillment additionally
				// needs a carrier that resolves for THIS order.
				if (!$blBuyShipping) {
					$aCarrier = $this->resolveCarrier($this->oOrder['orders_id']);
					if ($aCarrier['CarrierId'] === '' && $aCarrier['Carrier'] === '') {
						$aProblems[] = 'Shipping confirmation for order '.$sOrderId.' was not sent to Temu: no shipping carrier is configured or resolvable. Please select a carrier in the Temu order configuration (self-fulfillment section).';
					}
				}
			} elseif ($blBuyShipping) {
				// Buy Shipping, no tracking: the shipment rides on a purchased label. Confirm only
				// once THIS order's label is ready (CACHED), verified against Temu via
				// GetShipmentList — a CREATING/PENDING/ERROR/missing label is not ready.
				$sLabelStatus = $this->fetchBuyShippingLabelStatus($sOrderId);
				if ($sLabelStatus === self::LABEL_STATUS_FETCH_FAILED) {
					// Cannot verify this run — keep out-of-sync and retry later, no error entry.
					$this->out($this->marketplace.' ('.$this->mpID.') label status unavailable for order '.$sOrderId.' — retrying next run.'."\n");
					$this->markOrderDeferred($this->oOrder['orders_id']);
					return;
				}
				if ($sLabelStatus !== 'CACHED') {
					if ($sLabelStatus === null || $sLabelStatus === '') {
						// No label bought yet — the merchant may buy one OR enter a tracking number.
						$aProblems[] = 'Shipping confirmation for order '.$sOrderId.' was not sent to Temu: the order is not ready for shipment. Either buy a shipping label in the Buy Shipping Services tab (Buy Shipping), or enter a tracking number for the order (self-fulfillment). The order is confirmed automatically once one of these is done.';
					} else {
						$aProblems[] = 'Shipping confirmation for order '.$sOrderId.' was not sent to Temu: the Buy-Shipping label is not ready yet (status: '.$sLabelStatus.'). Buy or complete the label in the Buy Shipping Services tab; the order is confirmed automatically once the label is ready.';
					}
				}
			} else {
				// Self-fulfillment without a tracking code.
				$aProblems[] = 'Shipping confirmation for order '.$sOrderId.' was not sent to Temu: the order has no tracking code yet. Temu rejects self-fulfilled shipping confirmations without a tracking number.';
			}

			if (!empty($aProblems)) {
				foreach ($aProblems as $sMessage) {
					$this->addConfigErrorOncePerDay($sMessage, array('MOrderID' => $sOrderId));
					$this->out($this->marketplace.' ('.$this->mpID.') '.$sMessage."\n");
				}
				// Do NOT flag dirty and do NOT confirm — the order stays out-of-sync and is
				// retried on a later cron run (e.g. once the tracking code / label is ready),
				// without holding up the newest-500 selection in the meantime.
				$this->markOrderDeferred($this->oOrder['orders_id']);
				return;
			}
		}

		parent::prepareSingleOrder($date);
	}

	/**
	 * Fetch the current Buy-Shipping label status for one order from Temu (via GetShipmentList,
	 * the same source as the Buy Shipping Services > Overview tab). GetShipmentList only returns
	 * orders that have a label, so a missing row means no label was bought yet (null).
	 *
	 * @param string $sOrderId Temu MOrderID
	 * @return string|null 'CACHED'/'CREATING'/'PENDING'/'ERROR' (label status), null when the
	 *         order has no label, or self::LABEL_STATUS_FETCH_FAILED on an API error.
	 */
	protected function fetchBuyShippingLabelStatus($sOrderId) {
		if ($sOrderId === '' || $sOrderId === null) {
			return null;
		}
		try {
			$aResponse = MagnaConnector::gi()->submitRequestCached(array(
				'ACTION' => 'GetShipmentList',
				'SEARCH' => $sOrderId,
				'LIMIT'  => 50,
			), 60);
		} catch (Exception $oExc) {
			return self::LABEL_STATUS_FETCH_FAILED;
		}
		if (!isset($aResponse['STATUS']) || $aResponse['STATUS'] !== 'SUCCESS'
			|| !isset($aResponse['DATA']) || !is_array($aResponse['DATA'])
		) {
			return self::LABEL_STATUS_FETCH_FAILED;
		}
		// SEARCH is a LIKE match, so pick the row whose MOrderID equals this order exactly.
		foreach ($aResponse['DATA'] as $aShipment) {
			if (isset($aShipment['MOrderID']) && (string)$aShipment['MOrderID'] === (string)$sOrderId) {
				return isset($aShipment['ShippingLabelStatus']) ? $aShipment['ShippingLabelStatus'] : null;
			}
		}
		return null;
	}

	/**
	 * Writes a merchant-visible Error Log entry, at most one record per day per message —
	 * repeat runs of the same day only bump `dateadded` (no flooding while the config stays
	 * incomplete). Parity with v3 (md5 dedupe scoped by calendar day).
	 */
	protected function addConfigErrorOncePerDay($sMessage, $aAdditional = array()) {
		$iId = (int)MagnaDB::gi()->fetchOne('
			SELECT id FROM '.TABLE_MAGNA_COMPAT_ERRORLOG.'
			 WHERE mpID = '.(int)$this->mpID.'
			   AND origin = \'SyncOrderStatus\'
			   AND errormessage = \''.MagnaDB::gi()->escape($sMessage).'\'
			   AND DATE(dateadded) = \''.gmdate('Y-m-d').'\'
			 LIMIT 1
		');
		if ($iId > 0) {
			MagnaDB::gi()->update(TABLE_MAGNA_COMPAT_ERRORLOG, array(
				'dateadded' => gmdate('Y-m-d H:i:s'),
			), array('id' => $iId));
		} else {
			MagnaDB::gi()->insert(TABLE_MAGNA_COMPAT_ERRORLOG, array(
				'mpID'           => $this->mpID,
				'origin'         => 'SyncOrderStatus',
				'errormessage'   => $sMessage,
				'dateadded'      => gmdate('Y-m-d H:i:s'),
				'additionaldata' => serialize($aAdditional),
			));
		}
	}

	protected function confirmShipment($date) {
		$aConfirm = array(
			'MOrderID' => $this->oOrder['special'],
		);

		$blUseBuyShipping = ($this->config['UseBuyShipping'] === 'true');
		$aConfirm['FulfillmentMode'] = $blUseBuyShipping ? 'auto' : 'self';

		if (!empty($this->config['WarehouseId'])) {
			$aConfirm['WarehouseId'] = $this->config['WarehouseId'];
		}

		// Shipping date (parity with v3 getShippingDate). $date is the status-change timestamp
		// (when the order entered the "shipped" status), converted to the marketplace timezone.
		$aConfirm['ShippingDate'] = localTimeToMagnaTime($date);

		// Buy-shipping dimensions for the Temu-managed (auto) path.
		if ($blUseBuyShipping) {
			$aConfirm['BuyShipping'] = array(
				'Weight' => getDBConfigValue('temu.orderstatus.temu.buyshipping.weightamount', $this->mpID, '1'),
				'WeightUnit' => getDBConfigValue('temu.orderstatus.temu.buyshipping.weightunit', $this->mpID, 'KILOGRAM'),
				'Length' => getDBConfigValue('temu.orderstatus.temu.buyshipping.length', $this->mpID, '30'),
				'Width' => getDBConfigValue('temu.orderstatus.temu.buyshipping.width', $this->mpID, '20'),
				'Height' => getDBConfigValue('temu.orderstatus.temu.buyshipping.height', $this->mpID, '15'),
				'DimensionUnit' => getDBConfigValue('temu.orderstatus.temu.buyshipping.dimensionunit', $this->mpID, 'CENTIMETER'),
				'ShipLater' => (bool)getDBConfigValue('temu.orderstatus.temu.buyshipping.shiplater', $this->mpID, false),
				'ShipLogisticsType' => getDBConfigValue('temu.orderstatus.temu.buyshipping.logisticstype', $this->mpID, ''),
			);
		}

		// Carrier is sent regardless of fulfillment mode. FEAT-026: resolve the carrier PER ORDER
		// (Shop-Matching / DB-Matching / auto / default). Send the Temu CarrierId in `CarrierId` and
		// the carrier NAME in `Carrier` (the API resolves the id from `CarrierId`, and `Carrier` stays
		// the human-readable name). When only a name resolved (unmatched shop carrier, no id), send
		// just the name.
		$aCarrier = $this->resolveCarrier($this->oOrder['orders_id']);
		if ($aCarrier['CarrierId'] !== '') {
			$aConfirm['CarrierId'] = $aCarrier['CarrierId'];
		}
		if ($aCarrier['Carrier'] !== '') {
			$aConfirm['Carrier'] = $aCarrier['Carrier'];
		}

		// Forward the shop tracking code as `TrackingCode` (the field the API reads for the
		// self-fulfilled path — FulfillmentHelper::confirmShipment). The API routes buy-shipping
		// vs self-fulfilled by the order's ACTUAL fulfillment, not by our config, and has no
		// fallback for tracking, so a seller-fulfilled order needs it here — otherwise Temu
		// rejects with "Tracking number is blank". (v3 manipulateRequest needs the same addition.)
		$sTrackingCode = $this->getTrackingCode($this->oOrder['orders_id']);
		if (!empty($sTrackingCode)) {
			$aConfirm['TrackingCode'] = $sTrackingCode;
		}

		// Flag the order as dirty so saveDirtyOrders() persists the synced status once the API
		// confirms it.
		$this->oOrder['__dirty'] = true;
		$this->out($this->marketplace.' ('.$this->mpID.') sent shipping confirmation request for order '.$this->oOrder['special'].' ('.$this->oOrder['orders_id'].')'."\n");
		return $aConfirm;
	}

	protected function cancelOrder($date) {
		$this->oOrder['__dirty'] = true;
		$this->out($this->marketplace.' ('.$this->mpID.') sent cancel order request for order '.$this->oOrder['special'].' ('.$this->oOrder['orders_id'].')'."\n");
		return array(
			'MOrderID' => $this->oOrder['special'],
			'CancellationReason' => '1', // Temu supports only "Out of Stock" (1); see the config help.
		);
	}
}
