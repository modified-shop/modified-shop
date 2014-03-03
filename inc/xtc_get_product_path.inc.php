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
   (c) 2006 XT-Commerce (xtc_get_product_path.inc.php 1009 2005-07-11)

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
// Construct a category path to the product
// TABLES: products_to_categories
  function xtc_get_product_path($products_id) {
    $cPath = '';

    $category_query = xtDBquery(
      "SELECT p2c.categories_id 
         FROM " . TABLE_CATEGORIES. " c 
         JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
              ON p2c.products_id = '" . (int)$products_id . "'
                 AND p2c.categories_id = c.categories_id
                 AND p2c.categories_id != '0'
                 AND c.categories_status = '1'
         JOIN " . TABLE_PRODUCTS . " p
              ON p.products_id = p2c.products_id
                 AND p.products_status = '1' 
        LIMIT 1"
      );
    
    if (xtc_db_num_rows($category_query,true)) {
      $category = xtc_db_fetch_array($category_query);

      $categories = array();
      xtc_get_parent_categories($categories, $category['categories_id']);

      $categories = array_reverse($categories);

      $cPath = implode('_', $categories);

      if (xtc_not_null($cPath)) $cPath .= '_';
      $cPath .= $category['categories_id'];
    }

    return $cPath;
  }
?>