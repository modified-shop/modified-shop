<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_get_product_path.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2003 XT-Commerce (xtc_get_product_path.inc.php 1009 2005-07-11)
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

function xtc_get_product_path($products_id) {
  global $canonical_flag, $products_link_cat_id;
  
  $cPath = '';
  if (isset($_SESSION['CatPath']) 
      && trim($_SESSION['CatPath']) != '' 
      && !$canonical_flag
      && !$products_link_cat_id
      ) 
  {
    $cPath_array = xtc_parse_category_path($_SESSION['CatPath']);
    $check_query = xtDBquery("SELECT count(*) as total 
                                FROM ".TABLE_PRODUCTS_TO_CATEGORIES." 
                               WHERE categories_id = '".(int)$cPath_array[(sizeof($cPath_array) - 1)]."'
                                 AND products_id = '".(int)$products_id."'");
    $check = xtc_db_fetch_array($check_query, true);
    if ($check['total'] > 0) {
      return $_SESSION['CatPath'];
    }
  }
  
  $limit = (($canonical_flag === true || !isset($_SESSION['CatPath']) || !$products_link_cat_id) ? '' : " LIMIT 1");
  
  // set $category_check by $products_link_cat_id
  $category_check = (($products_link_cat_id > 0) ? " AND p2c.categories_id = '".(int)$products_link_cat_id."'" : '');
  
  // canonical
  $add_select = ((defined('PRODUCTS_CANONICAL_CAT_ID') && PRODUCTS_CANONICAL_CAT_ID === true) ? "p.products_canonical_cat_id," : '');
  
  $category_query = "SELECT ".$add_select."
                            p2c.categories_id
                       FROM " . TABLE_CATEGORIES . " c 
                       JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c 
                         ON p2c.categories_id != '0' 
                            AND p2c.categories_id = c.categories_id 
                            AND c.categories_status = '1' 
                                ".$category_check."
                       JOIN " . TABLE_PRODUCTS ." p 
                            ON p.products_id = p2c.products_id 
                               AND p.products_id = '" . (int)$products_id . "' 
                               AND p.products_status = '1' 
                            ".$limit;

  $category_query  = xtDBquery($category_query);
  if (xtc_db_num_rows($category_query, true)) {
    $first = true;
    while ($category = xtc_db_fetch_array($category_query, true)) {
      // fallback: use first hit if products_canonical_cat_id isn't set
      if ($first === true) {
        $cat_id = $category['categories_id'];
        $first = false;
      } 
      if (($canonical_flag || !isset($_SESSION['CatPath'])) 
          && !$products_link_cat_id 
          && isset($category['products_canonical_cat_id']) 
          && $category['products_canonical_cat_id'] > 0
          )
      {
        $cat_id = $category['products_canonical_cat_id'];            
        break;
      }
    }
    $category['categories_id'] = $cat_id;
    $cPath = xtc_get_category_path($category['categories_id']);
  }
  
  return $cPath;
}
?>