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
class Shopgate_Helper_Redirect_SettingsManager implements Shopgate_Helper_Redirect_SettingsManagerInterface
{
    /** @var ShopgateConfigInterface */
    protected $config;

    /** @var string */
    protected $disableRedirectGetParameterName;

    /** @var string */
    protected $disableRedirectCookieName;

    /**
     * @var int Seconds until the disable cookie should expire if set at $this->startTime.
     */
    protected $expiration;

    /**
     * @var array [string, mixed] A copy of $_GET.
     */
    protected $get;

    /**
     * @var array [string, mixed] A reference to $_COOKIE.
     */
    protected $cookie;

    /**
     * @param ShopgateConfigInterface $config
     * @param array                   $get              [string, mixed] A copy of $_GET.
     * @param array                   $cookie           [string, mixed] A reference to $_COOKIE.
     * @param int                     $cookieExpiration Seconds until the disable cookie should expire if set at
     *                                                  $startTime.
     * @param string                  $disableRedirectGetParameterName
     * @param string                  $disableRedirectCookieName
     */
    public function __construct(
        ShopgateConfigInterface $config,
        array $get = array(),
        array &$cookie = array(),
        $cookieExpiration = self::DISABLE_REDIRECT_COOKIE_EXPIRATION_1_WEEK,
        $disableRedirectGetParameterName = self::DEFAULT_DISABLE_REDIRECT_GET_PARAMETER_NAME,
        $disableRedirectCookieName = self::DEFAULT_DISABLE_REDIRECT_COOKIE_NAME
    ) {
        $this->config                          = $config;
        $this->get                             = $get;
        $this->cookie                          = $cookie;
        $this->expiration                      = $cookieExpiration;
        $this->disableRedirectGetParameterName = $disableRedirectGetParameterName;
        $this->disableRedirectCookieName       = $disableRedirectCookieName;
    }

    public function isDefaultRedirectDisabled()
    {
        return (bool)$this->config->getEnableDefaultRedirect();
    }

    public function isRedirectDisabled()
    {
        return
            $this->isMobileHeaderDisabled()
            || !empty($this->cookie[$this->disableRedirectCookieName])
            || !empty($this->get[$this->disableRedirectGetParameterName]);
    }

    public function isMobileHeaderDisabled()
    {
        return !$this->config->getShopNumber() || !$this->config->getShopIsActive();
    }

    public function setCookie($startTime = null)
    {
        if ($startTime === null) {
            $startTime = time();
        }

        setcookie(
            $this->disableRedirectCookieName,
            1,
            $startTime + $this->expiration,
            '/'
        );
    }

    public function getMobileUrl()
    {
        if ($this->config->getCname()) {
            return $this->fixCname($this->config->getCname());
        }

        return rtrim('http://' . $this->config->getAlias() . $this->getShopgateUrl(), '/');
    }

    public function getRedirectableGetParameters()
    {
        return http_build_query(array_intersect_key($this->get, array_flip($this->config->getRedirectableGetParams())));
    }

    public function getHtmlTags()
    {
        $htmlTags = $this->config->getHtmlTags();

        if (empty($htmlTags)) {
            throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_INVALID_VALUE, '', false, false);
        }

        return $htmlTags;
    }

    public function getDefaultTemplatesByPageType()
    {
        $templatePageDefault = $this->isDefaultRedirectDisabled()
            ? false
            : '{baseUrl}';

        return array(
            Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_DEFAULT  => $templatePageDefault,
            Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_HOME     => '{baseUrl}',
            Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_PRODUCT  => '{baseUrl}/item/{product_uid:hex}',
            Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_CATEGORY => '{baseUrl}/category/{category_uid:hex}',
            Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_CMS      => '{baseUrl}/cms/{page_uid}',
            Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_BRAND    => '{baseUrl}/brand?q={brand_name:urlencoded}',
            Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_SEARCH   => '{baseUrl}/search?s={search_query:urlencoded}',
        );
    }

    public function getShopgateStaticUrl()
    {
        $result = array();

        switch ($this->config->getServer()) {
            default: // fall through to 'live'
            case 'live':
                $result['ssl_url']     = self::SHOPGATE_STATIC_SSL;
                $result['non_ssl_url'] = self::SHOPGATE_STATIC;
                break;
            case 'sl':
                $result['ssl_url']     = self::SHOPGATE_SL_STATIC_SSL;
                $result['non_ssl_url'] = self::SHOPGATE_SL_STATIC;
                break;
            case 'pg':
                $result['ssl_url']     = self::SHOPGATE_PG_STATIC_SSL;
                $result['non_ssl_url'] = self::SHOPGATE_PG_STATIC;
                break;
            case 'custom':
                $result['ssl_url']     = self::SHOPGATE_DEV_STATIC_SSL;
                $result['non_ssl_url'] = self::SHOPGATE_DEV_STATIC;
                break;
        }

        return $result;
    }

    /**
     * Returns the URL to be appended to the alias of a shop.
     *
     * The method determines this by the "server" setting in ShopgateConfig. If it's set to
     * "custom", localdev.cc will be used for Shopgate local development and testing.
     *
     * @return string The URL that can be appended to the alias, e.g. ".shopgate.com"
     */
    protected function getShopgateUrl()
    {
        switch ($this->config->getServer()) {
            default: // fall through to "live"
            case 'live':
                return self::SHOPGATE_LIVE_ALIAS;
            case 'sl':
                return self::SHOPGATE_SL_ALIAS;
            case 'pg':
                return self::SHOPGATE_PG_ALIAS;
            case 'custom':
                return self::SHOPGATE_DEV_ALIAS; // for Shopgate development & testing
        }
    }

    /**
     * Prepends http:// to the CNAME if it doesn't start with http:// or https:// already.
     *
     * @param string $cname
     *
     * @return string
     */
    protected function fixCname($cname)
    {
        if (!preg_match("/^(https?:\/\/\S+)?$/i", $cname)) {
            $cname = "http://" . $cname;
        }

        return $cname;
    }
}
