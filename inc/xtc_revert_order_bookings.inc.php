<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_revert_order_bookings($order_id, $orders_products_id = 0) {
    if ((int)$order_id < 1) {
      return;
    }

    $bookings_where = '';
    if ((int)$orders_products_id > 0) {
      // a single position only gives back its specials quantity
      $bookings_where = " AND orders_products_id = '".(int)$orders_products_id."'
                          AND class = 'specials'";
    } else {
      xtc_db_query("DELETE FROM ".TABLE_COUPON_REDEEM_TRACK."
                          WHERE order_id = '".(int)$order_id."'");
    }

    $bookings_query = xtc_db_query("SELECT *
                                      FROM ".TABLE_ORDERS_BOOKINGS."
                                     WHERE orders_id = '".(int)$order_id."'
                                       AND class IN ('ot_gv', 'specials')
                                       AND value != 0
                                           ".$bookings_where);
    while ($bookings = xtc_db_fetch_array($bookings_query)) {
      // a record booked back keeps value 0, so neither a second run nor the order editor gives it twice
      xtc_db_query("UPDATE ".TABLE_ORDERS_BOOKINGS."
                       SET value = 0
                     WHERE orders_bookings_id = '".(int)$bookings['orders_bookings_id']."'
                       AND value != 0", 'db_link', $claim_info);
      if (!is_array($claim_info) || $claim_info['affected_rows'] != 1) {
        continue;
      }

      switch ($bookings['class']) {
        case 'ot_gv':
          xtc_db_query("UPDATE ".TABLE_COUPON_GV_CUSTOMER."
                           SET amount = amount + ".number_format((float)$bookings['value'], 4, '.', '')."
                         WHERE customer_id = '".(int)$bookings['reference_id']."'");
          break;

        case 'specials':
          // only a special the checkout used up comes back on, not one switched off, expired or saved since the last order that took from it
          $last_query = xtc_db_query("SELECT MAX(o.date_purchased) AS date_purchased
                                        FROM ".TABLE_ORDERS_BOOKINGS." ob
                                        JOIN ".TABLE_ORDERS." o
                                             ON o.orders_id = ob.orders_id
                                       WHERE ob.class = 'specials'
                                         AND ob.reference_id = '".(int)$bookings['reference_id']."'");
          $last = xtc_db_fetch_array($last_query);
          $status_set = '';
          if (is_array($last) && xtc_not_null($last['date_purchased'])) {
            // timestamps have no fractions, a change in the same second counts as later
            $status_set = "status = IF(status = '0'
                                       AND specials_quantity < 1
                                       AND specials_quantity + ".(int)$bookings['value']." > 0
                                       AND (date_status_change IS NULL OR date_status_change < '".xtc_db_input($last['date_purchased'])."')
                                       AND (specials_last_modified IS NULL OR specials_last_modified < '".xtc_db_input($last['date_purchased'])."'), '1', status),";
          }
          // quantity 0 means unlimited, an active one stays so
          xtc_db_query("UPDATE ".TABLE_SPECIALS."
                           SET ".$status_set."
                               specials_quantity = specials_quantity + ".(int)$bookings['value']."
                         WHERE specials_id = '".(int)$bookings['reference_id']."'
                           AND NOT (status = '1' AND specials_quantity = 0)");
          break;
      }
    }
  }
?>