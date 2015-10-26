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

if (!defined('MODULE_WISHLIST_SYSTEM_STATUS') || MODULE_WISHLIST_SYSTEM_STATUS == 'false') {
  xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
}

// include needed functions
require_once(DIR_FS_INC.'get_wishlist_content.inc.php');

// create smarty
$smarty = new Smarty;

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

$breadcrumb->add(NAVBAR_TITLE_WISHLIST, xtc_href_link(FILENAME_WISHLIST));

require (DIR_WS_INCLUDES.'header.php');

$module_data = array ();
if ($_SESSION['wishlist']->count_contents() > 0) {
  $wishlist_content_array = get_wishlist_content();
  $smarty->assign('PRODUCT_LIST_BOX', $wishlist_content_array['ATTRIBUTES']);
  $smarty->assign('module_content', $wishlist_content_array['DATA']);
} else {
  $smarty->assign('BUTTON_CONTINUE', '<a href="'.xtc_href_link(FILENAME_DEFAULT, '', 'NONSSL').'">'.xtc_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE).'</a>');
}

$smarty->assign('language', $_SESSION['language']);

$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/wishlist.html');

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
  $smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>