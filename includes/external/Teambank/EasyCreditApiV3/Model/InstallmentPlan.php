<?php
/**
 * InstallmentPlan
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
 * InstallmentPlan Class Doc Comment
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class InstallmentPlan implements ModelInterface, ArrayAccess, \JsonSerializable
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'InstallmentPlan';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'totalValuePlan' => 'float',
        'interests' => '\Teambank\EasyCreditApiV3\Model\Interests',
        'paymentPlan' => '\Teambank\EasyCreditApiV3\Model\PaymentPlan'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'totalValuePlan' => null,
        'interests' => null,
        'paymentPlan' => null
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
        'totalValuePlan' => 'totalValuePlan',
        'interests' => 'interests',
        'paymentPlan' => 'paymentPlan'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'totalValuePlan' => 'setTotalValuePlan',
        'interests' => 'setInterests',
        'paymentPlan' => 'setPaymentPlan'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'totalValuePlan' => 'getTotalValuePlan',
        'interests' => 'getInterests',
        'paymentPlan' => 'getPaymentPlan'
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
        $this->container['totalValuePlan'] = $data['totalValuePlan'] ?? null;
        $this->container['interests'] = $data['interests'] ?? null;
        $this->container['paymentPlan'] = $data['paymentPlan'] ?? null;
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
     * Gets totalValuePlan
     *
     * @return float|null
     */
    public function getTotalValuePlan()
    {
        return $this->container['totalValuePlan'];
    }

    /**
     * Sets totalValuePlan
     *
     * @param float|null $totalValuePlan Amount of the installment plan total value in € ( = Gesamtsumme in €)
     *
     * @return self
     */
    public function setTotalValuePlan($totalValuePlan)
    {
        $this->container['totalValuePlan'] = $totalValuePlan;

        return $this;
    }

    /**
     * Gets interests
     *
     * @return \Teambank\EasyCreditApiV3\Model\Interests|null
     */
    public function getInterests()
    {
        return $this->container['interests'];
    }

    /**
     * Sets interests
     *
     * @param \Teambank\EasyCreditApiV3\Model\Interests|null $interests interests
     *
     * @return self
     */
    public function setInterests($interests)
    {
        $this->container['interests'] = $interests;

        return $this;
    }

    /**
     * Gets paymentPlan
     *
     * @return \Teambank\EasyCreditApiV3\Model\PaymentPlan|null
     */
    public function getPaymentPlan()
    {
        return $this->container['paymentPlan'];
    }

    /**
     * Sets paymentPlan
     *
     * @param \Teambank\EasyCreditApiV3\Model\PaymentPlan|null $paymentPlan paymentPlan
     *
     * @return self
     */
    public function setPaymentPlan($paymentPlan)
    {
        $this->container['paymentPlan'] = $paymentPlan;

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


