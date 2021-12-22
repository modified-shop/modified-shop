<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class Tracker
 *
 * Creates a shipping tracker. 
 *
 * @package PayPal\Api
 *
 * @property string transaction_id
 * @property string tracking_number
 */
class TrackerIdentifier extends PayPalModel
{
    /**
     * The PayPal transaction ID.
     *
     * @param string $transaction_id
     * 
     * @return $this
     */
    public function setTransactionId($transaction_id)
    {
        $this->transaction_id = $transaction_id;
        return $this;
    }

    /**
     * The PayPal transaction ID.
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    /**
     * The tracking number for the shipment.
     *
     * @param string $tracking_number
     * 
     * @return $this
     */
    public function setTrackingNumber($tracking_number)
    {
        $this->tracking_number = $tracking_number;
        return $this;
    }

    /**
     * The tracking number for the shipment.
     *
     * @return string
     */
    public function getTrackingNumber()
    {
        return $this->tracking_number;
    }

}
