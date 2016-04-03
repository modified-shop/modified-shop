<?php
/* -----------------------------------------------------------------------------------------
   $Id: header.php 3808 2012-10-28 20:39:04Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_restock_order($order_id, $reactivate_product = true) {
    $order_query = xtc_db_query("SELECT orders_products_id, 
                                        products_id, 
                                        products_quantity 
                                   FROM ".TABLE_ORDERS_PRODUCTS." 
                                  WHERE orders_id = '".(int)$order_id."'");
    while ($order = xtc_db_fetch_array($order_query)) {
      $products_update = true;
      $orders_attributes_query = xtc_db_query("SELECT pa.orders_products_options_id,
                                                      pa.orders_products_options_values_id,
                                                      opd.orders_products_filename
                                                 FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES." pa
                                            LEFT JOIN ".TABLE_ORDERS_PRODUCTS_DOWNLOAD." opd 
                                                      ON opd.orders_products_id = pa.orders_products_id
                                                WHERE pa.orders_id = '" . (int)$order_id . "'
                                                  AND pa.orders_products_id = '" . $order['orders_products_id'] . "'");
      if (xtc_db_num_rows($orders_attributes_query) > 0) {
        while ($orders_attributes = xtc_db_fetch_array($orders_attributes_query)) {
          if ($orders_attributes['orders_products_filename'] == '') {
            xtc_db_query("UPDATE ".TABLE_PRODUCTS_ATTRIBUTES."
                             SET attributes_stock = attributes_stock + ".$order['products_quantity']." 
                           WHERE options_id = '" . $orders_attributes['orders_products_options_id'] . "'
                             AND options_values_id = '" . $orders_attributes['orders_products_options_values_id'] . "'
                             AND products_id = '" . $order['products_id'] . "'");
          } else {
            $products_update = false;
          }
        }
      }
      if ($products_update) {
        $set_sql = ($reactivate_product ? ' products_status = 1, ' : '');
        xtc_db_query("UPDATE ".TABLE_PRODUCTS."
                         SET ".$set_sql."
                             products_quantity = products_quantity + ".$order['products_quantity'].", 
                             products_ordered = products_ordered - ".$order['products_quantity']." 
                       WHERE products_id = '".$order['products_id']."'");
      }
    }
  }
?>