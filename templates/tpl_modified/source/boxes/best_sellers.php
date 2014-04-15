<?php
/* -----------------------------------------------------------------------------------------
   $Id: best_sellers.php 6176 2013-12-15 15:10:00Z hhacker $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(best_sellers.php,v 1.20 2003/02/10); www.oscommerce.com
   (c) 2003 nextcommerce (best_sellers.php,v 1.10 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Third Party contributions:
   Enable_Disable_Categories 1.3 Autor: Mikel Williams | mikel@ladykatcostumes.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

// include smarty
include(DIR_FS_BOXES_INC . 'smarty_default.php');

// set cache id
$cache_id = $_SESSION['language'].$current_category_id;

if (MIN_DISPLAY_BESTSELLERS > 0 && (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_best_sellers.html', $cache_id) || !$cache)) {
	
	// include needed functions
	require_once (DIR_FS_INC.'xtc_row_number_format.inc.php');
	
  if (isset($current_category_id) && $current_category_id > 0) {
    $best_sellers_query = "SELECT DISTINCT p.products_id,
                                           p.products_price,
                                           p.products_tax_class_id,
                                           p.products_image,
                                           p.products_vpe,
                                           p.products_vpe_status,
                                           p.products_vpe_value,
                                           pd.products_name
                                      FROM ".TABLE_PRODUCTS." p
                                      JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                           ON p.products_id = pd.products_id
                                              AND trim(pd.products_name) != ''
                                              AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                                      JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                                           ON p.products_id = p2c.products_id
                                      JOIN ".TABLE_CATEGORIES." c
                                           ON p2c.categories_id = c.categories_id
                                              AND c.categories_status = 1
                                              AND (c.categories_id = '" . (int)$current_category_id . "' 
                                                   OR c.parent_id = '" . (int)$current_category_id . "')
                                     WHERE p.products_status = 1
                                       AND p.products_ordered > 0
                                           ".PRODUCTS_CONDITIONS_P."
                                  ORDER BY p.products_ordered desc
                                     LIMIT ".MAX_DISPLAY_BESTSELLERS;
  } else {
    $best_sellers_query = "SELECT DISTINCT p.products_id,
                                           p.products_image,
                                           p.products_price,
                                           p.products_vpe,
                                           p.products_vpe_status,
                                           p.products_vpe_value,
                                           p.products_tax_class_id,
                                           pd.products_name 
                                      FROM ".TABLE_PRODUCTS." p
                                      JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                           ON p.products_id = pd.products_id
                                              AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                                     WHERE p.products_status = 1
                                       AND p.products_ordered > 0
                                           ".PRODUCTS_CONDITIONS_P."
                                  ORDER BY p.products_ordered desc
                                     LIMIT ".MAX_DISPLAY_BESTSELLERS;
  }

  $best_sellers_query = xtDBquery($best_sellers_query);
  $best_sellers_count = xtc_db_num_rows($best_sellers_query, true);
  if ($best_sellers_count > 0) {
    $rows = 0;
    $box_content = array();
    if ($best_sellers_count >= MIN_DISPLAY_BESTSELLERS) {  
      while ($best_sellers = xtc_db_fetch_array($best_sellers_query, true)) {
        $rows ++;
        $best_sellers = array_merge($best_sellers, array('ID' => xtc_row_number_format($rows)));
        $box_content[] = $product->buildDataArray($best_sellers);
      }
    }

    $box_smarty->assign('box_content', $box_content);
  }
}

if (!$cache) {
  $box_best_sellers = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_best_sellers.html');
} else {
  $box_best_sellers = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_best_sellers.html', $cache_id);
}

$smarty->assign('box_BESTSELLERS', $box_best_sellers);
?>