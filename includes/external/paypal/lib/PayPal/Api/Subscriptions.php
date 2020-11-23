<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;
use PayPal\Common\PayPalResourceModel;
use PayPal\Rest\ApiContext;
use PayPal\Transport\PayPalRestCall;
use PayPal\Validation\ArgumentValidator;
use PayPal\Core\PayPalConstants;

/**
 * Class Subscriptions
 *
 * Creates a subscription. 
 *
 * @package PayPal\Api
 *
 * @property string id
 * @property string status
 * @property string status_change_note
 * @property string status_update_time
 * @property string plan_id
 * @property string start_time
 * @property string quantity
 * @property string create_time
 * @property string update_time
 * @property \PayPal\Api\Currency shipping_amount
 * @property \PayPal\Api\Subscriber subscriber
 * @property \PayPal\Api\ApplicationContext application_context
 * @property \PayPal\Api\BillingInfo billing_info
 */
class Subscriptions extends PayPalResourceModel
{
    /**
     * Identifier of the billing plan. 128 characters max.
     *
     * @param string $id
     * 
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Identifier of the billing plan. 128 characters max.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * The status of the subscription. 
     * Valid Values: ["APPROVAL_PENDING", "APPROVAL_PENDING", "APPROVED", "APPROVED", "SUSPENDED", "CANCELLED", "EXPIRED"]
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
     * The status of the subscription. 
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * The reason or notes for the status of the subscription. 
     *
     * @param string $status_change_note
     * 
     * @return $this
     */
    public function setStatusChangeNote($status_change_note)
    {
        $this->status_change_note = $status_change_note;
        return $this;
    }

    /**
     * The reason or notes for the status of the subscription. 
     *
     * @return string
     */
    public function getStatusChangeNote()
    {
        return $this->status_change_note;
    }

    /**
     * The reason or notes for the status of the subscription. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @param string $status_update_time
     * 
     * @return $this
     */
    public function setStatusUpdateTime($status_update_time)
    {
        $this->status_update_time = $status_update_time;
        return $this;
    }

    /**
     * The reason or notes for the status of the subscription. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @return string
     */
    public function getStatusUpdateTime()
    {
        return $this->status_update_time;
    }

    /**
     * Identifier of the billing plan. 50 characters max.
     *
     * @param string $plan_id
     * 
     * @return $this
     */
    public function setPlanId($plan_id)
    {
        $this->plan_id = $plan_id;
        return $this;
    }

    /**
     * Identifier of the billing plan. 50 characters max.
     *
     * @return string
     */
    public function getPlanId()
    {
        return $this->plan_id;
    }

    /**
     * The date and time when the subscription started. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @param string $create_time
     * 
     * @return $this
     */
    public function setStartTime($start_time)
    {
        $this->start_time = $start_time;
        return $this;
    }

    /**
     * The date and time when the subscription started. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @return string
     */
    public function getStartTime()
    {
        return $this->start_time;
    }

    /**
     * The quantity of the product in the subscription. 
     *
     * @param string $name
     * 
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * The quantity of the product in the subscription. 
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * shipping amount
     *
     * @param \PayPal\Api\Currency $shipping_amount
     * 
     * @return $this
     */
    public function setShippingAmount($shipping_amount)
    {
        $this->shipping_amount = $shipping_amount;
        return $this;
    }

    /**
     * shipping amount
     *
     * @return \PayPal\Api\Currency
     */
    public function getShippingAmount()
    {
        return $this->shipping_amount;
    }

    /**
     * The subscriber request information. 
     *
     * @param \PayPal\Api\Subscriber $subscriber
     * 
     * @return $this
     */
    public function setSubscriber($subscriber)
    {
        $this->subscriber = $subscriber;
        return $this;
    }

    /**
     * The subscriber request information. 
     *
     * @return \PayPal\Api\Subscriber
     */
    public function getSubscriber()
    {
        return $this->subscriber;
    }

    /**
     * The application context, which customizes the payer experience during the subscription approval process with PayPal. 
     *
     * @param \PayPal\Api\ApplicationContext $application_context
     * 
     * @return $this
     */
    public function setApplicationContext($application_context)
    {
        $this->application_context = $application_context;
        return $this;
    }

