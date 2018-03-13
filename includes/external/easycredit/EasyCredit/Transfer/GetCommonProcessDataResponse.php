<?php

namespace EasyCredit\Transfer;

/**
 * @author info@senbyte.com
 *
 * @copyright 2017 senByte UG
 * @license
 */

/**
 * Class GetCommonProcessDataResponse
 * @package EasyCredit\Transfer
 */
class GetCommonProcessDataResponse extends BaseResponse
{
    /**
     * @var CommonProcessData
     * @apiName       allgemeineVorgangsdaten
     * @transferClass EasyCredit\Transfer\CommonProcessData
     */
    protected $commonProcessData;

    /**
     * @var string
     * @apiName tilgungsplanText
     */
    protected $paymentPlanText;
    
    /**
     * @return CommonProcessData
     */
    public function getCommonProcessData()
    {
        return $this->commonProcessData;
    }

    /**
     * @param CommonProcessData $commonProcessData
     */
    public function setCommonProcessData(CommonProcessData $commonProcessData)
    {
        $this->commonProcessData = $commonProcessData;
    }

    /**
     * @return string
     */
    public function getPaymentPlanText()
    {
        return $this->paymentPlanText;
    }

    /**
     * @param string $paymentPlanText
     */
    public function setPaymentPlanText($paymentPlanText)
    {
        $this->paymentPlanText = $paymentPlanText;
    }
 
}