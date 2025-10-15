<?php
/**
 * Customer
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
 * Customer Class Doc Comment
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class Customer implements ModelInterface, ArrayAccess, \JsonSerializable
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'Customer';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'gender' => 'string',
        'firstName' => 'string',
        'lastName' => 'string',
        'birthDate' => '\DateTime',
        'birthName' => 'string',
        'birthPlace' => 'string',
        'title' => 'string',
        'contact' => '\Teambank\EasyCreditApiV3\Model\Contact',
        'bank' => '\Teambank\EasyCreditApiV3\Model\Bank',
        'employment' => '\Teambank\EasyCreditApiV3\Model\Employment',
        'companyName' => 'string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'gender' => null,
        'firstName' => null,
        'lastName' => null,
        'birthDate' => 'date',
        'birthName' => null,
        'birthPlace' => null,
        'title' => null,
        'contact' => null,
        'bank' => null,
        'employment' => null,
        'companyName' => null
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
        'gender' => 'gender',
        'firstName' => 'firstName',
        'lastName' => 'lastName',
        'birthDate' => 'birthDate',
        'birthName' => 'birthName',
        'birthPlace' => 'birthPlace',
        'title' => 'title',
        'contact' => 'contact',
        'bank' => 'bank',
        'employment' => 'employment',
        'companyName' => 'companyName'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'gender' => 'setGender',
        'firstName' => 'setFirstName',
        'lastName' => 'setLastName',
        'birthDate' => 'setBirthDate',
        'birthName' => 'setBirthName',
        'birthPlace' => 'setBirthPlace',
        'title' => 'setTitle',
        'contact' => 'setContact',
        'bank' => 'setBank',
        'employment' => 'setEmployment',
        'companyName' => 'setCompanyName'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'gender' => 'getGender',
        'firstName' => 'getFirstName',
        'lastName' => 'getLastName',
        'birthDate' => 'getBirthDate',
        'birthName' => 'getBirthName',
        'birthPlace' => 'getBirthPlace',
        'title' => 'getTitle',
        'contact' => 'getContact',
        'bank' => 'getBank',
        'employment' => 'getEmployment',
        'companyName' => 'getCompanyName'
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

    const GENDER_MR = 'MR';
    const GENDER_MRS = 'MRS';
    const GENDER_DIVERS = 'DIVERS';
    const GENDER_NO_GENDER = 'NO_GENDER';
    const TITLE_PROFDR = 'PROFDR';
    const TITLE_DR = 'DR';
    const TITLE_PROF = 'PROF';
    const TITLE_DRDR = 'DRDR';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getGenderAllowableValues()
    {
        return [
            self::GENDER_MR,
            self::GENDER_MRS,
            self::GENDER_DIVERS,
            self::GENDER_NO_GENDER,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getTitleAllowableValues()
    {
        return [
            self::TITLE_PROFDR,
            self::TITLE_DR,
            self::TITLE_PROF,
            self::TITLE_DRDR,
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
        $this->container['gender'] = $data['gender'] ?? null;
        $this->container['firstName'] = $data['firstName'] ?? null;
        $this->container['lastName'] = $data['lastName'] ?? null;
        $this->container['birthDate'] = $data['birthDate'] ?? null;
        $this->container['birthName'] = $data['birthName'] ?? null;
        $this->container['birthPlace'] = $data['birthPlace'] ?? null;
        $this->container['title'] = $data['title'] ?? null;
        $this->container['contact'] = $data['contact'] ?? null;
        $this->container['bank'] = $data['bank'] ?? null;
        $this->container['employment'] = $data['employment'] ?? null;
        $this->container['companyName'] = $data['companyName'] ?? null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        $allowedValues = $this->getGenderAllowableValues();
        if (!is_null($this->container['gender']) && !in_array($this->container['gender'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'gender', must be one of '%s'",
                $this->container['gender'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getTitleAllowableValues();
        if (!is_null($this->container['title']) && !in_array($this->container['title'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'title', must be one of '%s'",
                $this->container['title'],
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
     * Gets gender
     *
     * @return string|null
     */
    public function getGender()
    {
        return $this->container['gender'];
    }

    /**
     * Sets gender
     *
     * @param string|null $gender MR = HERR, MRS = FRAU, DIVERS = DIVERS, NO_GENDER = OHNE
     *
     * @return self
     */
    public function setGender($gender)
    {
        $allowedValues = $this->getGenderAllowableValues();
        if (!is_null($gender) && !in_array($gender, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'gender', must be one of '%s'",
                    $gender,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['gender'] = $gender;

        return $this;
    }

    /**
     * Gets firstName
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->container['firstName'];
    }

    /**
     * Sets firstName
     *
     * @param string|null $firstName firstName
     *
     * @return self
     */
    public function setFirstName($firstName)
    {
        $this->container['firstName'] = $firstName;

        return $this;
    }

    /**
     * Gets lastName
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->container['lastName'];
    }

    /**
     * Sets lastName
     *
     * @param string|null $lastName lastName
     *
     * @return self
     */
    public function setLastName($lastName)
    {
        $this->container['lastName'] = $lastName;

        return $this;
    }

    /**
     * Gets birthDate
     *
     * @return \DateTime|null
     */
    public function getBirthDate()
    {
        return $this->container['birthDate'];
    }

    /**
     * Sets birthDate
     *
     * @param \DateTime|null $birthDate birthDate
     *
     * @return self
     */
    public function setBirthDate($birthDate)
    {
        $this->container['birthDate'] = $birthDate;

        return $this;
    }

    /**
     * Gets birthName
     *
     * @return string|null
     */
    public function getBirthName()
    {
        return $this->container['birthName'];
    }

    /**
     * Sets birthName
     *
     * @param string|null $birthName Buyer birth name
     *
     * @return self
     */
    public function setBirthName($birthName)
    {
        $this->container['birthName'] = $birthName;

        return $this;
    }

    /**
     * Gets birthPlace
     *
     * @return string|null
     */
    public function getBirthPlace()
    {
        return $this->container['birthPlace'];
    }

    /**
     * Sets birthPlace
     *
     * @param string|null $birthPlace Buyer birth place
     *
     * @return self
     */
    public function setBirthPlace($birthPlace)
    {
        $this->container['birthPlace'] = $birthPlace;

        return $this;
    }

    /**
     * Gets title
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->container['title'];
    }

    /**
     * Sets title
     *
     * @param string|null $title title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $allowedValues = $this->getTitleAllowableValues();
        if (!is_null($title) && !in_array($title, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'title', must be one of '%s'",
                    $title,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['title'] = $title;

        return $this;
    }

    /**
     * Gets contact
     *
     * @return \Teambank\EasyCreditApiV3\Model\Contact|null
     */
    public function getContact()
    {
        return $this->container['contact'];
    }

    /**
     * Sets contact
     *
     * @param \Teambank\EasyCreditApiV3\Model\Contact|null $contact contact
     *
     * @return self
     */
    public function setContact($contact)
    {
        $this->container['contact'] = $contact;

        return $this;
    }

    /**
     * Gets bank
     *
     * @return \Teambank\EasyCreditApiV3\Model\Bank|null
     */
    public function getBank()
    {
        return $this->container['bank'];
    }

    /**
     * Sets bank
     *
     * @param \Teambank\EasyCreditApiV3\Model\Bank|null $bank bank
     *
     * @return self
     */
    public function setBank($bank)
    {
        $this->container['bank'] = $bank;

        return $this;
    }

    /**
     * Gets employment
     *
     * @return \Teambank\EasyCreditApiV3\Model\Employment|null
     */
    public function getEmployment()
    {
        return $this->container['employment'];
    }

    /**
     * Sets employment
     *
     * @param \Teambank\EasyCreditApiV3\Model\Employment|null $employment employment
     *
     * @return self
     */
    public function setEmployment($employment)
    {
        $this->container['employment'] = $employment;

        return $this;
    }

    /**
     * Gets companyName
     *
     * @return string|null
     */
    public function getCompanyName()
    {
        return $this->container['companyName'];
    }

    /**
     * Sets companyName
     *
     * @param string|null $companyName companyName
     *
     * @return self
     */
    public function setCompanyName($companyName)
    {
        $this->container['companyName'] = $companyName;

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


