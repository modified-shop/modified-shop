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
  if ($paypal_subscription->enabled === true
      && isset($_SESSION['cart']->plans) 
      && is_array($_SESSION['cart']->plans)
      && count($_SESSION['cart']->plans) > 0
      && array_key_exists($products[$i]['id'], $_SESSION['cart']->plans)  
      ) 
  {  
    $plan_query = xtDBquery("SELECT *
                               FROM `paypal_plan`
                              WHERE plan_id = '".xtc_db_input($_SESSION['cart']->plans[$products[$i]['id']])."'
                                AND plan_status = 1");
    if (xtc_db_num_rows($plan_query, true) > 0) {
      $plan = xtc_db_fetch_array($plan_query, true);
      
      unset($module_content[$i]['BUTTON_WISHLIST']);

      $module_content[$i]['ATTRIBUTES'][-3] = array(
        'NAME' => TEXT_PAYPAL_PLAN_INTERVAL,
        'VALUE_NAME' => constant('TEXT_PAYPAL_PLAN_'.strtoupper($plan['plan_interval']))
      );
      
      if ($plan['plan_cycle'] > 0) {
        $module_content[$i]['ATTRIBUTES'][-2] = array(
          'NAME' => TEXT_PAYPAL_PLAN_CYCLE,
          'VALUE_NAME' => $plan['plan_cycle']
        );
      }

      $module_content[$i]['ATTRIBUTES'][-1] = array(
        'NAME' => TEXT_PAYPAL_PLAN_SETUP_FEE,
        'VALUE_NAME' => $xtPrice->xtcFormat($plan['plan_fee'], true)
      );
      
      $smarty->assign('PAYPALPLAN', 1);  
      $module_smarty->assign('PAYPALPLAN', 1);  
    }
  }