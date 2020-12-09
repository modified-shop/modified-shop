<?php

namespace EasyCredit\Client\Result;

use EasyCredit\Transfer\InstallmentPlan;

/**
 * Class EasyCreditClientFinancingDetailsResult
 */
class EasyCreditClientFinancingDetailsResult extends EasyCreditClientAbstractResult
{
    /**
     * @var InstallmentPlan
     */
    protected $installmentPlan;
    
    /**
     * @var string
     */
    protected $repaymentPlanText;
    
    /**
     * EasyCreditClientFinancingDetailsResult constructor.
     * 
     * @param int $httpStatusCode
     * @param array $messages
     * @param bool $error 
     * @param InstallmentPlan $installmentPlan
     * @param string $repaymentPlanText
     */
    public function __construct(
        $httpStatusCode,
        $messages,
        $error,
        $installmentPlan,
        $repaymentPlanText
        )
    {
        parent::__construct($httpStatusCode, $messages, $error);
        $this->installmentPlan   = $installmentPlan;
        $this->repaymentPlanText = $repaymentPlanText;
    }

    /**
     *
     * @return InstallmentPlan
     */
    public function getInstallmentPlan()
    {
        return $this->installmentPlan;
    }

    /**
     *
     * @return string
     */
    public function getRepaymentPlanText()
    {
        return $this->repaymentPlanText;
    }
 

}
