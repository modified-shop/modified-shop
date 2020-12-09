<?php

namespace EasyCredit\Client\Result;

use EasyCredit\Transfer\LegislativeText;

/**
 * Class EasyCreditClientLegislativeTextResult
 */
class EasyCreditClientLegislativeTextResult extends EasyCreditClientAbstractResult
{
    /**
     * 
     * @var LegislativeText
     */
    protected $legislativeText;
    
    /**
     * EasyCreditClientLegislativeTextResult constructor.
     * 
     * @param int             $httpStatusCode
     * @param array           $messages
     * @param bool            $error 
     * @param LegislativeText $legislativeText
     */
    public function __construct(
        $httpStatusCode,
        $messages,
        $error,
        $legislativeText
        )
    {
        parent::__construct($httpStatusCode, $messages, $error);
        $this->legislativeText = $legislativeText;
    }

    /**
     *
     * @return LegislativeText
     */
    public function getLegislativeText()
    {
        return $this->legislativeText;
    }
 

}
