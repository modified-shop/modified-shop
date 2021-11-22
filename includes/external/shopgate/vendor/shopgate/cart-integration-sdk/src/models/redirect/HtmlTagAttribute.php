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
 * @method        setName(string $value)
 * @method string getName()
 *
 * @method        setValue(string $value)
 * @method string getValue()
 *
 * @method                                        setDeeplinkSuffix(Shopgate_Model_Redirect_DeeplinkSuffix $value)
 * @method Shopgate_Model_Redirect_DeeplinkSuffix getDeeplinkSuffix()
 *
 * @method                                           setVariables(array $value)
 * @method Shopgate_Model_Redirect_HtmlTagVariable[] getVariables()
 */
class Shopgate_Model_Redirect_HtmlTagAttribute extends Shopgate_Model_Abstract
{
    public function __construct()
    {
        $this->allowedMethods = array(
            'Name',
            'Value',
            'DeeplinkSuffix',
            'Variables',
        );
    }
}
