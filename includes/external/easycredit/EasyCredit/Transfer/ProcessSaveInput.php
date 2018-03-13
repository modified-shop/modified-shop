<?php


namespace EasyCredit\Transfer;

/**
 * Class ProcessSaveInput
 *
 * @package EasyCredit\Transfer
 */
class ProcessSaveInput extends AbstractObject
{

    /**
     * @var integer
     * @apiName laufzeit
     */
    protected $term;

    /**
     * @var PersonData
     * @apiName       person
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
     * @var Agreement
     * @apiName       zustimmungserklaerung
     * @transferClass EasyCredit\Transfer\Agreement
     */
    protected $agreement;

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
}
