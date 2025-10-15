<?php
/**
 * OrderDetails
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
 * OrderDetails Class Doc Comment
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class OrderDetails implements ModelInterface, ArrayAccess, \JsonSerializable
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'OrderDetails';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'orderValue' => 'float',
        'orderId' => 'string',
        'numberOfProductsInShoppingCart' => 'int',
        'withoutFlexprice' => 'bool',
        'invoiceAddress' => '\Teambank\EasyCreditApiV3\Model\InvoiceAddress',
        'shippingAddress' => '\Teambank\EasyCreditApiV3\Model\ShippingAddress',
        'shoppingCartInformation' => '\Teambank\EasyCreditApiV3\Model\ShoppingCartInformationItem[]'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'orderValue' => null,
        'orderId' => null,
        'numberOfProductsInShoppingCart' => null,
        'withoutFlexprice' => null,
        'invoiceAddress' => null,
        'shippingAddress' => null,
        'shoppingCartInformation' => null
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
        'orderValue' => 'orderValue',
        'orderId' => 'orderId',
        'numberOfProductsInShoppingCart' => 'numberOfProductsInShoppingCart',
        'withoutFlexprice' => 'withoutFlexprice',
        'invoiceAddress' => 'invoiceAddress',
        'shippingAddress' => 'shippingAddress',
        'shoppingCartInformation' => 'shoppingCartInformation'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'orderValue' => 'setOrderValue',
        'orderId' => 'setOrderId',
        'numberOfProductsInShoppingCart' => 'setNumberOfProductsInShoppingCart',
        'withoutFlexprice' => 'setWithoutFlexprice',
        'invoiceAddress' => 'setInvoiceAddress',
        'shippingAddress' => 'setShippingAddress',
        'shoppingCartInformation' => 'setShoppingCartInformation'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'orderValue' => 'getOrderValue',
        'orderId' => 'getOrderId',
        'numberOfProductsInShoppingCart' => 'getNumberOfProductsInShoppingCart',
        'withoutFlexprice' => 'getWithoutFlexprice',
        'invoiceAddress' => 'getInvoiceAddress',
        'shippingAddress' => 'getShippingAddress',
        'shoppingCartInformation' => 'getShoppingCartInformation'
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
        $this->container['orderValue'] = $data['orderValue'] ?? null;
        $this->container['orderId'] = $data['orderId'] ?? null;
        $this->container['numberOfProductsInShoppingCart'] = $data['numberOfProductsInShoppingCart'] ?? null;
        $this->container['withoutFlexprice'] = $data['withoutFlexprice'] ?? false;
        $this->container['invoiceAddress'] = $data['invoiceAddress'] ?? null;
        $this->container['shippingAddress'] = $data['shippingAddress'] ?? null;
        $this->container['shoppingCartInformation'] = $data['shoppingCartInformation'] ?? null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if ($this->container['orderValue'] === null) {
            $invalidProperties[] = "'orderValue' can't be null";
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
     * Gets orderValue
     *
     * @return float
     */
    public function getOrderValue()
    {
        return $this->container['orderValue'];
    }

    /**
     * Sets orderValue
     *
     * @param float $orderValue Amount in €
     *
     * @return self
     */
    public function setOrderValue($orderValue)
    {
        $this->container['orderValue'] = $orderValue;

        return $this;
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
     * @param string|null $orderId Shop transaction identifier (allows the shop to store its own reference for the transaction)
     *
     * @return self
     */
    public function setOrderId($orderId)
    {
        $this->container['orderId'] = $orderId;

        return $this;
    }

    /**
     * Gets numberOfProductsInShoppingCart
     *
     * @return int|null
     */
    public function getNumberOfProductsInShoppingCart()
    {
        return $this->container['numberOfProductsInShoppingCart'];
    }

    /**
     * Sets numberOfProductsInShoppingCart
     *
     * @param int|null $numberOfProductsInShoppingCart anzahlProdukteImWarenkorb
     *
     * @return self
     */
    public function setNumberOfProductsInShoppingCart($numberOfProductsInShoppingCart)
    {
        $this->container['numberOfProductsInShoppingCart'] = $numberOfProductsInShoppingCart;

        return $this;
    }

    /**
     * Gets withoutFlexprice
     *
     * @return bool|null
     */
    public function getWithoutFlexprice()
    {
        return $this->container['withoutFlexprice'];
    }

    /**
     * Sets withoutFlexprice
     *
     * @param bool|null $withoutFlexprice Indicator if a flexprice should NOT be used if available
     *
     * @return self
     */
    public function setWithoutFlexprice($withoutFlexprice)
    {
        $this->container['withoutFlexprice'] = $withoutFlexprice;

        return $this;
    }

    /**
     * Gets invoiceAddress
     *
     * @return \Teambank\EasyCreditApiV3\Model\InvoiceAddress|null
     */
    public function getInvoiceAddress()
    {
        return $this->container['invoiceAddress'];
    }

    /**
     * Sets invoiceAddress
     *
     * @param \Teambank\EasyCreditApiV3\Model\InvoiceAddress|null $invoiceAddress invoiceAddress
     *
     * @return self
     */
    public function setInvoiceAddress($invoiceAddress)
    {
        $this->container['invoiceAddress'] = $invoiceAddress;

        return $this;
    }

    /**
     * Gets shippingAddress
     *
     * @return \Teambank\EasyCreditApiV3\Model\ShippingAddress|null
     */
    public function getShippingAddress()
    {
        return $this->container['shippingAddress'];
    }

    /**
     * Sets shippingAddress
     *
     * @param \Teambank\EasyCreditApiV3\Model\ShippingAddress|null $shippingAddress shippingAddress
     *
     * @return self
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->container['shippingAddress'] = $shippingAddress;

        return $this;
    }

    /**
     * Gets shoppingCartInformation
     *
     * @return \Teambank\EasyCreditApiV3\Model\ShoppingCartInformationItem[]|null
     */
    public function getShoppingCartInformation()
    {
        return $this->container['shoppingCartInformation'];
    }

    /**
     * Sets shoppingCartInformation
     *
     * @param \Teambank\EasyCreditApiV3\Model\ShoppingCartInformationItem[]|null $shoppingCartInformation shoppingCartInformation
     *
     * @return self
     */
    public function setShoppingCartInformation($shoppingCartInformation)
    {
        $this->container['shoppingCartInformation'] = $shoppingCartInformation;

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


