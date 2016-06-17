<?php
/* -----------------------------------------------------------------------------------------
   $Id: product_reviews_info.php 4250 2013-01-11 15:09:59Z gtb-modified $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(product_reviews_info.php,v 1.47 2003/02/13); www.oscommerce.com
   (c) 2003	 nextcommerce (product_reviews_info.php,v 1.12 2003/08/17); www.nextcommerce.org 
   (c) 2003 XT-Commerce
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

// create smarty elements
$smarty = new Smarty;

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_break_string.inc.php');
require_once (DIR_FS_INC.'xtc_date_long.inc.php');

if (!isset($_GET['reviews_id']) && !isset($_GET['products_id'])) {
	xtc_redirect(xtc_href_link(FILENAME_REVIEWS, '', 'NONSSL'));
}

if ($_SESSION['customers_status']['customers_status_read_reviews'] == '0') {
  xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}

$reviews_query = xtc_db_query("SELECT r.*,
                                      rd.reviews_text,
                                      p.products_id,
                                      p.products_image,
                                      pd.products_name
                                 FROM ".TABLE_REVIEWS." r
                                 JOIN ".TABLE_REVIEWS_DESCRIPTION." rd
                                      ON r.reviews_id = rd.reviews_id
                                         AND rd.languages_id = '".(int)$_SESSION['languages_id']."'
                                 JOIN ".TABLE_PRODUCTS." p 
                                      ON r.products_id = p.products_id
                                 JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd 
                                      ON p.products_id = pd.products_id 
                                         AND pd.language_id = '".(int) $_SESSION['languages_id']."'
                                WHERE r.reviews_id = '".(int) $_GET['reviews_id']."'
                                  AND r.products_id = '".(int) $_GET['products_id']."'
                                  AND p.products_status = '1'
                                  AND r.reviews_status = '1'
                                      ".PRODUCTS_CONDITIONS_P);

if (xtc_db_num_rows($reviews_query) < 1) {
	xtc_redirect(xtc_href_link(FILENAME_REVIEWS, '', 'NONSSL'));
}

$reviews = xtc_db_fetch_array($reviews_query);

xtc_db_query("UPDATE ".TABLE_REVIEWS." 
                 SET reviews_read = reviews_read+1 
               WHERE reviews_id = '".$reviews['reviews_id']."'");

$breadcrumb->add(NAVBAR_TITLE_PRODUCT_REVIEWS, xtc_href_link(FILENAME_PRODUCT_REVIEWS, xtc_get_all_get_params(array ('reviews_id'))));
require (DIR_WS_INCLUDES.'header.php');

$smarty->assign('AUTHOR', $reviews['customers_name']);
$smarty->assign('DATE', xtc_date_long($reviews['date_added']));
$smarty->assign('REVIEWS_TEXT', nl2br(xtc_break_string(encode_htmlspecialchars($reviews['reviews_text']), 60, '-<br />')));
$smarty->assign('RATING', xtc_image('templates/'.CURRENT_TEMPLATE.'/img/stars_'.$reviews['reviews_rating'].'.gif', sprintf(TEXT_OF_5_STARS, $reviews['reviews_rating'])));
$smarty->assign('PRODUCTS_NAME', $reviews['products_name']);
$smarty->assign('PRODUCTS_LINK', xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($reviews['products_id'], $reviews['products_name'])));
$smarty->assign('PRODUCTS_IMAGE', $product->productImage($reviews['products_image'], 'info'));
$smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(FILENAME_PRODUCT_REVIEWS).'">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
$smarty->assign('BUTTON_BUY_NOW', '<a href="'.xtc_href_link(FILENAME_DEFAULT, 'action=buy_now&BUYproducts_id='.$reviews['products_id']).'">'.xtc_image_button('button_in_cart.gif', IMAGE_BUTTON_IN_CART).'</a>');
$smarty->assign('PRODUCTS_BUTTON_DETAILS', '<a href="'.xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($reviews['products_id'], $reviews['products_name'])).'">'.xtc_image_button('button_product_more.gif', TEXT_INFO_DETAILS).'</a>');

$smarty->assign('language', $_SESSION['language']);

// set cache ID
 if (!CacheCheck()) {
	$smarty->caching = 0;
	$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/product_reviews_info.html');
} else {
	$smarty->caching = 1;
	$smarty->cache_lifetime = CACHE_LIFETIME;
	$smarty->cache_modified_check = CACHE_CHECK;
	$cache_id = md5($_SESSION['language'].$reviews['reviews_id']);
	$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/product_reviews_info.html', $cache_id);
}

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>