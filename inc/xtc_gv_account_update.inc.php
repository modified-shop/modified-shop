<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_gv_account_update.inc.php 899 2005-04-29 02:40:57Z hhgag $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(application_top.php,v 1.273 2003/05/19); www.oscommerce.com
   (c) 2003     nextcommerce (application_top.php,v 1.54 2003/08/25); www.nextcommerce.org

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c  Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org


   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

   // Update the Customers GV account
  function xtc_gv_account_update($customer_id, $gv_id) {
    $customer_gv_query = xtc_db_query("SELECT amount 
                                         FROM " . TABLE_COUPON_GV_CUSTOMER . " 
                                        WHERE customer_id = '" . (int)$customer_id . "'");
    $coupon_gv_query = xtc_db_query("SELECT coupon_amount 
                                       FROM " . TABLE_COUPONS . " 
                                      WHERE coupon_id = '" . (int)$gv_id . "'");
    $coupon_gv = xtc_db_fetch_array($coupon_gv_query);
    if (xtc_db_num_rows($customer_gv_query) > 0) {
      $customer_gv = xtc_db_fetch_array($customer_gv_query);
      $new_gv_amount = $customer_gv['amount'] + $coupon_gv['coupon_amount'];
      //prepare for DB insert
      $new_gv_amount = str_replace(",", ".", $new_gv_amount);
      $gv_query = xtc_db_query("UPDATE " . TABLE_COUPON_GV_CUSTOMER . " SET amount = '" . $new_gv_amount . "' WHERE customer_id = '" . (int)$customer_id . "'");
    } else {
      $gv_query = xtc_db_query("INSERT INTO " . TABLE_COUPON_GV_CUSTOMER . " (customer_id, amount) VALUES ('" . (int)$customer_id . "', '" . $coupon_gv['coupon_amount'] . "')");
    }
  }
?>