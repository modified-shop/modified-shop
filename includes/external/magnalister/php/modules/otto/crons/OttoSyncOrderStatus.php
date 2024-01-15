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

class OttoSyncOrderStatus extends MagnaCompatibleSyncOrderStatus {

    protected function submitStatusUpdate($action, $data) {
        if ($action == 'CancelShipment') {
            $action = 'CancelOrder';
        }

        parent::submitStatusUpdate($action, $data);
    }

    private function getCarrierValue($type, $configValue, $orderId) {
        $carrier = '';
        switch ($configValue) {
            case 'dbmatch':
                $this->config['CarrierMatchingTable'] = $this->config[$type.'CarrierDBMatchingTable'];
                $this->config['CarrierMatchingAlias'] = $this->config[$type.'CarrierDBMatchingAlias'];
                $carrier = parent::getCarrier($orderId);
                if ($carrier == 'dbmatch') {
                    $carrier = 'Matched carriers not added on the order.';
                }
                break;
            case 'shipmodulematch':
                $this->config['CarrierMatchingTable'] = '';
                $this->config['CarrierMatchingAlias'] = '';
                $sCarrierOrder = MagnaDB::gi()->fetchOne("
				SELECT shipping_method
				  FROM ".TABLE_ORDERS."
				 WHERE orders_id='".MagnaDB::gi()->escape($orderId)."'");

                foreach ($this->config[$type.'CarrierMatchingShop'] as $key => $value) {
                    if ($value == $sCarrierOrder) {
                        $carrier = $this->config[$type.'CarrierMatchingMarketplace'][$key];
                        break;
                    }
                }
                if ($carrier == ''){
                    $carrier = 'Matched carriers not added on the order.';
                }
                break;
            default:
                $carrier = $configValue;
                break;
        }

        return $carrier;
    }

    private function getReturnTrackingKey($orderId) {
        if (   (    (    $this->config['ReturnTrackingKeyDBMatchingTable']['table'] == 'orders_parcel_tracking_codes'
                      && $this->config['ReturnTrackingKeyDBMatchingTable']['column'] == 'tracking_code')
                 || (    empty($this->config['ReturnTrackingKeyDBMatchingTable']['table'])
                      && empty($this->config['ReturnTrackingKeyDBMatchingTable']['column']))
                )
            && MagnaDB::gi()->columnExistsInTable('is_return_delivery','orders_parcel_tracking_codes')
        ) {
            $returnTrackingCode = MagnaDB::gi()->fetchOne("
                SELECT tracking_code
                  FROM orders_parcel_tracking_codes
                 WHERE order_id = '".MagnaDB::gi()->escape($orderId)."'
                 AND is_return_delivery = 1
                 LIMIT 1
            ");
        } else {
            $returnTrackingCode = $this->runDbMatching(array(
                'Table' => $this->config['ReturnTrackingKeyDBMatchingTable'],
                'Alias' => $this->config['ReturnTrackingKeyDBMatchingAlias']
            ), 'orders_id', $orderId);
        }

        if ($returnTrackingCode == 'dbmatch') {
            $returnTrackingCode = 'Matched carriers not added on the order.';
        }
        return $returnTrackingCode;
    }

    protected function confirmShipment($date) {
        //get the key for the correct shipping from address
        $key = array_search($this->oOrder['orders_status_shop'], $this->config['StatusShipped']);

        $cfirm = array (
            'OttoOrderId' => $this->oOrder['special'],
            'StandardCarrier' => $this->getCarrierValue('Send', $this->config['SendCarrier'], $this->oOrder['orders_id']),
            'ForwardingCarrier' => $this->getCarrierValue('Forwarding', $this->config['ForwardingCarrier'], $this->oOrder['orders_id']),
            'TrackingCode' => $this->getTrackingCode($this->oOrder['orders_id']),
            'ReturnCarrier' => $this->getCarrierValue('Return', $this->config['ReturnCarrier'], $this->oOrder['orders_id']),
            'ReturnTrackingKey' => $this->getReturnTrackingKey($this->oOrder['orders_id']),
            'ShippingDate' => localTimeToMagnaTime($date),
            'ShipFromCity' => $this->config['ShippingCity'][$key],
            'ShipFromCountryCode' => $this->config['ShippingCountry'][$key],
            'ShipFromZip' => $this->config['ShippingZipCode'][$key]
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

    protected function cancelOrder($date) {
        $aRequest = array (
            'OttoOrderId' => $this->oOrder['special'],
            'CancellationReason' => $this->config['cancellationReason'],
        );

        $this->oOrder['data']['ML_LABEL_ORDER_CANCELLED'] = $date;
        // flag order as dirty, meaning that it has to be saved.
        $this->oOrder['__dirty'] = true;
        $this->out($this->marketplace.' ('.$this->mpID.') sent cancel order request for order '.$this->oOrder['special'] .' ('.$this->oOrder['orders_id'].')'."\n");
        return $aRequest;
    }


    protected function getConfigKeys() {
        $parent = parent::getConfigKeys();
        $parent['OrderStatusSync'] = array(
            'key' => 'orders.status.synchronization',
            'default' => false,
        );
        $parent['cancellationReason'] = array(
            'key' => 'orderstatus.cancelreason',
            'default' => false,
        );
        $parent['StatusCancelled'] = array(
            'key' => 'orders.cancel.with',
            'default' => false,
        );
        $parent['StatusShipped'] = array(
            'key' => 'shipping.status',
            'default' => false,
        );
        $parent['ShippingCity'] = array(
            'key' => 'orders.shipping.address.city',
            'default' => '',
        );
        $parent['ShippingCountry'] = array(
            'key' => 'orders.shipping.address.countrycode',
            'default' => false,
        );
        $parent['ShippingZipCode'] = array(
            'key' => 'orders.shipping.address.zip',
            'default' => '',
        );
        $parent['SendCarrier'] = array(
            'key' => 'send.carrier',
            'default' => '',
        );
        $parent['SendCarrierMatchingMarketplace'] = array(
            'key' => 'send.carrier.ottoToShopMatching.otto',
            'default' => '',
        );
        $parent['SendCarrierMatchingShop'] = array(
            'key' => 'send.carrier.ottoToShopMatching.shop',
            'default' => '',
        );
        $parent['SendCarrierDBMatchingTable'] = array(
            'key' => 'send.carrier.DBMatching.table',
            'default' => '',
        );
        $parent['SendCarrierDBMatchingAlias'] = array(
            'key' => 'send.carrier.DBMatching.alias',
            'default' => '',
        );
        $parent['ForwardingCarrier'] = array(
            'key' => 'forwarding.carrier',
            'default' => '',
        );
        $parent['ForwardingCarrierMatchingMarketplace'] = array(
            'key' => 'forwarding.carrier.ottoToShopMatching.otto',
            'default' => '',
        );
        $parent['ForwardingCarrierMatchingShop'] = array(
            'key' => 'forwarding.carrier.ottoToShopMatching.shop',
            'default' => '',
        );
        $parent['ForwardingCarrierDBMatchingTable'] = array(
            'key' => 'forwarding.carrier.DBMatching.table',
            'default' => '',
        );
        $parent['ForwardingCarrierDBMatchingAlias'] = array(
            'key' => 'forwarding.carrier.DBMatching.alias',
            'default' => '',
        );
        $parent['ReturnCarrier'] = array(
            'key' => 'return.carrier',
            'default' => '',
        );
        $parent['TrackingCodeMatchingTable'] = array(
            'key' => 'orders.tracking.key.DBMatching.table',
            'default' => '',
        );
        $parent['TrackingCodeMatchingAlias'] = array(
            'key' => 'orders.tracking.key.DBMatching.alias',
            'default' => '',
        );
        $parent['ReturnCarrierMatchingMarketplace'] = array(
            'key' => 'return.carrier.ottoToShopMatching.otto',
            'default' => '',
        );
        $parent['ReturnCarrierMatchingShop'] = array(
            'key' => 'return.carrier.ottoToShopMatching.shop',
            'default' => '',
        );
        $parent['ReturnCarrierDBMatchingTable'] = array(
            'key' => 'return.carrier.DBMatching.table',
            'default' => '',
        );
        $parent['ReturnCarrierDBMatchingAlias'] = array(
            'key' => 'return.carrier.DBMatching.alias',
            'default' => '',
        );
        $parent['ReturnTrackingKeyDBMatchingTable'] = array(
            'key' => 'orders.return.tracking.key.DBMatching.table',
            'default' => '',
        );
        $parent['ReturnTrackingKeyDBMatchingAlias'] = array(
            'key' => 'orders.return.tracking.key.DBMatching.alias',
            'default' => '',
        );
        return $parent;
    }

    protected function isProcessable() {
        $result = false;
        if (is_array($this->config['StatusShipped']) &&
            in_array($this->oOrder['orders_status_shop'], $this->config['StatusShipped'])) {
            $result = true;
        }
        if ($this->oOrder['orders_status_shop'] == $this->config['StatusCancelled']) {
            $result = true;
        }
        return $result;
    }

    protected function prepareSingleOrder($date) {
        if (in_array($this->oOrder['orders_status_shop'], $this->config['StatusShipped'])) {
            $this->confirmations[] = $this->confirmShipment($date);
        } else if ($this->oOrder['orders_status_shop'] == $this->config['StatusCancelled']) {
            $this->cancellations[] = $this->cancelOrder($date);
        }
    }
}
