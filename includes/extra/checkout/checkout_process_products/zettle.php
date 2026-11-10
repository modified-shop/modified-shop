<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (defined('MODULE_CATEGORIES_ZETTLE_CATEGORIES_STATUS')
      && MODULE_CATEGORIES_ZETTLE_CATEGORIES_STATUS == 'true'
      )
  {
    // include needed classes
    require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalZettle.php');
    
    if (!isset($PayPalZettle)) {
      $PayPalZettle = new PayPalZettle();
    }
    
    $zettle_query = xtc_db_query("SELECT *
                                    FROM ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                                   WHERE products_id = '".xtc_get_prid($order->products[$i]['id'])."'");
    if (xtc_db_num_rows($zettle_query) > 0) {
      $zettle = xtc_db_fetch_array($zettle_query);
      
      if ($zettle['stock'] == 1) {
        // xtc_get_products_stock() caches per request and misses later lines of the same product
        $stock_query = xtc_db_query("SELECT products_quantity
                                       FROM ".TABLE_PRODUCTS."
                                      WHERE products_id = '".xtc_get_prid($order->products[$i]['id'])."'");
        if (xtc_db_num_rows($stock_query) > 0) {
          $products_data = xtc_db_fetch_array($stock_query);
          $PayPalZettle->updateInventory($zettle['products_uuid'], $products_data);
        }
      }
    }
  }