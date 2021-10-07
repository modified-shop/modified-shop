<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_get_products_stock.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_get_products_stock($products_id) {
    global $modified_cache;
    static $products_quantity_array;

    if (!is_object($modified_cache)) {
      require_once(DIR_FS_CATALOG.'includes/classes/modified_cache.php');
      $_mod_cache_class = strtolower(DB_CACHE_TYPE).'_cache';
      if (!class_exists($_mod_cache_class)) {
        $_mod_cache_class = 'modified_cache';
      }
      $modified_cache = $_mod_cache_class::getInstance();
    }

    if (!isset($products_quantity_array)) {
      $products_quantity_array = array();
    }
    
    if (!isset($products_quantity_array[$products_id])) {
      $id = 'stock_'.$products_id;
      $modified_cache->setID($id);
    
      if ($modified_cache->isHit() === true) {
        $products_quantity_array[$products_id] = $modified_cache->get();
      } else {
        $products_quantity_array[$products_id] = 0;
        $products_query = xtc_db_query("SELECT products_quantity
                                          FROM ".TABLE_PRODUCTS." 
                                         WHERE products_id = '".(int)$products_id."'");
        if (xtc_db_num_rows($products_query) > 0) {
          $products = xtc_db_fetch_array($products_query);
          $products_quantity_array[$products_id] = $products['products_quantity'];
        }
        
        $modified_cache->set($products_quantity_array[$products_id]);
      }
    }
    
    return $products_quantity_array[$products_id];
  }
