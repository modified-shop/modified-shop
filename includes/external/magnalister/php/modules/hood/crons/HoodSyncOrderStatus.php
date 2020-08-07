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
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/crons/MagnaCompatibleSyncOrderStatus.php');

class HoodSyncOrderStatus extends MagnaCompatibleSyncOrderStatus {

    protected function confirmShipment($date) {
        $parent = parent::confirmShipment($date);
        $parent['SendMail'] = $this->config['SendMail'];
        return $parent;
    }

    protected function prepareSingleOrder($date) {
        if ($this->oOrder['orders_status_shop'] == $this->config['StatusShipped']) {
            $this->confirmations[] = $this->confirmShipment($date);
        } else if (in_array($this->oOrder['orders_status_shop'], array(
                    $this->config['StatusCancelledNS'], $this->config['StatusCancelledD'],
                    $this->config['StatusCancelledR'], $this->config['StatusCancelledNP']
                ))) {
            $this->cancellations[] = $this->cancelOrder($date);
        }
    }

    protected function isProcessable() {
        return in_array($this->oOrder['orders_status_shop'], array(
            $this->config['StatusShipped'],
            $this->config['StatusCancelledNS'], $this->config['StatusCancelledD'],
            $this->config['StatusCancelledR'], $this->config['StatusCancelledNP']
        ));
    }

    protected function cancelOrder($date) {
        $parent = parent::cancelOrder($date);
        $parent['SendMail'] = $this->config['SendMail'];
        switch ($this->oOrder['orders_status_shop']) {
            case $this->config['StatusCancelledNS']: {
                    $parent['Reason'] = 'noStock';
                    break;
                }
            case $this->config['StatusCancelledD']: {
                    $parent['Reason'] = 'defect';
                    break;
                }
            case $this->config['StatusCancelledR']: {
                    $parent['Reason'] = 'revoked';
                    break;
                }
            case $this->config['StatusCancelledNP']: {
                    $parent['Reason'] = 'noPayment';
                    break;
                }
            default: {
                    $parent['Reason'] = 'noStock';
                    break;
                }
        }
        return $parent;
    }

    protected function getConfigKeys() {
        $parent = parent::getConfigKeys();
        $parent['SendMail'] = array(
            'key' => array('orderstatus.sendmail', 'val'),
            'default' => false,
        );
        $parent['StatusCancelledNS'] = array(
            'key' => 'orderstatus.canceled.nostock',
            'default' => false,
        );
        $parent['StatusCancelledD'] = array(
            'key' => 'orderstatus.canceled.defect',
            'default' => false,
        );
        $parent['StatusCancelledR'] = array(
            'key' => 'orderstatus.canceled.revoked',
            'default' => false,
        );
        $parent['StatusCancelledNP'] = array(
            'key' => 'orderstatus.canceled.nopayment',
            'default' => false,
        );
        return $parent;
    }

}
