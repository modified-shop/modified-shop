<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;
use PayPal\Common\PayPalResourceModel;
use PayPal\Rest\ApiContext;
use PayPal\Transport\PayPalRestCall;
use PayPal\Validation\ArgumentValidator;
use PayPal\Core\PayPalConstants;

/**
 * Class Trackers
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
 * @property \PayPal\Api\Trackers[] trackers
 * @property \PayPal\Api\TrackerIdentifier[] $tracker_identifiers
 * @property \PayPal\Api\Error[] $errors
 */
class Shipping extends PayPalResourceModel
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

    /**
     * An array of tracking information for shipments.
     *
     * @param \PayPal\Api\Trackers[] $trackers
     * 
     * @return $this
     */
    public function setTrackers($trackers)
    {
        $this->trackers = $trackers;
        return $this;
    }

    /**
     * An array of tracking information for shipments.
     *
     * @return \PayPal\Api\Trackers[]
     */
    public function getTrackers()
    {
        return $this->trackers;
    }

    /**
     * Append Tracker to the list.
     *
     * @param \PayPal\Api\Trackers $trackers
     * @return $this
     */
    public function addTracker($trackers)
    {
        if (!$this->getTrackers()) {
            return $this->setTrackers(array($trackers));
        } else {
            return $this->setTrackers(
                array_merge($this->getTrackers(), array($trackers))
            );
        }
    }

    /**
     * An array of tracking IDs.
     *
     * @param \PayPal\Api\TrackerIdentifier[] $tracker_identifiers
     * 
     * @return $this
     */
    public function setTrackerIdentifiers($tracker_identifiers)
    {
        $this->tracker_identifiers = $tracker_identifiers;
        return $this;
    }

    /**
     * An array of tracking IDs.
     *
     * @return \PayPal\Api\TrackerIdentifier[]
     */
    public function getTrackerIdentifiers()
    {
        return $this->tracker_identifiers;
    }

    /**
     * Append Identifier to the list.
     *
     * @param \PayPal\Api\TrackerIdentifier $tracker_identifiers
     * 
     * @return $this
     */
    public function addTrackerIdentifiers($tracker_identifiers)
    {
        if (!$this->getTrackerIdentifiers()) {
            return $this->setTrackerIdentifiers(array($tracker_identifiers));
        } else {
            return $this->setTrackerIdentifiers(
                array_merge($this->getTrackerIdentifiers(), array($tracker_identifiers))
            );
        }
    }

    /**
     * A single ASCII error code from the following enum.
     *
     * @param string $errors
     * 
     * @return $this
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * A single ASCII error code from the following enum.
     *
     * @return \PayPal\Api\Error[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Retrieve the details for a subscription by passing the subscription ID to the request URI.
     *
     * @param string $transactionId
     * @param string $trackingNumber
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return Plan
     */
    public static function get($transactionId, $trackingNumber, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($transactionId, 'transactionId');
        ArgumentValidator::validate($trackingNumber, 'trackingNumber');
        $payLoad = "";
        $json = self::executeCall(
            "/v1/shipping/trackers/$transactionId-$trackingNumber",
            "GET",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );        
        $ret = new Shipping();
        $ret->fromJson($json);
        return $ret;
    }

    /**
     * Adds tracking information, with or without tracking numbers, for multiple PayPal transactions.
     *
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return Plan
     */
    public function create($apiContext = null, $restCall = null)
    {
        $payLoad = $this->toJSON();
        $json = self::executeCall(
            "/v1/shipping/trackers-batch",
            "POST",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $this->fromJson($json);
        return $this;
    }

    /**
     * Updates tracking information, with or without tracking numbers, for multiple PayPal transactions.
     *
     * @param PatchRequest $patchRequest
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return bool
     */
    public function replace($transactionId, $trackingNumber, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($transactionId, 'transactionId');
        ArgumentValidator::validate($trackingNumber, 'trackingNumber');
        $payLoad = $this->toJSON();
        self::executeCall(
            "/v1/shipping/trackers/$transactionId-$trackingNumber",
            "PUT",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        return true;
    }

}
