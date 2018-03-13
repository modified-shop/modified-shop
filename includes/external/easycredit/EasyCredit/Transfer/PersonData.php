<?php

namespace EasyCredit\Transfer;

/**
 * Class PersonData
 *
 * @package EasyCredit\Transfer
 */
class PersonData extends AbstractObject
{
    const SALUTATION_MRS = "FRAU";

    const SALUTATION_MR = "HERR";

    /**
     * @var string
     * @apiName vorname
     */
    protected $firstName;

    /**
     * @var string
     * @apiName nachname
     */
    protected $lastName;

    /**
     * @var string
     * @apiName anrede
     */
    protected $salutation;

    /**
     * @var \DateTime
     * @apiName   geburtsdatum
     * @apiFormat Y-m-d
     */
    protected $birthDate;

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * @param string $salutation
     */
    public function setSalutation($salutation)
    {
        $this->salutation = $salutation;
    }

    /**
     * @param string|null $format
     *
     * @return \DateTime|string
     */
    public function getBirthDate($format = null)
    {
        if ($format !== null && $this->birthDate instanceof \DateTime) {
            return $this->birthDate->format($format);
        }

        return $this->birthDate;
    }

    /**
     * @param \DateTime $birthDate
     */
    public function setBirthDate($birthDate)
    {
        if (!$birthDate instanceof \DateTime) {
            $birthDate = new \DateTime($birthDate);
        }
        $this->birthDate = $birthDate;
    }
}
