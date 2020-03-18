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
    static $products_quantity_array;

    if (!isset($products_quantity_array)) {
      $products_quantity_array = array();
    }
    
    if (!isset($products_quantity_array[$products_id])) {
      $products_query = xtc_db_query("SELECT products_quantity
                                        FROM ".TABLE_PRODUCTS." 
                                       WHERE products_id = '".(int)$products_id."'");
      $products = xtc_db_fetch_array($products_query);
      $products_quantity_array[$products_id] = $products['products_quantity'];
    }
    
    return $products_quantity_array[$products_id];
  }
?>