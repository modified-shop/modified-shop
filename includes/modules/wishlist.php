<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

// include needed functions
require_once(DIR_FS_INC.'get_wishlist_content.inc.php');

// create smarty
$module_smarty = new Smarty;

$module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

$module_data = array ();
if ($_SESSION['wishlist']->count_contents() > 0) {
  $wishlist_content_array = get_wishlist_content();
  $module_smarty->assign('PRODUCT_LIST_BOX', $wishlist_content_array['ATTRIBUTES']);
  $module_smarty->assign('module_content', $wishlist_content_array['DATA']);
}

$module_smarty->assign('wishlist_cart', true);
$module_smarty->assign('language', $_SESSION['language']);

$module_smarty->caching = 0;
$module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/wishlist.html');

$smarty->assign('MODULE_wishlist', $module);
?>