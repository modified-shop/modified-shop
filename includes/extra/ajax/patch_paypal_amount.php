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
  require_once(DIR_WS_CLASSES.'order_total.php');
  require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPaymentV2.php');


  // Apple Pay / Google Pay cart flow
  function patch_paypal_amount() {
    global $order;

    $allowed_payment_methods = array('paypalapplepay', 'paypalgooglepay');
    $payment_method = ((isset($_GET['payment_method']) && is_string($_GET['payment_method'])) ? $_GET['payment_method'] : '');

    if (!in_array($payment_method, $allowed_payment_methods, true)) {
      return array('success' => false);
    }

    $paypal = new PayPalPaymentV2($payment_method);
    if (!$paypal->is_valid_ajax_token()) {
      return array('success' => false);
    }

    if (!isset($_SESSION['cart'])
        || $_SESSION['cart']->count_contents() <= 0
        || !isset($_SESSION['paypal']['OrderID'])
        || $_SESSION['paypal']['OrderID'] == ''
        )
    {
      $paypal->LoggingManager->log('WARNING', 'Wallet PatchOrder aborted', array(
        'reason' => (!isset($_SESSION['cart']) || $_SESSION['cart']->count_contents() <= 0) ? 'empty cart' : 'missing OrderID',
      ));
      return array('success' => false);
    }

    // rebuild the order object (uses $_SESSION['shipping'] set during the
    // shipping selection) so the patched amount matches the sheet total
    $order = $paypal->set_order_object();

    // fill the delivery address from the wallet contact if available
    if (isset($_SESSION['paypal']['contact']['shipping'])
        && is_array($_SESSION['paypal']['contact']['shipping'])
        )
    {
      $address = $paypal->parse_contact($_SESSION['paypal']['contact']['shipping']);
      $order = $paypal->apply_address_to_delivery($order, $address);
      $order->customer['country']['iso_code_2'] = $order->delivery['country_iso_2'];
    }

    $result = $paypal->PatchOrder($_SESSION['paypal']['OrderID']);

    if ($result !== true) {
      $paypal->LoggingManager->log('WARNING', 'Wallet PatchOrder failed', array('order_id' => $_SESSION['paypal']['OrderID']));
    }

    return array('success' => ($result === true));
  }
