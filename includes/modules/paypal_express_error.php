<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if (isset($_SESSION['reshash']['ACK']) && strtoupper($_SESSION['reshash']['ACK']) != 'SUCCESS' && strtoupper($_SESSION['reshash']['ACK']) != 'SUCCESSWITHWARNING') {
  if (isset($_SESSION['reshash']['REDIRECTREQUIRED'])  && strtoupper($_SESSION['reshash']['REDIRECTREQUIRED']) == 'TRUE') {
    require_once(DIR_WS_CLASSES.'payment.php');
    $payment_modules = new payment($_SESSION['payment']);
    $_SESSION['paypal_fehler'] = (defined('PAYPAL_FEHLER') ? PAYPAL_FEHLER : 'PayPal Fehler...<br />');
    $_SESSION['paypal_warten'] = (defined('PAYPAL_WARTEN') ? PAYPAL_WARTEN : 'Sie muessen noch einmal zu PayPal. <br />');
    $payment_modules->giropay_process();
  }
}
unset($_SESSION['paypal_express_checkout']);

if (isset($_SESSION['paypal_fehler']) && !isset($_SESSION['paypal_warten'])) {
  if (!isset($_SESSION['reshash']['ACK'])) {
    $o_paypal->paypal_second_auth_call($_SESSION['tmp_oID']);
    xtc_redirect($o_paypal->payPalURL);
  }
  if (isset($_SESSION['reshash']['ACK']) && (strtoupper($_SESSION['reshash']['ACK']) == 'SUCCESS' || strtoupper($_SESSION['reshash']['ACK']) == 'SUCCESSWITHWARNING')) {
    $o_paypal->paypal_get_customer_data();
    if ($data['PayerID'] || $_SESSION['reshash']['PAYERID']) {
      require_once(DIR_WS_CLASSES.'order.php');
      $data = array_merge($_SESSION['nvpReqArray'],$_SESSION['reshash']);
      if (is_array($_GET)) $data = array_merge($data,$_GET);
      $o_paypal->complete_ceckout($_SESSION['tmp_oID'],$data);
      $o_paypal->write_status_history($_SESSION['tmp_oID']);
      $o_paypal->logging_status($_SESSION['tmp_oID']);
    }
  }
  $_SESSION['cart']->reset(true);

  // unregister session variables used during checkout
  $last_order =$_SESSION['tmp_oID'];
  unset($_SESSION['sendto']);
  unset($_SESSION['billto']);

  // avoid hack attempts during the checkout procedure by checking the internal cartID
  if ((isset ($_SESSION['cart']->cartID) && isset ($_SESSION['cartID'])) || (!isset($_SESSION['cartID']) && isset($_SESSION['shipping']))) {
    if ($_SESSION['cart']->cartID !== $_SESSION['cartID']) {
      unset($_SESSION['shipping']);
      unset($_SESSION['payment']);
    }
  }

  unset($_SESSION['comments']);
  unset($_SESSION['tmp_oID']);
  unset($_SESSION['cc']);

  //GV Code Start
  if (isset($_SESSION['credit_covers'])) {
    unset($_SESSION['credit_covers']);
  }
  require_once(DIR_WS_CLASSES.'order_total.php');
  $order_total_modules = new order_total();
  $order_total_modules->clear_posts(); //ICW ADDED F|| CREDIT CLASS SYSTEM
  // GV Code End

  if (isset($_SESSION['reshash']['ACK']) && (strtoupper($_SESSION['reshash']['ACK'])=='SUCCESS' || strtoupper($_SESSION['reshash']['ACK']) == 'SUCCESSWITHWARNING')) {
    $redirect = ((isset($_SESSION['reshash']['REDIRECTREQUIRED'])  && strtoupper($_SESSION['reshash']['REDIRECTREQUIRED'])=='TRUE') ? true : false);
    $o_paypal->paypal_get_customer_data();
    if ($data['PayerID'] || $_SESSION['reshash']['PAYERID']) {
      if ($redirect) {
        unset($_SESSION['paypal_fehler']);
        require_once(DIR_WS_CLASSES.'payment.php');
        $payment_modules = new payment('paypalexpress');
        $payment_modules->giropay_process();
      }
      $weiter = true;
    }
    unset($_SESSION['nvpReqArray']);
    unset($_SESSION['reshash']);
    if ($weiter) {
      unset($_SESSION['paypal_fehler']);
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
    }
  } else {
    unset($_SESSION['payment']);
    unset($_SESSION['nvpReqArray']);
    unset($_SESSION['reshash']);
  }
  $smarty->assign('paypal_error', $_SESSION['paypal_fehler']);
  unset($_SESSION['paypal_fehler']);
}
?>