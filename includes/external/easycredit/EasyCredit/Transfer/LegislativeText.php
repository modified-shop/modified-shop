<?php

namespace EasyCredit\Transfer;

/**
 * @author info@senbyte.com
 *
 * @copyright 2017 senByte UG
 * @license
 */

/**
 * Class LegislativeText
 * @package EasyCredit\Transfer
 */
class LegislativeText extends BaseResponse
{
    /**
     * @var string
     * @apiName zustimmungDatenuebertragungServiceIntegration
     */
    protected $dataProcessingServiceIntegration;
    
    /**
     * @var string
     * @apiName einwilligungsErklaerungServiceIntegration
     */
    protected $agreementServiceIntegration;
    
    /**
     * @var string
     * @apiName zustimmungDatenuebertragungPaymentPage
     */
    protected $dataProcessingPaymentPage;
    
    /**
     * @var string
     * @apiName zustimmungEmailWerbung
     */
    protected $emailAd;

    /**
     * @var string
     * @apiName zustimmungZurHandlungInEigenemNamen
     */
    protected $agreementActingOnOwnBehalf;

    /**
     *
     * @return the string
     */
    public function getDataProcessingServiceIntegration()
    {
        return $this->dataProcessingServiceIntegration;
    }

    /**
     * @param string $dataProcessingServiceIntegration
     */
    public function setDataProcessingServiceIntegration($dataProcessingServiceIntegration)
    {
        $this->dataProcessingServiceIntegration = $dataProcessingServiceIntegration;
    }

    /**
     * @return string
     */
    public function getAgreementServiceIntegration()
    {
        return $this->agreementServiceIntegration;
    }

    /**
     * @param string $agreementServiceIntegration
     */
    public function setAgreementServiceIntegration($agreementServiceIntegration)
    {
        $this->agreementServiceIntegration = $agreementServiceIntegration;
    }

    /**
     * @return string
     */
    public function getDataProcessingPaymentPage()
    {
        return $this->dataProcessingPaymentPage;
    }

    /**
     * @param string $dataProcessingPaymentPage
     */
    public function setDataProcessingPaymentPage($dataProcessingPaymentPage)
    {
        $this->dataProcessingPaymentPage = $dataProcessingPaymentPage;
    }

    /**
     * @return string
     */
    public function getEmailAd()
    {
        return $this->emailAd;
    }

    /**
     * @param string $emailAd
     */
    public function setEmailAd($emailAd)
    {
        $this->emailAd = $emailAd;
    }

    /**
     * @return string
     */
    public function getAgreementActingOnOwnBehalf()
    {
        return $this->agreementActingOnOwnBehalf;
    }

    /**
     * @param string $agreementActingOnOwnBehalf
     */
    public function setAgreementActingOnOwnBehalf($agreementActingOnOwnBehalf)
    {
        $this->agreementActingOnOwnBehalf = $agreementActingOnOwnBehalf;
    }
 
}