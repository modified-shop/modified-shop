<?php
/* -----------------------------------------------------------------------------------------
   $Id: account_history_info.php 3970 2012-11-16 12:30:38Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(account_history_info.php,v 1.97 2003/05/19); www.oscommerce.com
   (c) 2003 nextcommerce (account_history_info.php,v 1.17 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce (account_history_info.php 1309 2005-10-17)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');
// create smarty elements
$smarty = new Smarty;
// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');
// include needed functions
require_once (DIR_FS_INC.'xtc_date_short.inc.php');
require_once (DIR_FS_INC.'xtc_get_all_get_params.inc.php');
require_once (DIR_FS_INC.'xtc_image_button.inc.php');
require_once (DIR_FS_INC.'xtc_display_tax_value.inc.php');
require_once (DIR_FS_INC.'xtc_format_price_order.inc.php');

//security checks
// BOC added query string order_id to login.php to be able to redirect to account_history_info after login for link in change_order_mail, noRiddle
//if (!isset ($_SESSION['customer_id'])) { xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL')); }
if (!isset ($_SESSION['customer_id'])) { xtc_redirect(xtc_href_link(FILENAME_LOGIN, 'order_id=' .(int)$_GET['order_id'], 'SSL')); }
// EOC added query string order_id to login.php, noRiddle
if (!isset ($_GET['order_id']) || (isset ($_GET['order_id']) && !is_numeric($_GET['order_id']))) {
   xtc_redirect(xtc_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
}
$customer_info_query = xtc_db_query("select customers_id from ".TABLE_ORDERS." where orders_id = '".(int)$_GET['order_id']."'");
$customer_info = xtc_db_fetch_array($customer_info_query);
if ($customer_info['customers_id'] != $_SESSION['customer_id']) { xtc_redirect(xtc_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL')); }

$breadcrumb->add(NAVBAR_TITLE_1_ACCOUNT_HISTORY_INFO, xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2_ACCOUNT_HISTORY_INFO, xtc_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
$breadcrumb->add(sprintf(NAVBAR_TITLE_3_ACCOUNT_HISTORY_INFO, (int)$_GET['order_id']), xtc_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id='.(int)$_GET['order_id'], 'SSL'));

require (DIR_WS_CLASSES.'order.php');
$order = new order((int)$_GET['order_id']);
require (DIR_WS_INCLUDES.'header.php');

// Delivery Info
if ($order->delivery != false) {
  $smarty->assign('DELIVERY_LABEL', xtc_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br />'));
  if ($order->info['shipping_method']) { $smarty->assign('SHIPPING_METHOD', $order->info['shipping_method']); }
}

$order_total = $order->getTotalData($order->info['order_id']);

$smarty->assign('order_data', $order->getOrderData($order->info['order_id']));
$smarty->assign('order_total', $order_total['data']);

// Payment Method
if ($order->info['payment_method'] != '' && $order->info['payment_method'] != 'no_payment') {
  include (DIR_WS_LANGUAGES.'/'.$_SESSION['language'].'/modules/payment/'.$order->info['payment_method'].'.php');
  $smarty->assign('PAYMENT_METHOD', constant('MODULE_PAYMENT_'.strtoupper($order->info['payment_method']).'_TEXT_TITLE'));
}

//BOF  - web28 - 2010-03-27 PayPal Bezahl-Link
if ($order->info['payment_method'] == 'paypal_ipn' && MODULE_PAYMENT_PAYPAL_IPN_USE_ACCOUNT == 'True') {
  $order_id = $order->info['order_id'];
  $paypal_link = array();
  require (DIR_WS_CLASSES.'payment.php');
  $payment_modules = new payment('paypal_ipn');
  $payment_modules->create_paypal_link();
  $smarty->assign('PAYPAL_LINK', $paypal_link['html']);
}
//EOF  - web28 - 2010-03-27 PayPal Bezahl-Link

// Order History
$history_block = ''; //DokuMan - 2010-09-18 - set undefined variable
$statuses_query = xtc_db_query("-- /account_history_info.php
                                SELECT os.orders_status_name,
                                       osh.date_added,
                                       osh.comments,
                                       osh.comments_sent
                                FROM ".TABLE_ORDERS_STATUS." os,
                                     ".TABLE_ORDERS_STATUS_HISTORY." osh
                                WHERE osh.orders_id = '".$order->info['order_id']."'
                                  AND osh.customer_notified = 1
                                  AND osh.orders_status_id = os.orders_status_id
                                  AND os.language_id = '".(int) $_SESSION['languages_id']."'
                                ORDER BY osh.date_added");
while ($statuses = xtc_db_fetch_array($statuses_query)) {
  $history_block .= xtc_date_short($statuses['date_added']). '&nbsp;<strong>' .$statuses['orders_status_name']. '</strong>&nbsp;' . (empty ($statuses['comments']) || empty($statuses['comments_sent']) ? '&nbsp;' : nl2br(encode_htmlspecialchars($statuses['comments']))) .'<br />';
}
$smarty->assign('HISTORY_BLOCK', $history_block);

// Download-Products
if (DOWNLOAD_ENABLED == 'true') include (DIR_WS_MODULES.'downloads.php');

// Stuff
$smarty->assign('ORDER_NUMBER', $order->info['order_id']); //DokuMan - 2011-08-31 - fix order_id assignment
$smarty->assign('ORDER_DATE', xtc_date_long($order->info['date_purchased']));
$smarty->assign('ORDER_STATUS', $order->info['orders_status']);
$smarty->assign('BILLING_LABEL', xtc_address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br />'));
$smarty->assign('PRODUCTS_EDIT', xtc_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL')); // web28 - 2011-04-14 - change SSL -> NONSSL
$smarty->assign('SHIPPING_ADDRESS_EDIT', xtc_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'));
$smarty->assign('BILLING_ADDRESS_EDIT', xtc_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL'));
$smarty->assign('BUTTON_PRINT', '<a style="cursor:pointer" onclick="javascript:window.open(\''.xtc_href_link(FILENAME_PRINT_ORDER, 'oID='.$order->info['order_id']).'\', \'popup\', \'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, width=640, height=600\')"><img src="'.'templates/'.CURRENT_TEMPLATE.'/buttons/'.$_SESSION['language'].'/button_print.gif" alt="'.TEXT_PRINT.'" /></a>');

$from_history = preg_match("/page=/i", xtc_get_all_get_params()); // referer from account_history yes/no
$back_to = $from_history ? FILENAME_ACCOUNT_HISTORY : FILENAME_ACCOUNT; // if from account_history => return to account_history
$smarty->assign('BUTTON_BACK','<a href="' . xtc_href_link($back_to,xtc_get_all_get_params(array ('order_id')), 'SSL') . '">' . xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>');
$smarty->assign('language', $_SESSION['language']);
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/account_history_info.html');
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
  $smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>