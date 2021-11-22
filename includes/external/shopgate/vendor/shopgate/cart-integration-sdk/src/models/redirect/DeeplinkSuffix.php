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
class Shopgate_Model_Redirect_DeeplinkSuffix extends Shopgate_Model_Abstract
{
    /** @var Shopgate_Model_Redirect_DeeplinkSuffixValue [string, Shopgate_Model_Redirect_DeeplinkSuffixValue] */
    protected $valuesByType;

    /**
     * @param string                                      $type
     * @param Shopgate_Model_Redirect_DeeplinkSuffixValue $value
     */
    public function addValue($type, Shopgate_Model_Redirect_DeeplinkSuffixValue $value)
    {
        $this->valuesByType[$type] = $value;
    }

    /**
     * @param string $type
     *
     * @return Shopgate_Model_Redirect_DeeplinkSuffixValue
     */
    public function getValue($type)
    {
        if (!isset($this->valuesByType[$type]) || ($this->valuesByType[$type] === null)) {
            return new Shopgate_Model_Redirect_DeeplinkSuffixValueUnset();
        }

        if ($this->valuesByType[$type] === false) {
            return new Shopgate_Model_Redirect_DeeplinkSuffixValueDisabled();
        }

        return $this->valuesByType[$type];
    }
}
