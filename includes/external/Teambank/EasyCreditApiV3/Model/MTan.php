<?php
/**
 * MTan
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
 * MTan Class Doc Comment
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class MTan implements ModelInterface, ArrayAccess, \JsonSerializable
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'MTan';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'required' => 'bool',
        'status' => 'string',
        'remainingAttempts' => 'int',
        'successful' => 'bool',
        'mobilePhoneNumberInvalid' => 'bool',
        'skipMobilePhoneNumberValidation' => 'bool'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'required' => null,
        'status' => null,
        'remainingAttempts' => null,
        'successful' => null,
        'mobilePhoneNumberInvalid' => null,
        'skipMobilePhoneNumberValidation' => null
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
        'required' => 'required',
        'status' => 'status',
        'remainingAttempts' => 'remainingAttempts',
        'successful' => 'successful',
        'mobilePhoneNumberInvalid' => 'mobilePhoneNumberInvalid',
        'skipMobilePhoneNumberValidation' => 'skipMobilePhoneNumberValidation'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'required' => 'setRequired',
        'status' => 'setStatus',
        'remainingAttempts' => 'setRemainingAttempts',
        'successful' => 'setSuccessful',
        'mobilePhoneNumberInvalid' => 'setMobilePhoneNumberInvalid',
        'skipMobilePhoneNumberValidation' => 'setSkipMobilePhoneNumberValidation'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'required' => 'getRequired',
        'status' => 'getStatus',
        'remainingAttempts' => 'getRemainingAttempts',
        'successful' => 'getSuccessful',
        'mobilePhoneNumberInvalid' => 'getMobilePhoneNumberInvalid',
        'skipMobilePhoneNumberValidation' => 'getSkipMobilePhoneNumberValidation'
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

    const STATUS_PENDING = 'PENDING';
    const STATUS_FINISHED = 'FINISHED';
    const STATUS_FAILED = 'FAILED';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getStatusAllowableValues()
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_FINISHED,
            self::STATUS_FAILED,
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
        $this->container['required'] = $data['required'] ?? null;
        $this->container['status'] = $data['status'] ?? null;
        $this->container['remainingAttempts'] = $data['remainingAttempts'] ?? null;
        $this->container['successful'] = $data['successful'] ?? null;
        $this->container['mobilePhoneNumberInvalid'] = $data['mobilePhoneNumberInvalid'] ?? null;
        $this->container['skipMobilePhoneNumberValidation'] = $data['skipMobilePhoneNumberValidation'] ?? null;
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
     * Gets required
     *
     * @return bool|null
     */
    public function getRequired()
    {
        return $this->container['required'];
    }

    /**
     * Sets required
     *
     * @param bool|null $required required
     *
     * @return self
     */
    public function setRequired($required)
    {
        $this->container['required'] = $required;

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
     * @param string|null $status status
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
     * Gets remainingAttempts
     *
     * @return int|null
     */
    public function getRemainingAttempts()
    {
        return $this->container['remainingAttempts'];
    }

    /**
     * Sets remainingAttempts
     *
     * @param int|null $remainingAttempts remainingAttempts
     *
     * @return self
     */
    public function setRemainingAttempts($remainingAttempts)
    {
        $this->container['remainingAttempts'] = $remainingAttempts;

        return $this;
    }

    /**
     * Gets successful
     *
     * @return bool|null
     */
    public function getSuccessful()
    {
        return $this->container['successful'];
    }

    /**
     * Sets successful
     *
     * @param bool|null $successful successful
     *
     * @return self
     */
    public function setSuccessful($successful)
    {
        $this->container['successful'] = $successful;

        return $this;
    }

    /**
     * Gets mobilePhoneNumberInvalid
     *
     * @return bool|null
     */
    public function getMobilePhoneNumberInvalid()
    {
        return $this->container['mobilePhoneNumberInvalid'];
    }

    /**
     * Sets mobilePhoneNumberInvalid
     *
     * @param bool|null $mobilePhoneNumberInvalid mobilePhoneNumberInvalid
     *
     * @return self
     */
    public function setMobilePhoneNumberInvalid($mobilePhoneNumberInvalid)
    {
        $this->container['mobilePhoneNumberInvalid'] = $mobilePhoneNumberInvalid;

        return $this;
    }

    /**
     * Gets skipMobilePhoneNumberValidation
     *
     * @return bool|null
     */
    public function getSkipMobilePhoneNumberValidation()
    {
        return $this->container['skipMobilePhoneNumberValidation'];
    }

    /**
     * Sets skipMobilePhoneNumberValidation
     *
     * @param bool|null $skipMobilePhoneNumberValidation skipMobilePhoneNumberValidation
     *
     * @return self
     */
    public function setSkipMobilePhoneNumberValidation($skipMobilePhoneNumberValidation)
    {
        $this->container['skipMobilePhoneNumberValidation'] = $skipMobilePhoneNumberValidation;

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


