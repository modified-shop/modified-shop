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

    /**
     * Carriers accepted by the marketplace, fetched once per marketplace id.
     *
     * @var array
     */
    protected static $aAllowedCarriers = array();

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
        $carrier = $this->resolveCarrier($this->oOrder['orders_id']);
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

    /**
     * The marketplace only accepts carriers from its own list; anything else is
     * treated as a custom carrier, which is being restricted. Therefore the
     * resolved carrier is verified against that list and, if it does not match,
     * replaced by the carrier configured in the settings.
     *
     * @param int $orderId
     *
     * @return string|bool
     *   The carrier to submit, or false if none could be determined.
     */
    protected function resolveCarrier($orderId) {
        $mCarrier = $this->getCarrier($orderId);
        $aAllowed = $this->getAllowedCarriers();

        // Without a list there is nothing to check against, keep the resolved value.
        if (empty($aAllowed)) {
            return $mCarrier;
        }

        if (false != $mCarrier) {
            $mMatched = $this->matchCarrier($mCarrier, $aAllowed);
            if (false !== $mMatched) {
                return $mMatched;
            }
        }

        $sConfigured = $this->config['CarrierDefault'];
        if (!empty($sConfigured)) {
            $mMatched = $this->matchCarrier($sConfigured, $aAllowed);
            if (false !== $mMatched) {
                if (false != $mCarrier) {
                    $this->out(
                        'Carrier "'.$mCarrier.'" is not accepted by the marketplace, '
                        .'submitting the configured carrier "'.$mMatched.'" instead.'
                    );
                }

                return $mMatched;
            }
        }

        if (false != $mCarrier) {
            $this->out(
                'Carrier "'.$mCarrier.'" is not accepted by the marketplace and no '
                .'accepted carrier is configured, submitting it unchanged.'
            );
        }

        return $mCarrier;
    }

    /**
     * Compares a carrier against the accepted list, by id and by name, and
     * returns the accepted carrier id.
     *
     * @param string $sCarrier
     * @param array $aAllowed
     *   Accepted carriers as carrierId => carrierName.
     *
     * @return string|bool
     *   The accepted carrier id, or false if there is no match.
     */
    protected function matchCarrier($sCarrier, $aAllowed) {
        $sNeedle = strtolower(trim($sCarrier));
        if ('' === $sNeedle) {
            return false;
        }

        foreach ($aAllowed as $sId => $sName) {
            if (strtolower($sId) === $sNeedle || strtolower($sName) === $sNeedle) {
                return $sId;
            }
        }

        // Second pass on normalized values, so that spellings which only differ in
        // spaces or punctuation ("DHL-Express" vs. "DHL Express") still resolve to
        // an accepted carrier instead of being replaced. There is no prefix or
        // partial matching: "DHL Paket" or "dhl-de" do not resolve to "dhl".
        $sNormalizedNeedle = $this->normalizeCarrier($sNeedle);
        if ('' === $sNormalizedNeedle) {
            return false;
        }

        foreach ($aAllowed as $sId => $sName) {
            if ($this->normalizeCarrier($sId) === $sNormalizedNeedle
                || $this->normalizeCarrier($sName) === $sNormalizedNeedle
            ) {
                return $sId;
            }
        }

        return false;
    }

    /**
     * Reduces a carrier to its comparable core by stripping everything that is
     * not a letter or a digit, so that spelling variants can be compared.
     *
     * @param string $sCarrier
     *
     * @return string
     */
    protected function normalizeCarrier($sCarrier) {
        return preg_replace('/[^a-z0-9]/', '', strtolower($sCarrier));
    }

    /**
     * Carriers accepted by the marketplace, as carrierId => carrierName.
     * The list rarely changes, so the response is cached.
     *
     * @return array
     *   Empty array if the list could not be retrieved.
     */
    protected function getAllowedCarriers() {
        if (array_key_exists($this->mpID, self::$aAllowedCarriers)) {
            return self::$aAllowedCarriers[$this->mpID];
        }

        self::$aAllowedCarriers[$this->mpID] = array();
        try {
            $aResponse = MagnaConnector::gi()->submitRequestCached(array_merge(
                $this->getBaseRequest(),
                array('ACTION' => 'GetCarriers')
            ), 86400);
            if (is_array($aResponse) && isset($aResponse['DATA']) && is_array($aResponse['DATA'])) {
                self::$aAllowedCarriers[$this->mpID] = $aResponse['DATA'];
            }
        } catch (MagnaException $me) {
            // Keep the empty list, the resolved carrier is submitted unchanged.
        }

        return self::$aAllowedCarriers[$this->mpID];
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
            'key' => 'orderstatus.cancelreason',
            'default' => false,
        );
        return $parent;
    }
}
