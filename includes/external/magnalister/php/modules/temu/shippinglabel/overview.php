<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

global $_url;
// Keep view=overview in the AJAX URL so checkbox selections are dispatched to
// TemuShippingLabelOverview (selection name temu_shippinglabel_overview) instead
// of defaulting to the upload/orderlist view (temu_shippinglabel_orderlist).
$_url['view'] = 'overview';

if (!defined('DIR_MAGNALISTER_MODULES_TEMU_ORDERLIST')) {
    define('DIR_MAGNALISTER_MODULES_TEMU_ORDERLIST', DIR_MAGNALISTER_MODULES.'temu/shippinglabel/classes/Orderlist/');
}
if (!defined('DIR_MAGNALISTER_MODULES_TEMU_SHIPPINGLABEL_TEMPLATES')) {
    define('DIR_MAGNALISTER_MODULES_TEMU_SHIPPINGLABEL_TEMPLATES', DIR_MAGNALISTER_MODULES.'temu/shippinglabel/templates/');
}

require_once(DIR_MAGNALISTER_MODULES.'temu/shippinglabel/TemuShippingLabelOverview.php');
$oOrderlist = new TemuShippingLabelOverview();
echo $oOrderlist->render();
