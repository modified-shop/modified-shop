<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(advanced_search_result.php,v 1.68 2003/05/14); www.oscommerce.com
   (c) 2003 nextcommerce (advanced_search_result.php,v 1.17 2003/08/21); www.nextcommerce.org
   (c) 2006 XT-Commerce (advanced_search_result.php 1141 2005-08-10)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

// findologic
if (defined('MODULE_FINDOLOGIC_STATUS') && MODULE_FINDOLOGIC_STATUS == 'True' && !isset($_GET['f'])) {
  xtc_redirect(xtc_href_link(FILENAME_FINDOLOGIC, xtc_get_all_get_params()));
}
unset($_GET['f']);

// create smarty elements
$smarty = new Smarty;

// include needed functions
require_once (DIR_FS_INC.'xtc_parse_search_string.inc.php');

// security fix
$keywords = $_GET['keywords'] = isset($_GET['keywords']) && !empty($_GET['keywords']) ? stripslashes(trim(urldecode($_GET['keywords']))) : false;
$pfrom = $_GET['pfrom'] = isset($_GET['pfrom']) && !empty($_GET['pfrom']) ? str_replace(',', '.', stripslashes(trim(urldecode($_GET['pfrom'])))) : false;
$pto = $_GET['pto'] = isset($_GET['pto']) && !empty($_GET['pto']) ? str_replace(',', '.', stripslashes(trim(urldecode($_GET['pto'])))) : false;

// reset error
$errorno = 0;

// create $search_keywords array
$keywordcheck = xtc_parse_search_string($keywords, $search_keywords);

// error check
if ($keywords && mb_strlen($keywords) < (int)SEARCH_MIN_LENGTH && strlen($keywords) > 0) {
  $errorno += 1;
}
if (!$keywords && !$pfrom && !$pto) {
  $errorno += 1;
}
if ($pfrom && !settype($pfrom, "float")) {
  $errorno += 10000;
}
if ($pto && !settype($pto, "float")) {
  $errorno += 100000;
}
if ($pfrom && !(($errorno & 10000) == 10000) && $pto && !(($errorno & 100000) == 100000) && $pfrom > $pto) {
  $errorno += 1000000;
}
if ($keywords && !$keywordcheck) {
  $errorno += 10000000;
}

if ($errorno) {
  xtc_redirect(xtc_href_link(FILENAME_ADVANCED_SEARCH, xtc_get_all_get_params(array('errorno')).'errorno='.$errorno));

} else {

  // include boxes
  require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

  // build breadcrumb
  $breadcrumb->add(NAVBAR_TITLE1_ADVANCED_SEARCH, xtc_href_link(FILENAME_ADVANCED_SEARCH));
  $breadcrumb->add(NAVBAR_TITLE2_ADVANCED_SEARCH, xtc_href_link(FILENAME_ADVANCED_SEARCH_RESULT, xtc_get_all_get_params(array('filter', 'show', 'filter_id', 'cat'))));

  include (DIR_WS_MODULES.'default.php');
  require (DIR_WS_INCLUDES.'header.php');
}

$smarty->assign('language', $_SESSION['language']);
if (!defined('RM')) {
  $smarty->load_filter('output', 'note');
}
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>