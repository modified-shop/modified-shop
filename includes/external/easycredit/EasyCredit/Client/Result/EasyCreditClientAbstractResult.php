<?php

namespace EasyCredit\Client\Result;

/**
 * @author info@senbyte.com 
 *
 * @copyright 2019 senByte UG
 * @license 
 */
abstract class EasyCreditClientAbstractResult
{
    /**
     * @var int
     */
    protected $httpStatusCode;
    
    /**
     *
     * @var bool
     */
    protected $error;
    
    /**
     * @var array
     */
    protected $messages;
    
    /**
     * EasyCreditClientResponse constructor.
     *
     * @param int $httpStatusCode
     * @param array $messages
     * @param bool $error
     * @param mixed $value
     */
    public function __construct(
        $httpStatusCode,
        $messages,
        $error
        )
    {
        $this->httpStatusCode = $httpStatusCode;
        $this->messages       = $messages;
        $this->error          = $error;
    }
    
    /**
     *
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }
    
    /**
     *
     * @return bool
     */
    public function isError()
    {
        return $this->error;
    }
    
    /**
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}