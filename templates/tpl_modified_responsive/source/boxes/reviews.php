<?php
/* -----------------------------------------------------------------------------------------
   $Id: reviews.php 4209 2013-01-10 23:54:44Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(reviews.php,v 1.36 2003/02/12); www.oscommerce.com
   (c) 2003 nextcommerce (reviews.php,v 1.9 2003/08/17 22:40:08); www.nextcommerce.org
   (c) 2006 XT-Commerce (reviews.php 1262 2005-09-30)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include smarty
include(DIR_FS_BOXES_INC . 'smarty_default.php');

// reset cache id
$cache_id = '';

if ($product->isProduct() === true && $_SESSION['customers_status']['customers_status_write_reviews'] == '1') {
  
  // set cache id
  $cache_id = md5($_SESSION['language']);
  
  if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_reviews.html', $cache_id) || !$cache) {
    // display 'write a review' box
    $box_smarty->assign('REVIEWS_WRITE_REVIEW',BOX_REVIEWS_WRITE_REVIEW);
    $box_smarty->assign('REVIEWS_LINK', xtc_href_link(FILENAME_REVIEWS));
    $box_smarty->assign('PRODUCTS_LINK', xtc_href_link(FILENAME_PRODUCT_REVIEWS_WRITE, xtc_product_link($product->data['products_id'],$product->data['products_name'])));
  }
} elseif ($_SESSION['customers_status']['customers_status_read_reviews'] == 1) {

  $product_select = '';
  if ($product->isProduct() === true) {
    $product_select = "AND p.products_id = '" . $product->data['products_id'] . "'";
  }

  $reviews_query = "SELECT r.reviews_id,
                           r.reviews_rating,
                           substring(rd.reviews_text, 1, 60) as reviews_text,
                           p.products_id,
                           p.products_image,
                           pd.products_name
                      FROM ".TABLE_REVIEWS." r
                      JOIN ".TABLE_REVIEWS_DESCRIPTION." rd
                           ON r.reviews_id = rd.reviews_id
                              AND rd.languages_id = '" . (int)$_SESSION['languages_id'] . "'
                              AND trim(rd.reviews_text) != ''
                      JOIN ".TABLE_PRODUCTS." p
                           ON p.products_id = r.products_id
                              ".$product_select."
                      JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                           ON p.products_id = pd.products_id
                              AND trim(pd.products_name) != ''
                              AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                     WHERE p.products_status = '1'
                           ".PRODUCTS_CONDITIONS_P."
                       AND r.reviews_status = '1'
                  ORDER BY MD5(CONCAT(p.products_id, CURRENT_TIMESTAMP)) 
                     LIMIT 1";
  $reviews_query = xtc_db_query($reviews_query);
  
  if (xtc_db_num_rows($reviews_query) > 0) {                  
    $reviews = xtc_db_fetch_array($reviews_query);
    
    // set cache id
    $cache_id = md5($_SESSION['language'].$reviews['reviews_id']);
    
    if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_reviews.html', $cache_id) || !$cache) {
    
      $products_image = $product->productImage($reviews['products_image'], 'thumbnail');
      
      $review_image = xtc_image('templates/' . CURRENT_TEMPLATE . '/img/stars_' . $reviews['reviews_rating'] . '.png' , sprintf(BOX_REVIEWS_TEXT_OF_5_STARS, $reviews['reviews_rating']));
      $review_image_microtag = xtc_image('templates/' . CURRENT_TEMPLATE . '/img/stars_' . $reviews['reviews_rating'] . '.png' , sprintf(BOX_REVIEWS_TEXT_OF_5_STARS, $reviews['reviews_rating']),'','','itemprop="rating"');

      // include needed functions
      require_once(DIR_FS_INC . 'xtc_break_string.inc.php');
      $box_smarty->assign('REVIEWS_LINK', xtc_href_link(FILENAME_REVIEWS));
      $box_smarty->assign('PRODUCTS_IMAGE', $products_image);
      $box_smarty->assign('PRODUCTS_NAME', $reviews['products_name']);
      $box_smarty->assign('PRODUCTS_LINK', xtc_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $reviews['products_id'] . '&reviews_id=' . $reviews['reviews_id']));
      $box_smarty->assign('REVIEWS', xtc_break_string(encode_htmlspecialchars($reviews['reviews_text']), 15, '-<br />'));
      $box_smarty->assign('REVIEWS_IMAGE', $review_image);
      $box_smarty->assign('REVIEWS_IMAGE_MICROTAG', $review_image_microtag);
      $box_smarty->assign('RANDOM', 1);
    }
  }
}

if (!$cache) {
  $box_reviews = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_reviews.html');
} else {
  $box_reviews = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_reviews.html', $cache_id);
}

$smarty->assign('box_REVIEWS', $box_reviews);
?>