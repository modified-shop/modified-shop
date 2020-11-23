<?php

namespace PayPal\Api;

use PayPal\Api\PricingScheme;

/**
 * Class PricingSchemes
 *
 * The active pricing scheme for this billing cycle.
 *
 * @package PayPal\Api
 *
 * @property string create_time
 * @property string update_time
 */
class PricingSchemes extends PricingScheme
{
    /**
     * Array of PricingSchemes for this billing plan.
     *
     * @param \PayPal\Api\PricingSchemes[] $PricingScheme
     * 
     * @return $this
     */
    public function setPricingSchemes($pricing_schemes)
    {
        $this->pricing_schemes = $pricing_schemes;
        return $this;
    }

    /**
     * Array of PricingSchemes for this billing plan.
     *
     * @return \PayPal\Api\PricingSchemes[]
     */
    public function getPricingSchemes()
    {
        return $this->pricing_schemes;
    }

    /**
     * Append PricingSchemes to the list.
     *
     * @param \PayPal\Api\PricingSchemes $pricing_schemes
     * @return $this
     */
    public function addPricingSchemes($pricing_schemes)
    {
        if (!$this->getPricingSchemes()) {
            return $this->setPricingSchemes(array($pricing_schemes));
        } else {
            return $this->setPricingSchemes(
                array_merge($this->getPricingSchemes(), array($pricing_schemes))
            );
        }
    }

    /**
     * Remove PricingSchemes from the list.
     *
     * @param \PayPal\Api\PricingSchemes $billing_cycles
     * @return $this
     */
    public function removePricingSchemes($pricing_schemes)
    {
        return $this->setPricingSchemes(
            array_diff($this->getPricingSchemes(), array($pricing_schemes))
        );
    }

}
