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
 * @class Shopgate_Model_Catalog_Relation
 * @see   http://developer.shopgate.com/file_formats/xml/products
 *
 * @method          setType(string $value)
 * @method string   getType()
 *
 * @method          setValues(array $value)
 * @method array    getValues()
 *
 * @method          setLabel(string $value)
 * @method string   getLabel()
 *
 */
class Shopgate_Model_Catalog_Relation extends Shopgate_Model_AbstractExport
{
    const DEFAULT_RELATION_TYPE_CROSSSELL = 'crosssell';
    const DEFAULT_RELATION_TYPE_RELATION  = 'relation';
    const DEFAULT_RELATION_TYPE_CUSTOM    = 'custom';
    const DEFAULT_RELATION_TYPE_UPSELL    = 'upsell';

    /**
     * define allowed methods
     *
     * @var array
     */
    protected $allowedMethods = array(
        'Type',
        'Values',
        'Label',
    );

    /**
     * init default data
     */
    public function __construct()
    {
        $this->setValues(array());
    }

    /**
     * @param Shopgate_Model_XmlResultObject $itemNode
     *
     * @return Shopgate_Model_XmlResultObject
     */
    public function asXml(Shopgate_Model_XmlResultObject $itemNode)
    {
        /**
         * @var Shopgate_Model_XmlResultObject $relationNode
         */
        $relationNode = $itemNode->addChild('relation');
        $relationNode->addAttribute('type', $this->getType());
        if ($this->getType() == self::DEFAULT_RELATION_TYPE_CUSTOM) {
            $relationNode->addChildWithCDATA('label', $this->getLabel());
        }
        foreach ($this->getValues() as $value) {
            $relationNode->addChild('uid', $value);
        }

        return $itemNode;
    }

    /**
     * add new value
     *
     * @param int $value
     */
    public function addValue($value)
    {
        $values = $this->getValues();
        array_push($values, $value);
        $this->setValues($values);
    }
}