    /**
     * The application context, which customizes the payer experience during the subscription approval process with PayPal. 
     *
     * @return \PayPal\Api\ApplicationContext
     */
    public function getApplicationContext()
    {
        return $this->application_context;
    }

    /**
     * The application context, which customizes the payer experience during the subscription approval process with PayPal. 
     *
     * @param \PayPal\Api\BillingInfo $billing_info
     * 
     * @return $this
     */
    public function setBillingInfo($billing_info)
    {
        $this->billing_info = $billing_info;
        return $this;
    }

    /**
     * The application context, which customizes the payer experience during the subscription approval process with PayPal. 
     *
     * @return \PayPal\Api\BillingInfo
     */
    public function getBillingInfo()
    {
        return $this->billing_info;
    }

    /**
     * Time when the subscription was created. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
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
     * Time when the subscription was created. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @return string
     */
    public function getCreateTime()
    {
        return $this->create_time;
    }

    /**
     * Time when this subscription was updated. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
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
     * Time when this subscription was updated. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->update_time;
    }

    /**
     * Get Approval Link
     *
     * @return null|string
     */
    public function getApprovalLink()
    {        
        return $this->getLink('approve');
    }

    /**
     * Retrieve the details for a subscription by passing the subscription ID to the request URI.
     *
     * @param string $planId
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return Plan
     */
    public static function get($subscriptionId, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($subscriptionId, 'subscriptionId');
        $payLoad = "";
        $json = self::executeCall(
            "/v1/billing/subscriptions/$subscriptionId",
            "GET",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new Subscriptions();
        $ret->fromJson($json);
        return $ret;
    }

    /**
     * Create a new subscription by passing the details for the subscription, including the plan name, description, and type, to the request URI.
     *
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return Plan
     */
    public function create($apiContext = null, $restCall = null)
    {
        $payLoad = $this->toJSON();
        $json = self::executeCall(
            "/v1/billing/subscriptions/",
            "POST",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $this->fromJson($json);
        return $this;
    }

    /**
     * Cancels, a PayPal subscription that the payer has approved.
     *
     * @param PaymentExecution $paymentExecution
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return bool
     */
    public function cancel($subscriptionId, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($subscriptionId, 'subscriptionId');
        $payLoad = "";
        $json = self::executeCall(
            "/v1/billing/subscriptions/$subscriptionId/cancel",
            "POST",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        return true;
    }

    /**
     * Replace specific fields within a billing plan by passing the ID of the subscription to the request URI. In addition, pass a patch object in the request JSON that specifies the operation to perform, field to update, and new value for each update.
     *
     * @param PatchRequest $patchRequest
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return bool
     */
    public function update($patchRequest, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($this->getId(), "Id");
        ArgumentValidator::validate($patchRequest, 'patchRequest');
        $payLoad = $patchRequest->toJSON();
        self::executeCall(
            "/v1/billing/subscriptions/{$this->getId()}",
            "PATCH",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        return true;
    }

    /**
     * Suspend, a PayPal subscription that the payer has approved.
     *
     * @param PaymentExecution $paymentExecution
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return bool
     */
    public function suspend($subscriptionId, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($subscriptionId, 'subscriptionId');
        $payLoad = "";
        $json = self::executeCall(
            "/v1/billing/subscriptions/$subscriptionId/suspend",
            "POST",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        return true;
    }

    /**
     * Captures, a PayPal subscription that the payer has approved.
     *
     * @param PaymentExecution $paymentExecution
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return Subscription
     */
    public function capture($subscriptionId, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($subscriptionId, 'subscriptionId');
        $payLoad = "";
        $json = self::executeCall(
            "/v1/billing/subscriptions/$subscriptionId/capture",
            "POST",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new Subscriptions();
        $ret->fromJson($json);
        return $ret;
    }

    /**
     * List subscription according to optional query string parameters specified.
     *
     * @param array $params
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return PlanList
     */
    public static function all($params, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($params, 'params');
        $payLoad = "";
        $allowedParams = array(
            'product_id' => 1,
            'plan_ids' => 1,
            'page_size' => 1,
            'page' => 1,
            'total_required' => 1
        );        
        $json = self::executeCall(
            "/v1/billing/subscriptions/" . "?" . http_build_query(array_intersect_key($params, $allowedParams)),
            "GET",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new PlansList();
        $ret->fromJson($json);
        return $ret;
    }

}
