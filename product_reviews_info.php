<?php

/* -----------------------------------------------------------------------------------------
   $Id: product_reviews_info.php 4250 2013-01-11 15:09:59Z gtb-modified $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(product_reviews_info.php,v 1.47 2003/02/13); www.oscommerce.com
   (c) 2003	 nextcommerce (product_reviews_info.php,v 1.12 2003/08/17); www.nextcommerce.org 

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


// lets retrieve all $HTTP_GET_VARS keys and values..
$get_params = xtc_get_all_get_params(array ('reviews_id'));
$get_params = substr($get_params, 0, -1); //remove trailing &

$reviews_query = "select rd.reviews_text,
                         r.reviews_rating,
                         r.reviews_id,
                         r.products_id,
                         r.customers_name,
                         r.date_added,
                         r.last_modified,
                         r.reviews_read,
                         p.products_id,
                         pd.products_name,
                         p.products_image
                  from ".TABLE_REVIEWS." r
                  left join ".TABLE_PRODUCTS." p on (r.products_id = p.products_id)
                  left join ".TABLE_PRODUCTS_DESCRIPTION." pd on (p.products_id = pd.products_id 
                  and pd.language_id = '".(int) $_SESSION['languages_id']."'), ".TABLE_REVIEWS_DESCRIPTION." rd 
                  where r.reviews_id = '".(int) $_GET['reviews_id']."'
                  and r.reviews_id = rd.reviews_id
                  and p.products_status = '1'";
$reviews_query = xtc_db_query($reviews_query);

if (!xtc_db_num_rows($reviews_query))
	xtc_redirect(xtc_href_link(FILENAME_REVIEWS));
$reviews = xtc_db_fetch_array($reviews_query);

$breadcrumb->add(NAVBAR_TITLE_PRODUCT_REVIEWS, xtc_href_link(FILENAME_PRODUCT_REVIEWS, $get_params));

xtc_db_query("update ".TABLE_REVIEWS." set reviews_read = reviews_read+1 where reviews_id = '".$reviews['reviews_id']."'");

$reviews_text = xtc_break_string(encode_htmlspecialchars($reviews['reviews_text']), 60, '-<br />');

require (DIR_WS_INCLUDES.'header.php');

$smarty->assign('PRODUCTS_NAME', $reviews['products_name']);
$smarty->assign('AUTHOR', $reviews['customers_name']);
$smarty->assign('DATE', xtc_date_long($reviews['date_added']));
$smarty->assign('REVIEWS_TEXT', nl2br($reviews_text));
$smarty->assign('RATING', xtc_image('templates/'.CURRENT_TEMPLATE.'/img/stars_'.$reviews['reviews_rating'].'.gif', sprintf(TEXT_OF_5_STARS, $reviews['reviews_rating'])));
$smarty->assign('PRODUCTS_LINK', xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($reviews['products_id'], $reviews['products_name'])));
$smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(FILENAME_PRODUCT_REVIEWS, $get_params).'">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
$smarty->assign('BUTTON_BUY_NOW', '<a href="'.xtc_href_link(FILENAME_DEFAULT, 'action=buy_now&BUYproducts_id='.$reviews['products_id']).'">'.xtc_image_button('button_in_cart.gif', IMAGE_BUTTON_IN_CART).'</a>');
$smarty->assign('IMAGE', '<a href="javascript:popupImageWindow(\''.xtc_href_link(FILENAME_POPUP_IMAGE, 'pID='.$reviews['products_id']).'\')">'.xtc_image(DIR_WS_THUMBNAIL_IMAGES.$reviews['products_image'], $reviews['products_name'], '', '', 'align="center" hspace="5" vspace="5"').'<br /></a>');

$smarty->assign('language', $_SESSION['language']);

// set cache ID
 if (!CacheCheck()) {
	$smarty->caching = 0;
	$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/product_reviews_info.html');
} else {
	$smarty->caching = 1;
	$smarty->cache_lifetime = CACHE_LIFETIME;
	$smarty->cache_modified_check = CACHE_CHECK;
	$cache_id = $_SESSION['language'].$reviews['reviews_id'];
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