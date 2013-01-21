<?php

/* -----------------------------------------------------------------------------------------
   $Id: reviews.php 4250 2013-01-11 15:09:59Z gtb-modified $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(reviews.php,v 1.48 2003/05/27); www.oscommerce.com
   (c) 2003	 nextcommerce (reviews.php,v 1.12 2003/08/17); www.nextcommerce.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');
$smarty = new Smarty;
// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');
// include needed functions
require_once (DIR_FS_INC.'xtc_word_count.inc.php');
require_once (DIR_FS_INC.'xtc_date_long.inc.php');

$breadcrumb->add(NAVBAR_TITLE_REVIEWS, xtc_href_link(FILENAME_REVIEWS));

require (DIR_WS_INCLUDES.'header.php');

if ($_SESSION['customers_status']['customers_status_read_reviews'] == 0) {
             xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}

$reviews_query_raw = "select r.reviews_id,
                      left(rd.reviews_text, 250) as reviews_text,
                             r.reviews_rating,
                             r.date_added,
                             p.products_id,
                             pd.products_name,
                             p.products_image,
                             r.customers_name
                       from ".TABLE_REVIEWS." r,
                            ".TABLE_REVIEWS_DESCRIPTION." rd,
                            ".TABLE_PRODUCTS." p,
                            ".TABLE_PRODUCTS_DESCRIPTION." pd
                       where p.products_status = '1'
                       and p.products_id = r.products_id
                       and r.reviews_id = rd.reviews_id
                       and p.products_id = pd.products_id
                       and pd.language_id = '".(int) $_SESSION['languages_id']."'
                       and rd.languages_id = '".(int) $_SESSION['languages_id']."'
                       order by r.reviews_id DESC";
$reviews_split = new splitPageResults($reviews_query_raw, (int)$_GET['page'], MAX_DISPLAY_NEW_REVIEWS);

if ($reviews_split->number_of_rows > 0) {

//BOF - Dokuman - 2009-06-05 - replace table with div
	/*
	$smarty->assign('NAVBAR', '
	   <table border="0" width="100%" cellspacing="0" cellpadding="2">
	          <tr>
	            <td class="smallText">'.$reviews_split->display_count(TEXT_DISPLAY_NUMBER_OF_REVIEWS).'</td>
	            <td align="right" class="smallText">'.TEXT_RESULT_PAGE.' '.$reviews_split->display_links(MAX_DISPLAY_PAGE_LINKS, xtc_get_all_get_params(array ('page', 'info', 'x', 'y'))).'</td>
	          </tr>
	        </table>
	');	        
	*/
	$smarty->assign('NAVBAR', '
	<div style="width:100%;font-size:smaller">
		<div style="float:left">'.$reviews_split->display_count(TEXT_DISPLAY_NUMBER_OF_REVIEWS).'</div>
		<div style="float:right">'.TEXT_RESULT_PAGE.' '.$reviews_split->display_links(MAX_DISPLAY_PAGE_LINKS, xtc_get_all_get_params(array ('page', 'info', 'x', 'y'))).'</div>
	<br style="clear:both" /></div>
	');
//EOF - Dokuman - 2009-06-05 - replace table with div
	  
}

$module_data = array ();
if ($reviews_split->number_of_rows > 0) {
	$reviews_query = xtc_db_query($reviews_split->sql_query);
	while ($reviews = xtc_db_fetch_array($reviews_query)) {
		$module_data[] = array ('PRODUCTS_IMAGE' => DIR_WS_THUMBNAIL_IMAGES.$reviews['products_image'], $reviews['products_name'], 'PRODUCTS_LINK' => xtc_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id='.$reviews['products_id'].'&reviews_id='.$reviews['reviews_id']), 'PRODUCTS_NAME' => $reviews['products_name'], 'AUTHOR' => $reviews['customers_name'], 'TEXT' => '('.sprintf(TEXT_REVIEW_WORD_COUNT, xtc_word_count($reviews['reviews_text'], ' ')).')<br />'.nl2br(encode_htmlspecialchars($reviews['reviews_text'])).'..', 'RATING' => xtc_image('templates/'.CURRENT_TEMPLATE.'/img/stars_'.$reviews['reviews_rating'].'.gif', sprintf(TEXT_OF_5_STARS, $reviews['reviews_rating'])));

	}
	$smarty->assign('module_content', $module_data);
}

$smarty->assign('language', $_SESSION['language']);

// set cache ID
 if (!CacheCheck()) {
	$smarty->caching = 0;
	$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/reviews.html');
} else {
	$smarty->caching = 1;
	$smarty->cache_lifetime = CACHE_LIFETIME;
	$smarty->cache_modified_check = CACHE_CHECK;
	$cache_id = $_SESSION['language'];
	$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/reviews.html', $cache_id);
}

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>