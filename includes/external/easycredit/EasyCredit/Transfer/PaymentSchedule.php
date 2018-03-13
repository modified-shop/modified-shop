<?php

namespace EasyCredit\Transfer;

/**
 * Class PaymentSchedule
 *
 * @package EasyCredit\Transfer
 * @apiName zahlungsplan
 */
class PaymentSchedule extends AbstractObject
{
    /**
     * @var integer
     * @apiName anzahlRaten
     */
    protected $numberOfRates;

    /**
     * @var \DateTime
     * @apiName terminErsteRate
     */
    protected $firstRateDate;

    /**
     * @var \DateTime
     * @apiName terminLetzteRate
     */
    protected $lastRateDate;

    /**
     * @var float
     * @apiName betragRate
     */
    protected $amountOfRate;

    /**
     * @var float
     * @apiName betragLetzteRate
     */
    protected $amountOfLastRate;

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
     * @return \DateTime
     */
    public function getFirstRateDate()
    {
        return $this->firstRateDate;
    }

    /**
     * @param \DateTime $firstRateDate
     */
    public function setFirstRateDate($firstRateDate)
    {
        $this->firstRateDate = $firstRateDate;
    }

    /**
     * @return \DateTime
     */
    public function getLastRateDate()
    {
        return $this->lastRateDate;
    }

    /**
     * @param \DateTime $lastRateDate
     */
    public function setLastRateDate($lastRateDate)
    {
        $this->lastRateDate = $lastRateDate;
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
    public function getAmountOfLastRate()
    {
        return $this->amountOfLastRate;
    }

    /**
     * @param float $amountOfLastRate
     */
    public function setAmountOfLastRate($amountOfLastRate)
    {
        $this->amountOfLastRate = $amountOfLastRate;
    }
}
