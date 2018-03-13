<?php

namespace EasyCredit\Transfer;

/**
 * Class ModelCalculationShort
 *
 * @package EasyCredit\Transfer
 * @apiName kurzBeispielrechnungResponse
 */
class ModelCalculationShort extends AbstractObject
{

    /**
     * @var int
     * @apiName anzahlRaten
     */
    protected $numberOfRates;

    /**
     * @var float
     * @apiName betragRate
     */
    protected $amountOfRate;

    /**
     * @var float
     * @apiName gesamtsumme
     */
    protected $amount;

    /**
     * @return int
     */
    public function getNumberOfRates()
    {
        return $this->numberOfRates;
    }

    /**
     * @param int $numberOfRates
     */
    public function setNumberOfRates($numberOfRates)
    {
        $this->numberOfRates = $numberOfRates;
    }

    /**
     * @return float
     */
    public function getAmountOfRate()
    {
        return $this->amountOfRate;
    }

    /**
     * @param float $amountOfRate
     */
    public function setAmountOfRate($amountOfRate)
    {
        $this->amountOfRate = $amountOfRate;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }
}
