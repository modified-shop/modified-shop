<?php
/**
 * ShoppingCartInformationItem
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
 * ShoppingCartInformationItem Class Doc Comment
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class ShoppingCartInformationItem implements ModelInterface, ArrayAccess, \JsonSerializable
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'ShoppingCartInformationItem';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'productName' => 'string',
        'quantity' => 'int',
        'price' => 'float',
        'manufacturer' => 'string',
        'productCategory' => 'string',
        'productImageUrl' => 'string',
        'productUrl' => 'string',
        'articleNumber' => '\Teambank\EasyCreditApiV3\Model\ArticleNumberItem[]'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'productName' => null,
        'quantity' => null,
        'price' => null,
        'manufacturer' => null,
        'productCategory' => null,
        'productImageUrl' => null,
        'productUrl' => null,
        'articleNumber' => null
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
        'productName' => 'productName',
        'quantity' => 'quantity',
        'price' => 'price',
        'manufacturer' => 'manufacturer',
        'productCategory' => 'productCategory',
        'productImageUrl' => 'productImageUrl',
        'productUrl' => 'productUrl',
        'articleNumber' => 'articleNumber'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'productName' => 'setProductName',
        'quantity' => 'setQuantity',
        'price' => 'setPrice',
        'manufacturer' => 'setManufacturer',
        'productCategory' => 'setProductCategory',
        'productImageUrl' => 'setProductImageUrl',
        'productUrl' => 'setProductUrl',
        'articleNumber' => 'setArticleNumber'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'productName' => 'getProductName',
        'quantity' => 'getQuantity',
        'price' => 'getPrice',
        'manufacturer' => 'getManufacturer',
        'productCategory' => 'getProductCategory',
        'productImageUrl' => 'getProductImageUrl',
        'productUrl' => 'getProductUrl',
        'articleNumber' => 'getArticleNumber'
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
        $this->container['productName'] = $data['productName'] ?? null;
        $this->container['quantity'] = $data['quantity'] ?? null;
        $this->container['price'] = $data['price'] ?? null;
        $this->container['manufacturer'] = $data['manufacturer'] ?? null;
        $this->container['productCategory'] = $data['productCategory'] ?? null;
        $this->container['productImageUrl'] = $data['productImageUrl'] ?? null;
        $this->container['productUrl'] = $data['productUrl'] ?? null;
        $this->container['articleNumber'] = $data['articleNumber'] ?? null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if ($this->container['productName'] === null) {
            $invalidProperties[] = "'productName' can't be null";
        }
        if ($this->container['quantity'] === null) {
            $invalidProperties[] = "'quantity' can't be null";
        }
        if ($this->container['price'] === null) {
            $invalidProperties[] = "'price' can't be null";
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
     * Gets productName
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->container['productName'];
    }

    /**
     * Sets productName
     *
     * @param string $productName productName
     *
     * @return self
     */
    public function setProductName($productName)
    {
        $this->container['productName'] = $productName;

        return $this;
    }

    /**
     * Gets quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->container['quantity'];
    }

    /**
     * Sets quantity
     *
     * @param int $quantity quantity
     *
     * @return self
     */
    public function setQuantity($quantity)
    {
        $this->container['quantity'] = $quantity;

        return $this;
    }

    /**
     * Gets price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->container['price'];
    }

    /**
     * Sets price
     *
     * @param float $price Amount in €
     *
     * @return self
     */
    public function setPrice($price)
    {
        $this->container['price'] = $price;

        return $this;
    }

    /**
     * Gets manufacturer
     *
     * @return string|null
     */
    public function getManufacturer()
    {
        return $this->container['manufacturer'];
    }

    /**
     * Sets manufacturer
     *
     * @param string|null $manufacturer manufacturer
     *
     * @return self
     */
    public function setManufacturer($manufacturer)
    {
        $this->container['manufacturer'] = $manufacturer;

        return $this;
    }

    /**
     * Gets productCategory
     *
     * @return string|null
     */
    public function getProductCategory()
    {
        return $this->container['productCategory'];
    }

    /**
     * Sets productCategory
     *
     * @param string|null $productCategory productCategory
     *
     * @return self
     */
    public function setProductCategory($productCategory)
    {
        $this->container['productCategory'] = $productCategory;

        return $this;
    }

    /**
     * Gets productImageUrl
     *
     * @return string|null
     */
    public function getProductImageUrl()
    {
        return $this->container['productImageUrl'];
    }

    /**
     * Sets productImageUrl
     *
     * @param string|null $productImageUrl productImageUrl
     *
     * @return self
     */
    public function setProductImageUrl($productImageUrl)
    {
        $this->container['productImageUrl'] = $productImageUrl;

        return $this;
    }

    /**
     * Gets productUrl
     *
     * @return string|null
     */
    public function getProductUrl()
    {
        return $this->container['productUrl'];
    }

    /**
     * Sets productUrl
     *
     * @param string|null $productUrl productUrl
     *
     * @return self
     */
    public function setProductUrl($productUrl)
    {
        $this->container['productUrl'] = $productUrl;

        return $this;
    }

    /**
     * Gets articleNumber
     *
     * @return \Teambank\EasyCreditApiV3\Model\ArticleNumberItem[]|null
     */
    public function getArticleNumber()
    {
        return $this->container['articleNumber'];
    }

    /**
     * Sets articleNumber
     *
     * @param \Teambank\EasyCreditApiV3\Model\ArticleNumberItem[]|null $articleNumber Article number of a product
     *
     * @return self
     */
    public function setArticleNumber($articleNumber)
    {
        $this->container['articleNumber'] = $articleNumber;

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


