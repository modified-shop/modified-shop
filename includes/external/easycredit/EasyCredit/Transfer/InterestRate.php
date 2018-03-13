<?php

namespace EasyCredit\Transfer;

/**
 * Class InterestRate
 *
 * @package EasyCredit\Transfer
 * @apiName zinsen
 */
class InterestRate extends AbstractObject
{
    /**
     * @var float
     * @apiName effektivzins
     */
    protected $effectiveInterest;

    /**
     * @var float
     * @apiName nominalzins
     */
    protected $nominalInterest;

    /**
     * @var float
     * @apiName anfallendeZinsen
     */
    protected $accruingInterest;

    /**
     * @return float
     */
    public function getEffectiveInterest()
    {
        return $this->effectiveInterest;
    }

    /**
     * @param float $effectiveInterest
     */
    public function setEffectiveInterest($effectiveInterest)
    {
        $this->effectiveInterest = $effectiveInterest;
    }

    /**
     * @return float
     */
    public function getNominalInterest()
    {
        return $this->nominalInterest;
    }

    /**
     * @param float $nominalInterest
     */
    public function setNominalInterest($nominalInterest)
    {
        $this->nominalInterest = $nominalInterest;
    }

    /**
     * @return float
     */
    public function getAccruingInterest()
    {
        return $this->accruingInterest;
    }

    /**
     * @param float $accruingInterest
     */
    public function setAccruingInterest($accruingInterest)
    {
        $this->accruingInterest = $accruingInterest;
    }
}
