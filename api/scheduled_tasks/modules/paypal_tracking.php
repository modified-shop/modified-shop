<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  function cron_paypal_tracking() {
    if (defined('MODULE_PAYMENT_PAYPAL_SECRET')) {
      // include needed classes
      require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPaymentV2.php');

      $tracking_query = xtc_db_query("SELECT o.orders_id,
                                             ot.tracking_id
                                        FROM ".TABLE_ORDERS_TRACKING." ot
                                        JOIN ".TABLE_ORDERS." o
                                             ON o.orders_id = ot.orders_id
                                                AND o.payment_class LIKE ('%paypal%')
                                        JOIN ".TABLE_PAYPAL_PAYMENT." pp
                                             ON pp.orders_id = ot.orders_id
                                   LEFT JOIN ".TABLE_PAYPAL_TRACKING." pt
                                             ON ot.tracking_id = pt.tracking_id
                                       WHERE pt.tracking_id IS NULL
                                    ORDER BY ot.tracking_id DESC
                                       LIMIT 50");
      if (xtc_db_num_rows($tracking_query) > 0) {
        $paypal = new PayPalPaymentV2('paypal');
            
        while ($tracking = xtc_db_fetch_array($tracking_query)) { 
          $paypal->AddOrderTracking($tracking['orders_id'], $tracking['tracking_id']); 
        }
      }
    }
    
    return true;
  }