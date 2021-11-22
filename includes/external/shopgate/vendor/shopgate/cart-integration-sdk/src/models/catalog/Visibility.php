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
 * @class Shopgate_Model_Catalog_Visibility
 * @see   http://developer.shopgate.com/file_formats/xml/products
 *
 * @method          setMarketplace(bool $value)
 * @method bool     getMarketplace()
 *
 * @method          setLevel(string $value)
 * @method string   getLevel()
 *
 */
class Shopgate_Model_Catalog_Visibility extends Shopgate_Model_AbstractExport
{
    /**
     * define allowed methods
     *
     * @var array
     */
    protected $allowedMethods = array(
        'Marketplace',
        'Level',
    );

    const DEFAULT_VISIBILITY_CATALOG_AND_SEARCH = 'catalog_and_search';
    const DEFAULT_VISIBILITY_NOTHING            = 'nothing';
    /**
     * @deprecated
     * not supported yet, will be handled as catalog_and_search
     **/
    const DEFAULT_VISIBILITY_CATALOG = 'catalog';
    /**
     * @deprecated
     * not supported yet, will be handled as catalog_and_search
     **/
    const DEFAULT_VISIBILITY_SEARCH = 'search';
    /**
     * @deprecated
     * please use DEFAULT_VISIBILITY_NOTHING
     **/
    const DEFAULT_VISIBILITY_NOT_VISIBLE = 'nothing';

    /**
     * @param Shopgate_Model_XmlResultObject $itemNode
     *
     * @return Shopgate_Model_XmlResultObject
     */
    public function asXml(Shopgate_Model_XmlResultObject $itemNode)
    {
        /**
         * @var Shopgate_Model_XmlResultObject $visibilityNode
         */
        $visibilityNode = $itemNode->addChild('visibility');
        $visibilityNode->addAttribute('level', $this->getLevel());
        $visibilityNode->addAttribute('marketplace', $this->getMarketplace());

        return $itemNode;
    }

    /**
     * @return array|null
     */
    public function asArray()
    {
        $visibilityResult = new Shopgate_Model_Abstract();

        $visibilityResult->setData('level', $this->getLevel());
        $visibilityResult->setData('marketplace', $this->getMarketplace());

        return $visibilityResult->getData();
    }
}
