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
require_once (DIR_FS_INC.'xtc_image_button.inc.php');
require_once (DIR_FS_INC.'xtc_display_tax_value.inc.php');
require_once (DIR_FS_INC.'xtc_format_price_order.inc.php');
require_once (DIR_FS_INC.'get_tracking_link.inc.php');

//security checks
if (!isset ($_SESSION['customer_id'])) { 
  xtc_redirect(xtc_href_link(FILENAME_LOGIN, 'order_id=' .(int)$_GET['order_id'], 'SSL')); 
}

if (isset($_SESSION['customer_id']) 
    && $_SESSION['customers_status']['customers_status_id'] == DEFAULT_CUSTOMERS_STATUS_ID_GUEST
    && GUEST_ACCOUNT_EDIT != 'true'
    )
{ 
  xtc_redirect(xtc_href_link(FILENAME_DEFAULT, '', 'SSL'));
}

if (!isset ($_GET['order_id']) || (isset ($_GET['order_id']) && !is_numeric($_GET['order_id']))) {
   xtc_redirect(xtc_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
}

$customer_info_query = xtc_db_query("SELECT customers_id 
                                       FROM ".TABLE_ORDERS." 
                                      WHERE orders_id = '".(int)$_GET['order_id']."'");
$customer_info = xtc_db_fetch_array($customer_info_query);
if ($customer_info['customers_id'] != $_SESSION['customer_id']) { 
  xtc_redirect(xtc_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL')); 
}

require (DIR_WS_CLASSES.'order.php');
$order = new order((int)$_GET['order_id']);

$breadcrumb->add(NAVBAR_TITLE_1_ACCOUNT_HISTORY_INFO, xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2_ACCOUNT_HISTORY_INFO, xtc_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
$breadcrumb->add(sprintf(NAVBAR_TITLE_3_ACCOUNT_HISTORY_INFO, (int)$_GET['order_id']), xtc_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id='.(int)$_GET['order_id'], 'SSL'));

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

## PayPal
if ($order->info['payment_method'] == 'paypallink'
    || $order->info['payment_method'] == 'paypalpluslink'
    ) 
{
  require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');
  $paypal = new PayPalPayment($order->info['payment_method']);
  
  if ($paypal->get_config('MODULE_PAYMENT_'.strtoupper($order->info['payment_method']).'_USE_ACCOUNT') == 1) {
    $button = $paypal->create_paypal_link($order->info['order_id']);
    if ($button != '') {
      $smarty->assign('PAYPAL_LINK', sprintf(constant('MODULE_PAYMENT_'.strtoupper($order->info['payment_method']).'_TEXT_SUCCESS'), $button));
    }
    
    if ($messageStack->size($order->info['payment_method']) > 0) {
      $smarty->assign('info_message', $messageStack->output($order->info['payment_method']));
    }    
  }
}

// Order History
$history_block = '';
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
if (DOWNLOAD_ENABLED == 'true') {
  include (DIR_WS_MODULES.'downloads.php');
}

$smarty->assign('BUTTON_CART', '<a href="'.xtc_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'action=add_order&order_id='.$order->info['order_id'], 'SSL').'">'.xtc_image_button('button_in_cart.gif', IMAGE_BUTTON_IN_CART).'</a>');
$smarty->assign('ORDER_TRACKING', get_tracking_link($order->info['order_id'], $_SESSION['language_code']));
$smarty->assign('ORDER_NUMBER', $order->info['order_id']);
$smarty->assign('ORDER_DATE', xtc_date_long($order->info['date_purchased']));
$smarty->assign('ORDER_STATUS', $order->info['orders_status']);
$smarty->assign('BILLING_LABEL', xtc_address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br />'));
$smarty->assign('PRODUCTS_EDIT', xtc_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL'));
$smarty->assign('SHIPPING_ADDRESS_EDIT', xtc_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'));
$smarty->assign('BILLING_ADDRESS_EDIT', xtc_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL'));
$smarty->assign('BUTTON_PRINT', xtc_image_button('button_print.gif', TEXT_PRINT, 'style="cursor:pointer" onclick="javascript:window.open(\''.xtc_href_link(FILENAME_PRINT_ORDER, 'oID='.(int)$_GET['order_id'], 'SSL').'\', \'popup\', \'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,  '.(defined('TPL_POPUP_PRODUCT_PRINT_SIZE') ? TPL_POPUP_PRODUCT_PRINT_SIZE : POPUP_PRINT_ORDER_SIZE).'\')"'));
$smarty->assign('BUTTON_PRINT_LAYER', '<a class="iframe" target="_blank" rel="nofollow" href="'.xtc_href_link(FILENAME_PRINT_ORDER, 'oID='.(int)$_GET['order_id'], 'SSL'). '" title="'.TEXT_PRINT.'" />'. xtc_image_button('button_print.gif', TEXT_PRINT) .'</a>');

if (defined('MODULE_CHECKOUT_EXPRESS_STATUS') && MODULE_CHECKOUT_EXPRESS_STATUS == 'true') {
  $smarty->assign('BUTTON_CART_EXPRESS', '<a href="'.xtc_href_link(FILENAME_ACCOUNT, 'action=add_order&express=on&order_id='.$order->info['order_id'], 'SSL').'">'.xtc_image_button('button_checkout_express.gif', IMAGE_BUTTON_IN_CART).'</a>');
}

$from_history = preg_match("/page=/i", xtc_get_all_get_params());
$back_to = $from_history ? FILENAME_ACCOUNT_HISTORY : FILENAME_ACCOUNT;
$smarty->assign('BUTTON_BACK','<a href="' . xtc_href_link($back_to,xtc_get_all_get_params(array ('order_id')), 'SSL') . '">' . xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>');
$smarty->assign('language', $_SESSION['language']);

$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/account_history_info.html');

$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
  $smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>