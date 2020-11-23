<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class BillingInfo
 *
 * Billing information for the invoice recipient.
 *
 * @package PayPal\Api
 *
 * @property string email
 * @property string first_name
 * @property string last_name
 * @property string business_name
 * @property \PayPal\Api\InvoiceAddress address
 * @property string language
 * @property string additional_info
 * @property string notification_channel
 * @property \PayPal\Api\Phone phone
 * @property \PayPal\Api\Currency outstanding_balance
 * @property \PayPal\Api\CycleExecutions[] billing_cycles
 * @property string next_billing_time
 * @property string final_payment_time
 * @property integer failed_payments_count
 */
class BillingInfo extends PayPalModel
{
    /**
     * The invoice recipient email address. Maximum length is 260 characters.
     *
     * @param string $email
     * 
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * The invoice recipient email address. Maximum length is 260 characters.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * The invoice recipient first name. Maximum length is 30 characters.
     *
     * @param string $first_name
     * 
     * @return $this
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
        return $this;
    }

    /**
     * The invoice recipient first name. Maximum length is 30 characters.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * The invoice recipient last name. Maximum length is 30 characters.
     *
     * @param string $last_name
     * 
     * @return $this
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
        return $this;
    }

    /**
     * The invoice recipient last name. Maximum length is 30 characters.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * The invoice recipient company business name. Maximum length is 100 characters.
     *
     * @param string $business_name
     * 
     * @return $this
     */
    public function setBusinessName($business_name)
    {
        $this->business_name = $business_name;
        return $this;
    }

    /**
     * The invoice recipient company business name. Maximum length is 100 characters.
     *
     * @return string
     */
    public function getBusinessName()
    {
        return $this->business_name;
    }

    /**
     * The invoice recipient address.
     *
     * @param \PayPal\Api\InvoiceAddress $address
     * 
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * The invoice recipient address.
     *
     * @return \PayPal\Api\InvoiceAddress
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * The language in which the email was sent to the payer. Used only when the payer does not have a PayPal account.
     * Valid Values: ["da_DK", "de_DE", "en_AU", "en_GB", "en_US", "es_ES", "es_XC", "fr_CA", "fr_FR", "fr_XC", "he_IL", "id_ID", "it_IT", "ja_JP", "nl_NL", "no_NO", "pl_PL", "pt_BR", "pt_PT", "ru_RU", "sv_SE", "th_TH", "tr_TR", "zh_CN", "zh_HK", "zh_TW", "zh_XC"]
     *
     * @param string $language
     * 
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * The language in which the email was sent to the payer. Used only when the payer does not have a PayPal account.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Additional information, such as business hours. Maximum length is 40 characters.
     *
     * @param string $additional_info
     * 
     * @return $this
     */
    public function setAdditionalInfo($additional_info)
    {
        $this->additional_info = $additional_info;
        return $this;
    }

    /**
     * Additional information, such as business hours. Maximum length is 40 characters.
     *
     * @return string
     */
    public function getAdditionalInfo()
    {
        return $this->additional_info;
    }

    /**
     * Preferred notification channel of the payer. Email by default.
     * Valid Values: ["SMS", "EMAIL"]
     *
     * @param string $notification_channel
     * 
     * @return $this
     */
    public function setNotificationChannel($notification_channel)
    {
        $this->notification_channel = $notification_channel;
        return $this;
    }

    /**
     * Preferred notification channel of the payer. Email by default.
     *
     * @return string
     */
    public function getNotificationChannel()
    {
        return $this->notification_channel;
    }

    /**
     * Mobile Phone number of the recipient to which SMS will be sent if notification_channel is SMS.
     *
     * @param \PayPal\Api\Phone $phone
     * 
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * Mobile Phone number of the recipient to which SMS will be sent if notification_channel is SMS.
     *
     * @return \PayPal\Api\Phone
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     *  The total pending bill amount, to be paid by the subscriber. 
     *
     * @param \PayPal\Api\Currency $outstanding_balance
     * 
     * @return $this
     */
    public function setOutstandingBalance($outstanding_balance)
    {
        $this->outstanding_balance = $outstanding_balance;
        return $this;
    }

    /**
     *  The total pending bill amount, to be paid by the subscriber. 
     *
     * @return \PayPal\Api\Currency
     */
    public function getOutstandingBalance()
    {
        return $this->outstanding_balance;
    }

    /**
     * Array of BillingCycles for this billing plan.
     *
     * @param \PayPal\Api\CycleExecutions[] $billing_cycles
     * 
     * @return $this
     */
    public function setCycleExecutions($cycle_executions)
    {
        $this->cycle_executions = $cycle_executions;
        return $this;
    }

    /**
     * Array of BillingCycles for this billing plan.
     *
     * @return \PayPal\Api\CycleExecutions[]
     */
    public function getCycleExecutions()
    {
        return $this->cycle_executions;
    }

    /**
     * Append BillingCycles to the list.
     *
     * @param \PayPal\Api\CycleExecutions $cycle_executions
     * @return $this
     */
    public function addCycleExecutions($cycle_executions)
    {
        if (!$this->getCycleExecutions()) {
            return $this->setCycleExecutions(array($cycle_executions));
        } else {
            return $this->setCycleExecutions(
                array_merge($this->getCycleExecutions(), array($cycle_executions))
            );
        }
    }

    /**
     * Remove BillingCycles from the list.
     *
     * @param \PayPal\Api\CycleExecutions $billing_cycles
     * @return $this
     */
    public function removeCycleExecutions($cycle_executions)
    {
        return $this->setCycleExecutions(
            array_diff($this->getCycleExecutions(), array($cycle_executions))
        );
    }

    /**
     * The next date and time for billing this subscription. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @param string $next_billing_time
     * 
     * @return $this
     */
    public function setNextBillingTime($next_billing_time)
    {
        $this->next_billing_time = $next_billing_time;
        return $this;
    }

    /**
     * The next date and time for billing this subscription. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @return string
     */
    public function getNextBillingTime()
    {
        return $this->next_billing_time;
    }

    /**
     * The date and time when the final billing cycle occurs. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @param string $final_payment_time
     * 
     * @return $this
     */
    public function setFinalPaymentTime($final_payment_time)
    {
        $this->final_payment_time = $final_payment_time;
        return $this;
    }

    /**
     * The date and time when the final billing cycle occurs. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @return string
     */
    public function getFinalPaymentTime()
    {
        return $this->final_payment_time;
    }

    /**
     * The number of consecutive payment failures. Resets to 0 after a successful payment. If this reaches the payment_failure_threshold value, the subscription updates to the SUSPENDED state. 
     *
     * @param integer $failed_payments_count
     * 
     * @return $this
     */
    public function setFailedPaymentsCount($failed_payments_count)
    {
        $this->failed_payments_count = (int)$failed_payments_count;
        return $this;
    }

    /**
     * The number of consecutive payment failures. Resets to 0 after a successful payment. If this reaches the payment_failure_threshold value, the subscription updates to the SUSPENDED state. 
     *
     * @return integer
     */
    public function getFailedPaymentsCount()
    {
        return $this->failed_payments_count;
    }

}
