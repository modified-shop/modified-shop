<?php
/* -----------------------------------------------------------------------------------------
   $Id: checkout_success.php 896 2005-04-27 19:22:59Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(checkout_success.php,v 1.48 2003/02/17); www.oscommerce.com 
   (c) 2003	 nextcommerce (checkout_success.php,v 1.14 2003/08/17); www.nextcommerce.org

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

// BOF - GTB - 2011-04-12 - changes for Guest Account
// if the customer is not logged on, redirect them to the shopping cart page
/*
if (!isset ($_SESSION['customer_id'])) {
  xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
}
*/
if (isset ($_GET['action']) && ($_GET['action'] == 'update')) {

	if ($_POST['account_type'] != 1) {
  //if ($_SESSION['account_type'] != 1) {
    //BOF - web28.de - FIX redirect to NONSSL
    //xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
    xtc_redirect(xtc_href_link(FILENAME_DEFAULT),'NONSSL');
    //EOF - web28.de - FIX redirect to NONSSL
  } else {
    //xtc_redirect(xtc_href_link(FILENAME_LOGOFF));
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

//BOF - GTB - 2012-10-10 - include Vorkasse by Sofort
if (isset($_GET['vorkasse']) && $_GET['vorkasse']=='sofort') {
  include (DIR_WS_MODULES.'sofort_vorkasse.php');
}
//EOF - GTB - 2012-10-10 - include Vorkasse by Sofort

//BOF  - web28 - 2010-03-27 PayPal Bezahl-Link
if (isset($_SESSION['paypal_link']) && MODULE_PAYMENT_PAYPAL_IPN_USE_CHECKOUT == 'True') {
	$smarty->assign('PAYPAL_LINK',$_SESSION['paypal_link']);
    unset ($_SESSION['paypal_link']);    	
}    
//EOF  - web28 - 2010-03-27 PayPal Bezahl-Link

// BOF - GTB - 2011-04-12 - changes for Guest Account
// $smarty->assign('FORM_ACTION', xtc_draw_form('order', xtc_href_link(FILENAME_CHECKOUT_SUCCESS, 'action=update', 'SSL')));
$smarty->assign('FORM_ACTION', xtc_draw_form('order', xtc_href_link(FILENAME_CHECKOUT_SUCCESS, 'action=update', 'SSL')).xtc_draw_hidden_field('account_type', $_SESSION['account_type']));
// EOF - GTB - 2011-04-12 - changes for Guest Account
//BOF - Dokuman - 2009-11-23 - Make the print preview clickable, i.e. change mouse cursor to hand symbol
$smarty->assign('BUTTON_CONTINUE', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
//$smarty->assign('BUTTON_PRINT', '<img src="'.'templates/'.CURRENT_TEMPLATE.'/buttons/'.$_SESSION['language'].'/button_print.gif" style="cursor:pointer" onclick="window.open(\''.xtc_href_link(FILENAME_PRINT_ORDER, 'oID='.$orders['orders_id']).'\', \'popup\', \'toolbar=0, width=640, height=600\')" />');
//BOF - Tomcraft - 2010-04-03 - unified popups with scrollbars and make them resizable
//$smarty->assign('BUTTON_PRINT', '<a style="cursor:pointer" onclick="javascript:window.open(\''.xtc_href_link(FILENAME_PRINT_ORDER, 'oID='.$orders['orders_id']).'\', \'popup\', \'toolbar=0, width=640, height=600\')"><img src="'.'templates/'.CURRENT_TEMPLATE.'/buttons/'.$_SESSION['language'].'/button_print.gif" alt="'.TEXT_PRINT.'" /></a>');
//BOF - GTB - 2010-09-15 - change Print Button to Form for Guests
//$smarty->assign('BUTTON_PRINT', '<a style="cursor:pointer" onclick="javascript:window.open(\''.xtc_href_link(FILENAME_PRINT_ORDER, 'oID='.$orders['orders_id']).'\', \'popup\', \'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, width=640, height=600\')"><img src="'.'templates/'.CURRENT_TEMPLATE.'/buttons/'.$_SESSION['language'].'/button_print.gif" alt="'.TEXT_PRINT.'" /></a>');
$smarty->assign('FORM_ACTION_PRINT', xtc_draw_form('print_order', xtc_href_link(FILENAME_PRINT_ORDER, 'oID='.$orders['orders_id'], 'SSL'), 'post', 'target="popup" onsubmit="javascript:window.open(\''.xtc_href_link(FILENAME_PRINT_ORDER, 'oID='.$orders['orders_id'], 'SSL').'\', \'popup\', \'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, '.POPUP_PRINT_ORDER_SIZE.'\')"').xtc_draw_hidden_field('customer_id', $_SESSION['customer_id']));
$smarty->assign('BUTTON_PRINT', xtc_image_submit('print.gif', TEXT_PRINT));
//EOF - GTB - 2010-09-15 - change Print Button to Form for Guests
//EOF - Tomcraft - 2010-04-03 - unified popups with scrollbars and make them resizable
//EOF - Dokuman - 2009-11-23 - Make the print preview clickable, i.e. change mouse cursor to hand symbol

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

//BOF - Dokuman - 2012-06-19 - BILLSAFE payment module
echo '<script type="text/javascript"> if (top.lpg) top.lpg.close("'.xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL').'"); </script>';
//EOF - Dokuman - 2012-06-19 - BILLSAFE payment module

$smarty->assign('language', $_SESSION['language']);
//BOF - DokuMan - 2010-08-31 - PAYMENT_BLOCK not needed in checkout success
//$smarty->assign('PAYMENT_BLOCK', $payment_block);
//EOF - DokuMan - 2010-08-31 - PAYMENT_BLOCK not needed in checkout success
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/checkout_success.html');
// BOF - Tomcraft - 2009-11-28 - Included xs:booster
//$smarty->assign('main_content', $main_content);
$smarty->assign('main_content', $main_content.(isset($_SESSION['xtb2'])?"<div style=\"text-align:center;padding:3px;margin-top:10px;font-weight:bold;\"><a style=\"text-decoration:underline;color:blue;\" href=\"./callback/xtbooster/xtbcallback.php?reverse=true\">Zur&uuml;ck zur xs:booster Auktions&uuml;bersicht..</a></div>":""));
// EOF - Tomcraft - 2009-11-28 - Included xs:booster
$smarty->caching = 0;
if (!defined('RM'))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>
