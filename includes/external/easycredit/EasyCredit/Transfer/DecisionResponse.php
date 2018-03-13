<?php


namespace EasyCredit\Transfer;

/**
 * Class DecisionResponse
 *
 * @package EasyCredit\Transfer
 */
class DecisionResponse extends BaseResponse
{

    /**
     * @var string
     * @apiName entscheidungsergebnis
     */
    protected $result;

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param string $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }
}
