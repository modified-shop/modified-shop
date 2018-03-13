<?php

namespace EasyCredit\Transfer;

/**
 * @author info@senbyte.com
 *
 * @copyright 2017 senByte UG
 * @license
 */

/**
 * Class GetDecisionResponse
 * @package EasyCredit\Transfer
 */
class GetDecisionResponse extends BaseResponse
{
    /**
     * @var Decision
     * @apiName       entscheidung
     * @transferClass EasyCredit\Transfer\Decision
     */
    protected $decision;

    /**
     * @return Decision
     */
    public function getDecision()
    {
        return $this->decision;
    }

    /**
     * @param Decision $decision            
     */
    public function setDecision(Decision $decision)
    {
        $this->decision = $decision;
    }

}