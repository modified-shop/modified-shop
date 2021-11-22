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
interface Shopgate_Helper_Redirect_SettingsManagerInterface
{
    const DISABLE_REDIRECT_COOKIE_EXPIRATION_1_WEEK   = 604800;
    const DEFAULT_DISABLE_REDIRECT_GET_PARAMETER_NAME = 'shopgate_redirect';
    const DEFAULT_DISABLE_REDIRECT_COOKIE_NAME        = 'SHOPGATE_MOBILE_WEBPAGE';
    const SHOPGATE_STATIC                             = '//static.shopgate.com';
    const SHOPGATE_STATIC_SSL                         = self::SHOPGATE_STATIC;
    const SHOPGATE_PG_STATIC                          = '//static.shopgatepg.com';
    const SHOPGATE_PG_STATIC_SSL                      = self::SHOPGATE_PG_STATIC;
    const SHOPGATE_SL_STATIC                          = '//static.shopgatesl.com';
    const SHOPGATE_SL_STATIC_SSL                      = self::SHOPGATE_SL_STATIC;
    const SHOPGATE_DEV_STATIC                         = '//shopgatedev-public.s3.amazonaws.com';
    const SHOPGATE_DEV_STATIC_SSL                     = self::SHOPGATE_DEV_STATIC;
    const SHOPGATE_LIVE_ALIAS                         = '.shopgate.com';
    const SHOPGATE_SL_ALIAS                           = '.shopgatesl.com';
    const SHOPGATE_PG_ALIAS                           = '.shopgatepg.com';
    const SHOPGATE_DEV_ALIAS                          = '.localdev.cc/php/shopgate/index.php';

    /**
     * @return bool
     */
    public function isDefaultRedirectDisabled();

    /**
     * @return bool
     */
    public function isRedirectDisabled();

    /**
     * @return bool
     */
    public function isMobileHeaderDisabled();

    /**
     * Sends the cookie to disable the mobile redirect is sent to the requesting entity.
     *
     * @param int $startTime Pass null to use time().
     */
    public function setCookie($startTime = null);

    /**
     * Generates the root mobile Url for the redirect without trailing slashes.
     */
    public function getMobileUrl();

    /**
     * Filters the GET parameters that should be passed in the redirect and builds the new query string.
     *
     * @return string
     */
    public function getRedirectableGetParameters();

    /**
     * @return string
     * @throws ShopgateLibraryException in case the configuration value is null.
     */
    public function getHtmlTags();

    /**
     * @return array [string, string]
     */
    public function getDefaultTemplatesByPageType();

    /**
     * @return array [string, string] An array with indices 'ssl_url' and 'non_ssl_url' and the corresponding URLs.
     */
    public function getShopgateStaticUrl();
}
