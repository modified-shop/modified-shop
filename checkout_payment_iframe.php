<?php
/* -----------------------------------------------------------------------------------------
   $Id: checkout_payment_iframe.php 4221 2013-01-11 10:18:52Z gtb-modified $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(checkout_payment_iframe.php,v 1.128 2003/05/28); www.oscommerce.com
   (c) 2003	nextcommerce (checkout_payment_iframe.php,v 1.30 2003/08/24); www.nextcommerce.org
   (c) 2006 XT-Commerce (checkout_payment_iframe.php 1325 2005-10-30)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_calculate_tax.inc.php');
require_once (DIR_FS_INC.'xtc_address_label.inc.php');

$smarty = new Smarty;

// include boxes
require (DIR_FS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/source/boxes.php');

require (DIR_WS_INCLUDES.'checkout_requirements.php');

// load selected payment module
require (DIR_WS_CLASSES.'payment.php');
$payment_modules = new payment($_SESSION['payment']);

// load the selected shipping module
require (DIR_WS_CLASSES.'shipping.php');
$shipping_modules = new shipping($_SESSION['shipping']);

require (DIR_WS_CLASSES . 'order.php');
$order = new order();

require (DIR_WS_CLASSES . 'order_total.php');
$order_total_modules = new order_total();
$order_total_modules->process();

$iframe_url = $payment_modules->iframeAction();
if ($iframe_url =='') {
	xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
}

$breadcrumb->add(NAVBAR_TITLE_2_CHECKOUT_PAYMENT, xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));

require (DIR_WS_INCLUDES . 'header.php');

$smarty->assign('iframe_url', $iframe_url);
$main_content = '<iframe src="'.$iframe_url.'" width="100%" height="750" name="_top" frameborder="0"></iframe>';

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE . '/index.html');
include ('includes/application_bottom.php');
?>