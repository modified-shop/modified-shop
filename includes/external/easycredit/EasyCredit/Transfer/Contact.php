<?php


namespace EasyCredit\Transfer;

/**
 * Class Contact
 *
 * @package EasyCredit\Transfer
 */
class Contact extends AbstractObject
{

    /**
     * @var string
     * @apiName email
     */
    protected $email;

    /**
     * @var string
     * @apiName mobilfunknummer
     */
    protected $mobilphone;

    /**
     * @var boolean
     * @apiName pruefungMobilfunknummerUebergehen
     */
    protected $mobilphoneVerify;

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getMobilphone()
    {
        return $this->mobilphone;
    }

    /**
     * @param string $mobilphone
     */
    public function setMobilphone($mobilphone)
    {
        $this->mobilphone = $mobilphone;
    }

    /**
     * @return boolean
     */
    public function getMobilphoneVerify()
    {
        return $this->mobilphoneVerify;
    }

    /**
     * @param boolean $mobilphoneVerify
     */
    public function setMobilphoneVerify($mobilphoneVerify)
    {
        $this->mobilphoneVerify = $mobilphoneVerify;
    }
}
