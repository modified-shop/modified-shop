<?php
/**
 * Consent
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
 * Consent Class Doc Comment
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class Consent implements ModelInterface, ArrayAccess, \JsonSerializable
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'Consent';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'sepaMandate' => 'bool',
        'advertisement' => 'bool',
        'dataProcessing' => 'bool',
        'consentToActInOnwnName' => 'bool'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'sepaMandate' => null,
        'advertisement' => null,
        'dataProcessing' => null,
        'consentToActInOnwnName' => null
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
        'sepaMandate' => 'sepaMandate',
        'advertisement' => 'advertisement',
        'dataProcessing' => 'dataProcessing',
        'consentToActInOnwnName' => 'consentToActInOnwnName'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'sepaMandate' => 'setSepaMandate',
        'advertisement' => 'setAdvertisement',
        'dataProcessing' => 'setDataProcessing',
        'consentToActInOnwnName' => 'setConsentToActInOnwnName'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'sepaMandate' => 'getSepaMandate',
        'advertisement' => 'getAdvertisement',
        'dataProcessing' => 'getDataProcessing',
        'consentToActInOnwnName' => 'getConsentToActInOnwnName'
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
        $this->container['sepaMandate'] = $data['sepaMandate'] ?? null;
        $this->container['advertisement'] = $data['advertisement'] ?? null;
        $this->container['dataProcessing'] = $data['dataProcessing'] ?? null;
        $this->container['consentToActInOnwnName'] = $data['consentToActInOnwnName'] ?? null;
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
     * Gets sepaMandate
     *
     * @return bool|null
     */
    public function getSepaMandate()
    {
        return $this->container['sepaMandate'];
    }

    /**
     * Sets sepaMandate
     *
     * @param bool|null $sepaMandate sepaMandate
     *
     * @return self
     */
    public function setSepaMandate($sepaMandate)
    {
        $this->container['sepaMandate'] = $sepaMandate;

        return $this;
    }

    /**
     * Gets advertisement
     *
     * @return bool|null
     */
    public function getAdvertisement()
    {
        return $this->container['advertisement'];
    }

    /**
     * Sets advertisement
     *
     * @param bool|null $advertisement advertisement
     *
     * @return self
     */
    public function setAdvertisement($advertisement)
    {
        $this->container['advertisement'] = $advertisement;

        return $this;
    }

    /**
     * Gets dataProcessing
     *
     * @return bool|null
     */
    public function getDataProcessing()
    {
        return $this->container['dataProcessing'];
    }

    /**
     * Sets dataProcessing
     *
     * @param bool|null $dataProcessing dataProcessing
     *
     * @return self
     */
    public function setDataProcessing($dataProcessing)
    {
        $this->container['dataProcessing'] = $dataProcessing;

        return $this;
    }

    /**
     * Gets consentToActInOnwnName
     *
     * @return bool|null
     */
    public function getConsentToActInOnwnName()
    {
        return $this->container['consentToActInOnwnName'];
    }

    /**
     * Sets consentToActInOnwnName
     *
     * @param bool|null $consentToActInOnwnName consentToActInOnwnName
     *
     * @return self
     */
    public function setConsentToActInOnwnName($consentToActInOnwnName)
    {
        $this->container['consentToActInOnwnName'] = $consentToActInOnwnName;

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


