<?php

namespace EasyCredit\Transfer;

/**
 * @author info@senbyte.com
 *
 * @copyright 2017 senByte UG
 * @license
 */

/**
 * Class ResidualAmountPurchaseLimitResponse
 * @package EasyCredit\Transfer
 */
class ResidualAmountPurchaseLimitResponse extends BaseResponse
{
    /**
     * @var float
     * @apiName restbetrag
     */
    protected $residualAmount;
    
    /**
     * @return float
     */
    public function getResidualAmount()
    {
        return $this->residualAmount;
    }

    /**
     * @param float $residualAmount
     */
    public function setResidualAmount($residualAmount)
    {
        $this->residualAmount = $residualAmount;
    }
}