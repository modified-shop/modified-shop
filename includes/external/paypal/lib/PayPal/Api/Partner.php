<?php

namespace PayPal\Api;

use PayPal\Common\PayPalResourceModel;
use PayPal\Validation\ArgumentValidator;
use PayPal\Rest\ApiContext;

/**
 * Class Partner
 *
 * Get information about a seller-partner integration.
 *
 * @package PayPal\Api
 *
 * @property string tracking_id
 * @property string partner_id
 * @property string merchant_id
 * @property \PayPal\Api\Product[] products
 * @property boolean payments_receivable
 * @property boolean primary_email_confirmed
 * @property string primary_email
 * @property string date_created
 * @property array granted_permissions
 * @property object api_credentials
 * @property array oauth_integrations
 * @property array limitations
 * @property \PayPal\Api\Capabilities[] capabilities
 */
class Partner extends PayPalResourceModel
{
    /**
     * The partner-provided tracking ID.
     *
     * @param string $tracking_id
     *
     * @return $this
     */
    public function setTrackingId($tracking_id)
    {
        $this->tracking_id = $tracking_id;
        return $this;
    }

    /**
     * The partner-provided tracking ID.
     *
     * @return string
     */
    public function getTrackingId()
    {
        return $this->tracking_id;
    }

    /**
     * The ID of the partner for which to show onboarded seller status information.
     *
     * @param string $partner_id
     *
     * @return $this
     */
    public function setPartnerId($partner_id)
    {
        $this->partner_id = $partner_id;
        return $this;
    }

    /**
     * The ID of the partner for which to show onboarded seller status information.
     *
     * @return string
     */
    public function getPartnerId()
    {
        return $this->partner_id;
    }

    /**
     * The payer ID of the seller after creation of their PayPal account.
     *
     * @param string $merchant_id
     *
     * @return $this
     */
    public function setMerchantId($merchant_id)
    {
        $this->merchant_id = $merchant_id;
        return $this;
    }

    /**
     * The payer ID of the seller after creation of their PayPal account.
     *
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchant_id;
    }

   /**
     * An array of all products that are integrated with the partner for the seller.
     *
     * @param \PayPal\Api\Product[] $products
     * 
     * @return $this
     */
    public function setProducts($products)
    {
        $this->products = $products;
        return $this;
    }

    /**
     * An array of all products that are integrated with the partner for the seller.
     *
     * @return \PayPal\Api\Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Append Products to the list.
     *
     * @param \PayPal\Api\Product $product
     * @return $this
     */
    public function addProduct($product)
    {
        if (!$this->getProducts()) {
            return $this->setProducts(array($product));
        } else {
            return $this->setProducts(
                array_merge($this->getProducts(), array($product))
            );
        }
    }

    /**
     * Remove Products from the list.
     *
     * @param \PayPal\Api\Product $product
     * @return $this
     */
    public function removeProduct($product)
    {
        return $this->setProducts(
            array_diff($this->getProducts(), array($product))
        );
    }

    /**
     * Indicates whether the seller account can receive payments.
     *
     * @param string $payments_receivable
     *
     * @return $this
     */
    public function setPaymentsReceivable($payments_receivable)
    {
        $this->payments_receivable = $payments_receivable;
        return $this;
    }

    /**
     * Indicates whether the seller account can receive payments.
     *
     * @return bool
     */
    public function getPaymentsReceivable()
    {
        return $this->payments_receivable;
    }

    /**
     * Indicates whether the seller account can receive payments.
     *
     * @param string $primary_email_confirmed
     *
     * @return $this
     */
    public function setPrimaryEmailConfirmed($primary_email_confirmed)
    {
        $this->primary_email_confirmed = $primary_email_confirmed;
        return $this;
    }

    /**
     * Indicates whether the seller account can receive payments.
     *
     * @return bool
     */
    public function getPrimaryEmailConfirmed()
    {
        return $this->primary_email_confirmed;
    }

