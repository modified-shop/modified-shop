<?php
/* -----------------------------------------------------------------------------------------
   $Id$

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
  function xtc_gv_account_update($customer_id, $coupon_id) {
    $coupon_query = xtc_db_query("SELECT coupon_amount 
                                       FROM " . TABLE_COUPONS . " 
                                      WHERE coupon_id = '" . (int)$coupon_id . "'");
    $coupon = xtc_db_fetch_array($coupon_query);
    
    if (!is_array($coupon)) {
      return;
    }

    // one statement adds to the row or creates it, so redemptions at the same time cannot overwrite each other
    $gv_amount = number_format((float)$coupon['coupon_amount'], 4, '.', '');
    xtc_db_query("INSERT INTO " . TABLE_COUPON_GV_CUSTOMER . " (customer_id, amount) 
                       VALUES ('" . (int)$customer_id . "', '" . $gv_amount . "') 
         ON DUPLICATE KEY UPDATE amount = amount + " . $gv_amount);
  }
?>