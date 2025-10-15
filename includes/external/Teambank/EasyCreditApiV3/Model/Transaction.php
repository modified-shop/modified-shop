<?php
/**
 * Transaction
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
 * Transaction Class Doc Comment
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class Transaction implements ModelInterface, ArrayAccess, \JsonSerializable
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'Transaction';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'financingTerm' => 'int',
        'orderDetails' => '\Teambank\EasyCreditApiV3\Model\OrderDetails',
        'shopsystem' => '\Teambank\EasyCreditApiV3\Model\Shopsystem',
        'customer' => '\Teambank\EasyCreditApiV3\Model\Customer',
        'customerRelationship' => '\Teambank\EasyCreditApiV3\Model\CustomerRelationship',
        'consent' => '\Teambank\EasyCreditApiV3\Model\Consent',
        'redirectLinks' => '\Teambank\EasyCreditApiV3\Model\RedirectLinks',
        'paymentType' => 'string',
        'paymentSwitchPossible' => 'bool'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'financingTerm' => null,
        'orderDetails' => null,
        'shopsystem' => null,
        'customer' => null,
        'customerRelationship' => null,
        'consent' => null,
        'redirectLinks' => null,
        'paymentType' => null,
        'paymentSwitchPossible' => null
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
        'financingTerm' => 'financingTerm',
        'orderDetails' => 'orderDetails',
        'shopsystem' => 'shopsystem',
        'customer' => 'customer',
        'customerRelationship' => 'customerRelationship',
        'consent' => 'consent',
        'redirectLinks' => 'redirectLinks',
        'paymentType' => 'paymentType',
        'paymentSwitchPossible' => 'paymentSwitchPossible'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'financingTerm' => 'setFinancingTerm',
        'orderDetails' => 'setOrderDetails',
        'shopsystem' => 'setShopsystem',
        'customer' => 'setCustomer',
        'customerRelationship' => 'setCustomerRelationship',
        'consent' => 'setConsent',
        'redirectLinks' => 'setRedirectLinks',
        'paymentType' => 'setPaymentType',
        'paymentSwitchPossible' => 'setPaymentSwitchPossible'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'financingTerm' => 'getFinancingTerm',
        'orderDetails' => 'getOrderDetails',
        'shopsystem' => 'getShopsystem',
        'customer' => 'getCustomer',
        'customerRelationship' => 'getCustomerRelationship',
        'consent' => 'getConsent',
        'redirectLinks' => 'getRedirectLinks',
        'paymentType' => 'getPaymentType',
        'paymentSwitchPossible' => 'getPaymentSwitchPossible'
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

    const PAYMENT_TYPE_INSTALLMENT_PAYMENT = 'INSTALLMENT_PAYMENT';
    const PAYMENT_TYPE_BILL_PAYMENT = 'BILL_PAYMENT';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getPaymentTypeAllowableValues()
    {
        return [
            self::PAYMENT_TYPE_INSTALLMENT_PAYMENT,
            self::PAYMENT_TYPE_BILL_PAYMENT,
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
        $this->container['financingTerm'] = $data['financingTerm'] ?? null;
        $this->container['orderDetails'] = $data['orderDetails'] ?? null;
        $this->container['shopsystem'] = $data['shopsystem'] ?? null;
        $this->container['customer'] = $data['customer'] ?? null;
        $this->container['customerRelationship'] = $data['customerRelationship'] ?? null;
        $this->container['consent'] = $data['consent'] ?? null;
        $this->container['redirectLinks'] = $data['redirectLinks'] ?? null;
        $this->container['paymentType'] = $data['paymentType'] ?? 'INSTALLMENT_PAYMENT';
        $this->container['paymentSwitchPossible'] = $data['paymentSwitchPossible'] ?? null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if ($this->container['orderDetails'] === null) {
            $invalidProperties[] = "'orderDetails' can't be null";
        }
        $allowedValues = $this->getPaymentTypeAllowableValues();
        if (!is_null($this->container['paymentType']) && !in_array($this->container['paymentType'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'paymentType', must be one of '%s'",
                $this->container['paymentType'],
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
     * Gets financingTerm
     *
     * @return int|null
     */
    public function getFinancingTerm()
    {
        return $this->container['financingTerm'];
    }

    /**
     * Sets financingTerm
     *
     * @param int|null $financingTerm ' Duration in months, depending on individual shop conditions and order value (please check your ratenkauf widget). Will be set to default value if not available. '
     *
     * @return self
     */
    public function setFinancingTerm($financingTerm)
    {
        $this->container['financingTerm'] = $financingTerm;

        return $this;
    }

    /**
     * Gets orderDetails
     *
     * @return \Teambank\EasyCreditApiV3\Model\OrderDetails
     */
    public function getOrderDetails()
    {
        return $this->container['orderDetails'];
    }

    /**
     * Sets orderDetails
     *
     * @param \Teambank\EasyCreditApiV3\Model\OrderDetails $orderDetails orderDetails
     *
     * @return self
     */
    public function setOrderDetails($orderDetails)
    {
        $this->container['orderDetails'] = $orderDetails;

        return $this;
    }

    /**
     * Gets shopsystem
     *
     * @return \Teambank\EasyCreditApiV3\Model\Shopsystem|null
     */
    public function getShopsystem()
    {
        return $this->container['shopsystem'];
    }

    /**
     * Sets shopsystem
     *
     * @param \Teambank\EasyCreditApiV3\Model\Shopsystem|null $shopsystem shopsystem
     *
     * @return self
     */
    public function setShopsystem($shopsystem)
    {
        $this->container['shopsystem'] = $shopsystem;

        return $this;
    }

    /**
     * Gets customer
     *
     * @return \Teambank\EasyCreditApiV3\Model\Customer|null
     */
    public function getCustomer()
    {
        return $this->container['customer'];
    }

    /**
     * Sets customer
     *
     * @param \Teambank\EasyCreditApiV3\Model\Customer|null $customer customer
     *
     * @return self
     */
    public function setCustomer($customer)
    {
        $this->container['customer'] = $customer;

        return $this;
    }

    /**
     * Gets customerRelationship
     *
     * @return \Teambank\EasyCreditApiV3\Model\CustomerRelationship|null
     */
    public function getCustomerRelationship()
    {
        return $this->container['customerRelationship'];
    }

    /**
     * Sets customerRelationship
     *
     * @param \Teambank\EasyCreditApiV3\Model\CustomerRelationship|null $customerRelationship customerRelationship
     *
     * @return self
     */
    public function setCustomerRelationship($customerRelationship)
    {
        $this->container['customerRelationship'] = $customerRelationship;

        return $this;
    }

    /**
     * Gets consent
     *
     * @return \Teambank\EasyCreditApiV3\Model\Consent|null
     */
    public function getConsent()
    {
        return $this->container['consent'];
    }

    /**
     * Sets consent
     *
     * @param \Teambank\EasyCreditApiV3\Model\Consent|null $consent consent
     *
     * @return self
     */
    public function setConsent($consent)
    {
        $this->container['consent'] = $consent;

        return $this;
    }

    /**
     * Gets redirectLinks
     *
     * @return \Teambank\EasyCreditApiV3\Model\RedirectLinks|null
     */
    public function getRedirectLinks()
    {
        return $this->container['redirectLinks'];
    }

    /**
     * Sets redirectLinks
     *
     * @param \Teambank\EasyCreditApiV3\Model\RedirectLinks|null $redirectLinks redirectLinks
     *
     * @return self
     */
    public function setRedirectLinks($redirectLinks)
    {
        $this->container['redirectLinks'] = $redirectLinks;

        return $this;
    }

    /**
     * Gets paymentType
     *
     * @return string|null
     */
    public function getPaymentType()
    {
        return $this->container['paymentType'];
    }

    /**
     * Sets paymentType
     *
     * @param string|null $paymentType experimental
     *
     * @return self
     */
    public function setPaymentType($paymentType)
    {
        $allowedValues = $this->getPaymentTypeAllowableValues();
        if (!is_null($paymentType) && !in_array($paymentType, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'paymentType', must be one of '%s'",
                    $paymentType,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['paymentType'] = $paymentType;

        return $this;
    }

    /**
     * Gets paymentSwitchPossible
     *
     * @return bool|null
     */
    public function getPaymentSwitchPossible()
    {
        return $this->container['paymentSwitchPossible'];
    }

    /**
     * Sets paymentSwitchPossible
     *
     * @param bool|null $paymentSwitchPossible paymentSwitchPossible
     *
     * @return self
     */
    public function setPaymentSwitchPossible($paymentSwitchPossible)
    {
        $this->container['paymentSwitchPossible'] = $paymentSwitchPossible;

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


