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
class Shopgate_Helper_Redirect_Type_Js implements Shopgate_Helper_Redirect_Type_TypeInterface
{
    /** @var Shopgate_Helper_Redirect_JsScriptBuilderInterface */
    private $jsBuilder;

    /**
     * @param Shopgate_Helper_Redirect_JsScriptBuilderInterface $jsBuilder
     */
    public function __construct(Shopgate_Helper_Redirect_JsScriptBuilderInterface $jsBuilder)
    {
        $this->jsBuilder = $jsBuilder;
    }

    /**
     * @return Shopgate_Helper_Redirect_JsScriptBuilderInterface
     */
    public function getBuilder()
    {
        return $this->jsBuilder;
    }

    /**
     * @param string $manufacturer
     *
     * @return string
     */
    public function loadBrand($manufacturer)
    {
        return $this->jsBuilder->buildTags(
            Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_BRAND,
            array('brand_name' => $manufacturer)
        );
    }

    /**
     * @param int|string $categoryId
     *
     * @return string
     */
    public function loadCategory($categoryId)
    {
        return $this->jsBuilder->buildTags(
            Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_CATEGORY,
            array('category_uid' => $categoryId)
        );
    }

    /**
     * @param string $cmsPage
     *
     * @return string
     */
    public function loadCms($cmsPage)
    {
        return $this->jsBuilder->buildTags(
            Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_CMS,
            array('page_uid' => $cmsPage)
        );
    }

    /**
     * @return string
     */
    public function loadDefault()
    {
        return $this->jsBuilder->buildTags(Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_DEFAULT);
    }

    /**
     * @return string
     */
    public function loadHome()
    {
        return $this->jsBuilder->buildTags(Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_HOME);
    }

    /**
     * @param int|string $productId
     *
     * @return string
     */
    public function loadProduct($productId)
    {
        return $this->jsBuilder->buildTags(
            Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_PRODUCT,
            array('product_uid' => $productId)
        );
    }

    /**
     * @param string $query
     *
     * @return string
     */
    public function loadSearch($query)
    {
        return $this->jsBuilder->buildTags(
            Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_SEARCH,
            array('search_query' => $query)
        );
    }
}
