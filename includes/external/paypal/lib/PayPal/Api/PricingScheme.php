<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class PricingScheme
 *
 * The active pricing scheme for this billing cycle.
 *
 * @package PayPal\Api
 *
 * @property string create_time
 * @property string update_time
 * @property \PayPal\Api\Currency fixed_price
 */
class PricingScheme extends PayPalModel
{
    /**
     * The fixed amount to charge for the subscription. The changes to fixed amount are applicable to both existing and future subscriptions. For existing subscriptions, payments within 10 days of price change are not affected. 
     *
     * @param \PayPal\Api\Currency $fixed_price
     * 
     * @return $this
     */
    public function setFixedPrice($fixed_price)
    {
        $this->fixed_price = $fixed_price;
        return $this;
    }

    /**
     * The fixed amount to charge for the subscription. The changes to fixed amount are applicable to both existing and future subscriptions. For existing subscriptions, payments within 10 days of price change are not affected. 
     *
     * @return \PayPal\Api\Currency
     */
    public function getFixedPrice()
    {
        return $this->fixed_price;
    }

    /**
     * Time when the pricing scheme was created. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @param string $create_time
     * 
     * @return $this
     */
    public function setCreateTime($create_time)
    {
        $this->create_time = $create_time;
        return $this;
    }

    /**
     * Time when the pricing scheme was created. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @return string
     */
    public function getCreateTime()
    {
        return $this->create_time;
    }

    /**
     * Time when this pricing scheme was updated. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @param string $update_time
     * 
     * @return $this
     */
    public function setUpdateTime($update_time)
    {
        $this->update_time = $update_time;
        return $this;
    }

    /**
     * Time when this pricing scheme was updated. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->update_time;
    }

}
