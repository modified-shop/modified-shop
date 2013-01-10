<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(popup_coupon_help.php,v 1.1.2.5 2003/05/02); www.oscommerce.com
   (c) 2006 XT-Commerce (popup_coupon_help.php 1313 2005-10-18)


   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require ('includes/application_top.php');
require_once (DIR_FS_INC.'xtc_date_short.inc.php');

$smarty = new Smarty;

include ('includes/header.php');

$coupon_query = xtc_db_query("SELECT * FROM ".TABLE_COUPONS." WHERE coupon_id = '".(int)$_GET['cID']."'");
$coupon = xtc_db_fetch_array($coupon_query);
$coupon_desc_query = xtc_db_query("SELECT * FROM ".TABLE_COUPONS_DESCRIPTION." WHERE coupon_id = '".(int)$_GET['cID']."' AND language_id = '".$_SESSION['languages_id']."'");
$coupon_desc = xtc_db_fetch_array($coupon_desc_query);
$text_coupon_help = TEXT_COUPON_HELP_HEADER;
$text_coupon_help .= sprintf(TEXT_COUPON_HELP_NAME, $coupon_desc['coupon_name']);
if (xtc_not_null($coupon_desc['coupon_description'])) {
	$text_coupon_help .= sprintf(TEXT_COUPON_HELP_DESC, $coupon_desc['coupon_description']);
}
$coupon_amount = $coupon['coupon_amount'];
switch ($coupon['coupon_type']) {
	case 'F' :
		$text_coupon_help .= sprintf(TEXT_COUPON_HELP_FIXED, $xtPrice->xtcFormat($coupon['coupon_amount'], true));
		break;
	case 'P' :
		$text_coupon_help .= sprintf(TEXT_COUPON_HELP_FIXED, number_format($coupon['coupon_amount'], 2).'%');
		break;
	case 'S' :
		$text_coupon_help .= TEXT_COUPON_HELP_FREESHIP;
		break;
	default :
		}

if ($coupon['coupon_minimum_order'] > 0) {
	$text_coupon_help .= sprintf(TEXT_COUPON_HELP_MINORDER, $xtPrice->xtcFormat($coupon['coupon_minimum_order'], true));
}
$text_coupon_help .= sprintf(TEXT_COUPON_HELP_DATE, xtc_date_short($coupon['coupon_start_date']), xtc_date_short($coupon['coupon_expire_date']));
$text_coupon_help .= '<strong>'.TEXT_COUPON_HELP_RESTRICT.'</strong>';
$text_coupon_help .= '<br /><br />'.TEXT_COUPON_HELP_CATEGORIES;
$coupon_get = xtc_db_query("SELECT restrict_to_categories FROM ".TABLE_COUPONS." WHERE coupon_id='".(int)$_GET['cID']."'");
$get_result = xtc_db_fetch_array($coupon_get);

$cat_ids = explode(",", $get_result['restrict_to_categories']);
for ($i = 0, $n = count($cat_ids); $i < $n; $i ++) {
	$result = xtc_db_query("SELECT * 
                            FROM ".TABLE_CATEGORIES." c, 
                                 ".TABLE_CATEGORIES_DESCRIPTION." cd 
                           WHERE c.categories_id = cd.categories_id 
                             AND cd.language_id = '".$_SESSION['languages_id']."' 
                             AND c.categories_id='".$cat_ids[$i]."'");
	if ($row = xtc_db_fetch_array($result)) {
		$cats .= '<br />'.$row["categories_name"];
	}
}
if (!isset($cats) || $cats == '') {
	$cats = '<br />NONE';
}
$text_coupon_help .= $cats;
$text_coupon_help .= '<br /><br />'.TEXT_COUPON_HELP_PRODUCTS;
$coupon_get = xtc_db_query("SELECT restrict_to_products FROM ".TABLE_COUPONS."  where coupon_id='".(int)$_GET['cID']."'");
$get_result = xtc_db_fetch_array($coupon_get);

$pr_ids = explode(",", $get_result['restrict_to_products']);
for ($i = 0, $n < count($pr_ids); $i < $n; $i ++) {
	$result = xtc_db_query("SELECT * 
                            FROM ".TABLE_PRODUCTS." p, 
                                 ".TABLE_PRODUCTS_DESCRIPTION." pd 
                           WHERE p.products_id = pd.products_id 
                             AND pd.language_id = '".$_SESSION['languages_id']."'
                             AND p.products_id = '".$pr_ids[$i]."'");
	if ($row = xtc_db_fetch_array($result)) {
		$prods .= '<br />'.$row["products_name"];
	}
}
if (!isset($prods) || $prods == '') {
	$prods = '<br />NONE';
}
$text_coupon_help .= $prods;

$smarty->assign('TEXT_HELP', $text_coupon_help);
$smarty->assign('link_close', 'javascript:window.close()');
$smarty->assign('language', $_SESSION['language']);
$smarty->caching = 0;
$smarty->display(CURRENT_TEMPLATE.'/module/popup_coupon_help.html');
//include ('includes/application_bottom.php');
?>