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
interface Shopgate_Helper_Redirect_MobileRedirectInterface
{
    /**
     * @param bool $http
     * @param bool $javascript
     */
    public function supressRedirectTechniques($http = false, $javascript = false);

    /**
     * @param string $name One of the Shopgate_Helper_Redirect_TagsGeneratorInterface::SITE_PARAMETER_* constants.
     * @param string $value
     */
    public function addSiteParameter($name, $value);

    /**
     * @param string $url
     * @param bool   $sendVary
     *
     * @post ends script execution in case of http redirect
     */
    public function redirect($url, $sendVary = true);

    /**
     * @return string
     */
    public function buildScriptDefault();

    /**
     * @return string
     */
    public function buildScriptShop();

    /**
     * @param string $itemNumber
     *
     * @return string
     */
    public function buildScriptItem($itemNumber);

    /**
     * @param string $itemNumberPublic
     *
     * @return string
     */
    public function buildScriptItemPublic($itemNumberPublic);

    /**
     * @param string $categoryNumber
     *
     * @return string
     */
    public function buildScriptCategory($categoryNumber);

    /**
     * @param string $cmsPage
     *
     * @return string
     */
    public function buildScriptCms($cmsPage);

    /**
     * @param string $manufacturerName
     *
     * @return mixed
     */
    public function buildScriptBrand($manufacturerName);

    /**
     * @param string $searchQuery
     *
     * @return string
     */
    public function buildScriptSearch($searchQuery);
}
