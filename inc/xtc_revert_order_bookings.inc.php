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
          // the creation date stored at checkout tells the special from a later one that got its id again
          $reference_date = ((isset($bookings['reference_date']) && xtc_not_null($bookings['reference_date'])) ? "'".xtc_db_input($bookings['reference_date'])."'" : 'NULL');
          // only a special the checkout switched off comes back on, and only within its dates
          // quantity 0 means unlimited, an active one stays so
          xtc_db_query("UPDATE ".TABLE_SPECIALS."
                           SET status = IF(status = '0'
                                           AND specials_sold_out = '1'
                                           AND specials_quantity + ".(int)$bookings['value']." > 0
                                           AND (start_date <= now() OR start_date IS NULL OR start_date = 0)
                                           AND (expires_date >= now() OR expires_date IS NULL OR expires_date = 0), '1', status),
                               specials_quantity = specials_quantity + ".(int)$bookings['value']."
                         WHERE specials_id = '".(int)$bookings['reference_id']."'
                           AND NOT (status = '1' AND specials_quantity = 0)
                           AND specials_date_added <=> ".$reference_date, 'db_link', $specials_info);
          // a special that got the id of the original again is left alone
          if (!is_array($specials_info) || $specials_info['affected_rows'] != 1) {
            break;
          }
          // the mark only belongs to an inactive special
          xtc_db_query("UPDATE ".TABLE_SPECIALS."
                           SET specials_sold_out = '0'
                         WHERE specials_id = '".(int)$bookings['reference_id']."'
                           AND specials_sold_out = '1'
                           AND status = '1'", 'db_link', $active_info);
          if (is_array($active_info) && $active_info['affected_rows'] == 1) {
            // the special is on again, so modules like zettle can send its price
            $specials_query = xtc_db_query("SELECT specials_id,
                                                   products_id
                                              FROM ".TABLE_SPECIALS."
                                             WHERE specials_id = '".(int)$bookings['reference_id']."'");
            $specials = xtc_db_fetch_array($specials_query);
            foreach(auto_include(DIR_FS_CATALOG.'includes/extra/modules/revert_order_bookings_specials/','php') as $file) require ($file);
          }
          break;
      }
    }
  }
?>