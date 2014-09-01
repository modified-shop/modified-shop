<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_collect_posts.inc.php 2047 2011-07-25 10:51:53Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce coding standards; www.oscommerce.com
   (c) 2006 XT-Commerce (xtc_db_perform.inc.php 899 2005-04-29)

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c) Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

    function xtc_collect_posts() {
      global $coupon_no, $REMOTE_ADDR, $xtPrice, $cc_id;
      if (!$REMOTE_ADDR) $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
      if ($_POST['gv_redeem_code']) {
        $gv_query = xtc_db_query("select coupon_id,
                                  coupon_amount,
                                  coupon_type,
                                  coupon_minimum_order,
                                  uses_per_coupon,
                                  uses_per_user,
                                  restrict_to_products,
                                  restrict_to_categories
                                  from " . TABLE_COUPONS . "
                                  where coupon_code = '".xtc_db_input($_POST['gv_redeem_code'])."'
                                  and coupon_active = 'Y'");
        $gv_result = xtc_db_fetch_array($gv_query);

        if (xtc_db_num_rows($gv_query) != 0) {
          $redeem_query = xtc_db_query("select * from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . $gv_result['coupon_id'] . "'");
          if ( (xtc_db_num_rows($redeem_query) != 0) && ($gv_result['coupon_type'] == 'G') ) {
            xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'info_message=' . urlencode(ERROR_NO_INVALID_REDEEM_GV), 'NONSSL')); // web28 - 2011-04-14 - change SSL -> NONSSL
          }
        } else {
          xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'info_message=' . urlencode(ERROR_NO_INVALID_REDEEM_GV), 'NONSSL')); // web28 - 2011-04-14 - change SSL -> NONSSL
        }

        // GIFT CODE G START
        if ($gv_result['coupon_type'] == 'G') {
          $gv_amount = $gv_result['coupon_amount'];
          // Things to set
          // ip address of claimant
          // customer id of claimant
          // date
          // redemption flag
          // now update customer account with gv_amount
          $gv_amount_query = xtc_db_query("select amount from " . TABLE_COUPON_GV_CUSTOMER . " where customer_id = '" . $_SESSION['customer_id'] . "'");
          $customer_gv = false;
          $total_gv_amount = $gv_amount;
          if ($gv_amount_result = xtc_db_fetch_array($gv_amount_query)) {
            $total_gv_amount = $gv_amount_result['amount'] + $gv_amount;
            $customer_gv = true;
          }
          $gv_update = xtc_db_query("update " . TABLE_COUPONS . " set coupon_active = 'N' where coupon_id = '" . $gv_result['coupon_id'] . "'");
          $gv_redeem = xtc_db_query("insert into  " . TABLE_COUPON_REDEEM_TRACK . " (coupon_id, customer_id, redeem_date, redeem_ip) values ('" . $gv_result['coupon_id'] . "', '" . $_SESSION['customer_id'] . "', now(),'" . $REMOTE_ADDR . "')");
          if ($customer_gv) {
            // already has gv_amount so update
            $gv_update = xtc_db_query("update " . TABLE_COUPON_GV_CUSTOMER . " set amount = '" . $total_gv_amount . "' where customer_id = '" . $_SESSION['customer_id'] . "'");
          } else {
            // no gv_amount so insert
            $gv_insert = xtc_db_query("insert into " . TABLE_COUPON_GV_CUSTOMER . " (customer_id, amount) values ('" . $_SESSION['customer_id'] . "', '" . $total_gv_amount . "')");
          }
          xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'info=1info_message=' . urlencode(REDEEMED_AMOUNT. $xtPrice->xtcFormat($gv_amount,true,0,true)), 'NONSSL')); // web28 - 2011-04-13  New  class distinction  error / info // web28 - 2011-04-14 - change SSL -> NONSSL


      } else {

        if (xtc_db_num_rows($gv_query)==0) {
          xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'info_message=' . urlencode(ERROR_NO_INVALID_REDEEM_COUPON), 'NONSSL')); // web28 - 2011-04-14 - change SSL -> NONSSL
        }

        // web28 - 2011-11-06 - FIX: only active coupons
        $date_query=xtc_db_query("select coupon_start_date 
                                    from " . TABLE_COUPONS . " 
                                   where coupon_start_date <= now() 
                                     and coupon_code='".xtc_db_input($_POST['gv_redeem_code'])."'
                                     and coupon_active = 'Y'
                                 ");

        if (xtc_db_num_rows($date_query)==0) {
          xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'info_message=' . urlencode(ERROR_INVALID_STARTDATE_COUPON), 'NONSSL')); // web28 - 2011-04-14 - change SSL -> NONSSL
        }
        
        // web28 - 2011-11-06 - FIX: only active coupons
        $date_query=xtc_db_query("select coupon_expire_date 
                                    from " . TABLE_COUPONS . " 
                                   where coupon_expire_date >= now() 
                                     and coupon_code='".xtc_db_input($_POST['gv_redeem_code'])."'
                                     and coupon_active = 'Y'
                                 ");
                                 
        if (xtc_db_num_rows($date_query)==0) {
          xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'info_message=' . urlencode(ERROR_INVALID_FINISDATE_COUPON), 'NONSSL')); // web28 - 2011-04-14 - change SSL -> NONSSL
        }

        $coupon_count = xtc_db_query("select coupon_id from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . $gv_result['coupon_id']."'");
        $coupon_count_customer = xtc_db_query("select coupon_id from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . $gv_result['coupon_id']."' and customer_id = '" . $_SESSION['customer_id'] . "'");

        if (xtc_db_num_rows($coupon_count)>=$gv_result['uses_per_coupon'] && $gv_result['uses_per_coupon'] > 0) {
          xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'info_message=' . urlencode(ERROR_INVALID_USES_COUPON . $gv_result['uses_per_coupon'] . TIMES ), 'NONSSL')); // web28 - 2011-04-14 - change SSL -> NONSSL
        }
        if (xtc_db_num_rows($coupon_count_customer)>=$gv_result['uses_per_user'] && $gv_result['uses_per_user'] > 0) {
          xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'info_message=' . urlencode(ERROR_INVALID_USES_USER_COUPON . $gv_result['uses_per_user'] . TIMES ), 'NONSSL')); // web28 - 2011-04-14 - change SSL -> NONSSL
        }
        if ($gv_result['coupon_type']=='S') {
            $coupon_amount = $order->info['shipping_cost'];
        } else {
            $coupon_amount = $gv_result['coupon_amount'] . ' ';
        }
        if ($gv_result['coupon_type']=='P') {
          $coupon_amount = $gv_result['coupon_amount'] . '% ';
        }
        if ($gv_result['coupon_minimum_order']>0) { 
          $coupon_amount .= 'on orders greater than ' . $gv_result['coupon_minimum_order'];
        }
        //if (!xtc_session_is_registered('cc_id')) xtc_session_register('cc_id'); //Fred - this was commented out before  // Hetfield - 2009-08-19 - removed deprecated function session_is_registered and session_register to be ready for PHP >= 5.3
        $_SESSION['cc_id'] = $gv_result['coupon_id']; //Fred ADDED, set the global and session variable
        xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'info_message=' . urlencode(REDEEMED_COUPON), 'NONSSL')); // web28 - 2011-04-14 - change SSL -> NONSSL

    }

     }
     if ($_POST['submit_redeem_x'] && $gv_result['coupon_type'] == 'G') {
       xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'info_message=' . urlencode(ERROR_NO_REDEEM_CODE), 'NONSSL'));
     } // web28 - 2011-04-14 - change SSL -> NONSSL
   }
?>