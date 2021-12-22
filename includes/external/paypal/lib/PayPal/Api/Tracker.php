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
 * @property string tracking_number_type
 * @property string status
 * @property string shipment_date
 * @property string carrier
 * @property string carrier_name_other
 * @property string postage_payment_id
 * @property string notify_buyer
 * @property string quantity
 * @property string tracking_number_validated
 * @property string last_updated_time
 */
class Tracker extends PayPalModel
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

    /**
     * The type of tracking number.
     * Valid Values: ["CARRIER_PROVIDED", "E2E_PARTNER_PROVIDED"]
     *
     * @param string $tracking_number_type
     * 
     * @return $this
     */
    public function setTrackingNumberType($tracking_number_type)
    {
        $this->tracking_number_type = $tracking_number_type;
        return $this;
    }

    /**
     * The tracking number for the shipment.
     *
     * @return string
     */
    public function getTrackingNumberType()
    {
        return $this->tracking_number_type;
    }

    /**
     * The status of the item shipment.
     * Valid Values: ["SHIPPED", "ON_HOLD", "DELIVERED", "CANCELLED"]
     *
     * @param string $status
     * 
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * The status of the subscription. 
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The date when the shipment occurred. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @param string $shipment_date
     * 
     * @return $this
     */
    public function setShipmentDate($shipment_date)
    {
        $this->shipment_date = $shipment_date;
        return $this;
    }

    /**
     * The date when the shipment occurred. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @return string
     */
    public function getShipmentDate()
    {
        return $this->shipment_date;
    }

    /**
     * The carrier for the shipment.
     * Valid Values: ["DHL", "DE_GLS", "DPD", "DE_DHL_PARCEL", "AT_AUSTRIAN_POST_EMS", "DE_HERMES", "DE_TNT", "DE_FEDEX", "DE_DHL_PACKET", "DE_DEUTSCHE", "DE_DHL_EXPRESS", "DE_OTHER"]
     *
     * @param string $carrier
     * 
     * @return $this
     */
    public function setCarrier($carrier)
    {
        $this->carrier = $carrier;
        return $this;
    }

    /**
     * The carrier for the shipment.
     *
     * @return string
     */
    public function getCarrier()
    {
        return $this->carrier;
    }

    /**
     * The name of the carrier for the shipment. Provide this value only if the carrier parameter is OTHER.
     *
     * @param string $carrier_name_other
     * 
     * @return $this
     */
    public function setCarrierNameOther($carrier_name_other)
    {
        $this->carrier_name_other = $carrier_name_other;
        return $this;
    }

    /**
     * The name of the carrier for the shipment. Provide this value only if the carrier parameter is OTHER.
     *
     * @return string
     */
    public function getCarrierNameOther()
    {
        return $this->carrier_name_other;
    }

    /**
     * The postage payment ID.
     *
     * @param string $postage_payment_id
     * 
     * @return $this
     */
    public function setPostagePaymentId($postage_payment_id)
    {
        $this->postage_payment_id = $postage_payment_id;
        return $this;
    }

    /**
     * The postage payment ID.
     *
     * @return string
     */
    public function getPostagePaymentId()
    {
        return $this->postage_payment_id;
    }

    /**
     * Sends an email notification to the buyer of the PayPal transaction.
     *
     * @param string $notify_buyer
     * 
     * @return $this
     */
    public function setNotifyBuyer($notify_buyer)
    {
        $this->notify_buyer = $notify_buyer;
        return $this;
    }

    /**
     * Sends an email notification to the buyer of the PayPal transaction.
     *
     * @return string
     */
    public function getNotifyBuyer()
    {
        return $this->notify_buyer;
    }

    /**
     * The quantity of the product in the subscription. 
     *
     * @param string $quantity
     * 
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * The quantity of the product in the subscription. 
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Indicates whether the carrier validated the tracking number.
     *
     * @param string $tracking_number_validated
     * 
     * @return $this
     */
    public function setTrackingNumberValidated($tracking_number_validated)
    {
        $this->tracking_number_validated = $tracking_number_validated;
        return $this;
    }

    /**
     * Indicates whether the carrier validated the tracking number.
     *
     * @return string
     */
    public function getTrackingNumberValidated()
    {
        return $this->tracking_number_validated;
    }

    /**
     * The date and time when the tracking information was last updated. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @param string $last_updated_time
     * 
     * @return $this
     */
    public function setLastUpdateTime($last_updated_time)
    {
        $this->last_updated_time = $last_updated_time;
        return $this;
    }

    /**
     * The date and time when the tracking information was last updated. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @return string
     */
    public function getLastUpdateTime()
    {
        return $this->last_updated_time;
    }

}
