<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2014 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  function check_stock_specials($products_id, $quantity) {
    $stock_check_query = xtc_db_query("SELECT specials_quantity
                                         FROM ".TABLE_SPECIALS."
                                        WHERE products_id = '".(int)$products_id."'");
    $stock_check = xtc_db_fetch_array($stock_check_query);
    
    $out_of_stock = '';
    if ($stock_check['specials_quantity'] < (int)$quantity) {
      $out_of_stock = '<span class="markProductOutOfStock">' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</span>';
    }
    
    return $out_of_stock;
  }
?>