<?php

namespace EasyCredit\Client\Result;

/**
 * Class EasyCreditClientResidualAmountLimitResult
 */
class EasyCreditClientResidualAmountLimitResult extends EasyCreditClientAbstractResult
{
    /**
     * 
     * @var float
     */
    protected $residualAmountLimit;
    
    /**
     * EasyCreditClientResidualAmountLimitResult constructor.
     * 
     * @param int   $httpStatusCode
     * @param array $messages
     * @param bool  $error 
     * @param float $residualAmountLimit
     */
    public function __construct(
        $httpStatusCode,
        $messages,
        $error,
        $residualAmountLimit
        )
    {
        parent::__construct($httpStatusCode, $messages, $error);
        $this->residualAmountLimit = $residualAmountLimit;
    }

    /**
     *
     * @return float
     */
    public function getResidualAmountLimit()
    {
        return $this->residualAmountLimit;
    }
 

}
