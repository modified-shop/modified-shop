<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;
use PayPal\Common\PayPalResourceModel;
use PayPal\Rest\ApiContext;
use PayPal\Transport\PayPalRestCall;
use PayPal\Validation\UrlValidator;
use PayPal\Validation\ArgumentValidator;

/**
 * Class Product
 *
 * Product details.
 *
 * @package PayPal\Api
 *
 * @property string id
 * @property string name
 * @property string description
 * @property string quantity
 * @property string category
 * @property string type
 * @property string image_url
 * @property string home_url
 */
class Product extends PayPalResourceModel
{
    /**
     * Identifier of the product. 128 characters max.
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
     * Identifier of the product. 128 characters max.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Item name. 127 characters max.
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
     * Item name. 127 characters max.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Description of the item. Only supported when the `payment_method` is set to `paypal`.
     *
     * @param string $description
     * 
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Description of the item. Only supported when the `payment_method` is set to `paypal`.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Type of the product. Allowed values: `PHYSICAL`, `DIGITAL`, `SERVICE`.
     *
     * @param string $type
     * 
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Type of the product. Allowed values: `FIXED`, `INFINITE`.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The vetting status of the product, if applicable.
     *
     * @param string $vetting_status
     * 
     * @return $this
     */
    public function setVettingStatus($vetting_status)
    {
        $this->vetting_status = $vetting_status;
        return $this;
    }

    /**
     * The vetting status of the product, if applicable.
     *
     * @return string
     */
    public function getVettingStatus()
    {
        return $this->vetting_status;
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
     * Indicates whether the product is active.
     *
     * @param string $active
     *
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Indicates whether the product is active.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Category type of the item.
     * Valid Values: [see https://developer.paypal.com/docs/api/catalog-products/v1/#definition-product_category]
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
     * Category type of the item.
     * @deprecated Not publicly available
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * URL linking to image.
     *
     * @param string $url
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setImageUrl($url)
    {
        UrlValidator::validate($url, "Url");
        $this->image_url = $url;
        return $this;
    }

    /**
     * URL linking to image.
     *
     * @return string
     */
    public function getImageUrl()
    {
        return $this->image_url;
    }

    /**
     * URL linking to product information.
     *
     * @param string $url
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setHomeUrl($url)
    {
        UrlValidator::validate($url, "Url");
        $this->home_url = $url;
        return $this;
    }

    /**
     * URL linking to product information.
     *
     * @return string
     */
    public function getHomeUrl()
    {
        return $this->home_url;
    }

    /**
     * Time when the product was created. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
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
     * Time when the product was created. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @return string
     */
    public function getCreateTime()
    {
        return $this->create_time;
    }

    /**
     * Time when this product was updated. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
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
     * Time when this product was updated. Format YYYY-MM-DDTimeTimezone, as defined in [ISO8601](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->update_time;
    }

    /**
     * Retrieve the details for a particular product by passing the product ID to the request URI.
     *
     * @param string $planId
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return Plan
     */
    public static function get($product_id, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($product_id, 'product_id');
        $payLoad = "";
        $json = self::executeCall(
            "/v1/catalogs/products/$product_id",
            "GET",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new Product();
        $ret->fromJson($json);
        return $ret;
    }

    /**
     * Create a new products passing the details for the product, including the product name, description, and type, to the request URI.
     *
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return Plan
     */
    public function create($apiContext = null, $restCall = null)
    {
        $payLoad = $this->toJSON();
        $json = self::executeCall(
            "/v1/catalogs/products/",
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
     * Replace specific fields within a product by passing the ID of the product to the request URI. In addition, pass a patch object in the request JSON that specifies the operation to perform, field to update, and new value for each update.
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
            "/v1/catalogs/products/{$this->getId()}",
            "PATCH",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        return true;
    }

    /**
     * Delete a product by passing the ID of the product to the request URI.
     *
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return bool
     */
    public function delete($apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($this->getId(), "Id");
        $patchRequest = new PatchRequest();
        $patch = new Patch();
        $value = new PayPalModel('{
            "state":"DELETED"
        }');
        $patch->setOp('replace')
            ->setPath('/')
            ->setValue($value);
        $patchRequest->addPatch($patch);
        return $this->update($patchRequest, $apiContext, $restCall);
    }

    /**
     * List products according to optional query string parameters specified.
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
            'page_size' => 1,
            'page' => 1,
            'total_required' => 1
        );
        $json = self::executeCall(
            "/v1/catalogs/products/" . "?" . http_build_query(array_intersect_key($params, $allowedParams)),
            "GET",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new ProductList();
        $ret->fromJson($json);
        return $ret;
    }

}
