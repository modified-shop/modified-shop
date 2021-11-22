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
 * @class Shopgate_Model_Catalog_Identifier
 * @see   http://developer.shopgate.com/file_formats/xml/products
 *
 * @method          setUid(int $value)
 * @method int      getUid()
 *
 * @method          setType(string $value)
 * @method string   getType()
 *
 * @method          setValue(string $value)
 * @method string   getValue()
 *
 */
class Shopgate_Model_Catalog_Identifier extends Shopgate_Model_AbstractExport
{
    /**
     * define allowed methods
     *
     * @var array
     */
    protected $allowedMethods = array(
        'Uid',
        'Type',
        'Value',
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
        $identifierNode = $itemNode->addChildWithCDATA('identifier', $this->getValue());
        $identifierNode->addAttribute('uid', $this->getUid());
        $identifierNode->addAttribute('type', $this->getType());

        return $itemNode;
    }

    /**
     * @return array|null
     */
    public function asArray()
    {
        $identifiersResult = new Shopgate_Model_Abstract();

        $identifiersResult->setData('uid', $this->getUid());
        $identifiersResult->setData('value', $this->getValue());
        $identifiersResult->setData('type', $this->getType());

        return $identifiersResult->getData();
    }
}
