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
 * @class Shopgate_Model_Catalog_CategoryPath
 * @see   http://developer.shopgate.com/file_formats/xml/products
 *
 * @method                             setUid(int $value)
 * @method int                         getUid()
 *
 * @method                             setSortOrder(int $value)
 * @method int                         getSortOrder()
 *
 * @method                             setItems(array $value)
 * @method array                       getItems()
 *
 * @method                             setParentUid(int $value)
 * @method int                         getParentUid()
 *
 * @method                             setImage(Shopgate_Model_Media_Image $value)
 * @method Shopgate_Model_Media_Image  getImage()
 *
 * @method                             setIsActive(bool $value)
 * @method bool                        getIsActive()
 *
 * @method                             setDeeplink(string $value)
 * @method string                      getDeeplink()
 *
 */
class Shopgate_Model_Catalog_CategoryPath extends Shopgate_Model_AbstractExport
{
    /**
     * define allowed methods
     *
     * @var array
     */
    protected $allowedMethods = array(
        'Uid',
        'SortOrder',
        'Items',
        'ParentUid',
        'Image',
        'IsActive',
        'Deeplink',
    );

    /**
     * init default object
     */
    public function __construct()
    {
        $this->setItems(array());
    }

    /**
     * @param Shopgate_Model_XmlResultObject $itemNode
     *
     * @return Shopgate_Model_XmlResultObject
     */
    public function asXml(Shopgate_Model_XmlResultObject $itemNode)
    {
        /** @var Shopgate_Model_XmlResultObject $categoryPathNode */
        $categoryPathNode = $itemNode->addChild('category');
        $categoryPathNode->addAttribute('uid', $this->getUid());
        $categoryPathNode->addAttribute('sort_order', (int)$this->getSortOrder());

        return $itemNode;
    }

    /**
     * @return array|null
     */
    public function asArray()
    {
        $categoryPathResult = new Shopgate_Model_Abstract();

        $categoryPathResult->setData('uid', $this->getUid());
        $categoryPathResult->setData('sort_order', (int)$this->getSortOrder());

        $itemsData = array();

        /**
         * @var Shopgate_Model_Abstract $item
         */
        foreach ($this->getItems() as $item) {
            $itemResult = new Shopgate_Model_Abstract();
            $itemResult->setData('level', $item->getData('level'));
            $itemResult->setData('path', $item->getData('path'));
            array_push($itemsData, $itemResult->getData());
        }
        $categoryPathResult->setData('paths', $itemsData);

        return $categoryPathResult->getData();
    }

    /**
     * add category path
     *
     * @param int    $level
     * @param string $path
     */
    public function addItem($level, $path)
    {
        $items = $this->getItems();
        $item  = new Shopgate_Model_Abstract();
        $item->setData('level', $level);
        $item->setData('path', $path);
        array_push($items, $item);
        $this->setItems($items);
    }
}
