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
 * @class Shopgate_Model_Catalog_Manufacturer
 * @see   http://developer.shopgate.com/file_formats/xml/products
 *
 * @method         setUid(int $value)
 * @method int     getUid()
 *
 * @method         setItemNumber(string $value)
 * @method string  getItemNumber()
 *
 * @method         setTitle(string $value)
 * @method string  getTitle()
 *
 */
class Shopgate_Model_Catalog_Manufacturer extends Shopgate_Model_AbstractExport
{
    /**
     * define allowed methods
     *
     * @var array
     */
    protected $allowedMethods = array(
        'Uid',
        'ItemNumber',
        'Title',
    );

    /**
     * @param Shopgate_Model_XmlResultObject $itemNode
     *
     * @return Shopgate_Model_XmlResultObject
     */
    public function asXml(Shopgate_Model_XmlResultObject $itemNode)
    {
        /**
         * @var Shopgate_Model_XmlResultObject $manufacturerNode
         */
        $manufacturerNode = $itemNode->addChild('manufacturer');
        $manufacturerNode->addAttribute('uid', $this->getUid());
        $manufacturerNode->addChildWithCDATA('title', $this->getTitle(), false);
        $manufacturerNode->addChildWithCDATA('item_number', $this->getItemNumber(), false);

        return $itemNode;
    }

    /**
     * @return array|null
     */
    public function asArray()
    {
        $manufacturerResult = new Shopgate_Model_Abstract();

        $manufacturerResult->setData('uid', $this->getUid());
        $manufacturerResult->setData('title', $this->getTitle());
        $manufacturerResult->setData('item_number', $this->getItemNumber());

        return $manufacturerResult->getData();
    }
}
