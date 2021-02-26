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
   (c) 2003	 nextcommerce (xtc_count_products_in_category.inc.php,v 1.3 2003/08/13); www.nextcommerce.org 
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_count_products_in_category($category_id, $include_inactive = false) {
    static $products_count_array, $products_in_category_array;
    
    $active = (($include_inactive === false) ? 0 : 1);

    if (!is_array($products_count_array)) {
      $products_count_array = array();
    }

    if (!is_array($products_in_category_array)) {
      $products_in_category_array = array();
    }

    if (!isset($products_in_category_array[$active])) {
      $categories_query = xtDBquery("SELECT count(*) as total,
                                            p2c.categories_id
                                       FROM ".TABLE_PRODUCTS." p
                                       JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                            ON p.products_id = pd.products_id
                                               AND pd.language_id = '".(int)$_SESSION['languages_id']."' 
                                               AND trim(pd.products_name) != ''
                                       JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                                            ON p2c.products_id = p.products_id
                                      WHERE (p.products_status = '1'".(($include_inactive === true) ? " OR p.products_status = '0'" : '').")
                                            ".PRODUCTS_CONDITIONS_P."
                                   GROUP BY p2c.categories_id");
      if (xtc_db_num_rows($categories_query, true)) {
        while ($categories = xtc_db_fetch_array($categories_query, true)) {
          $products_in_category_array[$active][$categories['categories_id']] = $categories['total'];
        }
      }
    }
    
    if (!isset($products_count_array[$active][$category_id])) {
      $products_count_array[$active][$category_id] = 0;
      $products_count_array[$active][$category_id] += ((isset($products_in_category_array[$active][$category_id])) ? $products_in_category_array[$active][$category_id] : 0);
      
      // check sub categories		
      $child_categories_query = xtDBquery("SELECT c.categories_id
                                             FROM ".TABLE_CATEGORIES." c
                                             JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd
                                                  ON c.categories_id = cd.categories_id
                                                     AND cd.language_id = '".(int)$_SESSION['languages_id']."' 
                                                     AND trim(cd.categories_name) != ''
                                            WHERE c.parent_id = '".(int)$category_id."'
                                                  ".CATEGORIES_CONDITIONS_C);
      if (xtc_db_num_rows($child_categories_query, true)) {
        while ($child_categories = xtc_db_fetch_array($child_categories_query, true)) {
          $products_count_array[$active][$category_id] += xtc_count_products_in_category($child_categories['categories_id'], $include_inactive);
        }
      }
    }
    
    return $products_count_array[$active][$category_id];
  }
?>