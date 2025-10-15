<?php
/**
 * Refund
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
 * Refund Class Doc Comment
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class Refund implements ModelInterface, ArrayAccess, \JsonSerializable
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'Refund';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'refundAmount' => 'float',
        'refundBookingDate' => '\DateTime',
        'refundEntryDate' => '\DateTime',
        'refundDate' => '\DateTime',
        'reason' => 'string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'refundAmount' => null,
        'refundBookingDate' => 'date',
        'refundEntryDate' => 'date',
        'refundDate' => 'date',
        'reason' => null
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
        'refundAmount' => 'refundAmount',
        'refundBookingDate' => 'refundBookingDate',
        'refundEntryDate' => 'refundEntryDate',
        'refundDate' => 'refundDate',
        'reason' => 'reason'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'refundAmount' => 'setRefundAmount',
        'refundBookingDate' => 'setRefundBookingDate',
        'refundEntryDate' => 'setRefundEntryDate',
        'refundDate' => 'setRefundDate',
        'reason' => 'setReason'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'refundAmount' => 'getRefundAmount',
        'refundBookingDate' => 'getRefundBookingDate',
        'refundEntryDate' => 'getRefundEntryDate',
        'refundDate' => 'getRefundDate',
        'reason' => 'getReason'
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

    const REASON_REVOCATION_FULL = 'REVOCATION_FULL';
    const REASON_REVOCATION_PARTIAL = 'REVOCATION_PARTIAL';
    const REASON_REFUND_GUARANTEE_WARRANTY = 'REFUND_GUARANTEE_WARRANTY';
    const REASON_REDUCTION_GUARANTEE_WARRANTY = 'REDUCTION_GUARANTEE_WARRANTY';
    const REASON_REVOCATION_FINANCING = 'REVOCATION_FINANCING';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getReasonAllowableValues()
    {
        return [
            self::REASON_REVOCATION_FULL,
            self::REASON_REVOCATION_PARTIAL,
            self::REASON_REFUND_GUARANTEE_WARRANTY,
            self::REASON_REDUCTION_GUARANTEE_WARRANTY,
            self::REASON_REVOCATION_FINANCING,
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
        $this->container['refundAmount'] = $data['refundAmount'] ?? null;
        $this->container['refundBookingDate'] = $data['refundBookingDate'] ?? null;
        $this->container['refundEntryDate'] = $data['refundEntryDate'] ?? null;
        $this->container['refundDate'] = $data['refundDate'] ?? null;
        $this->container['reason'] = $data['reason'] ?? null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        $allowedValues = $this->getReasonAllowableValues();
        if (!is_null($this->container['reason']) && !in_array($this->container['reason'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'reason', must be one of '%s'",
                $this->container['reason'],
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
     * Gets refundAmount
     *
     * @return float|null
     */
    public function getRefundAmount()
    {
        return $this->container['refundAmount'];
    }

    /**
     * Sets refundAmount
     *
     * @param float|null $refundAmount Amount in € ( = widerrufenerBetrag in €)
     *
     * @return self
     */
    public function setRefundAmount($refundAmount)
    {
        $this->container['refundAmount'] = $refundAmount;

        return $this;
    }

    /**
     * Gets refundBookingDate
     *
     * @return \DateTime|null
     */
    public function getRefundBookingDate()
    {
        return $this->container['refundBookingDate'];
    }

    /**
     * Sets refundBookingDate
     *
     * @param \DateTime|null $refundBookingDate ( = buchungsdatum)
     *
     * @return self
     */
    public function setRefundBookingDate($refundBookingDate)
    {
        $this->container['refundBookingDate'] = $refundBookingDate;

        return $this;
    }

    /**
     * Gets refundEntryDate
     *
     * @return \DateTime|null
     */
    public function getRefundEntryDate()
    {
        return $this->container['refundEntryDate'];
    }

    /**
     * Sets refundEntryDate
     *
     * @param \DateTime|null $refundEntryDate ( = eingabedatum)
     *
     * @return self
     */
    public function setRefundEntryDate($refundEntryDate)
    {
        $this->container['refundEntryDate'] = $refundEntryDate;

        return $this;
    }

    /**
     * Gets refundDate
     *
     * @return \DateTime|null
     */
    public function getRefundDate()
    {
        return $this->container['refundDate'];
    }

    /**
     * Sets refundDate
     *
     * @param \DateTime|null $refundDate ( = rueckabwicklungsdatum)
     *
     * @return self
     */
    public function setRefundDate($refundDate)
    {
        $this->container['refundDate'] = $refundDate;

        return $this;
    }

    /**
     * Gets reason
     *
     * @return string|null
     */
    public function getReason()
    {
        return $this->container['reason'];
    }

    /**
     * Sets reason
     *
     * @param string|null $reason Reason for refund -> REVOCATION_FULL (WIDERRUF_VOLLSTAENDIG), REVOCATION_PARTIAL (WIDERRUF_TEILWEISE), REFUND_GUARANTEE_WARRANTY (RUECKGABE_GARANTIE_GEWAEHRLEISTUNG), REDUCTION_GUARANTEE_WARRANTY (MINDERUNG_GARANTIE_GEWAEHRLEISTUNG), REVOCATION_FINANCING(WIDERRUF_FINANZIERUNG)
     *
     * @return self
     */
    public function setReason($reason)
    {
        $allowedValues = $this->getReasonAllowableValues();
        if (!is_null($reason) && !in_array($reason, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'reason', must be one of '%s'",
                    $reason,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['reason'] = $reason;

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


