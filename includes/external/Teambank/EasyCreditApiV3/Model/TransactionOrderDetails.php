<?php
/**
 * TransactionOrderDetails
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
 * TransactionOrderDetails Class Doc Comment
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class TransactionOrderDetails implements ModelInterface, ArrayAccess, \JsonSerializable
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'TransactionOrderDetails';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'orderId' => 'string',
        'clearingDate' => '\DateTime',
        'orderDate' => '\DateTime',
        'currentOrderValue' => 'float',
        'originalOrderValue' => 'float'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'orderId' => null,
        'clearingDate' => 'date',
        'orderDate' => 'date',
        'currentOrderValue' => null,
        'originalOrderValue' => null
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
        'orderId' => 'orderId',
        'clearingDate' => 'clearingDate',
        'orderDate' => 'orderDate',
        'currentOrderValue' => 'currentOrderValue',
        'originalOrderValue' => 'originalOrderValue'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'orderId' => 'setOrderId',
        'clearingDate' => 'setClearingDate',
        'orderDate' => 'setOrderDate',
        'currentOrderValue' => 'setCurrentOrderValue',
        'originalOrderValue' => 'setOriginalOrderValue'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'orderId' => 'getOrderId',
        'clearingDate' => 'getClearingDate',
        'orderDate' => 'getOrderDate',
        'currentOrderValue' => 'getCurrentOrderValue',
        'originalOrderValue' => 'getOriginalOrderValue'
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
        $this->container['orderId'] = $data['orderId'] ?? null;
        $this->container['clearingDate'] = $data['clearingDate'] ?? null;
        $this->container['orderDate'] = $data['orderDate'] ?? null;
        $this->container['currentOrderValue'] = $data['currentOrderValue'] ?? null;
        $this->container['originalOrderValue'] = $data['originalOrderValue'] ?? null;
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
     * Gets orderId
     *
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->container['orderId'];
    }

    /**
     * Sets orderId
     *
     * @param string|null $orderId Order Id
     *
     * @return self
     */
    public function setOrderId($orderId)
    {
        $this->container['orderId'] = $orderId;

        return $this;
    }

    /**
     * Gets clearingDate
     *
     * @return \DateTime|null
     */
    public function getClearingDate()
    {
        return $this->container['clearingDate'];
    }

    /**
     * Sets clearingDate
     *
     * @param \DateTime|null $clearingDate Clearing date ( = abrechnungsdatum)
     *
     * @return self
     */
    public function setClearingDate($clearingDate)
    {
        $this->container['clearingDate'] = $clearingDate;

        return $this;
    }

    /**
     * Gets orderDate
     *
     * @return \DateTime|null
     */
    public function getOrderDate()
    {
        return $this->container['orderDate'];
    }

    /**
     * Sets orderDate
     *
     * @param \DateTime|null $orderDate Order date ( = bestelldatum)
     *
     * @return self
     */
    public function setOrderDate($orderDate)
    {
        $this->container['orderDate'] = $orderDate;

        return $this;
    }

    /**
     * Gets currentOrderValue
     *
     * @return float|null
     */
    public function getCurrentOrderValue()
    {
        return $this->container['currentOrderValue'];
    }

    /**
     * Sets currentOrderValue
     *
     * @param float|null $currentOrderValue Amount in € ( = aktuellerBestellwert in €)
     *
     * @return self
     */
    public function setCurrentOrderValue($currentOrderValue)
    {
        $this->container['currentOrderValue'] = $currentOrderValue;

        return $this;
    }

    /**
     * Gets originalOrderValue
     *
     * @return float|null
     */
    public function getOriginalOrderValue()
    {
        return $this->container['originalOrderValue'];
    }

    /**
     * Sets originalOrderValue
     *
     * @param float|null $originalOrderValue Amount in € ( = urspruenglicherBestellwert in €)
     *
     * @return self
     */
    public function setOriginalOrderValue($originalOrderValue)
    {
        $this->container['originalOrderValue'] = $originalOrderValue;

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


