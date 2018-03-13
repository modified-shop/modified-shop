<?php

namespace EasyCredit\Transfer;

/**
 * Class InstallmentPlan
 *
 * @package EasyCredit\Transfer
 * @apiName zahlungsplan
 */
class InstallmentPlan extends AbstractObject
{
    /**
     * @var float
     * @apiName gesamtsumme
     */
    protected $amount;

    /**
     * @var InterestRate
     * @apiName       zinsen
     * @transferClass EasyCredit\Transfer\InterestRate
     */
    protected $interestRate;

    /**
     * @var PaymentSchedule
     * @apiName       zahlungsplan
     * @transferClass EasyCredit\Transfer\PaymentSchedule
     */
    protected $paymentSchedule;

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return InterestRate
     */
    public function getInterestRate()
    {
        return $this->interestRate;
    }

    /**
     * @param InterestRate $interestRate
     */
    public function setInterestRate($interestRate)
    {
        $this->interestRate = $interestRate;
    }

    /**
     * @return PaymentSchedule
     */
    public function getPaymentSchedule()
    {
        return $this->paymentSchedule;
    }

    /**
     * @param PaymentSchedule $paymentSchedule
     */
    public function setPaymentSchedule($paymentSchedule)
    {
        $this->paymentSchedule = $paymentSchedule;
    }
}
