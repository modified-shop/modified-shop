<?php


namespace EasyCredit\Transfer;

/**
 * Class ProcessConfirm
 *
 * @package EasyCredit\Transfer
 */
class ProcessConfirm extends AbstractObject
{

    /**
     * @var string
     * @apiName       shopVorgangskennung
     */
    protected $customIdentifier;

    /**
     * @return string
     */
    public function getCustomIdentifier()
    {
        return $this->customIdentifier;
    }

    /**
     * @param $customIdentifier
     */
    public function setCustomIdentifier($customIdentifier)
    {
        $this->customIdentifier = $customIdentifier;
    }
 

}
