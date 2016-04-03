<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2014 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  function get_order_total($orders_id) {
    $total = '0';
    $orders_total_query = xtc_db_query("SELECT value
                                          FROM " . TABLE_ORDERS_TOTAL . "
                                         WHERE class IN ('ot_total', 'ot_subtotal_no_tax', 'ot_subtotal')
                                           AND orders_id = '".$orders_id."'
                                      ORDER BY sort_order DESC
                                         LIMIT 1");
    if (xtc_db_num_rows($orders_total_query) > 0) {                                    
      $orders_total = xtc_db_fetch_array($orders_total_query);
      $total = $orders_total['value'];
    }
    return $total;
  }
?>