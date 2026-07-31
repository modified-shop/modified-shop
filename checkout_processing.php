<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('RUN_MODE_NOSESSION', true);

include ('includes/application_top.php');
require_once (DIR_WS_CLASSES.'checkout.php');

$smarty = new Smarty();

$breadcrumb->add(NAVBAR_TITLE_1_CHECKOUT_PROCESSING);
$breadcrumb->add(NAVBAR_TITLE_2_CHECKOUT_PROCESSING);

require (DIR_WS_INCLUDES . 'header.php');

$display_mode = 'checkout';
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('CHECKOUT_PROCESSING_STATUS_URL', xtc_href_link('ajax.php', 'speed=1&ext=get_checkout_processing_status', 'SSL'));
$smarty->assign('CHECKOUT_PROCESSING_SUCCESS_URL', xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL', false));
$smarty->assign('CHECKOUT_PROCESSING_ERROR_URL', xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'processing_error=1', 'SSL'));
$smarty->assign('CHECKOUT_JAVASCRIPT', checkout::javascript_processing());

$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/checkout_processing.html');
$smarty->assign('main_content', $main_content);

$smarty->caching = 0;
if (!defined('RM')) {
  $smarty->load_filter('output', 'note');
}
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
