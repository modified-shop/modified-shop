<?php

namespace EasyCredit\Transfer;

/**
 * Class FinancingDetails
 * @package EasyCredit\Transfer
 */
class FinancingDetails extends BaseResponse
{
    /**
     * @var InstallmentPlan
     * @apiName       ratenplan
     * @transferClass EasyCredit\Transfer\InstallmentPlan
     */
    protected $installmentPlan;


    /**
     * @var string
     * @apiName tilgungsplanText
     */
    protected $repaymentPlanText;

    /**
     * @return InstallmentPlan
     */
    public function getInstallmentPlan()
    {
        return $this->installmentPlan;
    }

    /**
     * @param InstallmentPlan $installmentPlan
     */
    public function setInstallmentPlan($installmentPlan)
    {
        $this->installmentPlan = $installmentPlan;
    }

    /**
     * @return string
     */
    public function getRepaymentPlanText()
    {
        return $this->repaymentPlanText;
    }

    /**
     * @param string $repaymentPlanText
     */
    public function setRepaymentPlanText($repaymentPlanText)
    {
        $this->repaymentPlanText = $repaymentPlanText;
    }
}
