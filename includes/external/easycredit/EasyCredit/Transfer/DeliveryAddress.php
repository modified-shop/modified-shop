<?php


namespace EasyCredit\Transfer;

/**
 * Class DeliveryAddress
 *
 * @package EasyCredit\Transfer
 */
class DeliveryAddress extends BillingAddress
{

    /**
     * @var boolean
     * @apiName packstation
     */
    protected $packstation;
    
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
     * @return boolean
     */
    public function getPackstation()
    {
        return $this->packstation;
    }

    /**
     * @param boolean $packstation
     */
    public function setPackstation($packstation)
    {
        $this->packstation = $packstation;
    }

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
 
}