    /**
     * The primary email address of the seller.
     *
     * @param string $primary_email
     *
     * @return $this
     */
    public function setPrimaryEmail($primary_email)
    {
        $this->primary_email = $primary_email;
        return $this;
    }

    /**
     * The primary email address of the seller.
     *
     * @return string
     */
    public function getPrimaryEmail()
    {
        return $this->merchant_id;
    }

    /**
     * The date when the seller account was created.
     *
     * @param string $date_created
     *
     * @return $this
     */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;
        return $this;
    }

    /**
     * The date when the seller account was created.
     *
     * @return string
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * An array of permissions granted to the partner by the seller.
     *
     * @param array $granted_permissions
     * 
     * @return $this
     */
    public function setGrantedPermissions($granted_permissions)
    {
        $this->granted_permissions = $granted_permissions;
        return $this;
    }

    /**
     * An array of permissions granted to the partner by the seller.
     *
     * @return self[]
     */
    public function getGrantedPermissions()
    {
        return $this->granted_permissions;
    }

    /**
     * The API credentials of the seller.
     *
     * @param object $api_credentials
     * 
     * @return $this
     */
    public function setApiCredentials($api_credentials)
    {
        $this->api_credentials = $api_credentials;
        return $this;
    }

    /**
     * The API credentials of the seller.
     *
     * @return object
     */
    public function getApiCredentials()
    {
        return $this->api_credentials;
    }

    /**
     * An array of information about OAuth integrations between partners and sellers.
     *
     * @param array $oauth_integrations
     * 
     * @return $this
     */
    public function setOauthIntegrations($oauth_integrations)
    {
        $this->oauth_integrations = $oauth_integrations;
        return $this;
    }

    /**
     * An array of information about OAuth integrations between partners and sellers.
     *
     * @return self[]
     */
    public function getOauthIntegrations()
    {
        return $this->oauth_integrations;
    }

    /**
     * An array of limitations on the seller account.
     *
     * @param array $limitations
     * 
     * @return $this
     */
    public function setLimitations($limitations)
    {
        $this->limitations = $limitations;
        return $this;
    }

    /**
     * An array of limitations on the seller account.
     *
     * @return self[]
     */
    public function getLimitations()
    {
        return $this->limitations;
    }

    /**
     * An array of capabilities associated with the products integrated between seller and partner.
     *
     * @param \PayPal\Api\Capabilities[] $capabilities
     * 
     * @return $this
     */
    public function setCapabilities($capabilities)
    {
        $this->capabilities = $capabilities;
        return $this;
    }

    /**
     * An array of capabilities associated with the products integrated between seller and partner.
     *
     * @return \PayPal\Api\Capabilities[]
     */
    public function getCapabilities()
    {
        return $this->capabilities;
    }

    /**
     * Append Capabilities to the list.
     *
     * @param \PayPal\Api\Capabilities $capabilities
     * @return $this
     */
    public function addCapabilities($capabilities)
    {
        if (!$this->getCapabilities()) {
            return $this->setCapabilities(array($capabilities));
        } else {
            return $this->setCapabilities(
                array_merge($this->getCapabilities(), array($capabilities))
            );
        }
    }

    /**
     * Remove Capabilities from the list.
     *
     * @param \PayPal\Api\Capabilities $capabilities
     * @return $this
     */
    public function removeCapabilities($capabilities)
    {
        return $this->setCapabilities(
            array_diff($this->getCapabilities(), array($capabilities))
        );
    }

    /**
     * Shows details for an order, by ID.
     *
     * @param string $orderId
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return Order
     */
    public function get($apiContext = null, $restCall = null)
    {
        $payLoad = "";
        $json = self::executeCall(
            "/v1/customer/partners/{$this->getPartnerId()}/merchant-integrations/{$this->getMerchantId()}",
            "GET",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        
        $ret = new Partner();
        $ret->fromJson($json);
        return $ret;
    }

}
