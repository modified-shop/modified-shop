<?php

/**
 * Copyright Shopgate Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

/**
 * @class Shopgate_Model_Catalog_Shipping
 * @see   http://developer.shopgate.com/file_formats/xml/products
 *
 * @method          setCostsPerOrder(float $value)
 * @method float    getCostsPerOrder()
 *
 * @method          setAdditionalCostsPerUnit(float $value)
 * @method float    getAdditionalCostsPerUnit()
 *
 * @method          setIsFree(bool $value)
 * @method bool     getIsFree()
 *
 */
class Shopgate_Model_Catalog_Shipping extends Shopgate_Model_AbstractExport
{
    /**
     * define allowed methods
     *
     * @var array
     */
    protected $allowedMethods = array(
        'CostsPerOrder',
        'AdditionalCostsPerUnit',
        'IsFree',
    );

    /**
     * @param Shopgate_Model_XmlResultObject $itemNode
     *
     * @return Shopgate_Model_XmlResultObject
     */
    public function asXml(Shopgate_Model_XmlResultObject $itemNode)
    {
        /**
         * @var Shopgate_Model_XmlResultObject $shippingNode
         */
        $shippingNode = $itemNode->addChild('shipping');
        $shippingNode->addChild('costs_per_order', $this->getCostsPerOrder(), null, false);
        $shippingNode->addChild('additional_costs_per_unit', $this->getAdditionalCostsPerUnit(), null, false);
        $shippingNode->addChild('is_free', (int)$this->getIsFree());

        return $itemNode;
    }

    /**
     * @return array|null
     */
    public function asArray()
    {
        $shippingResult = new Shopgate_Model_Abstract();

        $shippingResult->setData('costs_per_order', $this->getCostsPerOrder());
        $shippingResult->setData('additional_costs_per_unit', $this->getAdditionalCostsPerUnit());
        $shippingResult->setData('is_free', (int)$this->getIsFree());

        return $shippingResult->getData();
    }
}
