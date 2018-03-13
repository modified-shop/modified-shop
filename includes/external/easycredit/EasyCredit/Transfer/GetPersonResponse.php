<?php

namespace EasyCredit\Transfer;

/**
 * Class GetPersonResponse
 * @package EasyCredit\Transfer
 */
class GetPersonResponse extends BaseResponse
{

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
     * @var BankDataSepa
     * @apiName       bankverbindung
     * @transferClass EasyCredit\Transfer\BankDataSepa
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
     * @return BankDataSepa
     */
    public function getBankData()
    {
        return $this->bankData;
    }

    /**
     * @param BankDataSepa $bankData
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
}
