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
interface Shopgate_Helper_Redirect_TagsGeneratorInterface
{
    const PAGE_TYPE_DEFAULT                        = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_DEFAULT;
    const PAGE_TYPE_HOME                           = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_HOME;
    const PAGE_TYPE_PRODUCT                        = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_PRODUCT;
    const PAGE_TYPE_CATEGORY                       = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_CATEGORY;
    const PAGE_TYPE_CMS                            = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_CMS;
    const PAGE_TYPE_BRAND                          = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_BRAND;
    const PAGE_TYPE_SEARCH                         = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_SEARCH;
    const SITE_PARAMETER_SITENAME                  = 'sitename';
    const SITE_PARAMETER_DESKTOP_URL               = 'desktop_url';
    const SITE_PARAMETER_MOBILE_WEB_URL            = 'mobile_web_url';
    const SITE_PARAMETER_TITLE                     = 'title';
    const SITE_PARAMETER_PRODUCT_IMAGE             = 'product_image';
    const SITE_PARAMETER_PRODUCT_NAME              = 'product_name';
    const SITE_PARAMETER_PRODUCT_DESCRIPTION_SHORT = 'product_description_short';
    const SITE_PARAMETER_PRODUCT_EAN               = 'product_ean';
    const SITE_PARAMETER_PRODUCT_AVAILABILITY      = 'product_availability';
    const SITE_PARAMETER_PRODUCT_CATEGORY          = 'product_category';
    const SITE_PARAMETER_PRODUCT_PRICE             = 'product_price';
    const SITE_PARAMETER_PRODUCT_CURRENCY          = 'product_currency';
    const SITE_PARAMETER_PRODUCT_PRETAX_PRICE      = 'product_pretax_price';
    const SITE_PARAMETER_PRODUCT_PRETAX_CURRENCY   = 'product_pretax_currency';

    /**
     * @param Shopgate_Model_Redirect_HtmlTag[] $htmlTags
     */
    public function setHtmlTags(array $htmlTags);

    /**
     * @param string $htmlTags A JSON encoded string containing the HTML tags.
     */
    public function setHtmlTagsFromJson($htmlTags);

    /**
     * @param string $pageType
     * @param array  $parameters [string, string]
     *
     * @return string
     */
    public function getTagsFor($pageType, array $parameters = array());
}
