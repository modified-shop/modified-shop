<?php
/**
 * InstallmentPlanResponse
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
 * InstallmentPlanResponse Class Doc Comment
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class InstallmentPlanResponse implements ModelInterface, ArrayAccess, \JsonSerializable
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'InstallmentPlanResponse';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'installmentPlans' => '\Teambank\EasyCreditApiV3\Model\CalculatorInstallmentPlan[]',
        'minFinancingAmount' => 'int',
        'maxFinancingAmount' => 'int'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'installmentPlans' => null,
        'minFinancingAmount' => null,
        'maxFinancingAmount' => null
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
        'installmentPlans' => 'installmentPlans',
        'minFinancingAmount' => 'minFinancingAmount',
        'maxFinancingAmount' => 'maxFinancingAmount'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'installmentPlans' => 'setInstallmentPlans',
        'minFinancingAmount' => 'setMinFinancingAmount',
        'maxFinancingAmount' => 'setMaxFinancingAmount'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'installmentPlans' => 'getInstallmentPlans',
        'minFinancingAmount' => 'getMinFinancingAmount',
        'maxFinancingAmount' => 'getMaxFinancingAmount'
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
        $this->container['installmentPlans'] = $data['installmentPlans'] ?? null;
        $this->container['minFinancingAmount'] = $data['minFinancingAmount'] ?? null;
        $this->container['maxFinancingAmount'] = $data['maxFinancingAmount'] ?? null;
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
     * Gets installmentPlans
     *
     * @return \Teambank\EasyCreditApiV3\Model\CalculatorInstallmentPlan[]|null
     */
    public function getInstallmentPlans()
    {
        return $this->container['installmentPlans'];
    }

    /**
     * Sets installmentPlans
     *
     * @param \Teambank\EasyCreditApiV3\Model\CalculatorInstallmentPlan[]|null $installmentPlans installmentPlans
     *
     * @return self
     */
    public function setInstallmentPlans($installmentPlans)
    {
        $this->container['installmentPlans'] = $installmentPlans;

        return $this;
    }

    /**
     * Gets minFinancingAmount
     *
     * @return int|null
     */
    public function getMinFinancingAmount()
    {
        return $this->container['minFinancingAmount'];
    }

    /**
     * Sets minFinancingAmount
     *
     * @param int|null $minFinancingAmount minFinancingAmount
     *
     * @return self
     */
    public function setMinFinancingAmount($minFinancingAmount)
    {
        $this->container['minFinancingAmount'] = $minFinancingAmount;

        return $this;
    }

    /**
     * Gets maxFinancingAmount
     *
     * @return int|null
     */
    public function getMaxFinancingAmount()
    {
        return $this->container['maxFinancingAmount'];
    }

    /**
     * Sets maxFinancingAmount
     *
     * @param int|null $maxFinancingAmount maxFinancingAmount
     *
     * @return self
     */
    public function setMaxFinancingAmount($maxFinancingAmount)
    {
        $this->container['maxFinancingAmount'] = $maxFinancingAmount;

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


