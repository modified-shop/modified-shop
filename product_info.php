<?php
/* -----------------------------------------------------------------------------------------
   $Id: product_info.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(product_info.php,v 1.94 2003/05/04); www.oscommerce.com
   (c) 2003 nextcommerce (product_info.php,v 1.46 2003/08/25); www.nextcommerce.org
   (c) 2006 XT-Commerce (product_info.php 1320 2005-10-25)

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist
   New Attribute Manager v4b                            Autor: Mike G | mp3man@internetwork.net | http://downloads.ephing.com
   Cross-Sell (X-Sell) Admin 1                          Autor: Joshua Dechant (dreamscape)
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

// create smarty elements
$smarty = new Smarty;

// redirect
if (!isset($_GET['products_id']) && !isset($_GET['info']) && !isset($_GET['action'])) {
  xtc_redirect(xtc_href_link(FILENAME_DEFAULT, '', 'NONSSL'));
}

/* web28 -2013-01-07 - comment out because cPath is allready set in includes/application_top.php or includes/modules/set_ids_by_url_parameters.php
if (isset($_GET['products_id'])) {
	$cat = xtc_db_query("SELECT categories_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE products_id='".(int) $_GET['products_id']."'");
	$catData = xtc_db_fetch_array($cat);
	require_once (DIR_FS_INC.'xtc_get_path.inc.php');
	if ($catData['categories_id'])
		$cPath = xtc_input_validation(xtc_get_path($catData['categories_id']), 'cPath', '');
}
*/

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_get_download.inc.php');
require_once (DIR_FS_INC.'xtc_get_all_get_params.inc.php');
require_once (DIR_FS_INC.'xtc_date_long.inc.php');
require_once (DIR_FS_INC.'xtc_draw_hidden_field.inc.php');
require_once (DIR_FS_INC.'xtc_draw_form.inc.php');
require_once (DIR_FS_INC.'xtc_draw_input_field.inc.php');
require_once (DIR_FS_INC.'xtc_image_submit.inc.php');

if (isset($_GET['action']) && $_GET['action'] == 'get_download') {
	xtc_get_download((int)$_GET['cID']); 
}

include (DIR_WS_MODULES.'product_info.php');

require (DIR_WS_INCLUDES.'header.php');

$smarty->assign('language', $_SESSION['language']);
$smarty->caching = 0;
if (!defined('RM'))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');

include ('includes/application_bottom.php');
?>