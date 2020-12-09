<?php

namespace EasyCredit\Client\Result;

use EasyCredit\Transfer\ModelCalculation;

/**
 * Class EasyCreditClientModelCalculationResult
 */
class EasyCreditClientModelCalculationResult extends EasyCreditClientAbstractResult
{
    /**
     * 
     * @var ModelCalculation
     */
    protected $modelCalculation;
    
    /**
     * EasyCreditClientModelCalculationResult constructor.
     * 
     * @param int              $httpStatusCode
     * @param array            $messages
     * @param bool             $error 
     * @param ModelCalculation $modelCalculation
     */
    public function __construct(
        $httpStatusCode,
        $messages,
        $error,
        $modelCalculation
        )
    {
        parent::__construct($httpStatusCode, $messages, $error);
        $this->modelCalculation = $modelCalculation;
    }

    /**
     *
     * @return ModelCalculation
     */
    public function getModelCalculation()
    {
        return $this->modelCalculation;
    }
 

}
