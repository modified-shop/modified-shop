<?php
/* -----------------------------------------------------------------------------------------
   $Id$

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
$cache_id = md5('lID:'.$_SESSION['language'].'|csID:'.$_SESSION['customers_status']['customers_status_id'].'|curr:'.$_SESSION['currency'].'|cID:'.$current_category_id.'|country:'.((isset($_SESSION['country'])) ? $_SESSION['country'] : ((isset($_SESSION['customer_country_id'])) ? $_SESSION['customer_country_id'] : STORE_COUNTRY)));

if (MIN_DISPLAY_BESTSELLERS > 0
    && MAX_DISPLAY_BESTSELLERS > 0
    && (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_best_sellers.html', $cache_id) || !$cache)
    ) 
{	
	// include needed functions
	require_once (DIR_FS_INC.'xtc_row_number_format.inc.php');
  
  $select = $join = '';
  $where = " AND p.products_ordered > 0 ";
  $order_by = "p.products_ordered";
  if (MAX_DISPLAY_BESTSELLERS_DAYS != '0') {
    $date_bestsellers = date(
      'Y-m-d',
      mktime(1, 1, 1, date('m'), date('d') - (int)MAX_DISPLAY_BESTSELLERS_DAYS, date('Y'))
    );
    $select = "sales.ordered, ";
    $join = "JOIN (
                     SELECT op.products_id,
                            SUM(op.products_quantity) AS ordered
                       FROM ".TABLE_ORDERS." o
                       JOIN ".TABLE_ORDERS_PRODUCTS." op
                         ON op.orders_id = o.orders_id
                      WHERE o.date_purchased > '".$date_bestsellers."'
                   GROUP BY op.products_id
                   ) sales
                ON sales.products_id = p.products_id";
    $order_by = "sales.ordered";
    $where = '';
  }

  $best_sellers_result = false;
  if (isset($current_category_id) && $current_category_id > 0) {
    $best_sellers_query = "SELECT ".$select."
                                  ".$product->default_select."
                             FROM ".TABLE_PRODUCTS." p
                                  ".$join."
                             JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                  ON p.products_id = pd.products_id
                                     AND trim(pd.products_name) != ''
                                     AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                            WHERE p.products_status = 1
                                  ".$where."
                                  ".PRODUCTS_CONDITIONS_P."
                              AND EXISTS (
                                    SELECT 1
                                      FROM ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                                      JOIN ".TABLE_CATEGORIES." c
                                        ON c.categories_id = p2c.categories_id
                                       AND c.categories_status = 1
                                       AND (c.categories_id = '".(int)$current_category_id."'
                                            OR c.parent_id = '".(int)$current_category_id."')
                                           ".CATEGORIES_CONDITIONS_C."
                                     WHERE p2c.products_id = p.products_id
                                  )
                         ORDER BY ".$order_by." DESC
                            LIMIT ".MAX_DISPLAY_BESTSELLERS;
    
    $best_sellers_result = xtDBquery($best_sellers_query);
  }
  
  if ($best_sellers_result === false
      || xtc_db_num_rows($best_sellers_result, true) < 1
      )
  {
    $best_sellers_query = "SELECT ".$select."
                                  ".$product->default_select."
                             FROM ".TABLE_PRODUCTS." p
                                  ".$join."
                             JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                  ON p.products_id = pd.products_id
                                     AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                                     AND trim(pd.products_name) != ''
                            WHERE p.products_status = 1
                                  ".$where."
                                  ".PRODUCTS_CONDITIONS_P."
                              AND EXISTS (
                                    SELECT 1
                                      FROM ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                                      JOIN ".TABLE_CATEGORIES." c
                                        ON c.categories_id = p2c.categories_id
                                       AND c.categories_status = 1
                                           ".CATEGORIES_CONDITIONS_C."
                                     WHERE p2c.products_id = p.products_id
                                  )
                         ORDER BY ".$order_by." DESC
                            LIMIT ".MAX_DISPLAY_BESTSELLERS;

    $best_sellers_result = xtDBquery($best_sellers_query);
  }

  if (MAX_DISPLAY_BESTSELLERS_DAYS != '0'
      && xtc_db_num_rows($best_sellers_result, true) < 1
      )
  {
    $best_sellers_query = "SELECT ".$product->default_select."
                             FROM ".TABLE_PRODUCTS." p
                             JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                               ON p.products_id = pd.products_id
                              AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                              AND trim(pd.products_name) != ''
                            WHERE p.products_status = 1
                              AND p.products_ordered > 0
                                ".PRODUCTS_CONDITIONS_P."
                              AND EXISTS (
                                    SELECT 1
                                      FROM ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                                      JOIN ".TABLE_CATEGORIES." c
                                        ON c.categories_id = p2c.categories_id
                                       AND c.categories_status = 1
                                           ".CATEGORIES_CONDITIONS_C."
                                     WHERE p2c.products_id = p.products_id
                                  )
                         ORDER BY p.products_ordered DESC
                            LIMIT ".MAX_DISPLAY_BESTSELLERS;

    $best_sellers_result = xtDBquery($best_sellers_query);
  }

  $best_sellers_count = xtc_db_num_rows($best_sellers_result, true);
  if ($best_sellers_count > 0) {
    $rows = 0;
    $box_content = array();
    if ($best_sellers_count >= MIN_DISPLAY_BESTSELLERS) {  
      while ($best_sellers = xtc_db_fetch_array($best_sellers_result, true)) {
        $box_content[$rows] = $product->buildDataArray($best_sellers);
        $box_content[$rows] = array_merge($box_content[$rows], array('ID' => xtc_row_number_format($rows + 1), 'COUNT' => xtc_row_number_format($rows + 1)));
        $rows ++;
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
