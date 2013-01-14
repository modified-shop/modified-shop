<?php

/* -----------------------------------------------------------------------------------------
   $Id: gv_redeem.php 4221 2013-01-11 10:18:52Z gtb-modified $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
   (c) 2002-2003 osCommerce (gv_redeem.php,v 1.3.2.1 2003/04/18); www.oscommerce.com

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

require ('includes/application_top.php');

if (ACTIVATE_GIFT_SYSTEM != 'true')
	xtc_redirect(FILENAME_DEFAULT);

// is customer logged on ?
	if (!isset ($_SESSION['customer_id'])) xtc_redirect(FILENAME_SHOPPING_CART);

$smarty = new Smarty;

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

require (DIR_WS_INCLUDES.'header.php');

// check for a voucher number in the url
if (isset ($_GET['gv_no'])) {
	
	
	
	$error = true;
	$gv_query = xtc_db_query("select c.coupon_id, c.coupon_amount from ".TABLE_COUPONS." c, ".TABLE_COUPON_EMAIL_TRACK." et where coupon_code = '".xtc_db_input($_GET['gv_no'])."' and c.coupon_id = et.coupon_id");
	if (xtc_db_num_rows($gv_query) > 0) {
		$coupon = xtc_db_fetch_array($gv_query);
		$redeem_query = xtc_db_query("select coupon_id from ".TABLE_COUPON_REDEEM_TRACK." where coupon_id = '".$coupon['coupon_id']."'");
		if (xtc_db_num_rows($redeem_query) == 0) {
			// check for required session variables
			$_SESSION['gv_id'] = $coupon['coupon_id'];
			$error = false;
		} else {
			$error = true;
		}
	}
} else {
	xtc_redirect(FILENAME_DEFAULT);
}
if ((!$error) && (isset ($_SESSION['customer_id']))) {
	// Update redeem status
	$gv_query = xtc_db_query("insert into  ".TABLE_COUPON_REDEEM_TRACK." (coupon_id, customer_id, redeem_date, redeem_ip) values ('".$coupon['coupon_id']."', '".$_SESSION['customer_id']."', now(),'".$REMOTE_ADDR."')");
	$gv_update = xtc_db_query("update ".TABLE_COUPONS." set coupon_active = 'N' where coupon_id = '".$coupon['coupon_id']."'");
	xtc_gv_account_update($_SESSION['customer_id'], $_SESSION['gv_id']);
	unset ($_SESSION['gv_id']);
}

$breadcrumb->add(NAVBAR_GV_REDEEM);

// if we get here then either the url gv_no was not set or it was invalid
// so output a message.
$smarty->assign('coupon_amount', $xtPrice->xtcFormat($coupon['coupon_amount'], true));
$smarty->assign('error', $error);
$smarty->assign('LINK_DEFAULT', '<a href="'.xtc_href_link(FILENAME_DEFAULT).'">'.xtc_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE).'</a>');
$smarty->assign('language', $_SESSION['language']);
$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/gv_redeem.html');

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>