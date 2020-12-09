<?php

namespace EasyCredit\Transfer;

use EasyCredit\Process\Status;
use EasyCredit\SaveHandler\SaveHandlerInterface;

/**
 * Class ProcessData
 *
 * @package EasyCredit\Transfer
 */
class ProcessData extends AbstractObject
{

    /**
     * @const string
     */
    const STEP_PAYMENT = 'step_payment';

    /**
     * @const string
     */
    const STEP_VERIFICATION = 'step_verification';

    /**
     * @var SaveHandlerInterface
     */
    private $saveHandler;

    /**
     * @var string
     * @apiName tbaId
     */
    protected $tbaId;

    /**
     * @var string
     * @apiName tbaId
     */
    protected $technicalTbaId;

    /**
     * @var string
     * @apiName status
     */
    protected $status;

    /**
     * @var string
     * @apiName hash
     */
    protected $hash;

    /**
     * @var Customer
     * @apiName       customer
     * @transferClass EasyCredit\Transfer\Customer
     */
    protected $customer;

    /**
     * @var CartInfoCollection
     * @transferClass EasyCredit\Transfer\CartInfoCollection
     * @apiName       products
     */
    protected $products;

    /**
     * @var float
     * @apiName orderTotal
     */
    protected $orderTotal;

    /**
     * @var BankData
     * @apiName       bankdatenInput
     * @transferClass EasyCredit\Transfer\BankData
     */
    protected $bankData;

    /**
     * @var BillingAddress
     * @apiName       rechnungsadresse
     * @transferClass EasyCredit\Transfer\BillingAddress
     */
    protected $billingAddress;

    /**
     * @var DeliveryAddress
     * @apiName       lieferadresse
     * @transferClass EasyCredit\Transfer\DeliveryAddress
     */
    protected $deliveryAddress;

    /**
     * @var RiskRelatedInfo
     * @apiName       risk
     * @transferClass EasyCredit\Transfer\RiskRelatedInfo
     */
    protected $riskInfo;

    /**
     * @var array
     */
    protected $messages = array();

    /**
     * @var Agreement
     * @transferClass EasyCredit\Transfer\Agreement
     */
    protected $agreement;

    /**
     * @var integer
     */
    protected $term;

    /**
     * @var \DateTime
     */
    protected $validUntil;

    /**
     * @var string
     */
    protected $currentStep;

    /**
     * @var CallbackUrls
     * @transferClass EasyCredit\Transfer\CallbackUrls
     */
    protected $callbackUrls;
    
    protected $mTan;

    public function __construct(array $data = array())
    {
        parent::__construct($data);
    }

    /**
     * Initializes an empty process object.
     * Any existing data will be cleared!
     */
    public function initEmpty()
    {
        if ($this->saveHandler) {
            $this->saveHandler->clear($this);
        }
        $this->clearMessages();
        $this->tbaId = null;
        $this->hash = null;
        $this->status = Status::NONE;

        $this->customer = new Customer();
        $this->bankData = new BankData();
        $this->products = new CartInfoCollection();
        $this->billingAddress = new BillingAddress();
        $this->deliveryAddress = new DeliveryAddress();
        $this->riskInfo = new RiskRelatedInfo();
        $this->agreement = new Agreement();
        $this->term = null;
        $this->orderTotal = null;
    }

    /**
     * @return int
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * @param int $term
     */
    public function setTerm($term)
    {
        $this->term = $term;
    }

    /**
     * @return string
     */
    public function getTbaId()
    {
        return $this->tbaId;
    }

