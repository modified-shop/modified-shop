<?php

namespace EasyCredit\Client\Result;

/**
 * Class EasyCreditClientInitializeResult
 */
class EasyCreditClientInitializeResult extends EasyCreditClientAbstractResult
{
    /**
     * 
     * @var string
     */
    protected $technicalProcessIdentifier;
    
    /**
     * EasyCreditClientInitializeResult constructor.
     * 
     * @param int $httpStatusCode
     * @param array $messages
     * @param bool $error 
     * @param string $technicalProcessIdentifier
     */
    public function __construct(
        $httpStatusCode,
        $messages,
        $error,
        $technicalProcessIdentifier
        )
    {
        parent::__construct($httpStatusCode, $messages, $error);
        $this->technicalProcessIdentifier = $technicalProcessIdentifier;
    }

    /**
     *
     * @return string
     */
    public function getTechnicalProcessIdentifier()
    {
        return $this->technicalProcessIdentifier;
    }
 

}
