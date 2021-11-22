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
 * @class Shopgate_Model_Catalog_Option
 * @see   http://developer.shopgate.com/file_formats/xml/products
 *
 * @method           setUid(int $value)
 * @method int       getUid()
 *
 * @method           setLabel(string $value)
 * @method string    getLabel()
 *
 * @method           setValue(string $value)
 * @method string    getValue()
 *
 * @method           setAdditionalPrice(float $value)
 * @method float     getAdditionalPrice()
 *
 * @method           setSortOrder(int $value)
 * @method int       getSortOrder()
 *
 */
class Shopgate_Model_Catalog_Option extends Shopgate_Model_AbstractExport
{
    /**
     * define allowed methods
     *
     * @var array
     */
    protected $allowedMethods = array(
        'Uid',
        'Label',
        'Value',
        'SortOrder',
        'AdditionalPrice',
    );

    /**
     * @param Shopgate_Model_XmlResultObject $itemNode
     *
     * @return Shopgate_Model_XmlResultObject
     */
    public function asXml(Shopgate_Model_XmlResultObject $itemNode)
    {
        /**
         * @var Shopgate_Model_XmlResultObject $optionNode
         */
        $optionNode = $itemNode->addChild('option');
        $optionNode->addAttribute('additional_price', $this->getAdditionalPrice());
        $optionNode->addAttribute('uid', $this->getUid());
        $optionNode->addAttribute('sort_order', $this->getSortOrder());
        $optionNode->addChildWithCDATA('label', $this->getLabel());
        $optionNode->addChildWithCDATA('value', $this->getValue());

        return $itemNode;
    }
}
