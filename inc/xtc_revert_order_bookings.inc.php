<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_revert_order_bookings($order_id) {
    if ((int)$order_id < 1) {
      return;
    }

    xtc_db_query("DELETE FROM ".TABLE_COUPON_REDEEM_TRACK."
                        WHERE order_id = '".(int)$order_id."'");

    $bookings_query = xtc_db_query("SELECT *
                                      FROM ".TABLE_ORDERS_BOOKINGS."
                                     WHERE orders_id = '".(int)$order_id."'
                                       AND class IN ('ot_gv', 'specials')");
    while ($bookings = xtc_db_fetch_array($bookings_query)) {
      // claimed before it is booked back, so a second run books nothing twice
      xtc_db_query("DELETE FROM ".TABLE_ORDERS_BOOKINGS."
                          WHERE orders_bookings_id = '".(int)$bookings['orders_bookings_id']."'", 'db_link', $claim_info);
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
          // quantity 0 means unlimited: an active one stays so, a used up one comes back on only above 0
          xtc_db_query("UPDATE ".TABLE_SPECIALS."
                           SET status = IF(status = '0' AND specials_quantity < 1 AND specials_quantity + ".(int)$bookings['value']." > 0, '1', status),
                               specials_quantity = specials_quantity + ".(int)$bookings['value']."
                         WHERE specials_id = '".(int)$bookings['reference_id']."'
                           AND NOT (status = '1' AND specials_quantity = 0)");
          break;
      }
    }
  }
?>