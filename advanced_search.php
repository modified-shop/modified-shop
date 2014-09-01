<?php

/* -----------------------------------------------------------------------------------------
   $Id: advanced_search.php 988 2005-06-18 16:42:42Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(advanced_search.php,v 1.49 2003/02/13); www.oscommerce.com 
   (c) 2003	 nextcommerce (advanced_search.php,v 1.13 2003/08/21); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');
// create smarty elements
$smarty = new Smarty;
// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');
// include needed functions
require_once (DIR_FS_INC.'xtc_get_categories.inc.php');
require_once (DIR_FS_INC.'xtc_get_manufacturers.inc.php');
require_once (DIR_FS_INC.'xtc_checkdate.inc.php');

$breadcrumb->add(NAVBAR_TITLE_ADVANCED_SEARCH, xtc_href_link(FILENAME_ADVANCED_SEARCH));

require (DIR_WS_INCLUDES.'header.php');

$smarty->assign('FORM_ACTION', xtc_draw_form('advanced_search', xtc_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', $request_type, false), 'get', 'onsubmit="return check_form(this);"').xtc_hide_session_id()); //WEB28 change NONSSL to $request_type

$smarty->assign('INPUT_KEYWORDS', xtc_draw_input_field('keywords', '', 'style="width: 100%"'));
//BOF - DokuMan - 2009-05-31 - removed quotationmark 
//$smarty->assign('HELP_LINK', xtc_href_link(FILENAME_POPUP_SEARCH_HELP.'?KeepThis=true&TB_iframe=true&height=400&width=600"'));
$smarty->assign('HELP_LINK', xtc_href_link(FILENAME_POPUP_SEARCH_HELP.'?KeepThis=true&TB_iframe=true&height=400&width=600'));
//EOF - DokuMan - 2009-05-31 - removed quotationmark
$smarty->assign('BUTTON_SUBMIT', xtc_image_submit('button_search.gif', IMAGE_BUTTON_SEARCH));


$smarty->assign('SELECT_CATEGORIES',xtc_draw_pull_down_menu('categories_id', xtc_get_categories(array (array ('id' => '', 'text' => TEXT_ALL_CATEGORIES)))));
$smarty->assign('ENTRY_SUBCAT',xtc_draw_checkbox_field('inc_subcat', '1', true));
$smarty->assign('SELECT_MANUFACTURERS',xtc_draw_pull_down_menu('manufacturers_id', xtc_get_manufacturers(array (array ('id' => '', 'text' => TEXT_ALL_MANUFACTURERS)))));
$smarty->assign('SELECT_PFROM',xtc_draw_input_field('pfrom'));
$smarty->assign('SELECT_PTO',xtc_draw_input_field('pto'));


$error = '';
if (isset ($_GET['errorno'])) {
	if (($_GET['errorno'] & 1) == 1) {
		$error .= str_replace('\n', '<br />', JS_AT_LEAST_ONE_INPUT);
	}
	if (($_GET['errorno'] & 10) == 10) {
		$error .= str_replace('\n', '<br />', JS_INVALID_FROM_DATE);
	}
	if (($_GET['errorno'] & 100) == 100) {
		$error .= str_replace('\n', '<br />', JS_INVALID_TO_DATE);
	}
	if (($_GET['errorno'] & 1000) == 1000) {
		$error .= str_replace('\n', '<br />', JS_TO_DATE_LESS_THAN_FROM_DATE);
	}
	if (($_GET['errorno'] & 10000) == 10000) {
		$error .= str_replace('\n', '<br />', JS_PRICE_FROM_MUST_BE_NUM);
	}
	if (($_GET['errorno'] & 100000) == 100000) {
		$error .= str_replace('\n', '<br />', JS_PRICE_TO_MUST_BE_NUM);
	}
	if (($_GET['errorno'] & 1000000) == 1000000) {
		$error .= str_replace('\n', '<br />', JS_PRICE_TO_LESS_THAN_PRICE_FROM);
	}
	if (($_GET['errorno'] & 10000000) == 10000000) {
		$error .= str_replace('\n', '<br />', JS_INVALID_KEYWORDS);
	}
}

$smarty->assign('error', $error);
$smarty->assign('language', $_SESSION['language']);
$smarty->assign('FORM_END', '</form>');

$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/advanced_search.html');

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>