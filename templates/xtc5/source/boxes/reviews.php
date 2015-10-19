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
  if ($_SESSION['customers_status']['customers_status_read_reviews'] == 1) {
    // Show if customer read reviews
    
    $box_smarty = new smarty;

    $box_smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

    $random = true;
    $products_link = '';

    // include needed functions
    require_once(DIR_FS_INC . 'xtc_random_select.inc.php');
    require_once(DIR_FS_INC . 'xtc_break_string.inc.php');

    // query restrictions
    $fsk_lock = ($_SESSION['customers_status']['customers_fsk18_display'] == '0' ? ' AND p.products_fsk18 != 1' : '');

    $random_select = "-- templates/xtc5/source/boxes/reviews.php
                      SELECT r.reviews_id,
                             r.reviews_rating,
                             p.products_id,
                             p.products_image,
                             pd.products_name
                        FROM ".TABLE_REVIEWS." r,
                             ".TABLE_REVIEWS_DESCRIPTION." rd,
                             ".TABLE_PRODUCTS." p,
                             ".TABLE_PRODUCTS_DESCRIPTION." pd
                       WHERE p.products_status = '1'
                         AND p.products_id = r.products_id
                         " . $fsk_lock . "
                         AND r.reviews_id = rd.reviews_id
                         AND rd.languages_id = '" . (int)$_SESSION['languages_id'] . "'
                         AND p.products_id = pd.products_id
                         AND trim(pd.products_name) != ''
                         AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "'";

    if ($product->isProduct() === true) {
      $random_select .= " AND p.products_id = '" . $product->data['products_id'] . "'";
    }
    $random_select .= " ORDER BY r.reviews_id DESC LIMIT " . MAX_RANDOM_SELECT_REVIEWS;
    $random_product = xtc_random_select($random_select);

    if ($product->isProduct() === true) {
      // display product review box
      $random = false;
      // no write permission if in customer group set to off
      if ($_SESSION['customers_status']['customers_status_write_reviews'] == 1) {
        // display 'write a review' box
        $products_link = xtc_href_link(FILENAME_PRODUCT_REVIEWS_WRITE, xtc_product_link($product->data['products_id'],$product->data['products_name']));
        $box_smarty->assign('REVIEWS_WRITE_REVIEW',BOX_REVIEWS_WRITE_REVIEW);
      } else {
        $box_smarty->assign('REVIEWS_WRITE_REVIEW',BOX_REVIEWS_NO_WRITE_REVIEW);
      }
    } elseif (!empty($random_product)) {
      // display random review box, but only if there's something to display
      $random = true;
      $review_query = "-- templates/xtc5/source/boxes/reviews.php
                       SELECT substring(reviews_text, 1, 60) as reviews_text
                         FROM " . TABLE_REVIEWS_DESCRIPTION . "
                        WHERE reviews_id = '" . $random_product['reviews_id'] . "'
                          AND languages_id = '" . (int)$_SESSION['languages_id'] . "'";
      $review_query = xtDBquery($review_query);
      $reviews = xtc_db_fetch_array($review_query,true);
      $reviews = encode_htmlspecialchars($reviews['reviews_text']);
      $reviews = xtc_break_string($reviews, 15, '-<br />');

      $products_image = $product->productImage($random_product['products_image'], 'thumbnail');
      $review_image = xtc_image('templates/' . CURRENT_TEMPLATE . '/img/stars_' . $random_product['reviews_rating'] . '.gif' , sprintf(BOX_REVIEWS_TEXT_OF_5_STARS, $random_product['reviews_rating']),'','','itemprop="rating"');

      $products_link = xtc_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $random_product['products_id'] . '&amp;reviews_id=' . $random_product['reviews_id']);

      $box_smarty->assign('PRODUCTS_IMAGE', $products_image);
      $box_smarty->assign('PRODUCTS_NAME',$random_product['products_name']);
      $box_smarty->assign('REVIEWS',$reviews);
      $box_smarty->assign('REVIEWS_IMAGE',$review_image);
    }
    $box_smarty->assign('PRODUCTS_LINK', $products_link);
    $box_smarty->assign('RANDOM', $random);
    $box_smarty->assign('language', $_SESSION['language']);

    // set cache ID
    if (!CacheCheck()) {
      $box_smarty->caching = 0;
      $box_reviews= $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_reviews.html');
    } else {
      $box_smarty->caching = 1;
      $box_smarty->cache_lifetime=CACHE_LIFETIME;
      $box_smarty->cache_modified_check=CACHE_CHECK;
      $cache_id = $_SESSION['language'].(isset($random_product['reviews_id']) ? $random_product['reviews_id'] : '0').(isset($product->data['products_id']) ? $product->data['products_id'] : '0').$_SESSION['language'];
      $box_reviews= $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_reviews.html',$cache_id);
    }
    $smarty->assign('box_REVIEWS',$box_reviews);
  }
?>