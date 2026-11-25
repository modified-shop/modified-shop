<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // switches an active voucher code off and credits its amount in one statement:
  // only one request redeems it, and a stop after the statement cannot lose the credit
  function xtc_gv_redeem_code($customer_id, $coupon_id) {
    // the joined update needs the balance row; adding nothing changes no balance
    xtc_db_query("INSERT INTO " . TABLE_COUPON_GV_CUSTOMER . " (customer_id, amount) 
                       VALUES ('" . (int)$customer_id . "', 0) 
         ON DUPLICATE KEY UPDATE amount = amount");

    xtc_db_query("UPDATE " . TABLE_COUPONS . " c 
                    JOIN " . TABLE_COUPON_GV_CUSTOMER . " gv 
                         ON gv.customer_id = '" . (int)$customer_id . "' 
                     SET c.coupon_active = 'N', 
                         gv.amount = gv.amount + c.coupon_amount 
                   WHERE c.coupon_id = '" . (int)$coupon_id . "' 
                     AND c.coupon_active = 'Y'", 'db_link', $query_info);

    return (is_array($query_info) && $query_info['success'] === true && $query_info['affected_rows'] > 0);
  }
