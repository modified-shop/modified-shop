<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class Transactions
 *
 * 
 *
 * @package PayPal\Api
 *
 */
class Capabilities extends PayPalModel
{
    /**
     * The partner-provided tracking ID.
     *
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * The partner-provided tracking ID.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The partner-provided tracking ID.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * The partner-provided tracking ID.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}
