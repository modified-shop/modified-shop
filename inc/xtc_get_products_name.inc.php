<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_products_name.inc.php 1009 2005-07-11 16:19:29Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_get_products_name.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_get_products_name($product_id, $language_id = '') {
    static $products_name_cache;
    
    if (!is_array($products_name_cache)) {
      $products_name_cache = array();
    }
    
    if (empty($language_id) || $language_id == 0) {
      $language_id = $_SESSION['languages_id'];
    }
    
    if (!isset($products_name_cache[$product_id][$language_id])) {  
      $product_query = xtDBquery("SELECT products_name 
                                    FROM " . TABLE_PRODUCTS_DESCRIPTION . " 
                                   WHERE products_id = '" . (int)$product_id . "' 
                                     AND language_id = '" . (int)$language_id . "'");
      $product = xtc_db_fetch_array($product_query, true);
      $products_name_cache[$product_id][$language_id] = $product['products_name'];
    }
    
    return $products_name_cache[$product_id][$language_id];
  }
 ?>