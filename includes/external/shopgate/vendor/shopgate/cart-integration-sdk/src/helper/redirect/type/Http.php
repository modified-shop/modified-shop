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
class Shopgate_Helper_Redirect_Type_Http implements Shopgate_Helper_Redirect_Type_TypeInterface
{
    /** @var Shopgate_Helper_Redirect_RedirectorInterface */
    private $redirector;

    /**
     * @param Shopgate_Helper_Redirect_RedirectorInterface $redirector
     */
    public function __construct(Shopgate_Helper_Redirect_RedirectorInterface $redirector)
    {
        $this->redirector = $redirector;
    }

    /**
     * @return Shopgate_Helper_Redirect_RedirectorInterface
     */
    public function getBuilder()
    {
        return $this->redirector;
    }

    /**
     * @param string $manufacturer
     *
     * @return void
     */
    public function loadBrand($manufacturer)
    {
        $this->redirector->redirectBrand($manufacturer);
    }

    /**
     * @param int|string $categoryId
     *
     * @return void
     */
    public function loadCategory($categoryId)
    {
        $this->redirector->redirectCategory($categoryId);
    }

    /**
     * @param string $cmsPage
     *
     * @return void
     */
    public function loadCms($cmsPage)
    {
        $this->redirector->redirectCms($cmsPage);
    }

    /**
     * @return void
     */
    public function loadDefault()
    {
        $this->redirector->redirectDefault();
    }

    /**
     * @return void
     */
    public function loadHome()
    {
        $this->redirector->redirectHome();
    }

    /**
     * @param int|string $productId
     *
     * @return void
     */
    public function loadProduct($productId)
    {
        $this->redirector->redirectProduct($productId);
    }

    /**
     * @param string $query
     *
     * @return void
     */
    public function loadSearch($query)
    {
        $this->redirector->redirectSearch($query);
    }
}
