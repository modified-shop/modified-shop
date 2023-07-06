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

  // reset cache id
  $cache_id = md5('lID:'.$_SESSION['language'].'|csID:'.$_SESSION['customers_status']['customers_status_id'].'|curr:'.$_SESSION['currency'].'|date:'.date('Y-m-d-H:i'));

  if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_specials.html', $cache_id) || !$cache) {
    $specials_query = xtc_db_query("SELECT ".$product->default_select."
                                      FROM ".TABLE_PRODUCTS." p
                                      JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                           ON pd.products_id = p.products_id
                                              AND trim(pd.products_name) != ''
                                              AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                                      JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                                           ON p.products_id = p2c.products_id
                                      JOIN ".TABLE_CATEGORIES." c
                                           ON c.categories_id = p2c.categories_id
                                              AND c.categories_status = 1
                                                  ".CATEGORIES_CONDITIONS_C."
                                      JOIN ".TABLE_SPECIALS." s 
                                           ON p.products_id = s.products_id
                                              ".SPECIALS_CONDITIONS_S."
                                     WHERE p.products_status = '1'
                                           ".PRODUCTS_CONDITIONS_P."                                             
                                  GROUP BY p.products_id
                                  ORDER BY s.expires_date ASC, p.products_id
                                     LIMIT ".MAX_PRODUCTS_RANDOM_POOL);

    $box_content = array();
    if (xtc_db_num_rows($specials_query, true) > 0) {
      $content_array = array();
      while ($specials = xtc_db_fetch_array($specials_query, true)) {
        $content_array[] = $specials;
      }
      shuffle($content_array);
      $content_array = array_slice($content_array, 0, MAX_PRODUCTS_BOX);

      foreach ($content_array as $specials) {
        $box_content[] = $product->buildDataArray($specials);
      }
    }

    $box_smarty->assign('BOX_CONTENT', $box_content);
    $box_smarty->assign('SPECIALS_LINK', xtc_href_link(FILENAME_SPECIALS));

    $box_specials = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_specials.html', $cache_id);

    $smarty->assign('box_SPECIALS', $box_specials);
  }
