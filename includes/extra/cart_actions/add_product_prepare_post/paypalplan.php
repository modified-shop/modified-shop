<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
  require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');
  $paypal_subscription = new PayPalPayment('paypalsubscription');
  if ($paypal_subscription->enabled === true) 
  {
    $plan_query = xtDBquery("SELECT *
                               FROM `paypal_plan`
                              WHERE products_id = '".(int)$_POST['products_id']."'
                                AND plan_status = 1");
    if (xtc_db_num_rows($plan_query, true) > 0) {
      if (!isset($_POST['plan_id'])) {
        $messageStack->add_session('paypalplan', TEXT_PAYPAL_ERROR_NO_PLAN);
        xtc_redirect(xtc_href_link(FILENAME_PRODUCT_INFO, 'products_id='.(int)$_POST['products_id'], 'NONSSL'));
      }
    }
  }