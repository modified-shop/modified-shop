<?php

namespace EasyCredit\Client\Result;

use EasyCredit\Transfer\ModelCalculationShort;

/**
 * Class EasyCreditClientBestModelCalculationResult
 */
class EasyCreditClientBestModelCalculationResult extends EasyCreditClientAbstractResult
{
    /**
     * 
     * @var ModelCalculationShort
     */
    protected $bestModelCalculation;
    
    /**
     * EasyCreditClientBestModelCalculationResult constructor.
     * 
     * @param int                   $httpStatusCode
     * @param array                 $messages
     * @param bool                  $error 
     * @param ModelCalculationShort $bestModelCalculation
     */
    public function __construct(
        $httpStatusCode,
        $messages,
        $error,
        $bestModelCalculation
        )
    {
        parent::__construct($httpStatusCode, $messages, $error);
        $this->bestModelCalculation = $bestModelCalculation;
    }

    /**
     *
     * @return ModelCalculationShort
     */
    public function getBestModelCalculation()
    {
        return $this->bestModelCalculation;
    }
 

}
