<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(checkout_success.php,v 1.48 2003/02/17); www.oscommerce.com
   (c) 2003 nextcommerce
 (checkout_success.php,v 1.14 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce (checkout_success.php 896 2005-04-27)

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
// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

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
// EOF - GTB - 2011-04-12 - changes for Guest Account

$breadcrumb->add(NAVBAR_TITLE_1_CHECKOUT_SUCCESS);
$breadcrumb->add(NAVBAR_TITLE_2_CHECKOUT_SUCCESS);

require (DIR_WS_INCLUDES.'header.php');

$orders_query = xtc_db_query("select orders_id,
                                     orders_status
                              from ".TABLE_ORDERS."
                              where customers_id = '".$_SESSION['customer_id']."'
                              order by orders_id desc limit 1");
$orders = xtc_db_fetch_array($orders_query);
$last_order = $orders['orders_id'];
$order_status = $orders['orders_status'];

//PayPal Bezahl-Link
if (isset($_SESSION['paypal_link']) && MODULE_PAYMENT_PAYPAL_IPN_USE_CHECKOUT == 'True') {
  $smarty->assign('PAYPAL_LINK',$_SESSION['paypal_link']);
  unset ($_SESSION['paypal_link']);
}

//Form and Buttons
$smarty->assign('FORM_ACTION', xtc_draw_form('order', xtc_href_link(FILENAME_CHECKOUT_SUCCESS, 'action=update', 'SSL')).xtc_draw_hidden_field('account_type', $_SESSION['account_type'])); // GTB - 2011-04-12 - changes for Guest Account
$smarty->assign('BUTTON_CONTINUE', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
$smarty->assign('FORM_ACTION_PRINT', xtc_draw_form('print_order', xtc_href_link(FILENAME_PRINT_ORDER, 'oID='.$orders['orders_id'], 'SSL'), 'post', 'target="popup" onsubmit="javascript:window.open(\''.xtc_href_link(FILENAME_PRINT_ORDER, 'oID='.$orders['orders_id'], 'SSL').'\', \'popup\', \'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, '.POPUP_PRINT_ORDER_SIZE.'\')"').xtc_draw_hidden_field('customer_id', $_SESSION['customer_id']));
$smarty->assign('BUTTON_PRINT', xtc_image_submit('print.gif', TEXT_PRINT));
$smarty->assign('FORM_END', '</form>');

// GV Code Start
$gv_query = xtc_db_query("select amount from ".TABLE_COUPON_GV_CUSTOMER." where customer_id='".$_SESSION['customer_id']."'");
if ($gv_result = xtc_db_fetch_array($gv_query)) {
  if ($gv_result['amount'] > 0) {
    $smarty->assign('GV_SEND_LINK', xtc_href_link(FILENAME_GV_SEND));
  }
}
// GV Code End

// Google Conversion tracking
if (GOOGLE_CONVERSION == 'true') {
  $smarty->assign('google_tracking', 'true');
  $smarty->assign('tracking_code', '
    <noscript>
    <a href="http://services.google.com/sitestats/'.GOOGLE_LANG.'.html" onclick="window.open(this.href); return false;">
    <img height=27 width=135 border=0 src="http://www.googleadservices.com/pagead/conversion/'.GOOGLE_CONVERSION_ID.'/?hl='.GOOGLE_LANG.'" />
    </a>
    </noscript>
        ');
}

if (DOWNLOAD_ENABLED == 'true') {
  include (DIR_WS_MODULES.'downloads.php');
}

//BOF - DokuMan - 2010-05-20 - Move guest deletion from logoff to checkout_success
//delete Guests from Database
if (($_SESSION['account_type'] == 1) && (DELETE_GUEST_ACCOUNT == 'true')) {
  xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS." WHERE customers_id = '".$_SESSION['customer_id']."'");
  xtc_db_query("DELETE FROM ".TABLE_ADDRESS_BOOK." WHERE customers_id = '".$_SESSION['customer_id']."'");
  xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_INFO." WHERE customers_info_id = '".$_SESSION['customer_id']."'");
  xtc_session_destroy();
  unset ($_SESSION['customer_id']);
  unset ($_SESSION['customer_default_address_id']);
  unset ($_SESSION['customer_first_name']);
  unset ($_SESSION['customer_country_id']);
  unset ($_SESSION['customer_zone_id']);
  unset ($_SESSION['comments']);
  unset ($_SESSION['user_info']);
  unset ($_SESSION['customers_status']);
  unset ($_SESSION['selected_box']);
  unset ($_SESSION['navigation']);
  unset ($_SESSION['shipping']);
  unset ($_SESSION['payment']);
  unset ($_SESSION['ccard']);
  unset ($_SESSION['gv_id']);
  unset ($_SESSION['cc_id']);
  require (DIR_WS_INCLUDES.'write_customers_status.php');
}
//EOF - DokuMan - 2010-05-20 - Move guest deletion from logoff to checkout_success

$smarty->assign('language', $_SESSION['language']);
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/checkout_success.html');
//Included xs:booster
$main_content .= isset($_SESSION['xtb2']) ? "<div style=\"text-align:center;padding:3px;margin-top:10px;font-weight:bold;\"><a style=\"text-decoration:underline;color:blue;\" href=\"./callback/xtbooster/xtbcallback.php?reverse=true\">Zur&uuml;ck zur xs:booster Auktions&uuml;bersicht..</a></div>":""; //DokuMan - Moved xtbcallback.php to callback directory
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
  $smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>