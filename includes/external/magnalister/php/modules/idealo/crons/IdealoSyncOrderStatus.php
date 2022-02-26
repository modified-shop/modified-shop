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


require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleSyncOrderStatus.php');

class IdealoSyncOrderStatus extends MagnaCompatibleSyncOrderStatus {

    /**
     * Builds the base API request for this marketplace.
     * @return array
     *   The base request
     */
    protected function getBaseRequest() {
        return array (
            'SUBSYSTEM' => 'ComparisonShopping',
            'SEARCHENGINE' => 'idealo',
            'MARKETPLACEID' => $this->mpID,
        );
    }

    protected function getConfigKeys() {
        $aConfigKeys = parent::getConfigKeys();

        $aConfigKeys['OrderRefundStatus'] = array (
            'key' => 'orderstatus.refund',
            'default' => '--',
        );

        return $aConfigKeys;
    }

    /**
     * Builds an element for the CancelShipment request
     * @return array
     */
    protected function cancelOrder($date) {
        $cncl = array (
            'MOrderID' => $this->oOrder['special']
        );

        $cancelReason = getDBConfigValue($this->marketplace . '.orderstatus.cancelreason', $this->mpID);
        $cancelComment = getDBConfigValue($this->marketplace . '.orderstatus.cancelcomment', $this->mpID);
        if (isset($cancelReason) && $cancelReason !== 'noselection') {
            $cncl['Reason'] = $cancelReason;
            if (!empty($cancelComment)) {
                $cncl['Comment'] = $cancelComment;
            }
        }

        $this->oOrder['data']['ML_LABEL_ORDER_CANCELLED'] = $date;
        // flag order as dirty, meaning that it has to be saved.
        $this->oOrder['__dirty'] = true;
        return $cncl;
    }


    protected function isProcessable() {
        $sDirectBuyActive = getDBConfigValue('idealo.directbuy.active', $this->mpID);

        if ($sDirectBuyActive === 'true' && $this->config['OrderRefundStatus'] !== '--' && $this->oOrder['orders_status_shop'] === $this->config['OrderRefundStatus']) {
            $aRequest = $this->getBaseRequest() + array(
                'ACTION' => 'DoRefund',
                'DATA' => array(
                    array(
                        'MOrderID' => $this->oOrder['special'],
                    )
                )
            );

            try {
                $aData = unserialize($this->oOrder['data']);
                if(!isset($aData['refund'])) {
                    MagnaConnector::gi()->submitRequest($aRequest);
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
                $aErrorData = array(
                    'MOrderID' => $this->oOrder['special'],
                );

                if (is_numeric($oEx->getCode())) {
                    $sOrigin = 'idealo';
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
}
