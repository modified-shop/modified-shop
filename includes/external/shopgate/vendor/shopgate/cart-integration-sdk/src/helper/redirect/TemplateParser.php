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
class Shopgate_Helper_Redirect_TemplateParser implements Shopgate_Helper_Redirect_TemplateParserInterface
{
    public function getVariables($template)
    {
        $matches = array();
        if (!preg_match_all('/{([\w:]+)}/', $template, $matches)) {
            return array();
        }

        $variables = array();
        foreach ($matches[1] as $variable) {
            $parts = explode(':', $variable);

            $variable = new Shopgate_Model_Redirect_HtmlTagVariable();
            $variable->setName($parts[0]);

            if (!empty($parts[1])) {
                $variable->setFunctionName($parts[1]);
            }

            $variables[] = $variable;
        }

        return $variables;
    }

    public function process($template, $variable, $replacement)
    {
        $variableString = '{' . $variable->getName();

        if ($variable->getFunctionName()) {
            $variableString .= ':' . $variable->getFunctionName();
        }

        $variableString .= '}';

        return str_replace(
            $variableString,
            $this->filterVariableValue($replacement, $variable->getFunctionName()),
            $template
        );
    }

    public function filterVariableValue($value, $functionName = '')
    {
        switch ($functionName) {
            case Shopgate_Helper_Redirect_TemplateParserInterface::FUNCTION_NAME_HEX:
                return bin2hex($value);
            case Shopgate_Helper_Redirect_TemplateParserInterface::FUNCTION_NAME_URLENCODED:
                return urlencode($value);
            case Shopgate_Helper_Redirect_TemplateParserInterface::FUNCTION_NAME_BASE64:
                return base64_encode($value);
            case Shopgate_Helper_Redirect_TemplateParserInterface::FUNCTION_NAME_ESCAPED:
                return addslashes(htmlentities($value));
        }

        return $value;
    }
}
