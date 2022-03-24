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


  function create_paypal_order() {
    global $order;
    
    $order = new order();
    $paypal = new PayPalPaymentV2(isset($_GET['payment_method']) ? $_GET['payment_method'] : 'paypal');

    $_SESSION['paypal'] = array(
      'cartID' => $_SESSION['cart']->cartID,
      'OrderID' => $paypal->CreateOrder()
    );

    return $_SESSION['paypal']['OrderID'];
  }
