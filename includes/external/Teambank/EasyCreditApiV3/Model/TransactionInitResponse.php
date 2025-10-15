<?php
/**
 * TransactionInitResponse
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
 * TransactionInitResponse Class Doc Comment
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class TransactionInitResponse implements ModelInterface, ArrayAccess, \JsonSerializable
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'TransactionInitResponse';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'technicalTransactionId' => 'string',
        'transactionId' => 'string',
        'deviceIdentToken' => 'string',
        'redirectUrl' => 'string',
        'timestamp' => '\DateTime',
        'transactionInformation' => '\Teambank\EasyCreditApiV3\Model\TransactionInformation'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'technicalTransactionId' => null,
        'transactionId' => null,
        'deviceIdentToken' => null,
        'redirectUrl' => null,
        'timestamp' => 'date-time',
        'transactionInformation' => null
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
        'technicalTransactionId' => 'technicalTransactionId',
        'transactionId' => 'transactionId',
        'deviceIdentToken' => 'deviceIdentToken',
        'redirectUrl' => 'redirectUrl',
        'timestamp' => 'timestamp',
        'transactionInformation' => 'transactionInformation'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'technicalTransactionId' => 'setTechnicalTransactionId',
        'transactionId' => 'setTransactionId',
        'deviceIdentToken' => 'setDeviceIdentToken',
        'redirectUrl' => 'setRedirectUrl',
        'timestamp' => 'setTimestamp',
        'transactionInformation' => 'setTransactionInformation'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'technicalTransactionId' => 'getTechnicalTransactionId',
        'transactionId' => 'getTransactionId',
        'deviceIdentToken' => 'getDeviceIdentToken',
        'redirectUrl' => 'getRedirectUrl',
        'timestamp' => 'getTimestamp',
        'transactionInformation' => 'getTransactionInformation'
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
        $this->container['technicalTransactionId'] = $data['technicalTransactionId'] ?? null;
        $this->container['transactionId'] = $data['transactionId'] ?? null;
        $this->container['deviceIdentToken'] = $data['deviceIdentToken'] ?? null;
        $this->container['redirectUrl'] = $data['redirectUrl'] ?? null;
        $this->container['timestamp'] = $data['timestamp'] ?? null;
        $this->container['transactionInformation'] = $data['transactionInformation'] ?? null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

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
     * Gets technicalTransactionId
     *
     * @return string|null
     */
    public function getTechnicalTransactionId()
    {
        return $this->container['technicalTransactionId'];
    }

    /**
     * Sets technicalTransactionId
     *
     * @param string|null $technicalTransactionId Unique TeamBank transaction identifier
     *
     * @return self
     */
    public function setTechnicalTransactionId($technicalTransactionId)
    {
        $this->container['technicalTransactionId'] = $technicalTransactionId;

        return $this;
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
     * Gets deviceIdentToken
     *
     * @return string|null
     */
    public function getDeviceIdentToken()
    {
        return $this->container['deviceIdentToken'];
    }

    /**
     * Sets deviceIdentToken
     *
     * @param string|null $deviceIdentToken Verification token to identify the device
     *
     * @return self
     */
    public function setDeviceIdentToken($deviceIdentToken)
    {
        $this->container['deviceIdentToken'] = $deviceIdentToken;

        return $this;
    }

    /**
     * Gets redirectUrl
     *
     * @return string|null
     */
    public function getRedirectUrl()
    {
        return $this->container['redirectUrl'];
    }

    /**
     * Sets redirectUrl
     *
     * @param string|null $redirectUrl Redirect url
     *
     * @return self
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->container['redirectUrl'] = $redirectUrl;

        return $this;
    }

    /**
     * Gets timestamp
     *
     * @return \DateTime|null
     */
    public function getTimestamp()
    {
        return $this->container['timestamp'];
    }

    /**
     * Sets timestamp
     *
     * @param \DateTime|null $timestamp timestamp
     *
     * @return self
     */
    public function setTimestamp($timestamp)
    {
        $this->container['timestamp'] = $timestamp;

        return $this;
    }

    /**
     * Gets transactionInformation
     *
     * @return \Teambank\EasyCreditApiV3\Model\TransactionInformation|null
     */
    public function getTransactionInformation()
    {
        return $this->container['transactionInformation'];
    }

    /**
     * Sets transactionInformation
     *
     * @param \Teambank\EasyCreditApiV3\Model\TransactionInformation|null $transactionInformation transactionInformation
     *
     * @return self
     */
    public function setTransactionInformation($transactionInformation)
    {
        $this->container['transactionInformation'] = $transactionInformation;

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


