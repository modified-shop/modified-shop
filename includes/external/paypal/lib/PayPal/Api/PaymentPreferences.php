<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class PaymentPreferences
 *
 * Subscription payment preferences.
 *
 * @package PayPal\Api
 *
 * @property boolean auto_bill_outstanding
 * @property string setup_fee_failure_action
 * @property integer payment_failure_threshold
 * @property \PayPal\Api\Currency setup_fee
 */
class PaymentPreferences extends PayPalModel
{
    /**
     * Indicates whether to automatically bill the outstanding amount in the next billing cycle. 
     *
     * @param boolean $email_verified
     * @return self
     */
    public function setAutoBillOutstanding($auto_bill_outstanding)
    {
        $this->auto_bill_outstanding = (bool)$auto_bill_outstanding;
        return $this;
    }

    /**
     * Indicates whether to automatically bill the outstanding amount in the next billing cycle. 
     *
     * @return boolean
     */
    public function getAutoBillOutstanding()
    {
        return $this->auto_bill_outstanding;
    }

    /**
     * The initial set-up fee for the service. 
     *
     * @param \PayPal\Api\Currency $amount
     * 
     * @return $this
     */
    public function setSetupFee($setup_fee)
    {
        $this->setup_fee = $setup_fee;
        return $this;
    }

    /**
     * The initial set-up fee for the service. 
     *
     * @return \PayPal\Api\Currency
     */
    public function getSetupFee()
    {
        return $this->setup_fee;
    }

    /**
     * The action to take on the subscription if the initial payment for the setup fails.
     * Valid Values: ["CONTINUE", "CANCEL"]
     *
     * @param string $setup_fee_failure_action
     * 
     * @return $this
     */
    public function setSetupFeeFailureAction($setup_fee_failure_action)
    {
        $this->setup_fee_failure_action = $setup_fee_failure_action;
        return $this;
    }

    /**
     * The action to take on the subscription if the initial payment for the setup fails.
     *
     * @return string
     */
    public function getSetupFeeFailureAction()
    {
        return $this->setup_fee_failure_action;
    }

    /**
     * The maximum number of payment failures before a subscription is suspended. For example, if payment_failure_threshold is 2, the subscription automatically updates to the SUSPEND state if two consecutive payments fail. 
     *
     * @param integer $payment_failure_threshold
     * 
     * @return $this
     */
    public function setPaymentFailureThreshold($payment_failure_threshold)
    {
        $this->payment_failure_threshold = (int)$payment_failure_threshold;
        return $this;
    }

    /**
     * The maximum number of payment failures before a subscription is suspended. For example, if payment_failure_threshold is 2, the subscription automatically updates to the SUSPEND state if two consecutive payments fail. 
     *
     * @return integer
     */
    public function getPaymentFailureThreshold()
    {
        return $this->payment_failure_threshold;
    }

}
