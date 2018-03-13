<?php

namespace EasyCredit\Transfer;

/**
 * @author info@senbyte.com
 *
 * @copyright 2017 senByte UG
 * @license
 */

/**
 * Class CommonProcessData
 * @package EasyCredit\Transfer
 */
class CommonProcessData extends AbstractObject
{
    /**
     * @var     string
     * @apiName shopKennung
     */
    protected $webShopId;

    /**
     * @var string
     * @apiName tbVorgangskennung
     */
    protected $tbaId;

    /**
     * @var     string
     * @apiName shopVorgangskennung
     */
    protected $customShopId;

    /**
     * @var string
     * @apiName fachlicheVorgangskennung
     */
    protected $technicalTbaId;

    /**
     * @var string
     * @apiName deviceIdentToken
     */
    protected $deviceIdentToken;

    /**
     * @var string
     * @apiName status
     */
    protected $status;

    /**
     * @var string
     * @apiName urlVorvertraglicheInformationen
     */
    protected $contractInfoURL;

    /**
     * @var string
     * @apiName haendlerspezifischerZinssatz
     */
    protected $interestRate;

    /**
     * @var InstallmentPlanCollection
     * @apiName       moeglicheRatenplaene
     * @transferClass EasyCredit\Transfer\InstallmentPlanCollection
     */
    protected $installmentPlanCollection;

    /**
     * @return string
     */
    public function getWebShopId()
    {
        return $this->webShopId;
    }

    /**
     * @param $webShopId
     */
    public function setWebShopId($webShopId)
    {
        $this->webShopId = $webShopId;
    }

    /**
     * @return string
     */
    public function getTbaId()
    {
        return $this->tbaId;
    }

    /**
     * @param $tbaId
     */
    public function setTbaId($tbaId)
    {
        $this->tbaId = $tbaId;
    }

    /**
     * @return string
     */
    public function getCustomShopId()
    {
        return $this->customShopId;
    }

    /**
     * @param $customShopId
     */
    public function setCustomShopId($customShopId)
    {
        $this->customShopId = $customShopId;
    }

    /**
     * @return string
     */
    public function getTechnicalTbaId()
    {
        return $this->technicalTbaId;
    }

    /**
     * @param $technicalTbaId
     */
    public function setTechnicalTbaId($technicalTbaId)
    {
        $this->technicalTbaId = $technicalTbaId;
    }

    /**
     * @return string
     */
    public function getDeviceIdentToken()
    {
        return $this->deviceIdentToken;
    }

    /**
     * @param $deviceIdentToken
     */
    public function setDeviceIdentToken($deviceIdentToken)
    {
        $this->deviceIdentToken = $deviceIdentToken;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getContractInfoURL()
    {
        return $this->contractInfoURL;
    }

    /**
     * @param $contractInfoURL
     */
    public function setContractInfoURL($contractInfoURL)
    {
        $this->contractInfoURL = $contractInfoURL;
    }

    /**
     * @return string
     */
    public function getInterestRate()
    {
        return $this->interestRate;
    }

    /**
     * @param $interestRate
     */
    public function setInterestRate($interestRate)
    {
        $this->interestRate = $interestRate;
    }

    /**
     * @return InstallmentPlanCollection
     */
    public function getInstallmentPlanCollection()
    {
        return $this->installmentPlanCollection;
    }

    /**
     * @param InstallmentPlanCollection $installmentPlanCollection
     */
    public function setInstallmentPlanCollection(InstallmentPlanCollection $installmentPlanCollection)
    {
        $this->installmentPlanCollection = $installmentPlanCollection;
    }
}