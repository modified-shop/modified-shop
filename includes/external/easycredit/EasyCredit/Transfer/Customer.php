<?php

namespace EasyCredit\Transfer;

/**
 * Class AbstractTransferObject
 *
 * @package EasyCredit\Transfer
 */
class Customer extends AbstractObject
{
    /**
     * @var PersonData
     * @transferClass EasyCredit\Transfer\PersonData
     */
    protected $personData;

    /**
     * @var EmploymentData
     * @transferClass EasyCredit\Transfer\EmploymentData
     */
    protected $employmentData;

    /**
     * @var Contact
     * @transferClass EasyCredit\Transfer\Contact
     */
    protected $contact;
    
    /**
     * @var AdditionalPersonData
     * @transferClass EasyCredit\Transfer\AdditionalPersonData
     */
    protected $additionalPersonData;

    /**
     * Customer constructor.
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->employmentData       = new EmploymentData();
        $this->contact              = new Contact();
        $this->personData           = new PersonData();
        $this->additionalPersonData = new AdditionalPersonData();

        parent::__construct($data);
    }

    /**
     * Returns the PersonData
     *
     * @return PersonData
     */
    public function getPersonData()
    {
        return $this->personData;
    }

    /**
     * Sets the PersonData
     *
     * @param PersonData $personData
     */
    public function setPersonData(PersonData $personData)
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
 
}
