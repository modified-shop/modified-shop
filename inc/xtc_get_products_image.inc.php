<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_products_image.inc.php 1009 2005-07-11 16:19:29Z mz $   

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
   
  function xtc_get_products_image($products_id = '') {

    $product_query = "SELECT products_image 
                        FROM " . TABLE_PRODUCTS . " 
                       WHERE products_id = '" . (int)$products_id . "'";
    $product_query  = xtDBquery($product_query);
    $products_image = xtc_db_fetch_array($product_query,true);

    return $products_image['products_image'];
  }
 ?>