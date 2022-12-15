<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003 nextcommerce (print_order.php,v 1.5 2003/08/24); www.nextcommerce.org
   (c) 2005 xtCommerce (print_order.php); www.xt-commerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

$smarty = new Smarty;

if (DIR_WS_BASE == '') {
  $smarty->assign('base_href', (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG);
}
$smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

$oID = (isset($_GET['oID']) ? (int)$_GET['oID'] : 0);
$customer_id = (isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : (isset($_SESSION['customer_gid']) ? (int)$_SESSION['customer_gid'] : 0));

// check if custmer is allowed to see this order!
$order_query_check = xtc_db_query("SELECT customers_id
                                     FROM ".TABLE_ORDERS."
                                    WHERE orders_id = '".$oID."'");
$order_check = xtc_db_fetch_array($order_query_check);

if ($customer_id > 0
    && isset($order_check['customers_id'])
    && $customer_id == $order_check['customers_id']
    )
{
  // get order data
  include (DIR_WS_CLASSES.'order.php');
  $order = new order($oID);
  $smarty->assign('address_label_customer', xtc_address_format($order->customer['format_id'], $order->customer, 1, '', '<br />'));
  $smarty->assign('address_label_shipping', xtc_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />'));
  $smarty->assign('address_label_payment', xtc_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />'));
  $smarty->assign('csID', $order->customer['csID']);
  
  // get products data
  $order_total = $order->getTotalData($oID);
  $smarty->assign('order_data', $order->getOrderData($oID));
  $smarty->assign('order_total', $order_total['data']);

  // allow duty-note in print_order
  $smarty->assign('DELIVERY_DUTY_INFO', $main->getDeliveryDutyInfo($order->delivery['country_iso_2']));

  // assign language to template for caching
  $smarty->assign('language', $order->info['language']);  
  $smarty->assign('charset', $_SESSION['language_charset'] );

  $smarty->assign('oID', $oID);
  $smarty->assign('COMMENT', $order->info['comments']);
  $smarty->assign('DATE', xtc_date_long($order->info['date_purchased']));
  $smarty->assign('SHIPPING_CLASS', $order->info['shipping_class']);

  // Payment Method
  if ($order->info['payment_method'] != '' && $order->info['payment_method'] != 'no_payment') {  
    $_SESSION['billing_zone'] = $order->billing['country_iso_2'];
    $last_order = $order->info['order_id'];
    require_once (DIR_WS_CLASSES . 'payment.php');
    $payment_modules = new payment($order->info['payment_class']);
    $smarty->assign('PAYMENT_INFO', $payment_modules->success());
    $smarty->assign('PAYMENT_METHOD', $payment_modules::payment_title($order->info['payment_method'], $order->info['order_id']));
    unset($_SESSION['billing_zone']);
  }

  // dont allow cache
  $smarty->caching =0;
  $smarty->display(CURRENT_TEMPLATE.'/module/print_order.html');
} else {
  die('You are not allowed to view this order!');
}
