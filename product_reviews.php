<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(product_reviews.php,v 1.47 2003/02/13); www.oscommerce.com
   (c) 2003 nextcommerce (product_reviews.php,v 1.12 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce (product_reviews.php 1238 2005-09-24)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

// BOF - DokuMan - 2011-05-10 - add check for products_id
if (!isset($_GET['products_id']) || !is_numeric($_GET['products_id'])) {
  xtc_redirect(xtc_href_link(FILENAME_REVIEWS));
}
// EOF - DokuMan - 2011-05-10 - add check for products_id

// create smarty elements
$smarty = new Smarty;
// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');
// include needed functions
require_once (DIR_FS_INC.'xtc_row_number_format.inc.php');
require_once (DIR_FS_INC.'xtc_date_short.inc.php');

// lets retrieve all $_GET keys and values..
$get_params = xtc_get_all_get_params();
$get_params_back = xtc_get_all_get_params(array ('reviews_id')); // for back button
$get_params = substr($get_params, 0, -1); //remove trailing &
if (xtc_not_null($get_params_back)) {
  $get_params_back = substr($get_params_back, 0, -1); //remove trailing &
} else {
  $get_params_back = $get_params;
}

// BOF - DokuMan - 2011-07-26 - Error catch needed in case this page is accessed without $_GET['products_id']
if (!isset($_GET['products_id']) || !is_numeric($_GET['products_id'])) {
  xtc_redirect(xtc_href_link(FILENAME_REVIEWS));
}
// EOF - DokuMan - 2011-07-26 - Error catch needed in case this page is accessed without $_GET['products_id']

$product_info_query = xtc_db_query("SELECT pd.products_name
                                      FROM ".TABLE_PRODUCTS_DESCRIPTION." pd
                                 LEFT JOIN ".TABLE_PRODUCTS." p
                                        ON pd.products_id = p.products_id
                                     WHERE pd.language_id = '".(int) $_SESSION['languages_id']."'
                                       AND p.products_status = '1'
                                       AND pd.products_id = '".(int) $_GET['products_id']."'");
if (!xtc_db_num_rows($product_info_query)) {
  xtc_redirect(xtc_href_link(FILENAME_REVIEWS));
}
$product_info = xtc_db_fetch_array($product_info_query);

$breadcrumb->add(NAVBAR_TITLE_PRODUCT_REVIEWS, xtc_href_link(FILENAME_PRODUCT_REVIEWS, $get_params));

require (DIR_WS_INCLUDES.'header.php');

$smarty->assign('PRODUCTS_NAME', $product_info['products_name']);

$data_reviews = array ();
$reviews_query = xtc_db_query("SELECT rd.reviews_text,
                                      r.reviews_rating,
                                      r.reviews_id,
                                      r.customers_name,
                                      r.date_added,
                                      r.last_modified,
                                      r.reviews_read
                                 FROM ".TABLE_REVIEWS." AS r
                            LEFT JOIN ".TABLE_REVIEWS_DESCRIPTION." AS rd 
                                   ON (rd.reviews_id = r.reviews_id AND rd.languages_id = ".(int) $_SESSION['languages_id'].")
                                WHERE r.products_id = '".(int) $_GET['products_id']."'
                             ORDER BY r.reviews_id DESC");
if (xtc_db_num_rows($reviews_query)) {
  $row = 0;
  while ($reviews = xtc_db_fetch_array($reviews_query)) {
    $row ++;
    $data_reviews[] = array (
    'ID' => $reviews['reviews_id'],
    'AUTHOR' => '<a href="'.xtc_href_link(FILENAME_PRODUCT_REVIEWS_INFO, $get_params.'&reviews_id='.$reviews['reviews_id']).'">'.$reviews['customers_name'].'</a>',
    'DATE' => xtc_date_short($reviews['date_added']),
    'RATING' => xtc_image('templates/'.CURRENT_TEMPLATE.'/img/stars_'.$reviews['reviews_rating'].'.gif', sprintf(BOX_REVIEWS_TEXT_OF_5_STARS, $reviews['reviews_rating']),'','','itemprop="rating"'),
    'TEXT' => $reviews['reviews_text']
    );
  }
}
$smarty->assign('module_content', $data_reviews);
$smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(FILENAME_PRODUCT_INFO, $get_params_back).'">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
$smarty->assign('BUTTON_WRITE', '<a href="'.xtc_href_link(FILENAME_PRODUCT_REVIEWS_WRITE, $get_params).'">'.xtc_image_button('button_write_review.gif', IMAGE_BUTTON_WRITE_REVIEW).'</a>');
$smarty->assign('language', $_SESSION['language']);

// set cache ID
 if (!CacheCheck()) {
  $smarty->caching = 0;
  $main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/product_reviews.html');
} else {
  $smarty->caching = 1;
  $smarty->cache_lifetime = CACHE_LIFETIME;
  $smarty->cache_modified_check = CACHE_CHECK;
  $cache_id = $_SESSION['language'].$_GET['products_id'];
  $main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/product_reviews.html', $cache_id);
}

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);
if (!defined('RM'))
  $smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>