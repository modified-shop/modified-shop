<?php
/**
 *
 * Copyright Shopgate Inc.
 *
 * Licensed under the GNU General Public License, Version 2 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU General Public License, Version 2
 *
 */
if (!defined('_VALID_XTC')) {
    define('_VALID_XTC', true);
}

$shopgatePath = dirname(__FILE__) . '/includes/external/shopgate';
date_default_timezone_set("Europe/Berlin");

include_once $shopgatePath . '/shopgate_library/shopgate.php';
ob_start();
include_once('includes/application_top.php');
ob_end_clean();
include_once $shopgatePath . '/plugin.php';

$ShopgateFramework = new ShopgateModifiedPlugin();
$ShopgateFramework->handleRequest($_REQUEST);
