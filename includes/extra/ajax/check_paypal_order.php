<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // include needed classes
  require_once(DIR_WS_CLASSES.'order.php');
  require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPaymentV2.php');

  function check_paypal_order() {
    if (isset($_SESSION['paypal']) 
        && isset($_SESSION['paypal']['OrderID'])
        && isset($_GET['payment_method'])
        && $_GET['payment_method'] != ''
        )
    {
      $paypal = new PayPalPaymentV2($_GET['payment_method']);
      $order = $paypal->GetOrder($_SESSION['paypal']['OrderID']);
      
      if (isset($order->payment_source)
          && isset($order->payment_source->card)
          && isset($order->payment_source->card->authentication_result)
          )
      {
        $authentication_result = $order->payment_source->card->authentication_result;
        
        if (isset($authentication_result->liability_shift)) {
          // with 3D secure
          if ($authentication_result->liability_shift == 'POSSIBLE'
              && isset($authentication_result->three_d_secure)
              && $authentication_result->three_d_secure->enrollment_status == 'Y'
              && in_array($authentication_result->three_d_secure->authentication_status, array('Y', 'A'))
              )
          {
            return true;
          }
          
          // without 3D secure
          if ($paypal->get_config('MODULE_PAYMENT_PAYPALACDC_EXTEND_CARDS') == '1'
              && $authentication_result->liability_shift == 'NO'
              && in_array($authentication_result->three_d_secure->enrollment_status, array('N', 'U', 'B'))
              )
          {
            return true;
          }
        }
        
      }      
    }
    
    return false;
  }
