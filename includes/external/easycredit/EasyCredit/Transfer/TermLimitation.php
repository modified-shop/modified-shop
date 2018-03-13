<?php

namespace EasyCredit\Transfer;

/**
 * @author info@senbyte.com
 *
 * @copyright 2017 senByte UG
 * @license
 */

/**
 * Class TermLimitation
 * @package EasyCredit\Transfer
 */
class TermLimitation extends AbstractObject
{
    /**
     * @var     int
     * @apiName minimaleLaufzeit
     */
    protected $min;

    /**
     * @var     int
     * @apiName maximaleLaufzeit
     */
    protected $max;

    /**
     * @return int
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param int $min
     */
    public function setMin($min)
    {
        $this->min = $min;
    }

    /**
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param int $max
     */
    public function setMax($max)
    {
        $this->max = $max;
    }

}