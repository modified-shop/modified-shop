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
    if (!isset($_SESSION['paypal'])
        || !isset($_SESSION['paypal']['OrderID'])
        || !isset($_GET['payment_method'])
        )
    {
      return false;
    }

    // card fields: liability shift (3D Secure) is the relevant check
    if (in_array($_GET['payment_method'], array('paypalacdc'))) {
      $paypal = new PayPalPaymentV2($_GET['payment_method']);
      return $paypal->CheckLiabilityShift($_SESSION['paypal']['OrderID']);
    }

    // wallets (Apple Pay / Google Pay)
    if (in_array($_GET['payment_method'], array('paypalapplepay', 'paypalgooglepay'))) {
      $paypal = new PayPalPaymentV2($_GET['payment_method']);
      $PayPalOrder = $paypal->GetOrder($_SESSION['paypal']['OrderID']);
      $approved = (isset($PayPalOrder->status) && in_array($PayPalOrder->status, array('COMPLETED', 'APPROVED')));

      if (!$approved) {
        $paypal->LoggingManager->log('WARNING', 'Wallet check_paypal_order not approved', array(
          'order_id' => $_SESSION['paypal']['OrderID'],
          'status' => (isset($PayPalOrder->status) ? $PayPalOrder->status : null),
        ));
      }

      return $approved;
    }

    return false;
  }
