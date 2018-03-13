<?php


namespace EasyCredit\Transfer;

/**
 * Class AdditionalPersonData
 *
 * @package EasyCredit\Transfer
 */
class AdditionalPersonData extends AbstractObject
{

    /**
     * @var string
     * @apiName telefonnummer
     */
    protected $phoneNumber;

    /**
     * @var string
     * @apiName titel
     */
    protected $title;

    /**
     * @var string
     * @apiName geburtsname
     */
    protected $birthName;

    /**
     * @var string
     * @apiName geburtsort
     */
    protected $birthCity;

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getBirthName()
    {
        return $this->birthName;
    }

    /**
     * @param string $birthName
     */
    public function setBirthName($birthName)
    {
        $this->birthName = $birthName;
    }

    /**
     * @return string
     */
    public function getBirthCity()
    {
        return $this->birthCity;
    }

    /**
     * @param string $birthCity
     */
    public function setBirthCity($birthCity)
    {
        $this->birthCity = $birthCity;
    }
}
