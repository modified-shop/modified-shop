<?php
chdir('../../');
require_once('includes/application_top.php');

if(!defined('MODULE_PAYMENT_MCP_SERVICE_ALLOWED_IP_ADDRESSES')) {
    require_once('lang/english/modules/payment/mcp_service.php');
}

$ok = true;
$data = $_REQUEST;

$mcp_allowed_hosts = explode(';',MODULE_PAYMENT_MCP_SERVICE_ALLOWED_IP_ADDRESSES);
// Sanity Checks
// IP Check
if(!MPDEV && !in_array($_SERVER['REMOTE_ADDR'], $mcp_allowed_hosts)) {
    $ok = false;
    $message = MODULE_PAYMENT_MCP_SERVICE_IP_NOT_ALLOWED;
}
// Parameter simple check
if(!is_array($data)) {
    $ok = false;
    $message = MODULE_PAYMENT_MCP_SERVICE_ERROR_TERMINATED;
}
// OrderId Value check
if($ok && (empty($data['orderid']))) {
    $ok = false;
    $error = MODULE_PAYMENT_MCP_SERVICE_ERROR_UNKNOWN_ORDER_ID;
} else {
    if(!preg_match('/^[\d]{1,}$/',$data['orderid'])) {
        $ok = false;
        echo 'REGEX FETCH';
        $error = MODULE_PAYMENT_MCP_SERVICE_ERROR_UNKNOWN_ORDER_ID;
    }
}
// Secret Field Check

if($ok && ((empty($data[MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD]) || $data[MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD] != MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD_VALUE))) {
    $ok = false;
    $error = MODULE_PAYMENT_MCP_SERVICE_ERROR_SECRET_FIELD_MISSMATCH;
}



$order_query  = xtc_db_query('SELECT * FROM '.TABLE_ORDERS.' WHERE `orders_id` = "' . xtc_db_prepare_input($data['orderid']) . '"');
if($ok && (xtc_db_num_rows($order_query) != 1)) {
    $ok = false;
    $error = MODULE_PAYMENT_MCP_SERVICE_UNKNOWN_ORDER_ID;
} elseif($ok) {
    $order = xtc_db_fetch_array($order_query);
    $total_query = xtc_db_query('SELECT value from '.TABLE_ORDERS_TOTAL.' WHERE orders_id = "'.(int) $data['orderid'].'"');
    $total = xtc_db_fetch_array($total_query);
    $total = $total['value'];
}
if($ok && (!preg_match('/^[\d]{1,}$/',$data['amount']) || abs($total-$data['amount']/100) > 1)) {
    $ok = false;
    $error = MODULE_PAYMENT_MCP_SERVICE_ERROR_AMOUNT_MISSMATCH;
}

// Sanity Checks end

