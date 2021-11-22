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
class Shopgate_Model_XmlResultObject extends SimpleXMLElement
{
    const DEFAULT_MAIN_NODE = '<items></items>';
    /**
     * finds all characters that are not allowed in XML
     *
     * @see http://www.w3.org/TR/REC-xml/#charsets
     */
    const PATTERN_INVALID_CHARS = '/[^\x{09}\x{0A}\x{0D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u';

    /**
     * Adds a child with $value inside CDATA
     *
     * @param string $name
     * @param mixed  $value
     * @param bool   $allowNull
     *
     * @return SimpleXMLElement
     */
    public function addChildWithCDATA($name, $value = null, $allowNull = true)
    {
        if (!$allowNull && $value === null) {
            return null;
        }
        $forceEmpty = false;
        if ($value === Shopgate_Model_AbstractExport::SET_EMPTY) {
            $forceEmpty = true;
            $value      = '';
        }
        $new_child = $this->addChild($name);

        if ($new_child !== null) {
            $node = dom_import_simplexml($new_child);
            $no   = $node->ownerDocument;
            if ($value != '') {
                $value = preg_replace(self::PATTERN_INVALID_CHARS, '', $value);
                $node->appendChild($no->createCDATASection($value));
            }
        }

        if ($forceEmpty) {
            $new_child->addAttribute('forceEmpty', '1');
        }

        return $new_child;
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @param string $namespace
     * @param bool   $allowNull
     *
     * @return null|SimpleXMLElement
     */
    public function addChild($name, $value = null, $namespace = null, $allowNull = true)
    {
        if (!$allowNull && $value === null) {
            return null;
        }
        if (!empty($value)) {
            $value = preg_replace(self::PATTERN_INVALID_CHARS, '', $value);
        }
        if ($value !== Shopgate_Model_AbstractExport::SET_EMPTY) {
            return parent::addChild($name, $value, $namespace);
        }
        $child = parent::addChild($name, '', $namespace);
        $child->addAttribute('forceEmpty', '1');

        return $child;
    }

    /**
     * @param SimpleXMLElement $new
     * @param SimpleXMLElement $old
     *
     * @return SimpleXMLElement
     */
    public function replaceChild(SimpleXMLElement $new, SimpleXMLElement $old)
    {
        $tmp = dom_import_simplexml($this);
        $new = $tmp->ownerDocument->importNode(dom_import_simplexml($new), true);

        $node = $tmp->replaceChild($new, dom_import_simplexml($old));

        return simplexml_import_dom($node, get_class($this));
    }

    /**
     * Adds an attribute to the SimpleXML element is value not empty
     *
     * @param string $name
     * @param string $value
     * @param string $namespace
     */
    public function addAttribute($name, $value = null, $namespace = null)
    {
        if (isset($value)) {
            parent::addAttribute($name, $value, $namespace);
        }
    }
}
