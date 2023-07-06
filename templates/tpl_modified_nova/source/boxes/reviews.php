<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // include smarty
  include (DIR_FS_BOXES_INC . 'smarty_default.php');

  // include needed functions
  require_once (DIR_FS_INC . 'xtc_break_string.inc.php');

  // reset cache id
  $cache_id = '';

  if ($_SESSION['customers_status']['customers_status_read_reviews'] == 1) {

    $product_select = '';
    if ($product->isProduct() === true) {
      $product_select = "AND p.products_id = '" . $product->data['products_id'] . "'";
    }

    $reviews_query = "SELECT ".$product->default_select.",
                             r.reviews_id,
                             r.reviews_rating,
                             r.customers_name,
                             r.date_added,
                             substring(rd.reviews_text, 1, 600) as reviews_text
                        FROM ".TABLE_REVIEWS." r
                        JOIN ".TABLE_REVIEWS_DESCRIPTION." rd
                             ON r.reviews_id = rd.reviews_id
                                AND rd.languages_id = '" . (int)$_SESSION['languages_id'] . "'
                        JOIN ".TABLE_PRODUCTS." p
                             ON p.products_id = r.products_id
                                ".$product_select."
                        JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                             ON p.products_id = pd.products_id
                                AND trim(pd.products_name) != ''
                                AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                        JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                             ON p.products_id = p2c.products_id
                        JOIN ".TABLE_CATEGORIES." c
                             ON c.categories_id = p2c.categories_id
                                AND c.categories_status = 1
                                    ".CATEGORIES_CONDITIONS_C."
                       WHERE p.products_status = '1'
                             AND r.reviews_status = '1'
                             ".PRODUCTS_CONDITIONS_P."
                    ORDER BY MD5(CONCAT(p.products_id, CURRENT_TIMESTAMP)) 
                       LIMIT 10";

    $reviews_query = xtc_db_query($reviews_query);

    $box_content = array();
    if (xtc_db_num_rows($reviews_query) > 0) {       
      while ($reviews = xtc_db_fetch_array($reviews_query)) {
        $review_image = xtc_image('templates/' . CURRENT_TEMPLATE . '/img/stars_' . $reviews['reviews_rating'] . '.png' , sprintf(BOX_REVIEWS_TEXT_OF_5_STARS, $reviews['reviews_rating']));
        $review_image_microtag = xtc_image('templates/' . CURRENT_TEMPLATE . '/img/stars_' . $reviews['reviews_rating'] . '.png' , sprintf(BOX_REVIEWS_TEXT_OF_5_STARS, $reviews['reviews_rating']),'','','itemprop="rating"');

        $box_content[$reviews['reviews_id']] = $product->buildDataArray($reviews);
        $box_content[$reviews['reviews_id']]['REVIEWS_VOTE'] = $reviews['reviews_rating'];
        $box_content[$reviews['reviews_id']]['PRODUCTS_LINK'] = xtc_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $reviews['products_id'] . '&reviews_id=' . $reviews['reviews_id']);
        $box_content[$reviews['reviews_id']]['REVIEWS'] = xtc_break_string(encode_htmlspecialchars($reviews['reviews_text']), 15, '-<br />');
        $box_content[$reviews['reviews_id']]['REVIEWS_IMAGE'] = $review_image;
        $box_content[$reviews['reviews_id']]['REVIEWS_IMAGE_MICROTAG'] = $review_image_microtag;
        $box_content[$reviews['reviews_id']]['AUTHOR'] = $reviews['customers_name'];
        $box_content[$reviews['reviews_id']]['DATE'] = xtc_date_short($reviews['date_added']);
      }
    }

    if (defined('REVIEWS_PURCHASED_INFOS') && REVIEWS_PURCHASED_INFOS != '') {
      $shop_content_data = $main->getContentData(REVIEWS_PURCHASED_INFOS);
      if (count($shop_content_data) > 0) {
        $box_smarty->assign('REVIEWS_NOTE', $main->getContentLink(REVIEWS_PURCHASED_INFOS, $shop_content_data['content_title'], $request_type, false));
      }
    }
  
    $box_smarty->assign('REVIEWS_LINK', xtc_href_link(FILENAME_REVIEWS));
    $box_smarty->assign('BOX_CONTENT', $box_content);
  }

  $box_reviews = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_reviews.html', $cache_id);

  $smarty->assign('box_REVIEWS', $box_reviews);
