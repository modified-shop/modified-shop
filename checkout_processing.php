<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Preserve URL sessions without starting the configured session handler.
define('SESSION_FORCE_COOKIE_USE', 'False');
define('RUN_MODE_NOSESSION', true);

include ('includes/application_top.php');
require_once (DIR_WS_CLASSES.'checkout.php');

$smarty = new Smarty();

$breadcrumb->add(NAVBAR_TITLE_1_CHECKOUT_PROCESSING);
$breadcrumb->add(NAVBAR_TITLE_2_CHECKOUT_PROCESSING);

require (DIR_WS_INCLUDES . 'header.php');

$display_mode = 'checkout';
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

$session_parameters = checkout::get_url_session_parameters();

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('CHECKOUT_PROCESSING_STATUS_URL', xtc_href_link('ajax.php', 'speed=1&ext=get_checkout_processing_status', 'SSL'));
$smarty->assign('CHECKOUT_PROCESSING_RESUME_URL', xtc_href_link(FILENAME_CHECKOUT_PROCESS, $session_parameters, 'SSL', false));
$smarty->assign('CHECKOUT_PROCESSING_SUCCESS_URL', xtc_href_link(FILENAME_CHECKOUT_SUCCESS, $session_parameters, 'SSL', false));
$smarty->assign('CHECKOUT_PROCESSING_ERROR_URL', xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $session_parameters, 'SSL', false));
$smarty->assign('CHECKOUT_JAVASCRIPT', checkout::javascript_processing());

$checkout_template = CURRENT_TEMPLATE.'/module/checkout_processing.html';
if (!is_file(DIR_FS_CATALOG.'templates/'.$checkout_template)) {
  $checkout_template = 'tpl_modified/module/checkout_processing.html';
}

$main_content = $smarty->fetch($checkout_template);
if (!is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/css/checkout_processing.css')) {
  $main_content = '<link rel="stylesheet" property="stylesheet" href="'
                  .DIR_WS_BASE.'templates/tpl_modified/css/checkout_processing.css" type="text/css" media="screen" />'
                  .$main_content;
}
$smarty->assign('main_content', $main_content);

$smarty->caching = 0;
if (!defined('RM')) {
  $smarty->load_filter('output', 'note');
}
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
