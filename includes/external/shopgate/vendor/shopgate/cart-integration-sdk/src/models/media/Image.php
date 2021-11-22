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
 *
 * @class Shopgate_Model_Media_Image
 * @see   http://developer.shopgate.com/file_formats/xml/products
 *
 * @method         setUid(int $value)
 * @method int     getUid()
 *
 * @method         setSortOrder(int $value)
 * @method int     getSortOrder()
 *
 * @method         setUrl(string $value)
 * @method string  getUrl()
 *
 * @method         setTitle(string $value)
 * @method string  getTitle()
 *
 * @method         setAlt(string $value)
 * @method string  getAlt()
 *
 * @method         setIsCover(bool $value)
 * @method string  getIsCover()
 *
 */
class Shopgate_Model_Media_Image extends Shopgate_Model_AbstractExport
{
    /**
     * define allowed methods
     *
     * @var array
     */
    protected $allowedMethods = array(
        'Uid',
        'SortOrder',
        'Url',
        'Title',
        'Alt',
        'IsCover',
    );

    /**
     * @param Shopgate_Model_XmlResultObject $itemNode
     *
     * @return Shopgate_Model_XmlResultObject
     */
    public function asXml(Shopgate_Model_XmlResultObject $itemNode)
    {
        /**
         * @var Shopgate_Model_XmlResultObject $imageNode
         */
        $imageNode = $itemNode->addChild('image');
        $imageNode->addAttribute('uid', $this->getUid());
        $imageNode->addAttribute('sort_order', $this->getSortOrder());
        $imageNode->addAttribute('is_cover', $this->getIsCover());
        $imageNode->addChildWithCDATA('url', $this->getUrl());
        $imageNode->addChildWithCDATA('title', $this->getTitle(), false);
        $imageNode->addChildWithCDATA('alt', $this->getAlt(), false);

        return $itemNode;
    }

    /**
     * @return array|null
     */
    public function asArray()
    {
        $imageResult = new Shopgate_Model_Media_Image();

        $imageResult->setUid($this->getUid());
        $imageResult->setSortOrder($this->getSortOrder());
        $imageResult->setUrl($this->getUrl());
        $imageResult->setTitle($this->getTitle());
        $imageResult->setAlt($this->getAlt());
        $imageResult->setIsCover($this->getIsCover());

        return $imageResult->getData();
    }
}
