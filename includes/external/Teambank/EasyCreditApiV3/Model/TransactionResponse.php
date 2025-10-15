<?php
/**
 * TransactionResponse
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 *
 * Transaction-V3 API Definition
 * @author   NETZKOLLEKTIV GmbH
 * @link     https://netzkollektiv.com

 */

namespace Teambank\EasyCreditApiV3\Model;

use \ArrayAccess;
use \Teambank\EasyCreditApiV3\ObjectSerializer;

/**
 * TransactionResponse Class Doc Comment
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class TransactionResponse implements ModelInterface, ArrayAccess, \JsonSerializable
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'TransactionResponse';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'transactionId' => 'string',
        'status' => 'string',
        'bookings' => '\Teambank\EasyCreditApiV3\Model\Booking[]',
        'customer' => '\Teambank\EasyCreditApiV3\Model\TransactionCustomer',
        'creditAccountNumber' => 'string',
        'orderDetails' => '\Teambank\EasyCreditApiV3\Model\TransactionOrderDetails',
        'refundDetails' => '\Teambank\EasyCreditApiV3\Model\Refund[]',
        'refundsTotalValue' => 'float',
        'expirationDateTime' => '\DateTime',
        'webshopId' => 'string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'transactionId' => null,
        'status' => null,
        'bookings' => null,
        'customer' => null,
        'creditAccountNumber' => null,
        'orderDetails' => null,
        'refundDetails' => null,
        'refundsTotalValue' => null,
        'expirationDateTime' => 'date-time',
        'webshopId' => null
    ];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPITypes()
    {
        return self::$openAPITypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPIFormats()
    {
        return self::$openAPIFormats;
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'transactionId' => 'transactionId',
        'status' => 'status',
        'bookings' => 'bookings',
        'customer' => 'customer',
        'creditAccountNumber' => 'creditAccountNumber',
        'orderDetails' => 'orderDetails',
        'refundDetails' => 'refundDetails',
        'refundsTotalValue' => 'refundsTotalValue',
        'expirationDateTime' => 'expirationDateTime',
        'webshopId' => 'webshopId'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'transactionId' => 'setTransactionId',
        'status' => 'setStatus',
        'bookings' => 'setBookings',
        'customer' => 'setCustomer',
        'creditAccountNumber' => 'setCreditAccountNumber',
        'orderDetails' => 'setOrderDetails',
        'refundDetails' => 'setRefundDetails',
        'refundsTotalValue' => 'setRefundsTotalValue',
        'expirationDateTime' => 'setExpirationDateTime',
        'webshopId' => 'setWebshopId'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'transactionId' => 'getTransactionId',
        'status' => 'getStatus',
        'bookings' => 'getBookings',
        'customer' => 'getCustomer',
        'creditAccountNumber' => 'getCreditAccountNumber',
        'orderDetails' => 'getOrderDetails',
        'refundDetails' => 'getRefundDetails',
        'refundsTotalValue' => 'getRefundsTotalValue',
        'expirationDateTime' => 'getExpirationDateTime',
        'webshopId' => 'getWebshopId'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$openAPIModelName;
    }

    const STATUS_REPORT_CAPTURE = 'REPORT_CAPTURE';
    const STATUS_REPORT_CAPTURE_EXPIRING = 'REPORT_CAPTURE_EXPIRING';
    const STATUS_IN_BILLING = 'IN_BILLING';
    const STATUS_BILLED = 'BILLED';
    const STATUS_EXPIRED = 'EXPIRED';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getStatusAllowableValues()
    {
        return [
            self::STATUS_REPORT_CAPTURE,
            self::STATUS_REPORT_CAPTURE_EXPIRING,
            self::STATUS_IN_BILLING,
            self::STATUS_BILLED,
            self::STATUS_EXPIRED,
        ];
    }

    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->container['transactionId'] = $data['transactionId'] ?? null;
        $this->container['status'] = $data['status'] ?? null;
        $this->container['bookings'] = $data['bookings'] ?? null;
        $this->container['customer'] = $data['customer'] ?? null;
        $this->container['creditAccountNumber'] = $data['creditAccountNumber'] ?? null;
        $this->container['orderDetails'] = $data['orderDetails'] ?? null;
        $this->container['refundDetails'] = $data['refundDetails'] ?? null;
        $this->container['refundsTotalValue'] = $data['refundsTotalValue'] ?? null;
        $this->container['expirationDateTime'] = $data['expirationDateTime'] ?? null;
        $this->container['webshopId'] = $data['webshopId'] ?? null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        $allowedValues = $this->getStatusAllowableValues();
        if (!is_null($this->container['status']) && !in_array($this->container['status'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'status', must be one of '%s'",
                $this->container['status'],
                implode("', '", $allowedValues)
            );
        }

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets transactionId
     *
     * @return string|null
     */
    public function getTransactionId()
    {
        return $this->container['transactionId'];
    }

    /**
     * Sets transactionId
     *
     * @param string|null $transactionId Unique functional transaction identifier (consists of 6 characters)
     *
     * @return self
     */
    public function setTransactionId($transactionId)
    {
        $this->container['transactionId'] = $transactionId;

        return $this;
    }

    /**
     * Gets status
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->container['status'];
    }

    /**
     * Sets status
     *
     * @param string|null $status Status structure = <Merchant-Transaction-Status>_<Booking-Status> -> Merchant-Transaction-Status are REPORT_CAPTURE (LIEFERUNG_MELDEN), REPORT_CAPTURE_EXPIRING (LIEFERUNG_MELDEN_AUSLAUFEND), IN_BILLING (IN_ABRECHNUNG), BILLED (ABGERECHNET), EXPIRED (ABGELAUFEN). Applicable Booking-Status for this scenario are FAILED, PENDING
     *
     * @return self
     */
    public function setStatus($status)
    {
        $allowedValues = $this->getStatusAllowableValues();
        if (!is_null($status) && !in_array($status, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'status', must be one of '%s'",
                    $status,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['status'] = $status;

        return $this;
    }

    /**
     * Gets bookings
     *
     * @return \Teambank\EasyCreditApiV3\Model\Booking[]|null
     */
    public function getBookings()
    {
        return $this->container['bookings'];
    }

    /**
     * Sets bookings
     *
     * @param \Teambank\EasyCreditApiV3\Model\Booking[]|null $bookings bookings
     *
     * @return self
     */
    public function setBookings($bookings)
    {
        $this->container['bookings'] = $bookings;

        return $this;
    }

    /**
     * Gets customer
     *
     * @return \Teambank\EasyCreditApiV3\Model\TransactionCustomer|null
     */
    public function getCustomer()
    {
        return $this->container['customer'];
    }

    /**
     * Sets customer
     *
     * @param \Teambank\EasyCreditApiV3\Model\TransactionCustomer|null $customer customer
     *
     * @return self
     */
    public function setCustomer($customer)
    {
        $this->container['customer'] = $customer;

        return $this;
    }

    /**
     * Gets creditAccountNumber
     *
     * @return string|null
     */
    public function getCreditAccountNumber()
    {
        return $this->container['creditAccountNumber'];
    }

    /**
     * Sets creditAccountNumber
     *
     * @param string|null $creditAccountNumber (= kreditKontonummer)
     *
     * @return self
     */
    public function setCreditAccountNumber($creditAccountNumber)
    {
        $this->container['creditAccountNumber'] = $creditAccountNumber;

        return $this;
    }

    /**
     * Gets orderDetails
     *
     * @return \Teambank\EasyCreditApiV3\Model\TransactionOrderDetails|null
     */
    public function getOrderDetails()
    {
        return $this->container['orderDetails'];
    }

    /**
     * Sets orderDetails
     *
     * @param \Teambank\EasyCreditApiV3\Model\TransactionOrderDetails|null $orderDetails orderDetails
     *
     * @return self
     */
    public function setOrderDetails($orderDetails)
    {
        $this->container['orderDetails'] = $orderDetails;

        return $this;
    }

    /**
     * Gets refundDetails
     *
     * @return \Teambank\EasyCreditApiV3\Model\Refund[]|null
     */
    public function getRefundDetails()
    {
        return $this->container['refundDetails'];
    }

    /**
     * Sets refundDetails
     *
     * @param \Teambank\EasyCreditApiV3\Model\Refund[]|null $refundDetails refundDetails
     *
     * @return self
     */
    public function setRefundDetails($refundDetails)
    {
        $this->container['refundDetails'] = $refundDetails;

        return $this;
    }

    /**
     * Gets refundsTotalValue
     *
     * @return float|null
     */
    public function getRefundsTotalValue()
    {
        return $this->container['refundsTotalValue'];
    }

    /**
     * Sets refundsTotalValue
     *
     * @param float|null $refundsTotalValue Sum of all the refund amounts in €
     *
     * @return self
     */
    public function setRefundsTotalValue($refundsTotalValue)
    {
        $this->container['refundsTotalValue'] = $refundsTotalValue;

        return $this;
    }

    /**
     * Gets expirationDateTime
     *
     * @return \DateTime|null
     */
    public function getExpirationDateTime()
    {
        return $this->container['expirationDateTime'];
    }

    /**
     * Sets expirationDateTime
     *
     * @param \DateTime|null $expirationDateTime Expiration date for transactions in state REPORT_CAPTURE
     *
     * @return self
     */
    public function setExpirationDateTime($expirationDateTime)
    {
        $this->container['expirationDateTime'] = $expirationDateTime;

        return $this;
    }

    /**
     * Gets webshopId
     *
     * @return string|null
     */
    public function getWebshopId()
    {
        return $this->container['webshopId'];
    }

    /**
     * Sets webshopId
     *
     * @param string|null $webshopId Webshop Id
     *
     * @return self
     */
    public function setWebshopId($webshopId)
    {
        $this->container['webshopId'] = $webshopId;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }

    /**
     * Sets value based on offset.
     *
     * @param int|null $offset Offset
     * @param mixed    $value  Value to be set
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed Returns data which can be serialized by json_encode(), which is a value
     * of any type other than a resource.
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
       return ObjectSerializer::sanitizeForSerialization($this);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(
            ObjectSerializer::sanitizeForSerialization($this),
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Gets a header-safe presentation of the object
     *
     * @return string
     */
    public function toHeaderValue()
    {
        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}


