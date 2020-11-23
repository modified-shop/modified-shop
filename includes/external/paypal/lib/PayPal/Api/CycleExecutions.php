<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class CycleExecutions
 *
 * PayPal generated billing cycles
 *
 * @package PayPal\Api
 *
 * @property string tenure_type
 * @property integer sequence
 * @property integer cycles_completed
 * @property integer cycles_remaining
 * @property integer current_pricing_scheme_version
 * @property integer total_cycles
 */
class CycleExecutions extends PayPalModel
{
    /**
     * The type of the billing cycle
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
     * The type of the billing cycle
     *
     * @return string
     */
    public function getTenureType()
    {
        return $this->tenure_type;
    }

    /**
     * The order in which to run this cycle among other billing cycles. 
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
     * The order in which to run this cycle among other billing cycles. 
     *
     * @return integer
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * The number of billing cycles that have completed. 
     *
     * @param integer $cycles_completed
     * 
     * @return $this
     */
    public function setCyclesCompleted($cycles_completed)
    {
        $this->cycles_completed = (int)$cycles_completed;
        return $this;
    }

    /**
     * The number of billing cycles that have completed. 
     *
     * @return integer
     */
    public function getCyclesCompleted()
    {
        return $this->cycles_completed;
    }

    /**
     * For a finite billing cycle, cycles_remaining is the number of remaining cycles. 
     *
     * @param integer $cycles_remaining
     * 
     * @return $this
     */
    public function setCyclesRemaining($cycles_remaining)
    {
        $this->cycles_remaining = (int)$cycles_remaining;
        return $this;
    }

    /**
     * For a finite billing cycle, cycles_remaining is the number of remaining cycles. 
     *
     * @return integer
     */
    public function getCyclesRemaining()
    {
        return $this->cycles_remaining;
    }

    /**
     * The active pricing scheme version for the billing cycle. 
     *
     * @param integer $current_pricing_scheme_version
     * 
     * @return $this
     */
    public function setCurrentPricingSchemeVersion($current_pricing_scheme_version)
    {
        $this->current_pricing_scheme_version = $current_pricing_scheme_version;
        return $this;
    }

    /**
     * The active pricing scheme version for the billing cycle. 
     *
     * @return integer
     */
    public function getCurrentPricingSchemeVersion()
    {
        return $this->current_pricing_scheme_version;
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
        $this->total_cycles = (int)$total_cycles;
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
