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
        && in_array($_GET['payment_method'], array('paypalacdc'))
        )
    {
      $paypal = new PayPalPaymentV2($_GET['payment_method']);
      return $paypal->CheckLiabilityShift($_SESSION['paypal']['OrderID']);
    }

    return false;
  }
