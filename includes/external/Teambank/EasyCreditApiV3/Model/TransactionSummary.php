<?php
/**
 * TransactionSummary
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
 * TransactionSummary Class Doc Comment
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 * @implements \ArrayAccess<TKey, TValue>
 * @template TKey int|null
 * @template TValue mixed|null
 */
class TransactionSummary implements ModelInterface, ArrayAccess, \JsonSerializable
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'TransactionSummary';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'transactionId' => 'string',
        'deviceIdentToken' => 'string',
        'orderValue' => 'float',
        'interest' => 'float',
        'originalInterest' => 'float',
        'nominalInterest' => 'float',
        'effectiveInterest' => 'float',
        'merchantSpecificInterest' => 'float',
        'totalValue' => 'float',
        'decisionOutcome' => 'string',
        'decisionOutcomeText' => 'string',
        'numberOfInstallments' => 'int',
        'minNumberOfInstallments' => 'int',
        'maxNumberOfInstallments' => 'int',
        'installment' => 'float',
        'lastInstallment' => 'float',
        'firstInstallmentDate' => '\DateTime',
        'lastInstallmentDate' => '\DateTime',
        'amortizationPlanText' => 'string',
        'urlPreContractualInformation' => 'string',
        'installmentPlans' => '\Teambank\EasyCreditApiV3\Model\InstallmentPlan[]',
        'mtan' => '\Teambank\EasyCreditApiV3\Model\MTan',
        'bankAccountCheck' => '\Teambank\EasyCreditApiV3\Model\BankAccountCheck'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'transactionId' => null,
        'deviceIdentToken' => null,
        'orderValue' => null,
        'interest' => null,
        'originalInterest' => null,
        'nominalInterest' => null,
        'effectiveInterest' => null,
        'merchantSpecificInterest' => null,
        'totalValue' => null,
        'decisionOutcome' => null,
        'decisionOutcomeText' => null,
        'numberOfInstallments' => null,
        'minNumberOfInstallments' => null,
        'maxNumberOfInstallments' => null,
        'installment' => null,
        'lastInstallment' => null,
        'firstInstallmentDate' => 'date',
        'lastInstallmentDate' => 'date',
        'amortizationPlanText' => null,
        'urlPreContractualInformation' => null,
        'installmentPlans' => null,
        'mtan' => null,
        'bankAccountCheck' => null
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
        'transactionId' => 'transactionId',
        'deviceIdentToken' => 'deviceIdentToken',
        'orderValue' => 'orderValue',
        'interest' => 'interest',
        'originalInterest' => 'originalInterest',
        'nominalInterest' => 'nominalInterest',
        'effectiveInterest' => 'effectiveInterest',
        'merchantSpecificInterest' => 'merchantSpecificInterest',
        'totalValue' => 'totalValue',
        'decisionOutcome' => 'decisionOutcome',
        'decisionOutcomeText' => 'decisionOutcomeText',
        'numberOfInstallments' => 'numberOfInstallments',
        'minNumberOfInstallments' => 'minNumberOfInstallments',
        'maxNumberOfInstallments' => 'maxNumberOfInstallments',
        'installment' => 'installment',
        'lastInstallment' => 'lastInstallment',
        'firstInstallmentDate' => 'firstInstallmentDate',
        'lastInstallmentDate' => 'lastInstallmentDate',
        'amortizationPlanText' => 'amortizationPlanText',
        'urlPreContractualInformation' => 'urlPreContractualInformation',
        'installmentPlans' => 'installmentPlans',
        'mtan' => 'mtan',
        'bankAccountCheck' => 'bankAccountCheck'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'transactionId' => 'setTransactionId',
        'deviceIdentToken' => 'setDeviceIdentToken',
        'orderValue' => 'setOrderValue',
        'interest' => 'setInterest',
        'originalInterest' => 'setOriginalInterest',
        'nominalInterest' => 'setNominalInterest',
        'effectiveInterest' => 'setEffectiveInterest',
        'merchantSpecificInterest' => 'setMerchantSpecificInterest',
        'totalValue' => 'setTotalValue',
        'decisionOutcome' => 'setDecisionOutcome',
        'decisionOutcomeText' => 'setDecisionOutcomeText',
        'numberOfInstallments' => 'setNumberOfInstallments',
        'minNumberOfInstallments' => 'setMinNumberOfInstallments',
        'maxNumberOfInstallments' => 'setMaxNumberOfInstallments',
        'installment' => 'setInstallment',
        'lastInstallment' => 'setLastInstallment',
        'firstInstallmentDate' => 'setFirstInstallmentDate',
        'lastInstallmentDate' => 'setLastInstallmentDate',
        'amortizationPlanText' => 'setAmortizationPlanText',
        'urlPreContractualInformation' => 'setUrlPreContractualInformation',
        'installmentPlans' => 'setInstallmentPlans',
        'mtan' => 'setMtan',
        'bankAccountCheck' => 'setBankAccountCheck'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'transactionId' => 'getTransactionId',
        'deviceIdentToken' => 'getDeviceIdentToken',
        'orderValue' => 'getOrderValue',
        'interest' => 'getInterest',
        'originalInterest' => 'getOriginalInterest',
        'nominalInterest' => 'getNominalInterest',
        'effectiveInterest' => 'getEffectiveInterest',
        'merchantSpecificInterest' => 'getMerchantSpecificInterest',
        'totalValue' => 'getTotalValue',
        'decisionOutcome' => 'getDecisionOutcome',
        'decisionOutcomeText' => 'getDecisionOutcomeText',
        'numberOfInstallments' => 'getNumberOfInstallments',
        'minNumberOfInstallments' => 'getMinNumberOfInstallments',
        'maxNumberOfInstallments' => 'getMaxNumberOfInstallments',
        'installment' => 'getInstallment',
        'lastInstallment' => 'getLastInstallment',
        'firstInstallmentDate' => 'getFirstInstallmentDate',
        'lastInstallmentDate' => 'getLastInstallmentDate',
        'amortizationPlanText' => 'getAmortizationPlanText',
        'urlPreContractualInformation' => 'getUrlPreContractualInformation',
        'installmentPlans' => 'getInstallmentPlans',
        'mtan' => 'getMtan',
        'bankAccountCheck' => 'getBankAccountCheck'
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

    const DECISION_OUTCOME_POSITIVE = 'POSITIVE';
    const DECISION_OUTCOME_NEGATIVE = 'NEGATIVE';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getDecisionOutcomeAllowableValues()
    {
        return [
            self::DECISION_OUTCOME_POSITIVE,
            self::DECISION_OUTCOME_NEGATIVE,
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
        $this->container['transactionId'] = $data['transactionId'] ?? null;
        $this->container['deviceIdentToken'] = $data['deviceIdentToken'] ?? null;
        $this->container['orderValue'] = $data['orderValue'] ?? null;
        $this->container['interest'] = $data['interest'] ?? null;
        $this->container['originalInterest'] = $data['originalInterest'] ?? null;
        $this->container['nominalInterest'] = $data['nominalInterest'] ?? null;
        $this->container['effectiveInterest'] = $data['effectiveInterest'] ?? null;
        $this->container['merchantSpecificInterest'] = $data['merchantSpecificInterest'] ?? null;
        $this->container['totalValue'] = $data['totalValue'] ?? null;
        $this->container['decisionOutcome'] = $data['decisionOutcome'] ?? null;
        $this->container['decisionOutcomeText'] = $data['decisionOutcomeText'] ?? null;
        $this->container['numberOfInstallments'] = $data['numberOfInstallments'] ?? null;
        $this->container['minNumberOfInstallments'] = $data['minNumberOfInstallments'] ?? null;
        $this->container['maxNumberOfInstallments'] = $data['maxNumberOfInstallments'] ?? null;
        $this->container['installment'] = $data['installment'] ?? null;
        $this->container['lastInstallment'] = $data['lastInstallment'] ?? null;
        $this->container['firstInstallmentDate'] = $data['firstInstallmentDate'] ?? null;
        $this->container['lastInstallmentDate'] = $data['lastInstallmentDate'] ?? null;
        $this->container['amortizationPlanText'] = $data['amortizationPlanText'] ?? null;
        $this->container['urlPreContractualInformation'] = $data['urlPreContractualInformation'] ?? null;
        $this->container['installmentPlans'] = $data['installmentPlans'] ?? null;
        $this->container['mtan'] = $data['mtan'] ?? null;
        $this->container['bankAccountCheck'] = $data['bankAccountCheck'] ?? null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        $allowedValues = $this->getDecisionOutcomeAllowableValues();
        if (!is_null($this->container['decisionOutcome']) && !in_array($this->container['decisionOutcome'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'decisionOutcome', must be one of '%s'",
                $this->container['decisionOutcome'],
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
     * Gets transactionId
     *
     * @return string|null
     */
    public function getTransactionId()
    {
        return $this->container['transactionId'];
    }

    /**
     * Sets transactionId
     *
     * @param string|null $transactionId Unique functional transaction identifier (consists of 6 characters)
     *
     * @return self
     */
    public function setTransactionId($transactionId)
    {
        $this->container['transactionId'] = $transactionId;

        return $this;
    }

    /**
     * Gets deviceIdentToken
     *
     * @return string|null
     */
    public function getDeviceIdentToken()
    {
        return $this->container['deviceIdentToken'];
    }

    /**
     * Sets deviceIdentToken
     *
     * @param string|null $deviceIdentToken deviceIdentToken
     *
     * @return self
     */
    public function setDeviceIdentToken($deviceIdentToken)
    {
        $this->container['deviceIdentToken'] = $deviceIdentToken;

        return $this;
    }

    /**
     * Gets orderValue
     *
     * @return float|null
     */
    public function getOrderValue()
    {
        return $this->container['orderValue'];
    }

    /**
     * Sets orderValue
     *
     * @param float|null $orderValue Amount of the order value in € ( = Bestellwert in €)
     *
     * @return self
     */
    public function setOrderValue($orderValue)
    {
        $this->container['orderValue'] = $orderValue;

        return $this;
    }

    /**
     * Gets interest
     *
     * @return float|null
     */
    public function getInterest()
    {
        return $this->container['interest'];
    }

    /**
     * Sets interest
     *
     * @param float|null $interest Amount of the interest accrued in € ( = anfallender Zinsbetrag in €)
     *
     * @return self
     */
    public function setInterest($interest)
    {
        $this->container['interest'] = $interest;

        return $this;
    }

    /**
     * Gets originalInterest
     *
     * @return float|null
     */
    public function getOriginalInterest()
    {
        return $this->container['originalInterest'];
    }

    /**
     * Sets originalInterest
     *
     * @param float|null $originalInterest Amount of the original interest accrued in € ( = urspruenglicher Zinsbetrag in €)
     *
     * @return self
     */
    public function setOriginalInterest($originalInterest)
    {
        $this->container['originalInterest'] = $originalInterest;

        return $this;
    }

    /**
     * Gets nominalInterest
     *
     * @return float|null
     */
    public function getNominalInterest()
    {
        return $this->container['nominalInterest'];
    }

    /**
     * Sets nominalInterest
     *
     * @param float|null $nominalInterest ( = nominalzins in €)
     *
     * @return self
     */
    public function setNominalInterest($nominalInterest)
    {
        $this->container['nominalInterest'] = $nominalInterest;

        return $this;
    }

    /**
     * Gets effectiveInterest
     *
     * @return float|null
     */
    public function getEffectiveInterest()
    {
        return $this->container['effectiveInterest'];
    }

    /**
     * Sets effectiveInterest
     *
     * @param float|null $effectiveInterest ( = effektivzins in €)
     *
     * @return self
     */
    public function setEffectiveInterest($effectiveInterest)
    {
        $this->container['effectiveInterest'] = $effectiveInterest;

        return $this;
    }

    /**
     * Gets merchantSpecificInterest
     *
     * @return float|null
     */
    public function getMerchantSpecificInterest()
    {
        return $this->container['merchantSpecificInterest'];
    }

    /**
     * Sets merchantSpecificInterest
     *
     * @param float|null $merchantSpecificInterest ( = haendlerspezifischerZinssatz in €)
     *
     * @return self
     */
    public function setMerchantSpecificInterest($merchantSpecificInterest)
    {
        $this->container['merchantSpecificInterest'] = $merchantSpecificInterest;

        return $this;
    }

    /**
     * Gets totalValue
     *
     * @return float|null
     */
    public function getTotalValue()
    {
        return $this->container['totalValue'];
    }

    /**
     * Sets totalValue
     *
     * @param float|null $totalValue Amount of the total value in € ( = Gesamtsumme in €)
     *
     * @return self
     */
    public function setTotalValue($totalValue)
    {
        $this->container['totalValue'] = $totalValue;

        return $this;
    }

    /**
     * Gets decisionOutcome
     *
     * @return string|null
     */
    public function getDecisionOutcome()
    {
        return $this->container['decisionOutcome'];
    }

    /**
     * Sets decisionOutcome
     *
     * @param string|null $decisionOutcome Outcome of the credit decision
     *
     * @return self
     */
    public function setDecisionOutcome($decisionOutcome)
    {
        $allowedValues = $this->getDecisionOutcomeAllowableValues();
        if (!is_null($decisionOutcome) && !in_array($decisionOutcome, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'decisionOutcome', must be one of '%s'",
                    $decisionOutcome,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['decisionOutcome'] = $decisionOutcome;

        return $this;
    }

    /**
     * Gets decisionOutcomeText
     *
     * @return string|null
     */
    public function getDecisionOutcomeText()
    {
        return $this->container['decisionOutcomeText'];
    }

    /**
     * Sets decisionOutcomeText
     *
     * @param string|null $decisionOutcomeText Text containing further information on the decision outcome ( = entscheidungsergebnisTextbaustein)
     *
     * @return self
     */
    public function setDecisionOutcomeText($decisionOutcomeText)
    {
        $this->container['decisionOutcomeText'] = $decisionOutcomeText;

        return $this;
    }

    /**
     * Gets numberOfInstallments
     *
     * @return int|null
     */
    public function getNumberOfInstallments()
    {
        return $this->container['numberOfInstallments'];
    }

    /**
     * Sets numberOfInstallments
     *
     * @param int|null $numberOfInstallments Number of Installments defined in the payment plan ( = anzahl der Raten)
     *
     * @return self
     */
    public function setNumberOfInstallments($numberOfInstallments)
    {
        $this->container['numberOfInstallments'] = $numberOfInstallments;

        return $this;
    }

    /**
     * Gets minNumberOfInstallments
     *
     * @return int|null
     */
    public function getMinNumberOfInstallments()
    {
        return $this->container['minNumberOfInstallments'];
    }

    /**
     * Sets minNumberOfInstallments
     *
     * @param int|null $minNumberOfInstallments minimum number of Installments defined in the payment plan ( = minimaleLaufzeit)
     *
     * @return self
     */
    public function setMinNumberOfInstallments($minNumberOfInstallments)
    {
        $this->container['minNumberOfInstallments'] = $minNumberOfInstallments;

        return $this;
    }

    /**
     * Gets maxNumberOfInstallments
     *
     * @return int|null
     */
    public function getMaxNumberOfInstallments()
    {
        return $this->container['maxNumberOfInstallments'];
    }

    /**
     * Sets maxNumberOfInstallments
     *
     * @param int|null $maxNumberOfInstallments maximum number of Installments defined in the payment plan ( = maximaleLaufzeit)
     *
     * @return self
     */
    public function setMaxNumberOfInstallments($maxNumberOfInstallments)
    {
        $this->container['maxNumberOfInstallments'] = $maxNumberOfInstallments;

        return $this;
    }

    /**
     * Gets installment
     *
     * @return float|null
     */
    public function getInstallment()
    {
        return $this->container['installment'];
    }

    /**
     * Sets installment
     *
     * @param float|null $installment Amount in € of a single installment according to the payment plan ( = betrag der Rate in €)
     *
     * @return self
     */
    public function setInstallment($installment)
    {
        $this->container['installment'] = $installment;

        return $this;
    }

    /**
     * Gets lastInstallment
     *
     * @return float|null
     */
    public function getLastInstallment()
    {
        return $this->container['lastInstallment'];
    }

    /**
     * Sets lastInstallment
     *
     * @param float|null $lastInstallment Amount in € of the last installment according to the payment plan ( = betrag der letzten Rate in €)
     *
     * @return self
     */
    public function setLastInstallment($lastInstallment)
    {
        $this->container['lastInstallment'] = $lastInstallment;

        return $this;
    }

    /**
     * Gets firstInstallmentDate
     *
     * @return \DateTime|null
     */
    public function getFirstInstallmentDate()
    {
        return $this->container['firstInstallmentDate'];
    }

    /**
     * Sets firstInstallmentDate
     *
     * @param \DateTime|null $firstInstallmentDate Date indicating the first installment payment ( = terminErsteRate)
     *
     * @return self
     */
    public function setFirstInstallmentDate($firstInstallmentDate)
    {
        $this->container['firstInstallmentDate'] = $firstInstallmentDate;

        return $this;
    }

    /**
     * Gets lastInstallmentDate
     *
     * @return \DateTime|null
     */
    public function getLastInstallmentDate()
    {
        return $this->container['lastInstallmentDate'];
    }

    /**
     * Sets lastInstallmentDate
     *
     * @param \DateTime|null $lastInstallmentDate Date indicating the last installment payment ( = terminLetzteRate)
     *
     * @return self
     */
    public function setLastInstallmentDate($lastInstallmentDate)
    {
        $this->container['lastInstallmentDate'] = $lastInstallmentDate;

        return $this;
    }

    /**
     * Gets amortizationPlanText
     *
     * @return string|null
     */
    public function getAmortizationPlanText()
    {
        return $this->container['amortizationPlanText'];
    }

    /**
     * Sets amortizationPlanText
     *
     * @param string|null $amortizationPlanText Text containing the amortization plan ( = tilgungsplanText)
     *
     * @return self
     */
    public function setAmortizationPlanText($amortizationPlanText)
    {
        $this->container['amortizationPlanText'] = $amortizationPlanText;

        return $this;
    }

    /**
     * Gets urlPreContractualInformation
     *
     * @return string|null
     */
    public function getUrlPreContractualInformation()
    {
        return $this->container['urlPreContractualInformation'];
    }

    /**
     * Sets urlPreContractualInformation
     *
     * @param string|null $urlPreContractualInformation ( = urlVorvertraglicheInformationen)
     *
     * @return self
     */
    public function setUrlPreContractualInformation($urlPreContractualInformation)
    {
        $this->container['urlPreContractualInformation'] = $urlPreContractualInformation;

        return $this;
    }

    /**
     * Gets installmentPlans
     *
     * @return \Teambank\EasyCreditApiV3\Model\InstallmentPlan[]|null
     */
    public function getInstallmentPlans()
    {
        return $this->container['installmentPlans'];
    }

    /**
     * Sets installmentPlans
     *
     * @param \Teambank\EasyCreditApiV3\Model\InstallmentPlan[]|null $installmentPlans List of possible installment payment plans
     *
     * @return self
     */
    public function setInstallmentPlans($installmentPlans)
    {
        $this->container['installmentPlans'] = $installmentPlans;

        return $this;
    }

    /**
     * Gets mtan
     *
     * @return \Teambank\EasyCreditApiV3\Model\MTan|null
     */
    public function getMtan()
    {
        return $this->container['mtan'];
    }

    /**
     * Sets mtan
     *
     * @param \Teambank\EasyCreditApiV3\Model\MTan|null $mtan mtan
     *
     * @return self
     */
    public function setMtan($mtan)
    {
        $this->container['mtan'] = $mtan;

        return $this;
    }

    /**
     * Gets bankAccountCheck
     *
     * @return \Teambank\EasyCreditApiV3\Model\BankAccountCheck|null
     */
    public function getBankAccountCheck()
    {
        return $this->container['bankAccountCheck'];
    }

    /**
     * Sets bankAccountCheck
     *
     * @param \Teambank\EasyCreditApiV3\Model\BankAccountCheck|null $bankAccountCheck bankAccountCheck
     *
     * @return self
     */
    public function setBankAccountCheck($bankAccountCheck)
    {
        $this->container['bankAccountCheck'] = $bankAccountCheck;

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


