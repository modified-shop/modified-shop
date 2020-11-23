<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class PlanList
 *
 * Resource representing a list of billing plans with basic information and get link.
 *
 * @package PayPal\Api
 *
 * @property \PayPal\Api\Plans[] plans
 * @property string total_items
 * @property string total_pages
 * @property \PayPal\Api\Links[] links
 */
class PlansList extends PayPalModel
{
    /**
     * Array of billing plans.
     *
     * @param \PayPal\Api\Plans[] $plans
     * 
     * @return $this
     */
    public function setPlans($plans)
    {
        $this->plans = $plans;
        return $this;
    }

    /**
     * Array of billing plans.
     *
     * @return \PayPal\Api\Plans[]
     */
    public function getPlans()
    {
        return $this->plans;
    }

    /**
     * Append Plans to the list.
     *
     * @param \PayPal\Api\Plans $plans
     * @return $this
     */
    public function addPlans($plans)
    {
        if (!$this->getPlans()) {
            return $this->setPlans(array($plans));
        } else {
            return $this->setPlans(
                array_merge($this->getPlans(), array($plans))
            );
        }
    }

    /**
     * Remove Plans from the list.
     *
     * @param \PayPal\Api\Plans $plans
     * @return $this
     */
    public function removePlans($plans)
    {
        return $this->setPlans(
            array_diff($this->getPlans(), array($plans))
        );
    }

    /**
     * Total number of items.
     *
     * @param string $total_items
     * 
     * @return $this
     */
    public function setTotalItems($total_items)
    {
        $this->total_items = $total_items;
        return $this;
    }

    /**
     * Total number of items.
     *
     * @return string
     */
    public function getTotalItems()
    {
        return $this->total_items;
    }

    /**
     * Total number of pages.
     *
     * @param string $total_pages
     * 
     * @return $this
     */
    public function setTotalPages($total_pages)
    {
        $this->total_pages = $total_pages;
        return $this;
    }

    /**
     * Total number of pages.
     *
     * @return string
     */
    public function getTotalPages()
    {
        return $this->total_pages;
    }

    /**
     * Sets Links
     *
     * @param \PayPal\Api\Links[] $links
     * 
     * @return $this
     */
    public function setLinks($links)
    {
        $this->links = $links;
        return $this;
    }

    /**
     * Gets Links
     *
     * @return \PayPal\Api\Links[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * Append Links to the list.
     *
     * @param \PayPal\Api\Links $links
     * @return $this
     */
    public function addLink($links)
    {
        if (!$this->getLinks()) {
            return $this->setLinks(array($links));
        } else {
            return $this->setLinks(
                array_merge($this->getLinks(), array($links))
            );
        }
    }

    /**
     * Remove Links from the list.
     *
     * @param \PayPal\Api\Links $links
     * @return $this
     */
    public function removeLink($links)
    {
        return $this->setLinks(
            array_diff($this->getLinks(), array($links))
        );
    }

}
