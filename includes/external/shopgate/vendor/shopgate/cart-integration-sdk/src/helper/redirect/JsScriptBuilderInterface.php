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
interface Shopgate_Helper_Redirect_JsScriptBuilderInterface
{
    /**
     * @param string $pageType
     * @param array  $parameters [string, string]
     *
     * @return string
     */
    public function buildTags($pageType, $parameters = array());

    /**
     * Sets the file path of javascript template
     * to use
     *
     * @param string $filePath
     *
     * @return Shopgate_Helper_Redirect_JsScriptBuilderInterface
     */
    public function setJsTemplateFilePath($filePath);

    /**
     * Helps set all parameters at once
     *
     * @param array $params - array(key => value)
     *
     * @return Shopgate_Helper_Redirect_JsScriptBuilderInterface
     */
    public function setSiteParameters($params);

    /**
     * @param string $key
     * @param string $value
     *
     * @return Shopgate_Helper_Redirect_JsScriptBuilderInterface
     */
    public function setSiteParameter($key, $value);

    /**
     * @param string $file
     *
     * @return Shopgate_Helper_Redirect_JsScriptBuilderInterface
     */
    public function setTemplateFile($file);

    /**
     * Prints a value to JS script to prevent
     * web app redirect
     *
     * @param bool $param
     *
     * @return Shopgate_Helper_Redirect_JsScriptBuilderInterface
     */
    public function suppressWebAppRedirect($param);
}
