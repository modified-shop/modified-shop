<?php
/* -----------------------------------------------------------------------------------------
   $Id: header.php 3808 2012-10-28 20:39:04Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_restock_order($order_id) {
    $order_query = xtc_db_query("SELECT orders_products_id, 
                                        products_id, 
                                        products_quantity 
                                   FROM ".TABLE_ORDERS_PRODUCTS." 
                                  WHERE orders_id = '".(int)$order_id."'");
    while ($order = xtc_db_fetch_array($order_query)) {
      xtc_db_query("UPDATE ".TABLE_PRODUCTS." 
                       SET products_quantity = products_quantity + ".$order['products_quantity'].", 
                           products_ordered = products_ordered - ".$order['products_quantity']." 
                     WHERE products_id = '".$order['products_id']."'");

      if (ATTRIBUTE_STOCK_CHECK == 'true') {
        $orders_attributes_query = xtc_db_query("SELECT orders_products_options_id,
                                                        orders_products_options_values_id
                                                   FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES."
                                                  WHERE orders_id = '" . (int)$order_id . "'
                                                    AND orders_products_id = '" . $order['orders_products_id'] . "'");
        if (xtc_db_num_rows($orders_attributes_query)) {
          while ($orders_attributes = xtc_db_fetch_array($orders_attributes_query)) {
            xtc_db_query("UPDATE ".TABLE_PRODUCTS_ATTRIBUTES."
                             SET attributes_stock = attributes_stock + ".$order['products_quantity']." 
                           WHERE options_id = '" . $orders_attributes['orders_products_options_id'] . "'
                             AND options_values_id = '" . $orders_attributes['orders_products_options_values_id'] . "'
                             AND products_id = '" . $order['products_id'] . "'
                        ");
          }
        }
      }
    }
  }
?>