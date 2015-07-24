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
 * (c) 2010 - 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleSyncOrderStatus.php');

class MeinpaketSyncOrderStatus extends MagnaCompatibleSyncOrderStatus {

	protected $confirmationResponseField = 'CONFIRMATIONS';

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
			'StatusCancelledCR' => array (
				'key' => 'orderstatus.cancelled.customerrequest',
				'default' => false
			),
			'StatusCancelledOOS' => array (
				'key' => 'orderstatus.cancelled.outofstock',
				'default' => false
			),
			'StatusCancelledDG' => array (
				'key' => 'orderstatus.cancelled.damagedgoods',
				'default' => false
			),
			'StatusCancelledDR' => array (
				'key' => 'orderstatus.cancelled.dealerrequest',
				'default' => false
			),
			'StatusShipped' => array (
				'key' => 'orderstatus.shipped',
				'default' => false,
			),
			'TrackingCodeMatchingTable' => array(
				'key' => 'orderstatus.trackingcode.table',
				'default' => false,
			),
			'TrackingCodeMatchingAlias' => array(
				'key' => 'orderstatus.trackingcode.alias',
				'default' => false,
			),
		);
	}
	
	/**
	 * Checks whether the status of the current order should be synchronized with
	 * the marketplace.
	 * @return bool
	 */
	protected function isProcessable() {
		return in_array($this->oOrder['orders_status_shop'], array(
			$this->config['StatusShipped'], 
			$this->config['StatusCancelledCR'], $this->config['StatusCancelledOOS'],
			$this->config['StatusCancelledDG'], $this->config['StatusCancelledDR']
		));
	}
	
	/**
	 * Builds an element for the ConfirmShipment request.
	 * @return array
	 */
	protected function confirmShipment($date) {
		$cfirm = parent::confirmShipment($date);
		$cfirm['ConsignmentID'] = $this->oOrder['orders_id'];
		return $cfirm;
	}
	
	/**
	 * Builds an element for the CancelShipment request
	 * @return array
	 */
	protected function cancelOrder($date) { 
		$cncl = parent::cancelOrder($date);
		$cncl['ConsignmentID'] = $this->oOrder['orders_id'];
		switch ($this->oOrder['orders_status_shop']) {
			case $this->config['StatusCancelledCR']: {
				$cncl['Reason'] = 'CustomerRequest';
				break;
			}
			case $this->config['StatusCancelledOOS']: {
				$cncl['Reason'] = 'OutOfStock';
				break;
			}
			case $this->config['StatusCancelledDG']: {
				$cncl['Reason'] = 'DamagedGoods';
				break;
			}
			case $this->config['StatusCancelledDR']:
			default: {
				$cncl['Reason'] = 'DealerRequest';
				break;
			}
		}
		return $cncl;
	}
		
	/**
	 * Processes the current order.
	 * @return void
	 */
	protected function prepareSingleOrder($date) {
		if ($this->oOrder['orders_status_shop'] == $this->config['StatusShipped']) {
			$this->confirmations[] = $this->confirmShipment($date);
		} else if (in_array($this->oOrder['orders_status_shop'], array(
			$this->config['StatusCancelledCR'], $this->config['StatusCancelledOOS'],
			$this->config['StatusCancelledDG'], $this->config['StatusCancelledDR']
		))) {
			$this->cancellations[] = $this->cancelOrder($date);
		}

		# Redmine Enhancement #834 !Hack!
		$this->oOrder['__dirty'] = false;
	}
	
	/**
	 * Adds an error to the meinpaket error log.
	 * 
	 * @param array $error
	 *   The entry for the error log.
	 * @return void
	 */
	protected function addToErrorLog($error) {
		/*
		    [ERRORLEVEL] => FATAL
		    [SUBSYSTEM] => Meinpaket
		    [APIACTION] => ConfirmShipment
		    [ERRORMESSAGE] => Die Versandnummer '400359' existiert bereits f�r die Bestellung 'M8LEB96HP2Z2'. (Code: 6015)
		    [DETAILS] => Array
		        (
		            [MOrderID] => M8LEB96HP2Z2
		            [ConsignmentID] => 400359
		            [ErrorCode] => INVALID_DATA
		            [ErrorMessage] => Die Versandnummer '400359' existiert bereits f�r die Bestellung 'M8LEB96HP2Z2'. (Code: 6015)
		        )
		*/
		$add = $error['DETAILS'];
		unset($add['ErrorCode']);
		unset($add['ErrorMessage']);
		$add['Action'] = $error['APIACTION'];
		
		MagnaDB::gi()->insert(
			TABLE_MAGNA_MEINPAKET_ERRORLOG,
			array (
				'mpID' => $this->mpID,
				'errormessage' => $error['ERRORMESSAGE'],
				'dateadded' => date('Y-m-d H:i:s'),
				'additionaldata' => serialize($add)
			)
		);
	}
}
