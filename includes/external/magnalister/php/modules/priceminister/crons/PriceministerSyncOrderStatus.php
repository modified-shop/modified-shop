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
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');


require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/crons/MagnaCompatibleSyncOrderStatus.php');

class PriceministerSyncOrderStatus extends MagnaCompatibleSyncOrderStatus
{
    /**
     * Main method of the class that manages the order status update.
     * @return bool
     *   false if the orderstatus sync has been disabled, true otherwise.
     */
    public function process()
    {
        if (defined('MAGNA_CALLBACK_MODE')){
            // giving continous output if callback
            $this->out("Start[" . get_class() . "]: " . $this->marketplace . " (" . $this->mpID . ")\n");
        }
        #echo print_m($this->config, '$this->config');
        $this->storeLogging('Config', $this->config);

        if ($this->config['OrderStatusSync'] != 'auto'){
            return false;
        }
        $this->aOrders = $this->getOrdersToSync();
        $this->log(print_m($this->aOrders, "\n" . '$this->aOrders'));

        if (empty($this->aOrders)){
            if (defined('MAGNA_CALLBACK_MODE')){
                // giving continous output if callback
                $this->out("End[" . get_class() . "]: " . $this->marketplace . " (" . $this->mpID . ")\n");
            }
            return true;
        }

        #return true;
        $this->confirmations = array();
        $this->cancellations = array();
        $this->acceptations = array();
        $this->refused = array();
        /** array ('order id' => 'current order status id') */
        $this->unprocessed = array();

        foreach ($this->aOrders as $key => &$oOrder){
            $this->oOrder = &$oOrder;
            $this->iOrderIndex = $key;

            if (!$this->isProcessable()){
                $this->unprocessed[$oOrder['orders_id']] = $oOrder['orders_status_shop'];
                unset($this->aOrders[$key]);
                continue;
            }

            if (defined('MAGNA_CALLBACK_MODE')){
                // giving continous output if callback
                $this->out($oOrder['orders_id'] . "\n");
            }
            $this->decodeData();
            // add order to lookup table
            $this->addToLookupTable();
            $this->prepareSingleOrder($this->getStatusChangeTimestamp());

            $requestSend = false;
            if (count($this->confirmations) >= $this->sizeOfBatch){
                $this->submitStatusUpdate('ConfirmShipment', $this->confirmations);
                $this->confirmations = array();
                $requestSend = true;
            }

            if (count($this->cancellations) >= $this->sizeOfBatch){
                $this->submitStatusUpdate('CancelShipment', $this->cancellations);
                $this->cancellations = array();
                $requestSend = true;
            }

            if (count($this->acceptations) >= $this->sizeOfBatch){
                $this->submitStatusUpdate('AcceptOrder', $this->acceptations);
                $this->acceptations = array();
                $requestSend = true;
            }

            if (count($this->refused) >= $this->sizeOfBatch){
                $this->submitStatusUpdate('RefuseOrder', $this->refused);
                $this->refused = array();
                $requestSend = true;
            }

            if ($requestSend){
                $this->saveDirtyOrders();
            }
        }
        //*
        $this->submitStatusUpdate('ConfirmShipment', $this->confirmations);
        $this->submitStatusUpdate('CancelShipment', $this->cancellations);
        $this->submitStatusUpdate('AcceptOrder', $this->acceptations);
        $this->submitStatusUpdate('RefuseOrder', $this->refused);

        $this->saveDirtyOrders();

        $this->updateUnprocessed();
        if (defined('MAGNA_CALLBACK_MODE')){
            // giving continous output if callback
            $this->out("End[" . get_class() . "]: " . $this->marketplace . " (" . $this->mpID . ")\n");
        }
        //*/
        return true;
    }

    /**
     * Processes the current order.
     * @return void
     */
    protected function prepareSingleOrder($date)
    {
        if ($this->oOrder['orders_status_shop'] == $this->config['StatusRefused']){
            $this->refused[] = $this->refuseShipment($date);
        } else if ($this->oOrder['orders_status_shop'] == $this->config['StatusAccepted']){
            $this->acceptations[] = $this->acceptShipment($date);
        } else{
            // if order is canceled or shipped
            parent::prepareSingleOrder($date);
        }
    }

