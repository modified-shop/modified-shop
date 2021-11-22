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
 * @class Shopgate_Model_Catalog_Stock
 * @see   http://developer.shopgate.com/file_formats/xml/products
 *
 * @method          setIsSaleable(bool $value)
 * @method bool     getIsSaleable()
 *
 * @method          setBackorders(bool $value)
 * @method bool     getBackorders()
 *
 * @method          setUseStock(bool $value)
 * @method bool     getUseStock()
 *
 * @method          setStockQuantity(int $value)
 * @method int      getStockQuantity()
 *
 * @method          setMinimumOrderQuantity(int $value)
 * @method int      getMinimumOrderQuantity()
 *
 * @method          setMaximumOrderQuantity(int $value)
 * @method int      getMaximumOrderQuantity()
 *
 * @method          setAvailabilityText(string $value)
 * @method string   getAvailabilityText()
 *
 */
class Shopgate_Model_Catalog_Stock extends Shopgate_Model_AbstractExport
{
    /**
     * define allowed methods
     *
     * @var array
     */
    protected $allowedMethods = array(
        'IsSaleable',
        'Backorders',
        'UseStock',
        'StockQuantity',
        'MinimumOrderQuantity',
        'MaximumOrderQuantity',
        'AvailabilityText',
    );

    /**
     * @param Shopgate_Model_XmlResultObject $itemNode
     *
     * @return Shopgate_Model_XmlResultObject
     */
    public function asXml(Shopgate_Model_XmlResultObject $itemNode)
    {
        /**
         * @var Shopgate_Model_XmlResultObject $stockNode
         */
        $stockNode = $itemNode->addChild('stock');
        $stockNode->addChild('is_saleable', (int)$this->getIsSaleable());
        $stockNode->addChild('backorders', (int)$this->getBackorders());
        $stockNode->addChild('use_stock', (int)$this->getUseStock());
        $stockNode->addChild('stock_quantity', $this->getStockQuantity());
        $stockNode->addChild('minimum_order_quantity', $this->getMinimumOrderQuantity(), null, false);
        $stockNode->addChild('maximum_order_quantity', $this->getMaximumOrderQuantity(), null, false);
        $stockNode->addChildWithCDATA('availability_text', $this->getAvailabilityText(), false);

        return $itemNode;
    }

    /**
     * @return array|null
     */
    public function asArray()
    {
        $stockResult = new Shopgate_Model_Abstract();

        $stockResult->setData('is_saleable', (int)$this->getIsSaleable());
        $stockResult->setData('backorders', $this->getBackorders());
        $stockResult->setData('use_stock', (int)$this->getUseStock());
        $stockResult->setData('stock_quantity', $this->getStockQuantity());
        $stockResult->setData('minimum_order_quantity', $this->getMinimumOrderQuantity());
        $stockResult->setData('maximum_order_quantity', $this->getMaximumOrderQuantity());
        $stockResult->setData('availability_text', $this->getAvailabilityText());

        return $stockResult->getData();
    }
}
