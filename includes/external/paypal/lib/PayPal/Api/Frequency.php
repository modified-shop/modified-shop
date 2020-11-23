<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class Frequency
 *
 * The frequency details for this billing cycle. 
 *
 * @package PayPal\Api
 *
 * @property string interval_unit
 * @property integer interval_count
 */
class Frequency extends PayPalModel
{
    /**
     * The interval at which the subscription is charged or billed.
     * Valid Values: ["DAY", "WEEK", "MONTH", "YEAR"]
     *
     * @param self $financing_options
     *
     * @return $this
     */
    public function setIntervalUnit($interval_unit)
    {
        $this->interval_unit = $interval_unit;
        return $this;
    }

    /**
     * The interval at which the subscription is charged or billed.
     *
     * @return string
     */
    public function getIntervalUnit()
    {
        return $this->interval_unit;
    }

    /**
     * The number of intervals after which a subscriber is billed. For example, if the interval_unit is DAY with an interval_count of 2, the subscription is billed once every two days. The following table lists the maximum allowed values for the interval_count for each interval_unit 
     *
     * @param integer $interval_count
     * 
     * @return $this
     */
    public function setIntervalCount($interval_count)
    {
        $this->interval_count = (int)$interval_count;
        return $this;
    }

    /**
     * The number of intervals after which a subscriber is billed. For example, if the interval_unit is DAY with an interval_count of 2, the subscription is billed once every two days. The following table lists the maximum allowed values for the interval_count for each interval_unit 
     *
     * @return integer
     */
    public function getIntervalCount()
    {
        return $this->interval_count;
    }

}