    /**
     * @param string $tbaId
     */
    public function setTbaId($tbaId)
    {
        $this->tbaId = $tbaId;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * Returns the customer assigned with this process.
     *
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Sets a customer
     *
     * @param Customer $customer
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Returns the shopping basket as list of products.
     *
     * @return CartInfoCollection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Sets the list of products (shopping basket)
     *
     * @param CartInfoCollection $products
     */
    public function setProducts(CartInfoCollection $products)
    {
        $this->products = $products;
    }

    /**
     * @return float
     */
    public function getOrderTotal()
    {
        return $this->orderTotal;
    }

    /**
     * @param float $orderTotal
     */
    public function setOrderTotal($orderTotal)
    {
        $this->orderTotal = $orderTotal;
    }

    /**
     * Returns the bank data
     *
     * @return BankData
     */
    public function getBankData()
    {
        return $this->bankData;
    }

    /**
     * Sets the bank data
     *
     * @param BankData $bankData
     */
    public function setBankData(BankData $bankData)
    {
        $this->bankData = $bankData;
    }

    /**
     * Returns the billing address
     *
     * @return BillingAddress
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * Sets the billing address
     *
     * @param BillingAddress $billingAddress
     */
    public function setBillingAddress(BillingAddress $billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * Returns the delivery address
     *
     * @return DeliveryAddress
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * Sets the delivery address
     *
     * @param DeliveryAddress $deliveryAddress
     */
    public function setDeliveryAddress(DeliveryAddress $deliveryAddress)
    {
        $this->deliveryAddress = $deliveryAddress;
    }

    /**
     * Returns the risk info
     *
     * @return RiskRelatedInfo
     */
    public function getRiskInfo()
    {
        return $this->riskInfo;
    }

    /**
     * Sets the risk info
     *
     * @param RiskRelatedInfo $riskInfo
     */
    public function setRiskInfo(RiskRelatedInfo $riskInfo)
    {
        $this->riskInfo = $riskInfo;
    }

    /**
     * @return Agreement
     */
    public function getAgreement()
    {
        return $this->agreement;
    }

    /**
     * @param Agreement $agreement
     */
    public function setAgreement($agreement)
    {
        $this->agreement = $agreement;
    }

    public function save()
    {
        if ($this->saveHandler) {
            $this->saveHandler->save($this);
        }
    }

    public function load()
    {
        if ($this->saveHandler && ($data = $this->saveHandler->get($this))) {
            $this->setData($data);
        }
    }

    /**
     * @param SaveHandlerInterface $saveHandler
     */
    public function setSaveHandler(SaveHandlerInterface $saveHandler)
    {
        $this->saveHandler = $saveHandler;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param string $message
     */
    public function addMessage($message, $key = null)
    {
        if ($key !== null) {
            $this->messages[$key] = $message;
        } else {
            $this->messages[] = $message;
        }
    }

    /**
     * Clear Messages
     */
    public function clearMessages()
    {
        $this->messages = array();
    }

    /**
     * @param array $messages
     */
    public function setMessages(array $messages)
    {
        $this->messages = $messages;
    }

    /**
     * @return string
     */
    public function getCurrentStep()
    {
        return $this->currentStep;
    }

    /**
     * @param string $currentStep
     */
    public function setCurrentStep($currentStep)
    {
        $this->currentStep = $currentStep;
    }

    /**
     * @return \DateTime
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * @param \DateTime $validUntil
     */
    public function setValidUntil($validUntil)
    {
        if (!$validUntil instanceof \DateTime) {
            $validUntil = new \DateTime($validUntil);
        }
        $this->validUntil = $validUntil;
    }

    /**
     * @return string
     */
    public function getTechnicalTbaId()
    {
        return $this->technicalTbaId;
    }

    /**
     * @param string $technicalTbaId
     */
    public function setTechnicalTbaId($technicalTbaId)
    {
        $this->technicalTbaId = $technicalTbaId;
    }

    /**
     * @return null|boolean
     */
    public function isValid()
    {
        if (!$this->validUntil instanceof \DateTime) {
            return null;
        }
        $currentDateTime = new \DateTime();

        if ($this->validUntil->getTimestamp() > $currentDateTime->getTimestamp()) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function generateHash()
    {
        $data = array(
            'customer' => $this->getCustomer()->toArray(),
            'bank_data' => $this->getBankData()->toArray(),
            'products' => $this->getProducts()->toArray(),
            'billing_address' => $this->getBillingAddress()->toArray(),
            'delivery_address' => $this->getDeliveryAddress()->toArray(),
            'agreement' => $this->getAgreement()->toArray(),
            'term' => $this->getTerm()
        );

        return md5(json_encode($data));
    }

    /**
     *
     * @return CallbackUrls
     */
    public function getCallbackUrls()
    {
        return $this->callbackUrls;
    }

    /**
     *
     * @param CallbackUrls $callbackUrls
     */
    public function setCallbackUrls($callbackUrls)
    {
        $this->callbackUrls = $callbackUrls;
    }

    /**
     *
     * @return string
     */
    public function getMTan()
    {
        return $this->mTan;
    }

    /**
     *
     * @param string $mTan            
     */
    public function setMTan($mTan)
    {
        $this->mTan = $mTan;
    }
 
 
}
