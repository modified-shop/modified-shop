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
interface Shopgate_Helper_Redirect_TemplateParserInterface
{
    const FUNCTION_NAME_HEX        = 'hex';
    const FUNCTION_NAME_URLENCODED = 'urlencoded';
    const FUNCTION_NAME_BASE64     = 'base64';
    const FUNCTION_NAME_ESCAPED    = 'escaped';

    /**
     * @param $template
     *
     * @return Shopgate_Model_Redirect_HtmlTagVariable[]
     */
    public function getVariables($template);

    /**
     * @param string                                  $template
     * @param Shopgate_Model_Redirect_HtmlTagVariable $variable
     * @param string                                  $replacement
     *
     * @return string
     */
    public function process($template, $variable, $replacement);

    /**
     * @param string $value
     * @param string $functionName
     *
     * @return string
     */
    public function filterVariableValue($value, $functionName = '');
}
