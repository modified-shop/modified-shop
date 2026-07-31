<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');
require_once (DIR_WS_CLASSES.'checkout.php');

if (!isset($_SESSION['customer_id'], $_SESSION['checkout_processing_key'])
    || !preg_match('/^[a-f0-9]{64}$/', $_SESSION['checkout_processing_key'])
    )
{
  xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
}

$checkout = new checkout($_SESSION['customer_id']);
$checkout->expire();
$processing = $checkout->find();
if (!is_array($processing)) {
  xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
}

if ($processing['processing_status'] === 'completed' && (int)$processing['orders_id'] > 0) {
  $_SESSION['checkout_completed_order_id'] = (int)$processing['orders_id'];
  xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
}

$smarty = new Smarty();

$breadcrumb->add(NAVBAR_TITLE_1_CHECKOUT_PROCESSING);
$breadcrumb->add(NAVBAR_TITLE_2_CHECKOUT_PROCESSING);

require (DIR_WS_INCLUDES . 'header.php');

$display_mode = 'checkout';
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('CHECKOUT_PROCESSING_STATUS_URL', xtc_href_link('ajax.php', 'ext=get_checkout_processing_status', 'SSL'));
$smarty->assign('CHECKOUT_PROCESSING_ERROR_URL', xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'processing_error=1', 'SSL'));
$smarty->assign('CHECKOUT_JAVASCRIPT', $checkout->javascript_processing());

$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/checkout_processing.html');
$smarty->assign('main_content', $main_content);

$smarty->caching = 0;
if (!defined('RM')) {
  $smarty->load_filter('output', 'note');
}
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
