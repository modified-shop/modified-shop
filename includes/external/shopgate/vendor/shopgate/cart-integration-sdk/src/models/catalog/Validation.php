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
 * @class Shopgate_Model_Catalog_Validation
 * @see   http://developer.shopgate.com/file_formats/xml/products
 *
 * @method          setValidationType(string $value)
 * @method string   getValidationType()
 *
 * @method          setValue(string $value)
 * @method string   getValue()
 *
 */
class Shopgate_Model_Catalog_Validation extends Shopgate_Model_AbstractExport
{
    /**
     * types
     */
    const DEFAULT_VALIDATION_TYPE_FILE     = 'file';
    const DEFAULT_VALIDATION_TYPE_VARIABLE = 'variable';
    const DEFAULT_VALIDATION_TYPE_REGEX    = 'regex';
    /**
     * file
     */
    const DEFAULT_VALIDATION_FILE_UNKNOWN = 'unknown';
    const DEFAULT_VALIDATION_FILE_TEXT    = 'text';
    const DEFAULT_VALIDATION_FILE_PDF     = 'pdf';
    const DEFAULT_VALIDATION_FILE_JPEG    = 'jpeg';
    /**
     * variable
     */
    const DEFAULT_VALIDATION_VARIABLE_NOT_EMPTY = 'not_empty';
    const DEFAULT_VALIDATION_VARIABLE_INT       = 'int';
    const DEFAULT_VALIDATION_VARIABLE_FLOAT     = 'float';
    const DEFAULT_VALIDATION_VARIABLE_STRING    = 'string';
    const DEFAULT_VALIDATION_VARIABLE_DATE      = 'date';
    const DEFAULT_VALIDATION_VARIABLE_TIME      = 'time';

    /**
     * define allowed methods
     *
     * @var array
     */
    protected $allowedMethods = array(
        'ValidationType',
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
         * @var Shopgate_Model_XmlResultObject $validationNode
         */
        $validationNode = $itemNode->addChildWithCDATA('validation', $this->getValue());
        $validationNode->addAttribute('type', $this->getValidationType());

        return $itemNode;
    }
}
