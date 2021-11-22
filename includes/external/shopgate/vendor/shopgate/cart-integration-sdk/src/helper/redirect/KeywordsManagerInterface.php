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
interface Shopgate_Helper_Redirect_KeywordsManagerInterface
{
    /**
     * @var int (hours) the default time to be set for updating the cache
     */
    const DEFAULT_CACHE_TIME = 24;

    /**
     * Returns a regular expression matching everything on the whitelist and not matching anything on the black list.
     *
     * @return string
     */
    public function toRegEx();

    /**
     * @return string[] A list of keywords that identify a smartphone user.
     */
    public function getWhitelist();

    /**
     * @return string[] A list keywords that identify a smartphone user but should be ignored in the redirect.
     */
    public function getBlacklist();

    /**
     * Updates the keyword cache from the merchant API regardless of expiry etc.
     *
     * @throws ShopgateLibraryException in case the request to the Shopgate Merchant API fails.
     */
    public function update();
}
