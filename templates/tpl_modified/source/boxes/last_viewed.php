<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003 XT-Commerce - www.xt-commerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include smarty
include(DIR_FS_BOXES_INC . 'smarty_default.php');

// reset cache id
$cache_id = '';

if (isset($_SESSION['tracking']['products_history']) && count($_SESSION['tracking']['products_history']) > 0) {
  $random_last_viewed = xtc_rand(0, (count($_SESSION['tracking']['products_history']) - 1));

  // set cache id
  $cache_id = md5($_SESSION['language'].$_SESSION['customers_status']['customers_status'].$_SESSION['tracking']['products_history'][$random_last_viewed]);

  if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_last_viewed.html', $cache_id) || !$cache) {

    $random_query = "SELECT p.products_id,
                            p.products_price,
                            p.products_tax_class_id,
                            p.products_image,
                            p.products_vpe,
                            p.products_vpe_status,
                            p.products_vpe_value,
                            pd.products_name,
                            p2c.categories_id,
                            cd.categories_name
                       FROM " . TABLE_PRODUCTS . " p
                       JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                            ON p.products_id = pd.products_id
                               AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                               AND trim(pd.products_name) != ''
                       JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                            ON p.products_id = p2c.products_id
                       JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd
                            ON cd.categories_id = p2c.categories_id
                               AND cd.language_id = '".(int)$_SESSION['languages_id']."'
                      WHERE p.products_status = '1'
                        AND p.products_id = '".(int)$_SESSION['tracking']['products_history'][$random_last_viewed]."'
                            ".PRODUCTS_CONDITIONS_P;

    $random_query = xtDBquery($random_query);

    if (xtc_db_num_rows($random_query, true) > 0) {
      $random_product = xtc_db_fetch_array($random_query, true);
      $box_smarty->assign('box_content', $product->buildDataArray($random_product));
      $box_smarty->assign('MY_PERSONAL_PAGE', xtc_href_link(FILENAME_ACCOUNT));
      $box_smarty->assign('CATEGORY_LINK', xtc_href_link(FILENAME_DEFAULT, xtc_category_link($random_product['categories_id'], $random_product['categories_name'])));
      $box_smarty->assign('CATEGORY_NAME', $random_product['categories_name']);
    }
  }
}

if (!$cache) {
  $box_last_viewed = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_last_viewed.html');
} else {
  $box_last_viewed = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_last_viewed.html', $cache_id);
}

$smarty->assign('box_LAST_VIEWED', $box_last_viewed);
?>