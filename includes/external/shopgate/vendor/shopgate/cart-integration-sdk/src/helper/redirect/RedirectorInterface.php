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
interface Shopgate_Helper_Redirect_RedirectorInterface
{
    /**
     * @post If enabled, a location header to the mobile default/fallback page is sent to the requesting entity.
     */
    public function redirectDefault();

    /**
     * @post A location header to the mobile home page is sent to the requesting entity.
     */
    public function redirectHome();

    /**
     * @param string $uid
     *
     * @post A location header to the mobile category detail page is sent to the requesting entity.
     */
    public function redirectCategory($uid);

    /**
     * @param string $uid
     *
     * @post A location header to the mobile product page is sent to the requesting entity.
     */
    public function redirectProduct($uid);

    /**
     * @param string $pageUid
     *
     * @post A location header to the mobile CMS page is sent to the requesting entity.
     */
    public function redirectCms($pageUid);

    /**
     * @param string $brandName
     *
     * @post A location header to the mobile brand search is sent to the requesting entity.
     */
    public function redirectBrand($brandName);

    /**
     * @param string $searchString
     *
     * @post A location header to the mobile searchpage is sent to the requesting entity.
     */
    public function redirectSearch($searchString);

    /**
     * @param string $url      The URL to redirect to.
     * @param bool   $sendVary True to send the "Vary: User-Agent" header.
     */
    public function redirect($url, $sendVary = true);

    /**
     * Checks current browser user agent string
     * against allowed mobile keywords, e.g. Iphone, Android, etc
     *
     * @return bool
     */
    public function isMobile();
}
