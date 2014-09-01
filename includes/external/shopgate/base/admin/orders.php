<?php

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

include_once DIR_FS_CATALOG.'includes/external/shopgate/shopgate_library/shopgate.php';
include_once DIR_FS_CATALOG.'includes/external/shopgate/plugin.php';

/**
 * Wrapper for setShopgateOrderlistStatus() with only one order.
 *
 * For compatibility reasons.
 *
 * @param int $orderId The ID of the order in the shop system.
 * @param int $status The ID of the order status that has been set in the shopping system.
 */
function setShopgateOrderStatus($orderId, $status) {
	if (empty($orderId)) {
		return;
	}
	
	setShopgateOrderlistStatus(array($orderId), $status);
}

/**
 * Wrapper for ShopgatePluginGambioGX::updateOrdersStatus(). Set the shipping status for a list of order IDs.
 *
 * @param int[] $orderIds The IDs of the orders in the shop system.
 * @param int $status The ID of the order status that has been set in the shopping system.
 */
function setShopgateOrderlistStatus($orderIds, $status) {
	if (empty($orderIds) || !is_array($orderIds)) {
		return;
	}
	
	$plugin = new ShopgateModifiedPlugin();
	$plugin->updateOrdersStatus($orderIds, $status);
}