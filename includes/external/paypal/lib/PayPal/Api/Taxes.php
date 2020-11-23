<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;
use PayPal\Converter\FormatConverter;
use PayPal\Validation\NumericValidator;

/**
 * Class Taxes
 *
 * Tax information.
 *
 * @package PayPal\Api
 *
 * @property boolean inclusive
 * @property \PayPal\Api\number percentage
 */
class Taxes extends PayPalModel
{
    /**
     * The tax percentage on the billing amount. 
     *
     * @param string|double $percent
     * 
     * @return $this
     */
    public function setPercentage($percentage)
    {
        NumericValidator::validate($percentage, "Percent");
        $percentage = FormatConverter::formatToPrice($percentage);
        $this->percentage = $percentage;
        return $this;
    }

    /**
     * The tax percentage on the billing amount. 
     *
     * @return string
     */
    public function getPercentage()
    {
        return $this->percentage;
    }

    /**
     * Indicates whether the tax was already included in the billing amount. 
     *
     * @param boolean $inclusive
     * 
     * @return $this
     */
    public function setInclusive($inclusive)
    {
        $this->inclusive = (bool)$inclusive;
        return $this;
    }

    /**
     * Indicates whether the tax was already included in the billing amount. 
     *
     * @return boolean
     */
    public function getInclusive()
    {
        return $this->inclusive;
    }

}
