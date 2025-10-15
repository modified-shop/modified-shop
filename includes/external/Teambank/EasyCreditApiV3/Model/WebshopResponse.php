<?php
/**
 * WebshopResponse
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
 * WebshopResponse Class Doc Comment
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class WebshopResponse implements ModelInterface, ArrayAccess, \JsonSerializable
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'WebshopResponse';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'maxFinancingAmount' => 'int',
        'minFinancingAmount' => 'int',
        'interestRate' => 'float',
        'availability' => 'bool',
        'testMode' => 'bool',
        'privacyApprovalForm' => 'string',
        'declarationOfConsent' => 'string',
        'illustrativeExample' => 'string',
        'productDetails' => 'string',
        'uuid' => 'string',
        'flexprice' => 'bool',
        'installmentPaymentActive' => 'bool',
        'billPaymentActive' => 'bool',
        'minBillingValue' => 'float',
        'maxBillingValue' => 'float',
        'minInstallmentValue' => 'float',
        'maxInstallmentValue' => 'float'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'maxFinancingAmount' => null,
        'minFinancingAmount' => null,
        'interestRate' => null,
        'availability' => null,
        'testMode' => null,
        'privacyApprovalForm' => null,
        'declarationOfConsent' => null,
        'illustrativeExample' => null,
        'productDetails' => null,
        'uuid' => null,
        'flexprice' => null,
        'installmentPaymentActive' => null,
        'billPaymentActive' => null,
        'minBillingValue' => null,
        'maxBillingValue' => null,
        'minInstallmentValue' => null,
        'maxInstallmentValue' => null
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
        'maxFinancingAmount' => 'maxFinancingAmount',
        'minFinancingAmount' => 'minFinancingAmount',
        'interestRate' => 'interestRate',
        'availability' => 'availability',
        'testMode' => 'testMode',
        'privacyApprovalForm' => 'privacyApprovalForm',
        'declarationOfConsent' => 'declarationOfConsent',
        'illustrativeExample' => 'illustrativeExample',
        'productDetails' => 'productDetails',
        'uuid' => 'uuid',
        'flexprice' => 'flexprice',
        'installmentPaymentActive' => 'installmentPaymentActive',
        'billPaymentActive' => 'billPaymentActive',
        'minBillingValue' => 'minBillingValue',
        'maxBillingValue' => 'maxBillingValue',
        'minInstallmentValue' => 'minInstallmentValue',
        'maxInstallmentValue' => 'maxInstallmentValue'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'maxFinancingAmount' => 'setMaxFinancingAmount',
        'minFinancingAmount' => 'setMinFinancingAmount',
        'interestRate' => 'setInterestRate',
        'availability' => 'setAvailability',
        'testMode' => 'setTestMode',
        'privacyApprovalForm' => 'setPrivacyApprovalForm',
        'declarationOfConsent' => 'setDeclarationOfConsent',
        'illustrativeExample' => 'setIllustrativeExample',
        'productDetails' => 'setProductDetails',
        'uuid' => 'setUuid',
        'flexprice' => 'setFlexprice',
        'installmentPaymentActive' => 'setInstallmentPaymentActive',
        'billPaymentActive' => 'setBillPaymentActive',
        'minBillingValue' => 'setMinBillingValue',
        'maxBillingValue' => 'setMaxBillingValue',
        'minInstallmentValue' => 'setMinInstallmentValue',
        'maxInstallmentValue' => 'setMaxInstallmentValue'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'maxFinancingAmount' => 'getMaxFinancingAmount',
        'minFinancingAmount' => 'getMinFinancingAmount',
        'interestRate' => 'getInterestRate',
        'availability' => 'getAvailability',
        'testMode' => 'getTestMode',
        'privacyApprovalForm' => 'getPrivacyApprovalForm',
        'declarationOfConsent' => 'getDeclarationOfConsent',
        'illustrativeExample' => 'getIllustrativeExample',
        'productDetails' => 'getProductDetails',
        'uuid' => 'getUuid',
        'flexprice' => 'getFlexprice',
        'installmentPaymentActive' => 'getInstallmentPaymentActive',
        'billPaymentActive' => 'getBillPaymentActive',
        'minBillingValue' => 'getMinBillingValue',
        'maxBillingValue' => 'getMaxBillingValue',
        'minInstallmentValue' => 'getMinInstallmentValue',
        'maxInstallmentValue' => 'getMaxInstallmentValue'
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
        $this->container['maxFinancingAmount'] = $data['maxFinancingAmount'] ?? null;
        $this->container['minFinancingAmount'] = $data['minFinancingAmount'] ?? null;
        $this->container['interestRate'] = $data['interestRate'] ?? null;
        $this->container['availability'] = $data['availability'] ?? null;
        $this->container['testMode'] = $data['testMode'] ?? null;
        $this->container['privacyApprovalForm'] = $data['privacyApprovalForm'] ?? null;
        $this->container['declarationOfConsent'] = $data['declarationOfConsent'] ?? null;
        $this->container['illustrativeExample'] = $data['illustrativeExample'] ?? null;
        $this->container['productDetails'] = $data['productDetails'] ?? null;
        $this->container['uuid'] = $data['uuid'] ?? null;
        $this->container['flexprice'] = $data['flexprice'] ?? false;
        $this->container['installmentPaymentActive'] = $data['installmentPaymentActive'] ?? null;
        $this->container['billPaymentActive'] = $data['billPaymentActive'] ?? null;
        $this->container['minBillingValue'] = $data['minBillingValue'] ?? null;
        $this->container['maxBillingValue'] = $data['maxBillingValue'] ?? null;
        $this->container['minInstallmentValue'] = $data['minInstallmentValue'] ?? null;
        $this->container['maxInstallmentValue'] = $data['maxInstallmentValue'] ?? null;
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
     * Gets interestRate
     *
     * @return float|null
     */
    public function getInterestRate()
    {
        return $this->container['interestRate'];
    }

    /**
     * Sets interestRate
     *
     * @param float|null $interestRate interestRate
     *
     * @return self
     */
    public function setInterestRate($interestRate)
    {
        $this->container['interestRate'] = $interestRate;

        return $this;
    }

    /**
     * Gets availability
     *
     * @return bool|null
     */
    public function getAvailability()
    {
        return $this->container['availability'];
    }

    /**
     * Sets availability
     *
     * @param bool|null $availability true if financing is available from this webshop
     *
     * @return self
     */
    public function setAvailability($availability)
    {
        $this->container['availability'] = $availability;

        return $this;
    }

    /**
     * Gets testMode
     *
     * @return bool|null
     */
    public function getTestMode()
    {
        return $this->container['testMode'];
    }

    /**
     * Sets testMode
     *
     * @param bool|null $testMode true if the webshop is in test mode
     *
     * @return self
     */
    public function setTestMode($testMode)
    {
        $this->container['testMode'] = $testMode;

        return $this;
    }

    /**
     * Gets privacyApprovalForm
     *
     * @return string|null
     */
    public function getPrivacyApprovalForm()
    {
        return $this->container['privacyApprovalForm'];
    }

    /**
     * Sets privacyApprovalForm
     *
     * @param string|null $privacyApprovalForm form for privacy approval (zustimmungDatenuebertragungPaymentPage)
     *
     * @return self
     */
    public function setPrivacyApprovalForm($privacyApprovalForm)
    {
        $this->container['privacyApprovalForm'] = $privacyApprovalForm;

        return $this;
    }

    /**
     * Gets declarationOfConsent
     *
     * @return string|null
     */
    public function getDeclarationOfConsent()
    {
        return $this->container['declarationOfConsent'];
    }

    /**
     * Sets declarationOfConsent
     *
     * @param string|null $declarationOfConsent (zustimmungEinwilligungserklaerungPaymentPage)
     *
     * @return self
     */
    public function setDeclarationOfConsent($declarationOfConsent)
    {
        $this->container['declarationOfConsent'] = $declarationOfConsent;

        return $this;
    }

    /**
     * Gets illustrativeExample
     *
     * @return string|null
     */
    public function getIllustrativeExample()
    {
        return $this->container['illustrativeExample'];
    }

    /**
     * Sets illustrativeExample
     *
     * @param string|null $illustrativeExample (repraesentativesBeispiel)
     *
     * @return self
     */
    public function setIllustrativeExample($illustrativeExample)
    {
        $this->container['illustrativeExample'] = $illustrativeExample;

        return $this;
    }

    /**
     * Gets productDetails
     *
     * @return string|null
     */
    public function getProductDetails()
    {
        return $this->container['productDetails'];
    }

    /**
     * Sets productDetails
     *
     * @param string|null $productDetails (produktangaben)
     *
     * @return self
     */
    public function setProductDetails($productDetails)
    {
        $this->container['productDetails'] = $productDetails;

        return $this;
    }

    /**
     * Gets uuid
     *
     * @return string|null
     */
    public function getUuid()
    {
        return $this->container['uuid'];
    }

    /**
     * Sets uuid
     *
     * @param string|null $uuid request-id
     *
     * @return self
     */
    public function setUuid($uuid)
    {
        $this->container['uuid'] = $uuid;

        return $this;
    }

    /**
     * Gets flexprice
     *
     * @return bool|null
     */
    public function getFlexprice()
    {
        return $this->container['flexprice'];
    }

    /**
     * Sets flexprice
     *
     * @param bool|null $flexprice true if the shop has an active flexprice or a flexprice time period is planned for the future
     *
     * @return self
     */
    public function setFlexprice($flexprice)
    {
        $this->container['flexprice'] = $flexprice;

        return $this;
    }

    /**
     * Gets installmentPaymentActive
     *
     * @return bool|null
     */
    public function getInstallmentPaymentActive()
    {
        return $this->container['installmentPaymentActive'];
    }

    /**
     * Sets installmentPaymentActive
     *
     * @param bool|null $installmentPaymentActive installmentPaymentActive
     *
     * @return self
     */
    public function setInstallmentPaymentActive($installmentPaymentActive)
    {
        $this->container['installmentPaymentActive'] = $installmentPaymentActive;

        return $this;
    }

    /**
     * Gets billPaymentActive
     *
     * @return bool|null
     */
    public function getBillPaymentActive()
    {
        return $this->container['billPaymentActive'];
    }

    /**
     * Sets billPaymentActive
     *
     * @param bool|null $billPaymentActive billPaymentActive
     *
     * @return self
     */
    public function setBillPaymentActive($billPaymentActive)
    {
        $this->container['billPaymentActive'] = $billPaymentActive;

        return $this;
    }

    /**
     * Gets minBillingValue
     *
     * @return float|null
     */
    public function getMinBillingValue()
    {
        return $this->container['minBillingValue'];
    }

    /**
     * Sets minBillingValue
     *
     * @param float|null $minBillingValue minBillingValue
     *
     * @return self
     */
    public function setMinBillingValue($minBillingValue)
    {
        $this->container['minBillingValue'] = $minBillingValue;

        return $this;
    }

    /**
     * Gets maxBillingValue
     *
     * @return float|null
     */
    public function getMaxBillingValue()
    {
        return $this->container['maxBillingValue'];
    }

    /**
     * Sets maxBillingValue
     *
     * @param float|null $maxBillingValue maxBillingValue
     *
     * @return self
     */
    public function setMaxBillingValue($maxBillingValue)
    {
        $this->container['maxBillingValue'] = $maxBillingValue;

        return $this;
    }

    /**
     * Gets minInstallmentValue
     *
     * @return float|null
     */
    public function getMinInstallmentValue()
    {
        return $this->container['minInstallmentValue'];
    }

    /**
     * Sets minInstallmentValue
     *
     * @param float|null $minInstallmentValue minInstallmentValue
     *
     * @return self
     */
    public function setMinInstallmentValue($minInstallmentValue)
    {
        $this->container['minInstallmentValue'] = $minInstallmentValue;

        return $this;
    }

    /**
     * Gets maxInstallmentValue
     *
     * @return float|null
     */
    public function getMaxInstallmentValue()
    {
        return $this->container['maxInstallmentValue'];
    }

    /**
     * Sets maxInstallmentValue
     *
     * @param float|null $maxInstallmentValue maxInstallmentValue
     *
     * @return self
     */
    public function setMaxInstallmentValue($maxInstallmentValue)
    {
        $this->container['maxInstallmentValue'] = $maxInstallmentValue;

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


