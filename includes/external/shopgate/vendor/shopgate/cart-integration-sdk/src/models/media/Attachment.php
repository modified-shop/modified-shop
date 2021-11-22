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
 * @class Shopgate_Model_Media_Attachment
 * @see   http://developer.shopgate.com/file_formats/xml/products
 *
 * @method         setNumber(int $value)
 * @method int     getNumber()
 *
 * @method         setUrl(string $value)
 * @method string  getUrl()
 *
 * @method         setTitle(string $value)
 * @method string  getTitle()
 *
 * @method         setDescription(string $value)
 * @method string  getDescription()
 *
 * @method         setMimeType(string $value)
 * @method string  getMimeType()
 *
 * @method         setFileName(string $value)
 * @method string  getFileName()
 *
 */
class Shopgate_Model_Media_Attachment extends Shopgate_Model_AbstractExport
{
    /**
     * @param Shopgate_Model_XmlResultObject $itemNode
     *
     * @return Shopgate_Model_XmlResultObject
     */
    public function asXml(Shopgate_Model_XmlResultObject $itemNode)
    {
        /**
         * @var Shopgate_Model_XmlResultObject $attachmentNode
         */
        $attachmentNode = $itemNode->addChild('attachment');
        $attachmentNode->addAttribute('number', $this->getNumber());
        $attachmentNode->addChildWithCDATA('url', $this->getUrl());
        $attachmentNode->addChild('mime_type', $this->getMimeType());
        $attachmentNode->addChild('file_name', $this->getFileName());
        $attachmentNode->addChildWithCDATA('title', $this->getTitle());
        $attachmentNode->addChildWithCDATA('description', $this->getDescription());

        return $itemNode;
    }
}
