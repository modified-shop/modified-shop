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
  include(DIR_FS_BOXES_INC . 'smarty_default.php');

  $days = '';
  if (MAX_DISPLAY_NEW_PRODUCTS_DAYS != '0') {
    $days = "AND p.products_date_added > '".date("Y-m-d", mktime(1, 1, 1, date("m"), date("d") - MAX_DISPLAY_NEW_PRODUCTS_DAYS, date("Y")))."'";
  }

  $products_id =  (isset($_GET['products_id']) && (int)$_GET['products_id'] > 0) ? 'AND p.products_id != ' . (int)$_GET['products_id'] : '';

  // reset cache id
  $cache_id = md5('lID:'.$_SESSION['language'].'|csID:'.$_SESSION['customers_status']['customers_status_id'].'|curr:'.$_SESSION['currency'].'|pID:'.$products_id.'|days:'.(int)MAX_DISPLAY_NEW_PRODUCTS_DAYS);

  if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_whatsnew.html', $cache_id) || !$cache) {
    // get random product data
    $whats_new_query = xtDBquery("SELECT DISTINCT ".$product->default_select."
                                             FROM ".TABLE_PRODUCTS." p
                                             JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd 
                                                  ON p.products_id = pd.products_id 
                                                     AND pd.language_id = ".(int)$_SESSION['languages_id']."
                                                     AND trim(pd.products_name) != ''
                                             JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                                                  ON p.products_id = p2c.products_id
                                             JOIN ".TABLE_CATEGORIES." c
                                                  ON c.categories_id = p2c.categories_id 
                                                     AND c.categories_status = 1 
                                                         ".CATEGORIES_CONDITIONS_C."
                                            WHERE p.products_status = 1
                                                  " . PRODUCTS_CONDITIONS_P . "
                                                  " . $products_id . "
                                                  " . $days . "                                           
                                         ORDER BY p.products_date_added DESC, p.products_id
                                            LIMIT ".MAX_PRODUCTS_RANDOM_POOL);

    $box_content = array();
    if (xtc_db_num_rows($whats_new_query, true) > 0) {
      $content_array = array();
      while ($whats_new = xtc_db_fetch_array($whats_new_query, true)) {
        $content_array[] = $whats_new;
      }
      shuffle($content_array);
      $content_array = array_slice($content_array, 0, MAX_PRODUCTS_BOX);

      foreach ($content_array as $whats_new) {
        $box_content[] = $product->buildDataArray($whats_new);
      }
    }

    $box_smarty->assign('BOX_CONTENT', $box_content);
    $box_smarty->assign('LINK_NEW_PRODUCTS', xtc_href_link(FILENAME_PRODUCTS_NEW));
  }

  $box_whats_new = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_whatsnew.html', $cache_id);

  $smarty->assign('box_WHATSNEW', $box_whats_new);
