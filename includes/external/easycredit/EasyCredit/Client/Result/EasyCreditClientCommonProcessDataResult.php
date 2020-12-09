<?php

namespace EasyCredit\Client\Result;

use EasyCredit\Transfer\CommonProcessData;

/**
 * Class EasyCreditClientCommonProcessDataResult
 */
class EasyCreditClientCommonProcessDataResult extends EasyCreditClientAbstractResult
{
    /**
     * 
     * @var CommonProcessData
     */
    protected $commonProcessData;
    
    /**
     * EasyCreditClientCommonProcessDataResult constructor.
     * 
     * @param int $httpStatusCode
     * @param array $messages
     * @param bool $error 
     * @param CommonProcessData $commonProcessData
     */
    public function __construct(
        $httpStatusCode,
        $messages,
        $error,
        $commonProcessData
        )
    {
        parent::__construct($httpStatusCode, $messages, $error);
        $this->commonProcessData = $commonProcessData;
    }

    /**
     *
     * @return EasyCreditClientCommonProcessDataResult
     */
    public function getCommonProcessData()
    {
        return $this->commonProcessData;
    }
 

}
