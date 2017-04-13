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
$cache_id = md5($_SESSION['language'].$current_category_id);

if (MIN_DISPLAY_BESTSELLERS > 0 && (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_best_sellers.html', $cache_id) || !$cache)) {
	
	// include needed functions
	require_once (DIR_FS_INC.'xtc_row_number_format.inc.php');
  
  $join_where = $select = $join = $where = '';
  $order_by = "p.products_ordered";
  $products_id_array = array();
  if (MAX_DISPLAY_BESTSELLERS_DAYS != '0') {
    $orders_query = "SELECT op.products_id,
                            op.products_quantity 
                       FROM ".TABLE_ORDERS_PRODUCTS." op
                       JOIN ".TABLE_ORDERS." o
                            ON o.orders_id = op.orders_id
                               AND o.date_purchased > '".date("Y-m-d", mktime(1, 1, 1, date("m"), date("d") - MAX_DISPLAY_BESTSELLERS_DAYS, date("Y")))."'
                   GROUP BY op.products_id
                   ORDER BY op.orders_id DESC";
    $orders_query = xtc_db_query($orders_query);
    while ($orders = xtc_db_fetch_array($orders_query)) {
      $products_id_array[] = $orders['products_id'];
    }
    if (count($products_id_array) > 0) {
      $select = "count(op.products_quantity) as ordered, ";
      $join = " JOIN ".TABLE_ORDERS_PRODUCTS." op
                     ON op.products_id = p.products_id ";
      $join_where = " AND p2c.products_id IN ('".implode("', '", $products_id_array)."') ";
      $where = " AND p.products_id IN ('".implode("', '", $products_id_array)."') ";
      $order_by = "ordered";
    }
	}
	
	$check_num = 0;
  if (isset($current_category_id) && $current_category_id > 0) {
    $best_sellers_query = "SELECT ".$select."
                                  p.products_id,
                                  p.products_price,
                                  p.products_tax_class_id,
                                  p.products_image,
                                  p.products_vpe,
                                  p.products_vpe_status,
                                  p.products_vpe_value,
                                  pd.products_name
                             FROM ".TABLE_PRODUCTS." p
                                  ".$join."
                             JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                  ON p.products_id = pd.products_id
                                     AND trim(pd.products_name) != ''
                                     AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                             JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                                  ON p.products_id = p2c.products_id
                                     ".$join_where."
                             JOIN ".TABLE_CATEGORIES." c
                                  ON p2c.categories_id = c.categories_id
                                     AND c.categories_status = 1
                                     AND (c.categories_id = '" . (int)$current_category_id . "' 
                                          OR c.parent_id = '" . (int)$current_category_id . "')
                            WHERE p.products_status = 1
                              AND p.products_ordered > 0
                                  ".PRODUCTS_CONDITIONS_P."
                         GROUP BY p.products_id
                         ORDER BY ".$order_by." DESC
                            LIMIT ".MAX_DISPLAY_BESTSELLERS;
    $check_result = xtDBquery($best_sellers_query);
    $check_num = xtc_db_num_rows($check_result, true);
    if ($check_num == 1 && count($products_id_array) > 0) {
      $check = xtc_db_fetch_array($check_result, true);
      $check_num = $check['ordered'];
    }
  }
  
  if ($check_num < 1) {
    $best_sellers_query = "SELECT ".$select."
                                  p.products_id,
                                  p.products_image,
                                  p.products_price,
                                  p.products_vpe,
                                  p.products_vpe_status,
                                  p.products_vpe_value,
                                  p.products_tax_class_id,
                                  pd.products_name 
                             FROM ".TABLE_PRODUCTS." p
                                  ".$join."
                             JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                  ON p.products_id = pd.products_id
                                     AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                            WHERE p.products_status = 1
                              AND p.products_ordered > 0
                                  ".$where."
                                  ".PRODUCTS_CONDITIONS_P."
                         GROUP BY p.products_id
                         ORDER BY ".$order_by." DESC
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