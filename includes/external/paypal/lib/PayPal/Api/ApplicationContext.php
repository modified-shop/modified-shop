<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;
use PayPal\Validation\UrlValidator;

/**
 * Class ApplicationContext
 *
 * The application context, which customizes the payer experience during the subscription approval process with PayPal. 
 *
 * @package PayPal\Api
 *
 * @property string brand_name
 * @property string locale
 * @property string shipping_preference
 * @property string user_action
 * @property string return_url
 * @property string cancel_url
 * @property \PayPal\Api\PaymentMethod payment_method
 */
class ApplicationContext extends PayPalModel
{
    /**
     * The label that overrides the business name in the PayPal account on the PayPal site. 
     *
     * @param string $brand_name
     * 
     * @return $this
     */
    public function setBrandName($brand_name)
    {
        $this->brand_name = $brand_name;
        return $this;
    }

    /**
     * The label that overrides the business name in the PayPal account on the PayPal site. 
     *
     * @return string
     */
    public function getBrandName()
    {
        return $this->brand_name;
    }

    /**
     * The BCP 47-formatted locale of pages that the PayPal payment experience shows. PayPal supports a five-character code.  
     *
     * @param string $locale
     * 
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * The BCP 47-formatted locale of pages that the PayPal payment experience shows. PayPal supports a five-character code. 
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
    
    /**
     * The location from which the shipping address is derived.
     * Valid Values: ["GET_FROM_FILE", "NO_SHIPPING", "SET_PROVIDED_ADDRESS"]
     *
     * @param string $shipping_preference
     * 
     * @return $this
     */
    public function setShippingPreference($shipping_preference)
    {
        $this->shipping_preference = $shipping_preference;
        return $this;
    }

    /**
     * The location from which the shipping address is derived.
     *
     * @return string
     */
    public function getShippingPreference()
    {
        return $this->shipping_preference;
    }

    /**
     * Configures the label name to Continue or Subscribe Now for subscription consent experience.
     * Valid Values: ["CONTINUE", "SUBSCRIBE_NOW"]
     *
     * @param string $user_action
     * 
     * @return $this
     */
    public function setUserAction($user_action)
    {
        $this->user_action = $user_action;
        return $this;
    }

    /**
     * Configures the label name to Continue or Subscribe Now for subscription consent experience.
     *
     * @return string
     */
    public function getUserAction()
    {
        return $this->user_action;
    }

    /**
     * The customer and merchant payment preferences. Currently only PAYPAL payment method is supported. 
     *
     * @param \PayPal\Api\Frequency $payment_method
     * 
     * @return $this
     */
    public function setPaymentMethod($payment_method)
    {
        $this->payment_method = $payment_method;
        return $this;
    }

    /**
     * The customer and merchant payment preferences. Currently only PAYPAL payment method is supported. 
     *
     * @return \PayPal\Api\PaymentMethod
     */
    public function getPaymentMethod()
    {
        return $this->payment_method;
    }

    /**
     * The URL where the customer is redirected after the customer approves the payment. 
     *
     * @param string $return_url
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setReturnUrl($return_url)
    {
        UrlValidator::validate($return_url, "Url");
        $this->return_url = $return_url;
        return $this;
    }

    /**
     * The URL where the customer is redirected after the customer approves the payment. 
     *
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->return_url;
    }

    /**
     * The URL where the customer is redirected after the customer approves the payment. 
     *
     * @param string $cancel_url
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setCancelUrl($cancel_url)
    {
        UrlValidator::validate($cancel_url, "Url");
        $this->cancel_url = $cancel_url;
        return $this;
    }

    /**
     * The URL where the customer is redirected after the customer approves the payment. 
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->cancel_url;
    }

}
