<?php
/**
 * Employment
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
 * Employment Class Doc Comment
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class Employment implements ModelInterface, ArrayAccess, \JsonSerializable
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'Employment';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'employmentType' => 'string',
        'monthlyNetIncome' => 'float'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'employmentType' => null,
        'monthlyNetIncome' => null
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
        'employmentType' => 'employmentType',
        'monthlyNetIncome' => 'monthlyNetIncome'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'employmentType' => 'setEmploymentType',
        'monthlyNetIncome' => 'setMonthlyNetIncome'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'employmentType' => 'getEmploymentType',
        'monthlyNetIncome' => 'getMonthlyNetIncome'
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

    const EMPLOYMENT_TYPE_EMPLOYEE = 'EMPLOYEE';
    const EMPLOYMENT_TYPE_EMPLOYEE_PUBLIC_SECTOR = 'EMPLOYEE_PUBLIC_SECTOR';
    const EMPLOYMENT_TYPE_WORKER = 'WORKER';
    const EMPLOYMENT_TYPE_CIVIL_SERVANT = 'CIVIL_SERVANT';
    const EMPLOYMENT_TYPE_RETIREE = 'RETIREE';
    const EMPLOYMENT_TYPE_SELF_EMPLOYED = 'SELF_EMPLOYED';
    const EMPLOYMENT_TYPE_UNEMPLOYED = 'UNEMPLOYED';
    const EMPLOYMENT_TYPE_OTHER = 'OTHER';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getEmploymentTypeAllowableValues()
    {
        return [
            self::EMPLOYMENT_TYPE_EMPLOYEE,
            self::EMPLOYMENT_TYPE_EMPLOYEE_PUBLIC_SECTOR,
            self::EMPLOYMENT_TYPE_WORKER,
            self::EMPLOYMENT_TYPE_CIVIL_SERVANT,
            self::EMPLOYMENT_TYPE_RETIREE,
            self::EMPLOYMENT_TYPE_SELF_EMPLOYED,
            self::EMPLOYMENT_TYPE_UNEMPLOYED,
            self::EMPLOYMENT_TYPE_OTHER,
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
        $this->container['employmentType'] = $data['employmentType'] ?? null;
        $this->container['monthlyNetIncome'] = $data['monthlyNetIncome'] ?? null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        $allowedValues = $this->getEmploymentTypeAllowableValues();
        if (!is_null($this->container['employmentType']) && !in_array($this->container['employmentType'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'employmentType', must be one of '%s'",
                $this->container['employmentType'],
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
     * Gets employmentType
     *
     * @return string|null
     */
    public function getEmploymentType()
    {
        return $this->container['employmentType'];
    }

    /**
     * Sets employmentType
     *
     * @param string|null $employmentType EMPLOYEE = ANGESTELLTER, EMPLOYEE_PUBLIC_SECTOR = ANGESTELLTER_OEFFENTLICHER_DIENST, WORKER = ARBEITER, CIVIL_SERVANT = BEAMTER, RETIREE = RENTNER, SELF_EMPLOYED = SELBSTAENDIGER, UNEMPLOYED = ARBEITSLOSER, OTHER = SONSTIGES
     *
     * @return self
     */
    public function setEmploymentType($employmentType)
    {
        $allowedValues = $this->getEmploymentTypeAllowableValues();
        if (!is_null($employmentType) && !in_array($employmentType, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'employmentType', must be one of '%s'",
                    $employmentType,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['employmentType'] = $employmentType;

        return $this;
    }

    /**
     * Gets monthlyNetIncome
     *
     * @return float|null
     */
    public function getMonthlyNetIncome()
    {
        return $this->container['monthlyNetIncome'];
    }

    /**
     * Sets monthlyNetIncome
     *
     * @param float|null $monthlyNetIncome Income in €
     *
     * @return self
     */
    public function setMonthlyNetIncome($monthlyNetIncome)
    {
        $this->container['monthlyNetIncome'] = $monthlyNetIncome;

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


