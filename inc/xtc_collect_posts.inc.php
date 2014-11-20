<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_collect_posts.inc.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

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
      global $coupon_no, $xtPrice, $cc_id;

      if ($_POST['gv_redeem_code']) {
        unset($_SESSION['cc_id']);
        $gv_query = xtc_db_query("SELECT coupon_id,
                                         coupon_amount,
                                         coupon_type,
                                         coupon_minimum_order,
                                         uses_per_coupon,
                                         uses_per_user,
                                         restrict_to_products,
                                         restrict_to_categories
                                    FROM " . TABLE_COUPONS . "
                                   WHERE coupon_code = '".xtc_db_input($_POST['gv_redeem_code'])."'
                                     AND coupon_active = 'Y'");
        $gv_result = xtc_db_fetch_array($gv_query);

        if (xtc_db_num_rows($gv_query) != 0) {
          $redeem_query = xtc_db_query("SELECT * 
                                          FROM " . TABLE_COUPON_REDEEM_TRACK . " 
                                         WHERE coupon_id = '" . $gv_result['coupon_id'] . "'");
          if ( (xtc_db_num_rows($redeem_query) != 0) && ($gv_result['coupon_type'] == 'G') ) {
            xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'coupon_message='.strtolower('ERROR_NO_INVALID_REDEEM_GV'), 'NONSSL'));
          }
        } else {
          xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'coupon_message='.strtolower('ERROR_NO_INVALID_REDEEM_GV'), 'NONSSL'));
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
          $gv_amount_query = xtc_db_query("SELECT amount 
                                             FROM " . TABLE_COUPON_GV_CUSTOMER . " 
                                            WHERE customer_id = '" . (int)$_SESSION['customer_id'] . "'");
          $customer_gv = false;
          $total_gv_amount = $gv_amount;
          if ($gv_amount_result = xtc_db_fetch_array($gv_amount_query)) {
            $total_gv_amount = $gv_amount_result['amount'] + $gv_amount;
            $customer_gv = true;
          }
          $gv_update = xtc_db_query("UPDATE " . TABLE_COUPONS . " SET coupon_active = 'N' WHERE coupon_id = '" . $gv_result['coupon_id'] . "'");
          
          $sql_data_array = array(
             'coupon_id' => $gv_result['coupon_id'], 
             'redeem_date' => 'now()',  
             'redeem_ip' => (isset($_SESSION['tracking']['ip']) xtc_db_prepare_input($_SESSION['tracking']['ip']) : ''),  
             'customer_id' => (int)$_SESSION['customer_id']  
          );
          $gv_redeem = xtc_db_perform(TABLE_COUPON_REDEEM_TRACK, $sql_data_array);
          if ($customer_gv) {
            // already has gv_amount so update
            $gv_update = xtc_db_query("UPDATE " . TABLE_COUPON_GV_CUSTOMER . " SET amount = '" . $total_gv_amount . "' WHERE customer_id = '" . (int)$_SESSION['customer_id'] . "'");
          } else {
            // no gv_amount so insert
            $sql_data_array = array(
               'customer_id' => (int)$_SESSION['customer_id'],
               'amount' => $total_gv_amount               
            );
            $gv_insert = xtc_db_perform(TABLE_COUPON_GV_CUSTOMER, $sql_data_array);
          }
          xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'info=1&coupon_message='.strtolower('REDEEMED_AMOUNT').'&add_info='.urlencode($xtPrice->xtcFormat($gv_amount,true,0,true)), 'NONSSL'));

      } else {

        if (xtc_db_num_rows($gv_query)==0) {
          xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'coupon_message='.strtolower('ERROR_NO_INVALID_REDEEM_COUPON'), 'NONSSL'));
        }

        $date_query=xtc_db_query("SELECT coupon_start_date
                                    FROM " . TABLE_COUPONS . "
                                   WHERE coupon_start_date <= now()
                                     AND coupon_code='".xtc_db_input($_POST['gv_redeem_code'])."'
                                     AND coupon_active = 'Y'
                                 ");
        if (xtc_db_num_rows($date_query)==0) {
          xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'coupon_message='.strtolower('ERROR_INVALID_STARTDATE_COUPON'), 'NONSSL'));
        }

        $date_query=xtc_db_query("SELECT coupon_expire_date
                                    FROM " . TABLE_COUPONS . "
                                   WHERE coupon_expire_date >= now()
                                     AND coupon_code='".xtc_db_input($_POST['gv_redeem_code'])."'
                                     AND coupon_active = 'Y'
                                 ");
        if (xtc_db_num_rows($date_query)==0) {
          xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'coupon_message='.strtolower('ERROR_INVALID_FINISDATE_COUPON'), 'NONSSL'));
        }

        $coupon_count = xtc_db_query("SELECT coupon_id 
                                        FROM " . TABLE_COUPON_REDEEM_TRACK . " 
                                       WHERE coupon_id = '" . $gv_result['coupon_id']."'");
        $coupon_count_customer = xtc_db_query("SELECT coupon_id 
                                                 FROM " . TABLE_COUPON_REDEEM_TRACK . " 
                                                WHERE coupon_id = '" . $gv_result['coupon_id']."' 
                                                  AND customer_id = '" . (int)$_SESSION['customer_id'] . "'");
        if (xtc_db_num_rows($coupon_count)>=$gv_result['uses_per_coupon'] && $gv_result['uses_per_coupon'] > 0) {
          xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'coupon_message='.strtolower('ERROR_INVALID_USES_COUPON').'&add_info='.urlencode($gv_result['uses_per_coupon'] . TIMES ), 'NONSSL'));
        }
        if (xtc_db_num_rows($coupon_count_customer)>=$gv_result['uses_per_user'] && $gv_result['uses_per_user'] > 0) {
          xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'coupon_message='.strtolower('ERROR_INVALID_USES_USER_COUPON').'&add_info='. urlencode($gv_result['uses_per_user'] . TIMES ), 'NONSSL'));
        }
        if ($gv_result['coupon_type']=='S') {
          $coupon_amount = TEXT_COUPON_HELP_FIXED; //$order->info['shipping_cost'];
        } else {
            $coupon_amount = sprintf(TEXT_COUPON_HELP_FIXED,$xtPrice->xtcFormat($gv_result['coupon_amount'],true,0,true)) . ' ';
        }
        if ($gv_result['coupon_type']=='P') {
          $coupon_amount = sprintf(TEXT_COUPON_HELP_FIXED,round($gv_result['coupon_amount'],0)) . '% ';
        }
        if ($gv_result['coupon_minimum_order'] > 0) {          
          $coupon_amount .= sprintf(TEXT_COUPON_HELP_MINORDER, $xtPrice->xtcFormat($gv_result['coupon_minimum_order'],true,0,true));
        }
        if ($gv_result['restrict_to_products'] != '') {
          $coupon_amount .= '<br /><br />'.TEXT_COUPON_PRODUCTS_RESTRICT;
        }
        if ($gv_result['restrict_to_categories'] != '') {
          $coupon_amount .= '<br /><br />'.TEXT_COUPON_CATEGORIES_RESTRICT;
        }
        $_SESSION['cc_amount_min_order'] = $xtPrice->xtcCalculateCurr($gv_result['coupon_minimum_order']);
        $_SESSION['cc_amount_info'] = $coupon_amount;
        if ($_SESSION['cc_amount_min_order'] <= $_SESSION['cart']->total) {
          $_SESSION['cc_id'] = $gv_result['coupon_id'];
        }
        $_SESSION['cc_post'] = true;
        xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL'));
        //xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'info=1&coupon_message='.strtolower('REDEEMED_COUPON'), 'NONSSL'));
    }

     }
     if ($_POST['submit_redeem_x'] && $gv_result['coupon_type'] == 'G') {
       xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'coupon_message='.strtolower('ERROR_NO_REDEEM_CODE'), 'NONSSL'));
     } 
   }
?>