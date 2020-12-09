<?php

namespace EasyCredit\Client\Result;

use EasyCredit\Transfer\Decision;

/**
 * Class EasyCreditClientDecisionResult
 */
class EasyCreditClientDecisionResult extends EasyCreditClientAbstractResult
{
    /**
     * 
     * @var Decision
     */
    protected $decision;
    
    /**
     * EasyCreditClientDecisionResult constructor.
     * 
     * @param int      $httpStatusCode
     * @param array    $messages
     * @param bool     $error 
     * @param Decision $decision
     */
    public function __construct(
        $httpStatusCode,
        $messages,
        $error,
        $decision
        )
    {
        parent::__construct($httpStatusCode, $messages, $error);
        $this->decision = $decision;
    }

    /**
     *
     * @return Decision
     */
    public function getDecision()
    {
        return $this->decision;
    }
 

}
