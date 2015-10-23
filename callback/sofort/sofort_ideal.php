<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
 	 based on:
	  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
	  (c) 2002-2003 osCommerce - www.oscommerce.com
	  (c) 2001-2003 TheMedia, Dipl.-Ing Thomas Plänkers - http://www.themedia.at & http://www.oscommerce.at
	  (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com
    (c) 2010 Payment Network AG - http://www.payment-network.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

chdir('../../');
require_once('includes/application_top.php');

// include needed classes
require_once(DIR_WS_CLASSES.'order.php');
require_once(DIR_FS_EXTERNAL.'sofort/classes/sofortLibNotificationClassic.inc.php');

// set callback type
$sofort_code = substr(basename($PHP_SELF), 0, -4);

list ($userId, $projectId) = explode(':', constant('MODULE_PAYMENT_'.strtoupper($sofort_code).'_KEY'));
$sofortLibNotification = new sofortLibNotificationClassic($userId,
                                                          $projectId,
                                                          constant('MODULE_PAYMENT_'.strtoupper($sofort_code).'_NOTIFY_PASS'),
                                                          constant('MODULE_PAYMENT_'.strtoupper($sofort_code).'_HASH_ALGORITHM')
                                                          );
$sofortLibNotification->getNotification(array_merge($_GET, $_POST));

// check hash
if ($sofortLibNotification->_hashCheck === false) {
  die('ERROR_WRONG_HASH');
}

$orders_query = xtc_db_query("SELECT order_id
                                FROM sofort_ideal
                               WHERE transaction_id = '".xtc_db_input($sofortLibNotification->getUserVariable(2))."'");
if (xtc_db_num_rows($orders_query) == 1) {

  // order id
  $orders = xtc_db_fetch_array($orders_query);
  $order = new order($orders['order_id']);

  $tID = $sofortLibNotification->getTransaction();
  $status = $sofortLibNotification->getStatus();
  $reason = $sofortLibNotification->getStatusReason(0,0);

  include_once (DIR_FS_CATALOG . 'lang/'.$order->info['language'].'/modules/payment/'.$order->info['payment_method'].'.php');

  if (number_format($order->info['pp_total'], 2, '.', '') == $sofortLibNotification->getAmount()) {
    if ($status != '') {
      switch ($status) {
        case 'pending':
          $order_status_id = constant('MODULE_PAYMENT_'.strtoupper($order->info['payment_method']).'_ORDER_STATUS_ID');
          $comments = sprintf(constant('MODULE_PAYMENT_'.strtoupper($order->info['payment_method']).'_SUCCESS_TRANSACTION'), $tID);
          break;
        case 'received':
          $order_status_id = constant('MODULE_PAYMENT_'.strtoupper($order->info['payment_method']).'_REC_STATUS_ID');
          $comments = sprintf(constant('MODULE_PAYMENT_'.strtoupper($order->info['payment_method']).'_SUCCESS_PAYMENT'), $tID);
          break;
        case 'refunded':
          $order_status_id = constant('MODULE_PAYMENT_'.strtoupper($order->info['payment_method']).'_REF_STATUS_ID');
          $comments = sprintf(constant('MODULE_PAYMENT_'.strtoupper($order->info['payment_method']).'_SUCCESS_REFUNDED'), $tID);
          break;
        case 'loss':
          $order_status_id = constant('MODULE_PAYMENT_'.strtoupper($order->info['payment_method']).'_LOSS_STATUS_ID');
          $comments = sprintf(constant('MODULE_PAYMENT_'.strtoupper($order->info['payment_method']).'_ERROR_PAYMENT'), $tID);
          break;
        default:
          $order_status_id = constant('MODULE_PAYMENT_'.strtoupper($order->info['payment_method']).'_UNC_STATUS_ID');
          $comments = sprintf(constant('MODULE_PAYMENT_'.strtoupper($order->info['payment_method']).'_ERROR_UNEXPECTED_STATUS'), $tID);
          break;
      }
    } else {
      $order_status_id = constant('MODULE_PAYMENT_'.strtoupper($order->info['payment_method']).'_ORDER_STATUS_ID');
      $comments = sprintf(constant('MODULE_PAYMENT_'.strtoupper($order->info['payment_method']).'_SUCCESS_TRANSACTION'), $tID);
    }
  } else {
    $order_status_id = constant('MODULE_PAYMENT_'.strtoupper($order->info['payment_method']).'_UNC_STATUS_ID');
    $comments = sprintf(constant('MODULE_PAYMENT_'.strtoupper($order->info['payment_method']).'_ERROR_TRANSACTION'), $tID);
  }

  if ($order_status_id < 0) {
    $order_status_id = $order->info['orders_status_id'];
  }
  if ($reason != '') {
    $comments .= "\n".'Reason: ' . constant('TEXT_SOFORT_'.strtoupper($reason));
  }

  xtc_db_query("UPDATE ".TABLE_ORDERS." SET orders_status = '".$order_status_id."' WHERE orders_id = '".(int) $orders['order_id']."'");
  $sql_data_array = array('orders_id' => (int) $orders['order_id'],
                          'orders_status_id' => $order_status_id,
                          'date_added' => 'now()',
                          'customer_notified' => '0',
                          'comments' => decode_htmlentities($comments),
                          'comments_sent' => '0'
                          );
  xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

} else {

  // order is missing
  header("HTTP/1.0 404 Not Found");
  header("Status: 404 Not Found");
  
  /*
  if(constant('MODULE_PAYMENT_'.strtoupper($sofort_code).'_TMP_ORDER') == 'False') {
    // wait before redirect
    sleep(3);

    xtc_redirect($sofortLibNotification->getUserVariable(3).'&nonexistorder=true');
  }
  */

}