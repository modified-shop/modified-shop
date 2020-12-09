<?php

namespace EasyCredit\Client\Result;

/**
 * Class EasyCreditClientBaseResult
 */
class EasyCreditClientBaseResult extends EasyCreditClientAbstractResult
{
    /**
     * 
     * @var bool
     */
    protected $processed;
    
    /**
     * EasyCreditClientResponse constructor.
     * 
     * @param int $httpStatusCode
     * @param array $messages
     * @param bool $error 
     * @param mixed $processed
     */
    public function __construct(
        $httpStatusCode,
        $messages,
        $error,
        $processed
        )
    {
        parent::__construct($httpStatusCode, $messages, $error);
        $this->processed = $processed;
    }

    /**
     *
     * @return bool
     */
    public function isProcessed()
    {
        return $this->processed;
    }
 
    
}
