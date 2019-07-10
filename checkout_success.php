<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(checkout_success.php,v 1.48 2003/02/17); www.oscommerce.com
   (c) 2003	nextcommerce (checkout_success.php,v 1.14 2003/08/17); www.nextcommerce.org
   (c) 2003 XT-Commerce

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c  Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

// create smarty elements
$smarty = new Smarty;

if (isset ($_GET['action']) && ($_GET['action'] == 'update')) {
	if ($_POST['account_type'] != 1) {
    xtc_redirect(xtc_href_link(FILENAME_DEFAULT),'NONSSL');
  } else {
    xtc_redirect(xtc_href_link(FILENAME_LOGOFF), 'NONSSL');
  }
}

// if the customer is not logged on, redirect them to the shopping cart page
if (!isset ($_SESSION['customer_id'])) {
  xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART), 'NONSSL');
}

$orders_query = xtc_db_query("SELECT orders_id,
                                     orders_status,
                                     payment_class
                                FROM ".TABLE_ORDERS."
                               WHERE customers_id = '".(int)$_SESSION['customer_id']."'
                                 AND unix_timestamp(date_purchased) > (unix_timestamp(now()) - '".(int)SESSION_LIFE_CUSTOMERS."')
                            ORDER BY orders_id DESC
                               LIMIT 1");

// if no order exists for customer redirect them to the shopping cart page
if (xtc_db_num_rows($orders_query) < 1) {
  xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART), 'NONSSL');
} else {
  $orders = xtc_db_fetch_array($orders_query);
  $last_order = $orders['orders_id'];
  $order_status = $orders['orders_status'];
  $payment_class = $orders['payment_class'];
}

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

$_SESSION['customer_gid'] = $_SESSION['customer_id'];

// load the selected payment module
require_once (DIR_WS_CLASSES . 'payment.php');
$payment_modules = new payment($payment_class);
$smarty->assign('PAYMENT_INFO', $payment_modules->success());

$smarty->assign('FORM_ACTION', xtc_draw_form('order', xtc_href_link(FILENAME_CHECKOUT_SUCCESS, 'action=update', 'SSL')).xtc_draw_hidden_field('account_type', $_SESSION['account_type']));
$smarty->assign('BUTTON_CONTINUE', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
$smarty->assign('FORM_END', '</form>');

$smarty->assign('BUTTON_PRINT', xtc_image_button('print.gif', TEXT_PRINT, 'style="cursor:pointer" onclick="javascript:window.open(\''.xtc_href_link(FILENAME_PRINT_ORDER, 'oID='.(int)$last_order, 'SSL').'\', \'popup\', \'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,  '.(defined('TPL_POPUP_PRODUCT_PRINT_SIZE') ? TPL_POPUP_PRODUCT_PRINT_SIZE : POPUP_PRINT_ORDER_SIZE).'\')"'));
$smarty->assign('BUTTON_PRINT_LAYER', '<a class="iframe" target="_blank" rel="nofollow" href="'.xtc_href_link(FILENAME_PRINT_ORDER, 'oID='.(int)$last_order, 'SSL'). '" title="'.TEXT_PRINT.'" />'. xtc_image_button('print.gif', TEXT_PRINT) .'</a>');

// GV Code
if (ACTIVATE_GIFT_SYSTEM == 'true') {
  $gv_query = xtc_db_query("SELECT amount 
                              FROM ".TABLE_COUPON_GV_CUSTOMER." 
                             WHERE customer_id='".(int)$_SESSION['customer_id']."'");
  if ($gv_result = xtc_db_fetch_array($gv_query)) {
    if ($gv_result['amount'] > 0) {
      $smarty->assign('GV_SEND_LINK', xtc_href_link(FILENAME_GV_SEND));
    }
  }
}

// Downloads
if (DOWNLOAD_ENABLED == 'true') {
	include (DIR_WS_MODULES.'downloads.php');
}

if (isset($_SESSION['NO_SHIPPING']) && $_SESSION['NO_SHIPPING'] === true) {
  $smarty->assign('NO_SHIPPING', $_SESSION['NO_SHIPPING']);
}

//delete Guests from Database
if ($_SESSION['account_type'] == '1') {
  if (DELETE_GUEST_ACCOUNT == 'true') {
    xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS." WHERE customers_id = '".(int)$_SESSION['customer_id']."'");
    xtc_db_query("DELETE FROM ".TABLE_ADDRESS_BOOK." WHERE customers_id = '".(int)$_SESSION['customer_id']."'");
    xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_INFO." WHERE customers_info_id = '".(int)$_SESSION['customer_id']."'");
    xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_IP." WHERE customers_id = '".(int)$_SESSION['customer_id']."'");
  } 
  $_SESSION['customer_gid'] = $_SESSION['customer_id'];
  
  unset ($_SESSION['customer_id']);
  unset ($_SESSION['customer_default_address_id']);
  unset ($_SESSION['customer_first_name']);
  unset ($_SESSION['customer_country_id']);
  unset ($_SESSION['customer_zone_id']);
  unset ($_SESSION['comments']);
  unset ($_SESSION['user_info']);
  unset ($_SESSION['customers_status']);
  unset ($_SESSION['selected_box']);
  unset ($_SESSION['shipping']);
  unset ($_SESSION['payment']);
  unset ($_SESSION['ccard']);
  unset ($_SESSION['gv_id']);
  unset ($_SESSION['cc_id']);
  
  require (DIR_WS_INCLUDES.'write_customers_status.php');
}

$breadcrumb->add(NAVBAR_TITLE_1_CHECKOUT_SUCCESS);
$breadcrumb->add(NAVBAR_TITLE_2_CHECKOUT_SUCCESS);

require (DIR_WS_INCLUDES.'header.php');

$smarty->assign('language', $_SESSION['language']);
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/checkout_success.html');
$smarty->assign('main_content', $main_content);

$smarty->caching = 0;
if (!defined('RM')) {
	$smarty->load_filter('output', 'note');
}
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>