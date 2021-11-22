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
interface Shopgate_Helper_Redirect_Type_TypeInterface
{
    const HTTP = 'http';
    const JS   = 'js';

    /**
     * @return Shopgate_Helper_Redirect_JsScriptBuilderInterface | Shopgate_Helper_Redirect_RedirectorInterface
     */
    public function getBuilder();

    /**
     * @param string $manufacturer
     *
     * @return string | void
     */
    public function loadBrand($manufacturer);

    /**
     * @param string | int $categoryId
     *
     * @return string | void
     */
    public function loadCategory($categoryId);

    /**
     * @param string $cmsPage
     *
     * @return string | void
     */
    public function loadCms($cmsPage);

    /**
     * @return string | void
     */
    public function loadDefault();

    /**
     * @return string | void
     */
    public function loadHome();

    /**
     * @param string | int $productId
     *
     * @return string | void
     */
    public function loadProduct($productId);

    /**
     * @param string $query
     *
     * @return string | void
     */
    public function loadSearch($query);
}