    /**
     * Builds an element for the ConfirmShipment request.
     * @return array
     */
    protected function confirmShipment($date)
    {
        $carrier = getDBConfigValue($this->marketplace . '.orderstatus.carrier', $this->mpID);
        $cfirm = array(
            'MOrderID' => $this->oOrder['special'],
            'Carrier' => $carrier,
            'TrackingCode' => ''
        );

        $this->oOrder['data']['ML_LABEL_CARRIER'] = $carrier;
        $trackercode = $this->getTrackingCode($this->oOrder['orders_id']);
        if (false != $trackercode) {
            $this->oOrder['data']['ML_LABEL_TRACKINGCODE'] = $cfirm['TrackingCode'] = $trackercode;
        }

        $this->oOrder['data']['ML_LABEL_SHIPPING_DATE'] = $cfirm[localTimeToMagnaTime($date)];

        // flag order as dirty, meaning that it has to be saved.
        $this->oOrder['__dirty'] = true;
        return $cfirm;
    }

    /**
     * Builds an element for the CancelShipment request
     * @return array
     */
    protected function cancelOrder($date)
    {
        $cncl = array(
            'MOrderID' => $this->oOrder['special'],
            'Comment' => getDBConfigValue($this->marketplace . '.orderstatus.cancelreason', $this->mpID)
        );

        $this->oOrder['data']['ML_LABEL_ORDER_CANCELLED'] = $date;
        // flag order as dirty, meaning that it has to be saved.
        $this->oOrder['__dirty'] = true;
        return $cncl;
    }

    /**
     * Builds an element for the refuseShipment request.
     * @return array
     */
    protected function refuseShipment($date)
    {
        $rfs = array(
            'MOrderID' => $this->oOrder['special'],
        );

        $this->oOrder['data']['ML_LABEL_REFUSE_DATE'] = $rfs[$date];

        // flag order as dirty, meaning that it has to be saved.
        $this->oOrder['__dirty'] = true;
        return $rfs;
    }

    /**
     * Builds an element for the acceptShipment request.
     * @return array
     */
    protected function acceptShipment($date)
    {
        $accpt = array(
            'MOrderID' => $this->oOrder['special'],
        );

        $this->oOrder['data']['ML_LABEL_ACCEPT_DATE'] = $accpt[$date];

        // flag order as dirty, meaning that it has to be saved.
        $this->oOrder['__dirty'] = true;
        return $accpt;
    }

    /**
     * Specifies the settings and their default values for order status
     * synchronisation. Assumes the order status synchronisation is
     * disabled.
     * @return array
     *   List of settings
     */
    protected function getConfigKeys()
    {
        $aConfigKeys = parent::getConfigKeys();
        $aConfigKeys['StatusAccepted'] = array(
            'key' => 'orderstatus.accepted',
            'default' => false,
        );

        $aConfigKeys['StatusRefused'] = array(
            'key' => 'orderstatus.refused',
            'default' => false,
        );

        return $aConfigKeys;
    }

    /**
     * Checks whether the status of the current order should be synchronized with
     * the marketplace.
     * @return bool
     */
    protected function isProcessable()
    {
        return ($this->oOrder['orders_status_shop'] == $this->config['StatusShipped'])
        || ($this->oOrder['orders_status_shop'] == $this->config['StatusCancelled'])
        || ($this->oOrder['orders_status_shop'] == $this->config['StatusAccepted'])
        || ($this->oOrder['orders_status_shop'] == $this->config['StatusRefused']);
    }

    /**
     * Adds an error to the Priceminister error log.
     *
     * @param array $error
     *   The entry for the error log.
     * @return void
     */
    protected function addToErrorLog($error)
    {
        $add = $error['DETAILS'];
        unset($add['ErrorCode']);
        unset($add['ErrorMessage']);
        $add['Action'] = $error['APIACTION'];

        MagnaDB::gi()->insert(
            TABLE_MAGNA_COMPAT_ERRORLOG,
            array(
                'mpID' => $this->mpID,
                'errormessage' => $error['ERRORMESSAGE'],
                'dateadded' => date('Y-m-d H:i:s'),
                'additionaldata' => serialize($add)
            )
        );
    }

}
