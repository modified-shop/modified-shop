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
interface Shopgate_Helper_Redirect_LinkBuilderInterface
{
    const LINK_TYPE_DEFAULT  = 'default';
    const LINK_TYPE_HOME     = 'home';
    const LINK_TYPE_PRODUCT  = 'product';
    const LINK_TYPE_CATEGORY = 'category';
    const LINK_TYPE_CMS      = 'cms';
    const LINK_TYPE_BRAND    = 'brand';
    const LINK_TYPE_SEARCH   = 'search';

    /**
     * @param Shopgate_Model_Redirect_DeeplinkSuffix[] $parameters
     *
     * @return string
     */
    public function buildDefault(array $parameters = array());

    /**
     * @param Shopgate_Model_Redirect_DeeplinkSuffix[] $parameters
     *
     * @return string
     */
    public function buildHome(array $parameters = array());

    /**
     * @param string                                   $uid
     * @param Shopgate_Model_Redirect_DeeplinkSuffix[] $parameters
     *
     * @return string
     */
    public function buildProduct($uid, array $parameters = array());

    /**
     * @param string                                   $uid
     * @param Shopgate_Model_Redirect_DeeplinkSuffix[] $parameters
     *
     * @return string
     */
    public function buildCategory($uid, array $parameters = array());

    /**
     * @param string                                   $pageUid
     * @param Shopgate_Model_Redirect_DeeplinkSuffix[] $parameters
     *
     * @return string
     */
    public function buildCms($pageUid, array $parameters = array());

    /**
     * @param string $brandName
     *
     * @return string
     */
    public function buildBrand($brandName);

    /**
     * @param string                                   $searchQuery
     * @param Shopgate_Model_Redirect_DeeplinkSuffix[] $parameters
     *
     * @return string
     */
    public function buildSearch($searchQuery, array $parameters = array());

    /**
     * @param string                                    $pageType
     * @param Shopgate_Model_Redirect_HtmlTagVariable[] $variables
     * @param array                                     $parameters       [string, string]
     * @param string                                    $overrideTemplate Set to null to not override the default
     *                                                                    template
     *
     * @return string
     */
    public function getUrlFor($pageType, array $variables, array $parameters = array(), $overrideTemplate = null);
}
