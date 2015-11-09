<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_count_products_in_category.inc.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $   

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
    $products_count = 0;

    // products status
    $products_status = $include_inactive ? '' : " AND p.products_status = '1' ";

    $products_query = xtDBquery("SELECT count(*) as total 
                                   FROM ".TABLE_PRODUCTS." p
                                   JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                        ON p.products_id = pd.products_id
                                           AND pd.language_id = '".(int)$_SESSION['languages_id']."' 
                                           AND trim(pd.products_name) != ''                      
                                   JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c 
                                        ON p.products_id = p2c.products_id
                                           AND p2c.categories_id = '".(int)$category_id."'
                                        ".$products_status."
                                        ".PRODUCTS_CONDITIONS_P); 
    $products = xtc_db_fetch_array($products_query, true);
    $products_count += $products['total'];
  
    // check sub categories		
    $child_categories_query = xtDBquery("SELECT c.categories_id
                                           FROM ".TABLE_CATEGORIES." c
                                          WHERE c.parent_id = '".(int)$category_id."'
                                                ".CATEGORIES_CONDITIONS_C);
    if (xtc_db_num_rows($child_categories_query, true)) {
      while ($child_categories = xtc_db_fetch_array($child_categories_query, true)) {
        $products_count += xtc_count_products_in_category($child_categories['categories_id'], $include_inactive);
      }
    }

    return $products_count;
  }
?>