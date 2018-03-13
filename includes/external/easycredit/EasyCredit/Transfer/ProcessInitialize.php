<?php

namespace EasyCredit\Transfer;

/**
 * Class ProcessInitialize
 *
 * @package EasyCredit\Transfer
 */
class ProcessInitialize extends AbstractObject
{

    /**
     * @const
     */
    const INTEGRATION_TYPE_PAYMENT_PAGE = 'PAYMENT_PAGE';
    
    /**
     * @const
     */
    const INTEGRATION_TYPE_SERVICE_INTEGRATION = 'SERVICE_INTEGRATION';
    
    /**
     * @const
     */
    const INTEGRATION_TYPE_POS = 'POS';
    
    /**
     * @var string
     * @apiName shopKennung
     */
    protected $shopId;

    /**
     * @var float
     * @apiName bestellwert
     */
    protected $amount;

    /**
     * @var integer
     * @apiName laufzeit
     */
    protected $term;

    /**
     * @var string
     * @apiName vorgangskennungShop
     */
    protected $processIdentifierShop;

    /**
     * @var PersonData
     * @apiName       personendaten
     * @transferClass EasyCredit\Transfer\PersonData
     */
    protected $personData;

    /**
     * @var EmploymentData
     * @apiName       beschaeftigungsdaten
     * @transferClass EasyCredit\Transfer\EmploymentData
     */
    protected $employmentData;

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
     * @var Contact
     * @apiName       kontakt
     * @transferClass EasyCredit\Transfer\Contact
     */
    protected $contact;

    /**
     * @var BankData
     * @apiName       bankdatenInput
     * @transferClass EasyCredit\Transfer\BankData
     */
    protected $bankData;

    /**
     * @var AdditionalPersonData
     * @apiName       weitereKaeuferangaben
     * @transferClass EasyCredit\Transfer\AdditionalPersonData
     */
    protected $additionalPersonData;

    /**
     * @var RiskRelatedInfo
     * @apiName       risikorelevanteAngaben
     * @transferClass EasyCredit\Transfer\RiskRelatedInfo
     */
    protected $riskRelatedInfo;

    /**
     * @var CartInfoCollection
     * @apiName       warenkorbinfos
     * @transferClass EasyCredit\Transfer\CartInfoCollection
     */
    protected $cartInfos;
    
    /**
     * @var CallbackUrls
     * @apiName       ruecksprungadressen
     * @transferClass EasyCredit\Transfer\CallbackUrls
     */
    protected $callbackUrls;

    /**
     * @var TechnicalShopParams
     * @apiName       technischeShopparameter
     * @transferClass EasyCredit\Transfer\TechnicalShopParams
     */
    protected $technicalShopParams;
    
    /**
     * @var string
     * @apiName       integrationsart
     */
    protected $integrationType;
    
    /**
     * @return string
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @param string $shopId
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
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
    public function getProcessIdentifierShop()
    {
        return $this->processIdentifierShop;
    }

    /**
     * @param string $processIdentifierShop
     */
    public function setProcessIdentifierShop($processIdentifierShop)
    {
        $this->processIdentifierShop = $processIdentifierShop;
    }


    /**
     * @return PersonData
     */
    public function getPersonData()
    {
        return $this->personData;
    }

    /**
     * @param PersonData $personData
     */
    public function setPersonData($personData)
    {
        $this->personData = $personData;
    }

    /**
     * @return EmploymentData
     */
    public function getEmploymentData()
    {
        return $this->employmentData;
    }

    /**
     * @param EmploymentData $employmentData
     */
    public function setEmploymentData($employmentData)
    {
        $this->employmentData = $employmentData;
    }

    /**
     * @return BillingAddress
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param BillingAddress $billingAddress
     */
    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @return DeliveryAddress
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * @param DeliveryAddress $deliveryAddress
     */
    public function setDeliveryAddress($deliveryAddress)
    {
        $this->deliveryAddress = $deliveryAddress;
    }

    /**
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param Contact $contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    /**
     * @return BankData
     */
    public function getBankData()
    {
        return $this->bankData;
    }

    /**
     * @param BankData $bankData
     */
    public function setBankData($bankData)
    {
        $this->bankData = $bankData;
    }

    /**
     * @return AdditionalPersonData
     */
    public function getAdditionalPersonData()
    {
        return $this->additionalPersonData;
    }

    /**
     * @param AdditionalPersonData $additionalPersonData
     */
    public function setAdditionalPersonData($additionalPersonData)
    {
        $this->additionalPersonData = $additionalPersonData;
    }

    /**
     * @return RiskRelatedInfo
     */
    public function getRiskRelatedInfo()
    {
        return $this->riskRelatedInfo;
    }

    /**
     * @param RiskRelatedInfo $riskRelatedInfo
     */
    public function setRiskRelatedInfo($riskRelatedInfo)
    {
        $this->riskRelatedInfo = $riskRelatedInfo;
    }

    /**
     * @return CartInfoCollection
     */
    public function getCartInfos()
    {
        return $this->cartInfos;
    }

    /**
     * @param CartInfoCollection $cartInfos
     */
    public function setCartInfos($cartInfos)
    {
        $this->cartInfos = $cartInfos;
    }

    /**
     * @return CallbackUrls
     */
    public function getCallbackUrls()
    {
        return $this->callbackUrls;
    }

    /**
     * @param CallbackUrls $callbackUrls
     */
    public function setCallbackUrls($callbackUrls)
    {
        $this->callbackUrls = $callbackUrls;
    }

    /**
     * @return TechnicalShopParams
     */
    public function getTechnicalShopParams()
    {
        return $this->technicalShopParams;
    }

    /**
     * @param TechnicalShopParams $technicalShopParams
     */
    public function setTechnicalShopParams($technicalShopParams)
    {
        $this->technicalShopParams = $technicalShopParams;
    }

    /**
     * @return string
     */
    public function getIntegrationType()
    {
        return $this->integrationType;
    }

    /**
     * @param string $integrationType
     */
    public function setIntegrationType($integrationType)
    {
        $this->integrationType = $integrationType;
    }
 
 
}
