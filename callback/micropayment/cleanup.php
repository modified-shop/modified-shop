<?php
/**
 *
 * @package    micropayment
 * @copyright  Copyright (c) 2022 Micropayment GmbH (http://www.micropayment.de)
 * @author     micropayment GmbH (TE) <support@micropayment.de>
 */
chdir('../../');

// needed define
define('SESSION_FORCE_COOKIE_USE', 'False');

require_once('includes/application_top.php');

$method_class_file = DIR_FS_EXTERNAL.'micropayment/class.micropayment_method.php';
require_once($method_class_file);

if (defined('MODULE_PAYMENT_MCP_SERVICE_STATUS')
    && $_SESSION['customers_status']['customers_status'] == 0
    )
{
    $age = MODULE_PAYMENT_MCP_SERVICE_EXPIRE_DAYS;
    if($age < 3 ) {
        $age = 3;
    }
    $query = xtc_db_query(
        sprintf(
            'SELECT *,
                (SELECT `function` FROM `micropayment_log` WHERE `order_id` = `micropayment_orders`.`order_id` ORDER BY `created` DESC LIMIT 1) `mcp_status` FROM micropayment_orders
             LEFT JOIN `orders` ON `orders`.`orders_id` = `micropayment_orders`.`order_id`
             WHERE `micropayment_orders`.`createdon` <= DATE_SUB(NOW(),INTERVAL %s DAY)
             ',
            $age
        )
    );
    while($data = xtc_db_fetch_array($query)) {
        if($data['mcp_status'] != 'new') {
            continue;
        } elseif($data['orders_status'] != 1 && $data['orders_status'] != MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ID) {
            continue;
        } else {
            $model = new micropayment_method();
            $model->mcp_remove_order($data['order_id'],true);
            xtc_db_query(sprintf('DELETE FROM micropayment_orders WHERE order_id = "%s"',$data['order_id']));
            xtc_db_query(sprintf('DELETE FROM micropayment_log WHERE order_id = "%s"',$data['order_id']));
        }
    }
    echo 'Incomplete orders which are ' . $age . ' days old have been removed.<a href="javascript:void(0);" onclick="self.close();return false;">Click here to return to your shop backend.</a>';
}