<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class BillingCycles
 *
 * PayPal generated billing cycles
 *
 * @package PayPal\Api
 *
 * @property string tenure_type
 * @property integer sequence
 * @property integer total_cycles
 * @property \PayPal\Api\PricingScheme pricing_scheme
 * @property \PayPal\Api\Frequency frequency
 */
class BillingCycles extends PayPalModel
{
    /**
     * The frequency details for this billing cycle. 
     *
     * @param \PayPal\Api\PricingScheme $pricing_scheme
     * 
     * @return $this
     */
    public function setPricingScheme($pricing_scheme)
    {
        $this->pricing_scheme = $pricing_scheme;
        return $this;
    }

    /**
     * The frequency details for this billing cycle. 
     *
     * @return \PayPal\Api\PricingScheme
     */
    public function getPricingScheme()
    {
        return $this->pricing_scheme;
    }

    /**
     * The frequency details for this billing cycle. 
     *
     * @param \PayPal\Api\Frequency $frequency
     * 
     * @return $this
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
        return $this;
    }

    /**
     * The frequency details for this billing cycle. 
     *
     * @return \PayPal\Api\Frequency
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * The tenure type of the billing cycle. In case of a plan having trial cycle, only 2 trial cycles are allowed per plan. The possible values are: 
     * Valid Values: ["REGULAR", "TRIAL"]
     *
     * @param string $tenure_type
     * 
     * @return $this
     */
    public function setTenureType($tenure_type)
    {
        $this->tenure_type = $tenure_type;
        return $this;
    }

    /**
     * The tenure type of the billing cycle. In case of a plan having trial cycle, only 2 trial cycles are allowed per plan. The possible values are: 
     *
     * @return string
     */
    public function getTenureType()
    {
        return $this->tenure_type;
    }

    /**
     * The order in which this cycle is to run among other billing cycles. For example, a trial billing cycle has a sequence of 1 while a regular billing cycle has a sequence of 2, so that trial cycle runs before the regular cycle. 
     *
     * @param integer $sequence
     * 
     * @return $this
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
        return $this;
    }

    /**
     * The order in which this cycle is to run among other billing cycles. For example, a trial billing cycle has a sequence of 1 while a regular billing cycle has a sequence of 2, so that trial cycle runs before the regular cycle. 
     *
     * @return integer
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * The billing cycle sequence. 
     *
     * @param integer
     * 
     * @return $this
     */
    public function setBillingCycleSequence($billing_cycle_sequence)
    {
        $this->billing_cycle_sequence = (int)$billing_cycle_sequence;
        return $this;
    }

    /**
     * The billing cycle sequence. 
     *
     * @return integer
     */
    public function getBillingCycleSequence()
    {
        return $this->billing_cycle_sequence;
    }

    /**
     * The number of times this billing cycle gets executed. Trial billing cycles can only be executed a finite number of times (value between 1 and 999 for total_cycles). Regular billing cycles can be executed infinite times (value of 0 for total_cycles) or a finite number of times (value between 1 and 999 for total_cycles).
     *
     * @param integer $total_cycles
     * 
     * @return $this
     */
    public function setTotalCycles($total_cycles)
    {
        $this->total_cycles = $total_cycles;
        return $this;
    }

    /**
     * The number of times this billing cycle gets executed. Trial billing cycles can only be executed a finite number of times (value between 1 and 999 for total_cycles). Regular billing cycles can be executed infinite times (value of 0 for total_cycles) or a finite number of times (value between 1 and 999 for total_cycles).
     *
     * @return integer
     */
    public function getTotalCycles()
    {
        return $this->total_cycles;
    }

}