// Setting up successful payment
switch($data['function']) {
    case 'billing':
        if($ok) {
            $customer_notification = 0;
            $order_status          = MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PROCESSING_ID;
            $comment               = sprintf(MODULE_PAYMENT_MCP_SERVICE_SUCCESS_TRANSACTION, $data['auth']);

            $sql_data_array = array(
                'orders_id'         => (int) $data['orderid'],
                'orders_status_id'  => $order_status ,
                'date_added'        => 'now()' ,
                'customer_notified' => $customer_notification ,
                'comments'          => $comment
            );
            xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
            xtc_db_query('UPDATE ' . TABLE_ORDERS . ' SET `orders_status` = "' . $order_status . '", `last_modified` = now() WHERE `orders_id` = "' . (int) $data['orderid'] . '"');

            // Cleaning up the Cart.
        }

        $xtc_url = xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL');

        if($ok) {
            $result = array(
                'status=OK',
                'forward=1',
                'target=_parent',
                'url='.$xtc_url
            );
        } else {
            $result = array(
                'status=ERROR',
                'forward=1',
                'target=_parent',
                'message='.urlencode($error),
                'url='.$xtc_url
            );

        }
        echo implode(PHP_EOL,$result);
    break;

    case 'init':
        if($ok) {
            $sql_data_array = array(
                'orders_id'         => (int) $data['orderid'],
                'orders_status_id'  => $order['order_status'],
                'date_added'        => 'now()' ,
                'customer_notified' => 0,
                'comments'          => sprintf(MODULE_PAYMENT_MCP_PREPAY_COMMENT_INIT,$data['expire'])
            );
            xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        }

        $xtc_url = xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL');

        if($ok) {
            $result = array(
                'status=OK',
                'forward=1',
                'target=_parent',
                'url='.$xtc_url
            );
        } else {
            $result = array(
                'status=ERROR',
                'forward=1',
                'target=_parent',
                'message='.urlencode($error),
                'url='.$xtc_url
            );
        }
        echo implode(PHP_EOL,$result);
    break;
    case 'storno':
        if($ok) {
            $order_status          = MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CANCELLED_ID;
            $comment               = MODULE_PAYMENT_MCP_SERVICE_TRANSACTION_CANCELLED;
            $sql_data_array = array(
                'orders_id'         => (int) $data['orderid'],
                'orders_status_id'  => $order_status,
                'date_added'        => 'now()' ,
                'customer_notified' => 0,
                'comments'          => $comment
            );
            xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
            xtc_db_query('UPDATE ' . TABLE_ORDERS . ' SET `orders_status` = "' . $order_status . '", `last_modified` = now() WHERE `orders_id` = "' . (int) $data['orderid'] . '"');
            echo 'status=OK';
        }
    break;
    case 'expire':
        if($ok) {
            $order_status          = MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CANCELLED_ID;
            $comment               = MODULE_PAYMENT_MCP_PREPAY_COMMENT_EXPIRED;
            $sql_data_array = array(
                'orders_id'         => (int) $data['orderid'],
                'orders_status_id'  => $order_status,
                'date_added'        => 'now()' ,
                'customer_notified' => 0,
                'comments'          => $comment
            );
            xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
            xtc_db_query('UPDATE ' . TABLE_ORDERS . ' SET `orders_status` = "' . $order_status . '", `last_modified` = now() WHERE `orders_id` = "' . (int) $data['orderid'] . '"');
            echo 'status=OK';
        }
    break;
    case 'backpay':
        if($ok) {
            $order_status          = MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PROCESSING_ID;
            $comment               = MODULE_PAYMENT_MCP_SERVICE_TRANSACTION_BACKPAY;

            $sql_data_array = array(
                'orders_id'         => (int) $data['orderid'],
                'orders_status_id'  => $order_status ,
                'date_added'        => 'now()' ,
                'customer_notified' => 0,
                'comments'          => $comment
            );
            xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
            xtc_db_query('UPDATE ' . TABLE_ORDERS . ' SET `orders_status` = "' . $order_status . '", `last_modified` = now() WHERE `orders_id` = "' . (int) $data['orderid'] . '"');
            echo 'status=OK';
        }
    break;
    case 'payin':
        $comment = sprintf(MODULE_PAYMENT_MCP_PREPAY_COMMENT_PAYIN,$data['amount']/100,$data['currency']);
        $sql_data_array = array(
            'orders_id'         => (int) $data['orderid'],
            'orders_status_id'  => $order['order_status'],
            'date_added'        => 'now()' ,
            'customer_notified' => 0,
            'comments'          => $comment
        );
        xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        echo 'status=OK';
    break;
    case 'change':
        $comment = sprintf(MODULE_PAYMENT_MCP_SERVICE_PREPAY_CHANGE,$data['amount']/100,$data['openamount']/100,$data['paidamount']/100);
        $sql_data_array = array(
            'orders_id'         => (int) $data['orderid'],
            'orders_status_id'  => $order['order_status'],
            'date_added'        => 'now()' ,
            'customer_notified' => 0,
            'comments'          => $comment
        );
        xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        echo 'status=OK';
    break;
    default:
        echo 'status=ERROR'.PHP_EOL.'message=unknown+function';
    break;
}

?>