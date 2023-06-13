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

    protected $blIsPaymentProgramAvailable;
	public function __construct($mpID, $marketplace) {
		parent::__construct($mpID, $marketplace);

        $this->blIsPaymentProgramAvailable = false;
        try {
            $aResponse = MagnaConnector::gi()->submitRequest(array(
                'SUBSYSTEM' => $marketplace,
                'MARKETPLACEID' => $mpID,
                'ACTION' => 'CheckPaymentProgramAvailability',
            ));
            $this->blIsPaymentProgramAvailable = isset($aResponse['IsAvailable']) ? $aResponse['IsAvailable'] : false;
        } catch (MagnaException $oEx) {
        }
		$this->confirmationResponseField = 'DATA';
		$this->sizeOfBatch = 256;
            if (($hp = magnaContribVerify('EbaySyncOrderStatus_beforeSyncOrderStatus', 1)) !== false) {
                 require($hp);
            } 
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
				$this->out($this->marketplace.' ('.$this->mpID.') success response received for order '.$oOrder['special'] .' ('.$oOrder['orders_id'].')'."\n");
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

    protected function getConfigKeys() {
	    $aReturn = parent::getConfigKeys();

        $aReturn['OrderRefundStatus'] = array (
            'key' => 'refundstatus',
            'default' => '--',
        );
        $aReturn['OrderRefundReason'] = array (
            'key' => 'refundreason',
            'default' => false,
        );
        $aReturn['OrderRefundComment'] = array (
            'key' => 'refundcomment',
            'default' => false,
        );
	    return $aReturn;
    }

    /**
     * @return bool
     */
    protected function isProcessable() {
        if ($this->blIsPaymentProgramAvailable && $this->config['OrderRefundStatus'] !== '--' && $this->oOrder['orders_status_shop'] === $this->config['OrderRefundStatus']) {
            /* {Hook} "EbaySyncOrderStatus_beforeDoRefund": called before a refund to eBay is triggered.
                    Variables that can be used:
                     <ul><li>$order: The order that is going to be imported. The order is an 
                             associative array representing the structures of the order and customer related shop tables.</li>
                         <li>$sOrderRefundReason - the reason of the refund, for eBay, one of:
                             <ul><li>BUYER_CANCEL</li>
                                 <li>SELLER_CANCEL</li>
                                 <li>ITEM_NOT_RECEIVED</li>
                                 <li>BUYER_RETURN</li>
                                 <li>ITEM_NOT_AS_DESCRIBED</li>
                                 <li>OTHER_ADJUSTMENT</li>
                         </ul></li>
                         <li>$sOrderRefundComment - a comment for the refund (defined in the configuration, but can be changed here)</li>
                         <li>$mpID: The ID of the marketplace.</li>
                     </ul>
			Please use MagnaDB methods to access the database.
             */
            $sOrderRefundReason  = $this->config['OrderRefundReason'];
            $sOrderRefundComment = $this->config['OrderRefundComment'];
            if (($hp = magnaContribVerify('EbaySyncOrderStatus_beforeDoRefund', 1)) !== false) {
                 $order   = $this->oOrder;
                 $mpID    = $this->mpID;
                 require($hp);
                 $this->oOrder = $order;
            }
            $request = $this->getBaseRequest();
            $request = array_merge($request, array(
                'ACTION' => 'DoRefund',
                'MagnalisterOrderId' => $this->oOrder['special'],
                'ReasonOfRefund' => $sOrderRefundReason,
                'Comment' => $sOrderRefundComment
            ));

            try {
                // in osc this $this->oOrder['data'] is at this point serialized but not in veyton
                $aData = unserialize($this->oOrder['data']);
                if (!isset($aData['refund'])) {
                    MagnaConnector::gi()->submitRequest($request);
                    $aData['refund'] = 'requested';
                    $this->oOrder['data'] = serialize($aData);
                    MagnaDB::gi()->update(TABLE_MAGNA_ORDERS,
                        array(
                            'data' => $this->oOrder['data']
                        )
                        , array(
                            'orders_id' => $this->oOrder['orders_id']
                        )
                    );
                }
            } catch (MagnaException $oEx) {
                echo print_m($oEx->getMessage(), 'DoRefund ('.$this->oOrder['special'].')');
                $aErrorData = array(
                    'MOrderID' => $this->oOrder['special'],
                );

                if (is_numeric(substr($oEx->getMessage(), 0, 5))) {
                    $sOrigin = 'eBay';
                } else {
                    $sOrigin = 'magnalister';
                }

                MagnaDB::gi()->insert(
                    TABLE_MAGNA_COMPAT_ERRORLOG,
                    array(
                        'mpID' => $this->mpID,
                        'errormessage' => $oEx->getMessage(),
                        'dateadded' => date('Y-m-d H:i:s'),
                        'additionaldata' => serialize($aErrorData),
                        'origin' => $sOrigin
                    )
                );
            }
        }
        return parent::isProcessable();
    }

	protected function updateUnprocessed() {
		parent::updateUnprocessed();
		if ($this->_debugDryRun) {
			return;
		}
		// Orders older than 3 months cannot be updated, set as done
		$iMinOrderId = MagnaDB::gi()->fetchOne('SELECT min(orders_id)
			 FROM `'.TABLE_ORDERS.'`
			WHERE UNIX_TIMESTAMP(date_purchased) > (UNIX_TIMESTAMP() - 92 * 86400)');
		if ($iMinOrderId !== false) {
			MagnaDB::gi()->query('
			    UPDATE '.TABLE_MAGNA_ORDERS.' mo,
			           '.TABLE_ORDERS.' o 
			       SET mo.orders_status = o.orders_status
			     WHERE mo.orders_id = o.orders_id
			           AND mo.mpID = "'.$this->mpID.'"
			           AND mo.orders_id < '.$iMinOrderId
			);
		}
	}
}
