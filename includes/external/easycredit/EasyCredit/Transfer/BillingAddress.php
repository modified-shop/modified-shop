<?php


namespace EasyCredit\Transfer;

/**
 * Class BillingAddress
 *
 * @package EasyCredit\Transfer
 */
class BillingAddress extends AbstractObject implements AddressInterface
{

    /**
     * @var string
     * @apiName strasseHausNr
     */
    protected $street;

    /**
     * @var string
     * @apiName adresszusatz
     */
    protected $addressAdditional;

    /**
     * @var string
     * @apiName plz
     */
    protected $zip;

    /**
     * @var string
     * @apiName ort
     */
    protected $city;

    /**
     * @var string
     * @apiName land
     */
    protected $countryCode;

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getAddressAdditional()
    {
        return $this->addressAdditional;
    }

    /**
     * @param string $addressAdditional
     */
    public function setAddressAdditional($addressAdditional)
    {
        $this->addressAdditional = $addressAdditional;
    }

    /**
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param string $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }
}
