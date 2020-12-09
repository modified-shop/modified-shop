<?php

namespace EasyCredit\Client\Result;

/**
 * Class EasyCreditClientDecideResult
 */
class EasyCreditClientDecideResult extends EasyCreditClientAbstractResult
{
    /**
     * 
     * @var string
     */
    protected $decision;
    
    /**
     * EasyCreditClientDecideResult constructor.
     * 
     * @param int    $httpStatusCode
     * @param array  $messages
     * @param bool   $error 
     * @param string $decision
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
     * @return string
     */
    public function getDecision()
    {
        return $this->decision;
    }
 

}
