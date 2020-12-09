<?php


namespace EasyCredit\Transfer;

/**
 * Class ProcessVerifyMTan
 *
 * @package EasyCredit\Transfer
 */
class ProcessVerifyMTan extends AbstractObject
{

    /**
     * @var string
     * @apiName       mTAN
     */
    protected $mTan;

    /**
     *
     * @return string
     */
    public function getMTan()
    {
        return $this->mTan;
    }

    /**
     *
     * @param $mTan
     */
    public function setMTan($mTan)
    {
        $this->mTan = $mTan;
    }
 

}
