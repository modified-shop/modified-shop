<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class Subscriber
 *
 * The subscriber request information.
 *
 * @package PayPal\Api
 *
 * @property string payer_id
 * @property string email_address
 * @property \PayPal\Api\Name[] name
 * @property \PayPal\Api\ShippingAddress shipping_address
 * @property \PayPal\Api\PaymentSource payment_source
 */
class Subscriber extends PayPalModel
{
    /**
     * The PayPal-assigned ID for the payer. 
     *
     * @param string $payer_id
     * 
     * @return $this
     */
    public function setPayerId($payer_id)
    {
        $this->payer_id = $payer_id;
        return $this;
    }

    /**
     * The PayPal-assigned ID for the payer. 
     *
     * @return string
     */
    public function getPayerId()
    {
        return $this->payer_id;
    }

    /**
     * Name of the recipient at this address.
     *
     * @param \PayPal\Api\Name $name
     * 
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Name of the recipient at this address.
     *
     * @return \PayPal\Api\Name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Email address representing the payer. 127 characters max.
     *
     * @param string $email_address
     * 
     * @return $this
     */
    public function setEmailAddress($email_address)
    {
        $this->email_address = $email_address;
        return $this;
    }

    /**
     * Email address representing the payer. 127 characters max.
     *
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->email_address;
    }

    /**
     * Name of the recipient at this address.
     *
     * @param \PayPal\Api\ShippingAddress $shipping_address
     * 
     * @return $this
     */
    public function setShippingAddress($shipping_address)
    {
        $this->shipping_address = $shipping_address;
        return $this;
    }

    /**
     * Name of the recipient at this address.
     *
     * @return \PayPal\Api\ShippingAddress
     */
    public function getShippingAddress()
    {
        return $this->shipping_address;
    }

    /**
     * The payment source used to fund the payment. 
     *
     * @param \PayPal\Api\PaymentSource $payment_source
     * 
     * @return $this
     */
    public function setPaymentSource($payment_source)
    {
        $this->payment_source = $payment_source;
        return $this;
    }

    /**
     * The payment source used to fund the payment. 
     *
     * @return \PayPal\Api\PaymentSource
     */
    public function getPaymentSource()
    {
        return $this->payment_source;
    }

}
