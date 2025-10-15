<?php
/**
 * CustomerRelationship
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
 * CustomerRelationship Class Doc Comment
 *
 * @category Class
 * @description Risk relevant information
 * @package  Teambank\EasyCreditApiV3
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class CustomerRelationship implements ModelInterface, ArrayAccess, \JsonSerializable
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'CustomerRelationship';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'customerStatus' => 'string',
        'customerSince' => '\DateTime',
        'orderDoneWithLogin' => 'bool',
        'numberOfOrders' => 'int',
        'negativePaymentInformation' => 'string',
        'riskyItemsInShoppingCart' => 'bool',
        'logisticsServiceProvider' => 'string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'customerStatus' => null,
        'customerSince' => 'date',
        'orderDoneWithLogin' => null,
        'numberOfOrders' => null,
        'negativePaymentInformation' => null,
        'riskyItemsInShoppingCart' => null,
        'logisticsServiceProvider' => null
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
        'customerStatus' => 'customerStatus',
        'customerSince' => 'customerSince',
        'orderDoneWithLogin' => 'orderDoneWithLogin',
        'numberOfOrders' => 'numberOfOrders',
        'negativePaymentInformation' => 'negativePaymentInformation',
        'riskyItemsInShoppingCart' => 'riskyItemsInShoppingCart',
        'logisticsServiceProvider' => 'logisticsServiceProvider'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'customerStatus' => 'setCustomerStatus',
        'customerSince' => 'setCustomerSince',
        'orderDoneWithLogin' => 'setOrderDoneWithLogin',
        'numberOfOrders' => 'setNumberOfOrders',
        'negativePaymentInformation' => 'setNegativePaymentInformation',
        'riskyItemsInShoppingCart' => 'setRiskyItemsInShoppingCart',
        'logisticsServiceProvider' => 'setLogisticsServiceProvider'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'customerStatus' => 'getCustomerStatus',
        'customerSince' => 'getCustomerSince',
        'orderDoneWithLogin' => 'getOrderDoneWithLogin',
        'numberOfOrders' => 'getNumberOfOrders',
        'negativePaymentInformation' => 'getNegativePaymentInformation',
        'riskyItemsInShoppingCart' => 'getRiskyItemsInShoppingCart',
        'logisticsServiceProvider' => 'getLogisticsServiceProvider'
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

    const CUSTOMER_STATUS_NEW_CUSTOMER = 'NEW_CUSTOMER';
    const CUSTOMER_STATUS_EXISTING_CUSTOMER = 'EXISTING_CUSTOMER';
    const CUSTOMER_STATUS_PREMIUM_CUSTOMER = 'PREMIUM_CUSTOMER';
    const NEGATIVE_PAYMENT_INFORMATION_NO_PAYMENT_DISRUPTION = 'NO_PAYMENT_DISRUPTION';
    const NEGATIVE_PAYMENT_INFORMATION_PAYMENT_DELAY = 'PAYMENT_DELAY';
    const NEGATIVE_PAYMENT_INFORMATION_PAYMENT_NOT_DONE = 'PAYMENT_NOT_DONE';
    const NEGATIVE_PAYMENT_INFORMATION_NO_INFORMATION = 'NO_INFORMATION';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getCustomerStatusAllowableValues()
    {
        return [
            self::CUSTOMER_STATUS_NEW_CUSTOMER,
            self::CUSTOMER_STATUS_EXISTING_CUSTOMER,
            self::CUSTOMER_STATUS_PREMIUM_CUSTOMER,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getNegativePaymentInformationAllowableValues()
    {
        return [
            self::NEGATIVE_PAYMENT_INFORMATION_NO_PAYMENT_DISRUPTION,
            self::NEGATIVE_PAYMENT_INFORMATION_PAYMENT_DELAY,
            self::NEGATIVE_PAYMENT_INFORMATION_PAYMENT_NOT_DONE,
            self::NEGATIVE_PAYMENT_INFORMATION_NO_INFORMATION,
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
        $this->container['customerStatus'] = $data['customerStatus'] ?? null;
        $this->container['customerSince'] = $data['customerSince'] ?? null;
        $this->container['orderDoneWithLogin'] = $data['orderDoneWithLogin'] ?? null;
        $this->container['numberOfOrders'] = $data['numberOfOrders'] ?? null;
        $this->container['negativePaymentInformation'] = $data['negativePaymentInformation'] ?? null;
        $this->container['riskyItemsInShoppingCart'] = $data['riskyItemsInShoppingCart'] ?? null;
        $this->container['logisticsServiceProvider'] = $data['logisticsServiceProvider'] ?? null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        $allowedValues = $this->getCustomerStatusAllowableValues();
        if (!is_null($this->container['customerStatus']) && !in_array($this->container['customerStatus'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'customerStatus', must be one of '%s'",
                $this->container['customerStatus'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getNegativePaymentInformationAllowableValues();
        if (!is_null($this->container['negativePaymentInformation']) && !in_array($this->container['negativePaymentInformation'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'negativePaymentInformation', must be one of '%s'",
                $this->container['negativePaymentInformation'],
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
     * Gets customerStatus
     *
     * @return string|null
     */
    public function getCustomerStatus()
    {
        return $this->container['customerStatus'];
    }

    /**
     * Sets customerStatus
     *
     * @param string|null $customerStatus NEW_CUSTOMER = NEUKUNDE, EXISTING_CUSTOMER = BESTANDSKUNDE, PREMIUM_CUSTOMER = PREMIUMKUNDE
     *
     * @return self
     */
    public function setCustomerStatus($customerStatus)
    {
        $allowedValues = $this->getCustomerStatusAllowableValues();
        if (!is_null($customerStatus) && !in_array($customerStatus, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'customerStatus', must be one of '%s'",
                    $customerStatus,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['customerStatus'] = $customerStatus;

        return $this;
    }

    /**
     * Gets customerSince
     *
     * @return \DateTime|null
     */
    public function getCustomerSince()
    {
        return $this->container['customerSince'];
    }

    /**
     * Sets customerSince
     *
     * @param \DateTime|null $customerSince customerSince
     *
     * @return self
     */
    public function setCustomerSince($customerSince)
    {
        $this->container['customerSince'] = $customerSince;

        return $this;
    }

    /**
     * Gets orderDoneWithLogin
     *
     * @return bool|null
     */
    public function getOrderDoneWithLogin()
    {
        return $this->container['orderDoneWithLogin'];
    }

    /**
     * Sets orderDoneWithLogin
     *
     * @param bool|null $orderDoneWithLogin true if the order was placed via customer login
     *
     * @return self
     */
    public function setOrderDoneWithLogin($orderDoneWithLogin)
    {
        $this->container['orderDoneWithLogin'] = $orderDoneWithLogin;

        return $this;
    }

    /**
     * Gets numberOfOrders
     *
     * @return int|null
     */
    public function getNumberOfOrders()
    {
        return $this->container['numberOfOrders'];
    }

    /**
     * Sets numberOfOrders
     *
     * @param int|null $numberOfOrders numberOfOrders
     *
     * @return self
     */
    public function setNumberOfOrders($numberOfOrders)
    {
        $this->container['numberOfOrders'] = $numberOfOrders;

        return $this;
    }

    /**
     * Gets negativePaymentInformation
     *
     * @return string|null
     */
    public function getNegativePaymentInformation()
    {
        return $this->container['negativePaymentInformation'];
    }

    /**
     * Sets negativePaymentInformation
     *
     * @param string|null $negativePaymentInformation Indicates whether the customer has already been in late payment or has not made the payment -> NO_PAYMENT_DISRUPTION = KEINE_ZAHLUNGSSTOERUNGEN, PAYMENT_DELAY = ZAHLUNGSVERZOEGERUNG, PAYMENT_NOT_DONE = ZAHLUNGSAUSFALL, NO_INFORMATION = KEINE_INFORMATION
     *
     * @return self
     */
    public function setNegativePaymentInformation($negativePaymentInformation)
    {
        $allowedValues = $this->getNegativePaymentInformationAllowableValues();
        if (!is_null($negativePaymentInformation) && !in_array($negativePaymentInformation, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'negativePaymentInformation', must be one of '%s'",
                    $negativePaymentInformation,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['negativePaymentInformation'] = $negativePaymentInformation;

        return $this;
    }

    /**
     * Gets riskyItemsInShoppingCart
     *
     * @return bool|null
     */
    public function getRiskyItemsInShoppingCart()
    {
        return $this->container['riskyItemsInShoppingCart'];
    }

    /**
     * Sets riskyItemsInShoppingCart
     *
     * @param bool|null $riskyItemsInShoppingCart risikoartikelImWarenkorb
     *
     * @return self
     */
    public function setRiskyItemsInShoppingCart($riskyItemsInShoppingCart)
    {
        $this->container['riskyItemsInShoppingCart'] = $riskyItemsInShoppingCart;

        return $this;
    }

    /**
     * Gets logisticsServiceProvider
     *
     * @return string|null
     */
    public function getLogisticsServiceProvider()
    {
        return $this->container['logisticsServiceProvider'];
    }

    /**
     * Sets logisticsServiceProvider
     *
     * @param string|null $logisticsServiceProvider Logistics service provider for the delivery of the order
     *
     * @return self
     */
    public function setLogisticsServiceProvider($logisticsServiceProvider)
    {
        $this->container['logisticsServiceProvider'] = $logisticsServiceProvider;

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


