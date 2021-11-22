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
include_once DIR_FS_CATALOG
    . 'includes/external/shopgate/shopgate_library/shopgate.php';
include_once DIR_FS_CATALOG . 'includes/external/shopgate/plugin.php';

/**
 * Wrapper for setShopgateOrderlistStatus() with only one order.
 *
 * For compatibility reasons.
 *
 * @param int $orderId The ID of the order in the shop system.
 * @param int $status  The ID of the order status that has been set in the
 *                     shopping system.
 */
function setShopgateOrderStatus($orderId, $status)
{
    if (empty($orderId)) {
        return;
    }

    setShopgateOrderlistStatus(array($orderId), $status);
}

/**
 * Wrapper for ShopgatePluginGambioGX::updateOrdersStatus(). Set the shipping
 * status for a list of order IDs.
 *
 * @param int[] $orderIds The IDs of the orders in the shop system.
 * @param int   $status   The ID of the order status that has been set in the
 *                        shopping system.
 */
function setShopgateOrderlistStatus($orderIds, $status)
{
    if (empty($orderIds) || !is_array($orderIds)) {
        return;
    }

    $plugin = new ShopgateModifiedPlugin();
    $plugin->updateOrdersStatus($orderIds, $status);
}
