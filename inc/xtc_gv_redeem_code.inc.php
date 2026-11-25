<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // redeems an active voucher code: one statement switches it off, credits its amount and dates its receipt,
  // so a request redeems it completely or leaves it active; true, false when it cannot be redeemed, null on a database error
  function xtc_gv_redeem_code($customer_id, $coupon_id, $redeem_ip = '') {
    // the joined update needs the balance row; adding nothing changes no balance
    xtc_db_query("INSERT INTO " . TABLE_COUPON_GV_CUSTOMER . " (customer_id, amount) 
                       VALUES ('" . (int)$customer_id . "', 0) 
         ON DUPLICATE KEY UPDATE amount = amount", 'db_link', $query_info);
    if (!is_array($query_info) || $query_info['success'] !== true) {
      return null;
    }

    // the receipt starts without a redeem date; only receipts with a date count as redeemed
    xtc_db_query("INSERT INTO " . TABLE_COUPON_REDEEM_TRACK . " (coupon_id, customer_id, redeem_ip) 
                       VALUES ('" . (int)$coupon_id . "', '" . (int)$customer_id . "', '" . xtc_db_input((string)$redeem_ip) . "')", 'db_link', $query_info);
    if (!is_array($query_info) || $query_info['success'] !== true || (int)$query_info['insert_id'] < 1) {
      return null;
    }
    $receipt_id = (int)$query_info['insert_id'];

    xtc_db_query("UPDATE " . TABLE_COUPONS . " c 
                    JOIN " . TABLE_COUPON_GV_CUSTOMER . " gv 
                         ON gv.customer_id = '" . (int)$customer_id . "' 
                    JOIN " . TABLE_COUPON_REDEEM_TRACK . " crt 
                         ON crt.unique_id = '" . $receipt_id . "' 
                     SET c.coupon_active = 'N', 
                         gv.amount = gv.amount + c.coupon_amount, 
                         crt.redeem_date = NOW() 
                   WHERE c.coupon_id = '" . (int)$coupon_id . "' 
                     AND c.coupon_active = 'Y' 
                     AND crt.redeem_date = '0000-00-00 00:00:00'", 'db_link', $query_info);
    $done = (is_array($query_info) && $query_info['success'] === true);
    $redeemed = ($done && $query_info['affected_rows'] > 0);

    // receipts without a date are left from requests that did not redeem the code; once it is redeemed none of them counts
    xtc_db_query("DELETE FROM " . TABLE_COUPON_REDEEM_TRACK . " 
                        WHERE " . (($redeemed === true) ? "coupon_id = '" . (int)$coupon_id . "'" : "unique_id = '" . $receipt_id . "'") . " 
                          AND redeem_date = '0000-00-00 00:00:00'");

    if ($redeemed === true) {
      return true;
    }
    return (($done === true) ? false : null);
  }
