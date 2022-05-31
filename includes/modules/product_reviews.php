<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(product_reviews.php,v 1.47 2003/02/13); www.oscommerce.com
   (c) 2003 nextcommerce (product_reviews.php,v 1.12 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include needed functions
require_once (DIR_FS_INC.'xtc_row_number_format.inc.php');
require_once (DIR_FS_INC.'xtc_date_short.inc.php');

// create smarty elements
$module_smarty = new Smarty;
$module_smarty->assign('language', $_SESSION['language']);
$module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

// set cache ID
if (!CacheCheck()) {
  $cache = false;
  $module_smarty->caching = 0;
  $cache_id = null;
} else {
  $cache = true;
  $module_smarty->caching = 1;
  $module_smarty->cache_lifetime = CACHE_LIFETIME;
  $module_smarty->cache_modified_check = CACHE_CHECK == 'true';
  $cache_id = md5('lID:'.$_SESSION['language'].'|csID:'.$_SESSION['customers_status']['customers_status_id'].'|pID:'.$product->data['products_id'].'|count:'.$product->getReviewsCount());
}

if (!$module_smarty->is_cached(CURRENT_TEMPLATE.'/module/products_reviews.html', $cache_id) || !$cache) {
  $button_preview = '';
  if ($_SESSION['customers_status']['customers_status_write_reviews'] == 1) {
    $button_preview = '<a href="'.xtc_href_link(FILENAME_PRODUCT_REVIEWS_WRITE, 'products_id='.$product->data['products_id']).'">'.xtc_image_button('button_write_review.gif', IMAGE_BUTTON_WRITE_REVIEW).'</a>';
  }

  $module_smarty->assign('BUTTON_WRITE', $button_preview);  
  $reviews_count = $product->getReviewsCount();

  if (($_SESSION['customers_status']['customers_status_read_reviews'] == '1' && $reviews_count > 0) 
      || $_SESSION['customers_status']['customers_status_write_reviews'] == 1
      )
  {    
    $module_smarty->assign('reviews_count', $reviews_count);
    $module_smarty->assign('reviews_avg', $product->getReviewsAverage());
    $module_smarty->assign('module_content', $product->getReviews());     
    
    if (defined('REVIEWS_PURCHASED_INFOS') && REVIEWS_PURCHASED_INFOS != '') {
      $shop_content_data = $main->getContentData(REVIEWS_PURCHASED_INFOS);
      $module_smarty->assign('reviews_note', $main->getContentLink(REVIEWS_PURCHASED_INFOS, $shop_content_data['content_title'], $request_type, false));
    }
  }
}

$module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/products_reviews.html', $cache_id);
$info_smarty->assign('MODULE_products_reviews', !empty($module) ? trim($module) : $module);
