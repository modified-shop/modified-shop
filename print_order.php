<?php
/* -----------------------------------------------------------------------------------------
   $Id: print_order.php 3113 2012-06-22 16:23:20Z web28 $

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

// include needed functions
require_once (DIR_FS_INC.'xtc_get_order_data.inc.php');
require_once (DIR_FS_INC.'xtc_get_attributes_model.inc.php');

$smarty = new Smarty;

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('base_href', (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG);

$oID = (int) $_GET['oID'];
// check if custmer is allowed to see this order!
$order_query_check = xtc_db_query("SELECT customers_id
                                     FROM ".TABLE_ORDERS."
                                    WHERE orders_id=".$oID);
$order_check = xtc_db_fetch_array($order_query_check);
//BOF - GTB - 2010-09-15 - change Print Button to Form for Guests
if ((isset($_SESSION['customer_id']) && $_SESSION['customer_id'] == $order_check['customers_id']) || (isset($_POST['customer_id']) && $_POST['customer_id'] == $order_check['customers_id'])) {
//EOF - GTB - 2010-09-15 - change Print Button to Form for Guests

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

  //allow duty-note in print_order
  $smarty->assign('DELIVERY_DUTY_INFO', $main->getDeliveryDutyInfo($order->delivery['country_iso_2']));

  // assign language to template for caching
  $smarty->assign('language', $_SESSION['language']);
  $smarty->assign('oID', (int) $_GET['oID']);
  $payment_method = false; //DokuMan - 2010-03-18 - set undefined variable
  if ($order->info['payment_method'] != '' && $order->info['payment_method'] != 'no_payment') {
    include_once (DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/payment/'.$order->info['payment_method'].'.php');
    $payment_method = constant(strtoupper('MODULE_PAYMENT_'.$order->info['payment_method'].'_TEXT_TITLE'));
  }
  $smarty->assign('PAYMENT_METHOD', $payment_method);
  $smarty->assign('COMMENT', $order->info['comments']);
  $smarty->assign('DATE', xtc_date_long($order->info['date_purchased']));
  $path = 'templates/'.CURRENT_TEMPLATE.'/';
  $smarty->assign('tpl_path', $path);

  //BOF - web28 - 2010-08-17 - define missing charset
  $smarty->assign('charset', $_SESSION['language_charset'] );
  //EOF - web28 - 2010-08-17 - define missing charset

  // dont allow cache
  $smarty->caching =0;
  $smarty->display(CURRENT_TEMPLATE.'/module/print_order.html');
} else {
  $smarty->assign('ERROR', 'You are not allowed to view this order!');
  $smarty->display(CURRENT_TEMPLATE.'/module/error_message.html');
}
?>