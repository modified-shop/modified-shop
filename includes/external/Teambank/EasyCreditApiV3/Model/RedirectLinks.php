<?php
/**
 * RedirectLinks
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
 * RedirectLinks Class Doc Comment
 *
 * @category Class
 * @description Redirect url addresses in case of success, cancellation and denial
 * @package  Teambank\EasyCreditApiV3
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class RedirectLinks implements ModelInterface, ArrayAccess, \JsonSerializable
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'RedirectLinks';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'urlSuccess' => 'string',
        'urlCancellation' => 'string',
        'urlDenial' => 'string',
        'urlAuthorizationCallback' => 'string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'urlSuccess' => null,
        'urlCancellation' => null,
        'urlDenial' => null,
        'urlAuthorizationCallback' => null
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
        'urlSuccess' => 'urlSuccess',
        'urlCancellation' => 'urlCancellation',
        'urlDenial' => 'urlDenial',
        'urlAuthorizationCallback' => 'urlAuthorizationCallback'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'urlSuccess' => 'setUrlSuccess',
        'urlCancellation' => 'setUrlCancellation',
        'urlDenial' => 'setUrlDenial',
        'urlAuthorizationCallback' => 'setUrlAuthorizationCallback'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'urlSuccess' => 'getUrlSuccess',
        'urlCancellation' => 'getUrlCancellation',
        'urlDenial' => 'getUrlDenial',
        'urlAuthorizationCallback' => 'getUrlAuthorizationCallback'
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
        $this->container['urlSuccess'] = $data['urlSuccess'] ?? null;
        $this->container['urlCancellation'] = $data['urlCancellation'] ?? null;
        $this->container['urlDenial'] = $data['urlDenial'] ?? null;
        $this->container['urlAuthorizationCallback'] = $data['urlAuthorizationCallback'] ?? null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if ($this->container['urlSuccess'] === null) {
            $invalidProperties[] = "'urlSuccess' can't be null";
        }
        if ($this->container['urlCancellation'] === null) {
            $invalidProperties[] = "'urlCancellation' can't be null";
        }
        if ($this->container['urlDenial'] === null) {
            $invalidProperties[] = "'urlDenial' can't be null";
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
     * Gets urlSuccess
     *
     * @return string
     */
    public function getUrlSuccess()
    {
        return $this->container['urlSuccess'];
    }

    /**
     * Sets urlSuccess
     *
     * @param string $urlSuccess urlErfolg -> Return url address if the transaction is successful
     *
     * @return self
     */
    public function setUrlSuccess($urlSuccess)
    {
        $this->container['urlSuccess'] = $urlSuccess;

        return $this;
    }

    /**
     * Gets urlCancellation
     *
     * @return string
     */
    public function getUrlCancellation()
    {
        return $this->container['urlCancellation'];
    }

    /**
     * Sets urlCancellation
     *
     * @param string $urlCancellation urlAbbruch -> Return url address if the transaction is canceled
     *
     * @return self
     */
    public function setUrlCancellation($urlCancellation)
    {
        $this->container['urlCancellation'] = $urlCancellation;

        return $this;
    }

    /**
     * Gets urlDenial
     *
     * @return string
     */
    public function getUrlDenial()
    {
        return $this->container['urlDenial'];
    }

    /**
     * Sets urlDenial
     *
     * @param string $urlDenial urlAblehnung -> Return url address if the transaction is denied
     *
     * @return self
     */
    public function setUrlDenial($urlDenial)
    {
        $this->container['urlDenial'] = $urlDenial;

        return $this;
    }

    /**
     * Gets urlAuthorizationCallback
     *
     * @return string|null
     */
    public function getUrlAuthorizationCallback()
    {
        return $this->container['urlAuthorizationCallback'];
    }

    /**
     * Sets urlAuthorizationCallback
     *
     * @param string|null $urlAuthorizationCallback ' Optional Callback-Url for authorization endpoint. If provided the callback will be performed, else not. '
     *
     * @return self
     */
    public function setUrlAuthorizationCallback($urlAuthorizationCallback)
    {
        $this->container['urlAuthorizationCallback'] = $urlAuthorizationCallback;

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


