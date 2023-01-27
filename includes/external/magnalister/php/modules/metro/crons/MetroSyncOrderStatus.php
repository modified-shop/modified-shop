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
 * (c) 2010 - 2022 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');


require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleSyncOrderStatus.php');

class MetroSyncOrderStatus extends MagnaCompatibleSyncOrderStatus {

    protected function submitStatusUpdate($action, $data) {
        if ($action == 'CancelShipment') {
            $action = 'CancelOrder';
        }

        parent::submitStatusUpdate($action, $data);
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
