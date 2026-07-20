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

    $paypal = new PayPalPaymentV2(isset($_REQUEST['payment_method']) ? $_REQUEST['payment_method'] : 'paypalapplepay');

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

      $order->delivery['firstname'] = $address['firstname'];
      $order->delivery['lastname'] = $address['lastname'];
      $order->delivery['company'] = $address['company'];
      $order->delivery['street_address'] = $address['street_address'];
      $order->delivery['suburb'] = $address['suburb'];
      $order->delivery['city'] = $address['city'];
      $order->delivery['postcode'] = $address['postcode'];
      $order->delivery['state'] = $address['state'];
      $order->delivery['zone_id'] = $address['zone_id'];
      $order->delivery['country_id'] = $address['country_id'];
      $order->delivery['country_iso_2'] = $address['country_iso_code_2'];
      $order->customer['country']['iso_code_2'] = $address['country_iso_code_2'];
    }

    $result = $paypal->PatchOrder($_SESSION['paypal']['OrderID']);

    if ($result !== true) {
      $paypal->LoggingManager->log('WARNING', 'Wallet PatchOrder failed', array('order_id' => $_SESSION['paypal']['OrderID']));
    }

    return array('success' => ($result === true));
  }
