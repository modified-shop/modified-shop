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
 * (c) 2010 - 2019 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');


require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleSyncOrderStatus.php');

class MetroSyncOrderStatus extends MagnaCompatibleSyncOrderStatus {

    public function process() {
        #echo print_m($this->config, '$this->config');
        $this->storeLogging('Config', $this->config);

        if ($this->config['OrderStatusSync'] != 'auto') {
            return false;
        }
        $this->aOrders = $this->getOrdersToSync();
        $this->log(print_m($this->aOrders, "\n".'$this->aOrders'));

        if (empty($this->aOrders)) return true;

        #return true;
        $this->confirmations = array();
        $this->cancellations = array();
        $this->unprocessed = array();

        foreach ($this->aOrders as $key => &$oOrder) {
            $this->oOrder = &$oOrder;
            $this->iOrderIndex = $key;

            if (!$this->isProcessable()) {
                $this->unprocessed[] = $oOrder['orders_id'];
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
                $this->submitStatusUpdate('CancelOrder', $this->cancellations);
                $this->cancellations = array();
                $requestSend = true;
            }
            if ($requestSend) {
                $this->saveDirtyOrders();
            }
        }
        //*
        $this->submitStatusUpdate('ConfirmShipment', $this->confirmations);
        $this->submitStatusUpdate('CancelOrder',  $this->cancellations);

        $this->saveDirtyOrders();

        $this->storeLogging('Unprocessed', $this->unprocessed);
        $this->updateUnprocessed();
        //*/
        return true;
    }

    protected function confirmShipment($date) {
        $cfirm = array (
            'MetroOrderId' => $this->oOrder['special'],
            'ShippingDate' => localTimeToMagnaTime($date),
            'Country' => 'DE',
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
        return $cfirm;
    }

    protected function cancelOrder($date) {
        $aRequest = array (
            'MetroOrderId' => $this->oOrder['special'],
            'CancellationReason' => $this->config['cancellationReason'],
        );

        $this->oOrder['data']['ML_LABEL_ORDER_CANCELLED'] = $date;
        // flag order as dirty, meaning that it has to be saved.
        $this->oOrder['__dirty'] = true;
        return $aRequest;
    }


    protected function getConfigKeys() {
        $parent = parent::getConfigKeys();
        $parent['cancellationReason'] = array(
            'key' => array('orderstatus.cancelreason'),
            'default' => false,
        );
        return $parent;
    }
}
