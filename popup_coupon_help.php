<?php

/* -----------------------------------------------------------------------------------------
   $Id: popup_coupon_help.php 1313 2005-10-18 15:49:15Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(popup_coupon_help.php,v 1.1.2.5 2003/05/02); www.oscommerce.com


   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require ('includes/application_top.php');
require_once (DIR_FS_INC.'xtc_date_short.inc.php');

$popup_smarty = new Smarty;

$coupon_query = xtc_db_query("SELECT * 
                                FROM ".TABLE_COUPONS." c
                                JOIN ".TABLE_COUPONS_DESCRIPTION." cd
                                     ON c.coupon_id = cd.coupon_id
                                        AND cd.language_id = '".(int)$_SESSION['languages_id']."'
                               WHERE c.coupon_id = '".(int)$_GET['cID']."'");
$coupon = xtc_db_fetch_array($coupon_query);

$text_coupon_help = TEXT_COUPON_HELP_HEADER;
$text_coupon_help .= sprintf(TEXT_COUPON_HELP_NAME, $coupon['coupon_name']);
if (xtc_not_null($coupon['coupon_description'])) {
	$text_coupon_help .= sprintf(TEXT_COUPON_HELP_DESC, $coupon['coupon_description']);
}

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
	  break;
}

if ($coupon['coupon_minimum_order'] > 0) {
	$text_coupon_help .= sprintf(TEXT_COUPON_HELP_MINORDER, $xtPrice->xtcFormat($coupon['coupon_minimum_order'], true));
}
$text_coupon_help .= sprintf(TEXT_COUPON_HELP_DATE, xtc_date_short($coupon['coupon_start_date']), xtc_date_short($coupon['coupon_expire_date']));
$text_coupon_help .= '<strong>'.TEXT_COUPON_HELP_RESTRICT.'</strong>';

$text_coupon_help .= '<br /><br />'.TEXT_COUPON_HELP_CATEGORIES;
$cats = '<br />---';
$cat_ids = explode(",", $coupon['restrict_to_categories']);
$categories_query = xtc_db_query("SELECT categories_name
                                    FROM ".TABLE_CATEGORIES_DESCRIPTION."
                                   WHERE categories_id IN ('" . implode("', '", $cat_ids) . "')
                                     AND language_id = '".(int)$_SESSION['languages_id']."'
                                     AND trim(categories_name) != ''");
if (xtc_db_num_rows($categories_query) > 0) {
  $cats = '';
  while ($categories = xtc_db_fetch_array($categories_query)) {
    $cats .= '<br />'.$row["categories_name"];
  }
}
$text_coupon_help .= $cats;

$text_coupon_help .= '<br /><br />'.TEXT_COUPON_HELP_PRODUCTS;
$prods = '<br />---';
$pr_ids = explode(",", $coupon['restrict_to_products']); // Hetfield - 2009-08-18 - replaced deprecated function split with explode to be ready for PHP >= 5.3
$products_query = xtc_db_query("SELECT products_name
                                  FROM ".TABLE_PRODUCTS_DESCRIPTION."
                                 WHERE products_id IN ('" . implode("', '", $pr_ids) . "')
                                   AND language_id = '".(int)$_SESSION['languages_id']."'
                                   AND trim(products_name) != ''");
if (xtc_db_num_rows($products_query) > 0) {
  $prods = '';
	if ($products = xtc_db_fetch_array($products_query)) {
		$prods .= '<br />'.$products["products_name"];
	}
}
$text_coupon_help .= $prods;

$popup_smarty->assign('TEXT_HELP', $text_coupon_help);
$popup_smarty->assign('link_close', 'javascript:window.close()');
$popup_smarty->assign('language', $_SESSION['language']);

$popup_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
$popup_smarty->assign('html_params', ((TEMPLATE_HTML_ENGINE == 'xhtml') ? ' '.HTML_PARAMS : ' lang="'.$_SESSION['language_code'].'"'));
$popup_smarty->assign('doctype', ((TEMPLATE_HTML_ENGINE == 'xhtml') ? ' PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"' : ''));
$popup_smarty->assign('charset', $_SESSION['language_charset']);
$popup_smarty->assign('title', htmlspecialchars($content_data['content_heading'], ENT_QUOTES, strtoupper($_SESSION['language_charset'])));
if (DIR_WS_BASE == '') {
  $popup_smarty->assign('base', (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG);
}
$popup_smarty->caching = 0;
$popup_smarty->display(CURRENT_TEMPLATE.'/module/popup_coupon_help.html');
?>