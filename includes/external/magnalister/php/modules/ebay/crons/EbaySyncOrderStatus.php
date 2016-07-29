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

class EbaySyncOrderStatus extends MagnaCompatibleSyncOrderStatus {
	public function __construct($mpID, $marketplace) {
		parent::__construct($mpID, $marketplace);
		
		$this->confirmationResponseField = 'DATA';
		$this->sizeOfBatch = 256;
	}
	
	/**
	 * Adds the current order's index to a lookup table where the key is
	 * the MOrderID. Joined orders are split.
	 * @return void
	 */
	protected function addToLookupTable() {
		$mOrderIds = explode("\n", $this->oOrder['special']);
		foreach ($mOrderIds as $mOrderId) {
			$this->aMOrderID2Order[$mOrderId] = $this->iOrderIndex;
		}
	}
	
	/**
	 * Builds an element for the ConfirmShipment request.
	 * @return array
	 */
	protected function confirmShipment($date) {
		$cfirm = parent::confirmShipment($date);
		// DON'T flag order as dirty, so the status doesn't get updated.
		$this->oOrder['__dirty'] = false;
		return $cfirm;
	}
	
	/**
	 * Builds an element for the CancelShipment request
	 * @return array
	 */
	protected function cancelOrder($date) {
		$cncl = parent::cancelOrder($date);
		// DON'T flag order as dirty, so the status doesn't get updated.
		$this->oOrder['__dirty'] = false;
		return $cncl;
	}
	
	/**
	 * Processes the confirmations send from the API.
	 * @param array $result
	 *   The entire API result.
	 * @return void
	 */
	protected function processResponseConfirmations($result) {
		if (!isset($result[$this->confirmationResponseField][0])) {
			return;
		}
		
		foreach ($result[$this->confirmationResponseField] as $cData) {
			/* $cData = array (
					[MOrderID] => 370836896301-496024749024
					[TooOld] => false
					[ShippingDate] => 2013-07-26 10:06:10
					[Status] => Success
				)
			*/
			if (!isset($cData['MOrderID']) || ('Success' != $cData['Status'])) {
				continue;
			}
			$oOrder = &$this->getFromLookupTable($cData['MOrderID']);
			if ($oOrder !== null) {
				// save the confirmation and update the status.
				$oOrder['__dirty'] = true;
			}
		}
	}

	/* Only for a hook: Allows to replace the parent function
	 * @param int $orderId
	 * @return string
	 */
	protected function getTrackingCode($orderId) {
		/* {Hook} "EbaySyncOrderStatus_replaceGetTrackingCode": Allows to overwrite getTrackingCode.
			Variables that can be used:
			<ul><li>$orderId</li>
			    <li>$TrackingCode (must be calculated in the contrib file)</li>
			</ul>
			Please use MagnaDB methods to access the database.
		*/
		if (function_exists('magnaContribVerify') && (($hp = magnaContribVerify('EbaySyncOrderStatus_replaceGetTrackingCode', 1)) !== false)) {
			require($hp);
			if (isset($TrackingCode)) {
				return $TrackingCode;
			} else {
				return '';
			}
		} else {
			return parent::getTrackingCode($orderId);
		}
	}

	/* Only for a hook: Allows to replace the parent function 
	 * @param int $orderId
	 * @return string
	 */
	protected function getCarrier($orderId) {
		/* {Hook} "EbaySyncOrderStatus_replaceGetCarrier": Allows to overwrite getCarrier.
			Variables that can be used:
			<ul><li>$orderId</li>
			    <li>$Carrier (must be calculated in the contrib file)</li>
			</ul>
			Please use MagnaDB methods to access the database.
		*/
		if (function_exists('magnaContribVerify') && (($hp = magnaContribVerify('EbaySyncOrderStatus_replaceGetCarrier', 1)) !== false)) {
			require($hp);
			if (isset($Carrier)) {
				return $Carrier;
			} else {
				return '';
			}
		} else {
			return parent::getCarrier($orderId);
		}
	}
}
