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
    $module_content = array();
    $plan_query = xtDBquery("SELECT *
                               FROM `paypal_plan`
                              WHERE products_id = '".$product->data['products_id']."'
                                AND plan_status = 1");
    if (xtc_db_num_rows($plan_query, true) > 0) {
      $module_smarty = new Smarty();
      if ($_SESSION['cart']->count_contents() > 0) {
        $module_smarty->assign('error', TEXT_PAYPAL_ERROR_MAX_PRODUCTS);
        $info_smarty->clear_assign('ADD_QTY');
        $info_smarty->clear_assign('ADD_CART_BUTTON');
      } else {
      
        $i = 0;
        while ($plan = xtc_db_fetch_array($plan_query, true)) {
          $fields = array();
          $fields[] = array(
            'title' => TEXT_PAYPAL_PLAN_INTERVAL,
            'field' => constant('TEXT_PAYPAL_PLAN_'.strtoupper($plan['plan_interval']))
          );
      
          if ($plan['plan_cycle'] > 0) {
            $fields[] = array(
              'title' => TEXT_PAYPAL_PLAN_CYCLE,
              'field' => $plan['plan_cycle']
            );
          }

          $fields[] = array(
            'title' => TEXT_PAYPAL_PLAN_SETUP_FEE,
            'field' => $xtPrice->xtcFormat($plan['plan_fee'], true)
          );     
                
          $module_content[$i] = array(
            'id' => $plan['plan_id'],
            'module' => $plan['plan_name'],
            'description' => '',
            'fields' => $fields,
            'module_cost' => $xtPrice->xtcFormat($plan['plan_price'], true),
            'radio_buttons' => $i,
            'checked' => 0,
            'selection' => xtc_draw_radio_field('plan_id', $plan['plan_id'], false, 'id="rd-'.($i+1).'"'),
          );
          
          $i ++;
        }
        $module_smarty->assign('module_content', $module_content);
        $plan_content = $module_smarty->fetch(CURRENT_TEMPLATE . '/module/checkout_payment_block.html');
        
        $plan_content = str_replace('id="horizontalAccordion"', 'id="horizontalAccordionPlan"', $plan_content);
        $module_smarty->assign('plan_content', $plan_content);
        
        if ($messageStack->size('paypalplan') > 0) {
          $module_smarty->assign('error', $messageStack->output('paypalplan'));
        }    
      }
      $module_smarty->assign('language', $_SESSION['language']);
      $plan_content = $module_smarty->fetch(DIR_FS_EXTERNAL . '/paypal/templates/plan.html');

      $info_smarty->assign('PAYPALPLAN', $plan_content);
      $info_smarty->clear_assign('ADD_CART_BUTTON_WISHLIST');
      $info_smarty->clear_assign('ADD_CART_BUTTON_WISHLIST_TEXT');
    } else {
      if ($_SESSION['cart']->count_contents() > 0) {
        $plan_query = xtc_db_query("SELECT *
                                      FROM `paypal_plan`
                                     WHERE products_id IN ('".implode("', '", $_SESSION['cart']->get_product_id_array())."')
                                       AND plan_status = 1");
        if (xtc_db_num_rows($plan_query) > 0) {        
          $module_smarty = new Smarty();
          $module_smarty->assign('error', TEXT_PAYPAL_ERROR_SUBSCRIPTION_PRODUCTS);

          $module_smarty->assign('language', $_SESSION['language']);
          $plan_content = $module_smarty->fetch(DIR_FS_EXTERNAL . '/paypal/templates/plan.html');

          $info_smarty->assign('PAYPALPLAN', $plan_content);
          $info_smarty->clear_assign('ADD_CART_BUTTON_WISHLIST');
          $info_smarty->clear_assign('ADD_CART_BUTTON_WISHLIST_TEXT');
          $info_smarty->clear_assign('ADD_QTY');
          $info_smarty->clear_assign('ADD_CART_BUTTON');
        }
      }
    }
  }