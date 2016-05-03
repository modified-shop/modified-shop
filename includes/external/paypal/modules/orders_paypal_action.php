<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if (isset($order) && is_object($order)) {
  if ($order->info['payment_method'] == 'paypalclassic' 
      || $order->info['payment_method'] == 'paypalcart'
      || $order->info['payment_method'] == 'paypalplus'
      || $order->info['payment_method'] == 'paypallink'
      || $order->info['payment_method'] == 'paypalpluslink'
      ) 
  {
    require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalInfo.php');
    $paypal = new PayPalInfo($order->info['payment_method']);
    
    // action
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      if ($_POST['cmd'] == 'refund') {
        if ($_POST['refund_price'] > 0) {
          $paypal->refund_payment($order->info['order_id'], $_POST['refund_price'], $_POST['refund_comment']);
        } else {
          $_SESSION['pp_error'] = TEXT_PAYPAL_ERROR_AMOUNT;
        }
      }
      if ($_POST['cmd'] == 'capture') {
        if ($_POST['capture_price'] > 0) {
          $paypal->capture_payment_admin($order->info['order_id'], $_POST['capture_price'], (isset($_POST['final_capture'])));
        } else {
          $_SESSION['pp_error'] = TEXT_PAYPAL_ERROR_AMOUNT;
        }
      }
    }
  }
}
?>