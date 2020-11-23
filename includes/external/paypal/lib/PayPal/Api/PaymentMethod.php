<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class PaymentMethod
 *
 * The customer and merchant payment preferences. Currently only PAYPAL payment method is supported. 
 *
 * @package PayPal\Api
 *
 * @property string payer_selected
 * @property string payee_preferred
 * @property string category
 */
class PaymentMethod extends PayPalModel
{
    /**
     * The customer-selected payment method on the merchant site. 
     *
     * @param string $payer_selected
     * 
     * @return $this
     */
    public function setPayerSelected($payer_selected)
    {
        $this->payer_selected = $payer_selected;
        return $this;
    }

    /**
     * The customer-selected payment method on the merchant site. 
     *
     * @return string
     */
    public function getPayerSelected()
    {
        return $this->payer_selected;
    }

    /**
     * The location from which the shipping address is derived.
     * Valid Values: ["UNRESTRICTED", "IMMEDIATE_PAYMENT_REQUIRED"]
     *
     * @param string $payee_preferred
     * 
     * @return $this
     */
    public function setPayeePreferred($payee_preferred)
    {
        $this->payee_preferred = $payee_preferred;
        return $this;
    }

    /**
     * The location from which the shipping address is derived.
     *
     * @return string
     */
    public function getPayeePreferred()
    {
        return $this->payee_preferred;
    }
    
    /**
     * The location from which the shipping address is derived.
     * Valid Values: ["CUSTOMER_PRESENT_SINGLE_PURCHASE", "CUSTOMER_NOT_PRESENT_RECURRING", "CUSTOMER_PRESENT_RECURRING_FIRST", "CUSTOMER_PRESENT_UNSCHEDULED", "CUSTOMER_NOT_PRESENT_UNSCHEDULED", "MAIL_ORDER_TELEPHONE_ORDER"]
     *
     * @param string $category
     * 
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * The location from which the shipping address is derived.
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

}
