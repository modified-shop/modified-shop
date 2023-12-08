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
    $out_of_stock = '';
    $stock_check_query = xtc_db_query("SELECT specials_quantity
                                         FROM ".TABLE_SPECIALS."
                                        WHERE products_id = '".(int)$products_id."'
                                              ".SPECIALS_CONDITIONS);
    if (xtc_db_num_rows($stock_check_query) > 0) {
      $stock_check = xtc_db_fetch_array($stock_check_query);

      if ($stock_check['specials_quantity'] < (int)$quantity) {
        $out_of_stock = '<span class="markProductOutOfStock">' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</span>';
      }
    }

    return $out_of_stock;
  }
