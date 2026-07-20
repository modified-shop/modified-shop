<?php

/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


chdir('../../');
include('includes/application_top.php');

// include needed classes
require_once(DIR_WS_CLASSES . 'order.php');
require_once(DIR_FS_EXTERNAL . 'paypal/classes/PayPalPaymentV2.php');


// shared guest-checkout callback for wallet cart flows
$allowed_wallet_payment_methods = array('paypalapplepay', 'paypalgooglepay');

$payment_method = (isset($_GET['payment_method']) && in_array($_GET['payment_method'], $allowed_wallet_payment_methods)) ? $_GET['payment_method'] : '';

if ($payment_method != ''
    && isset($_SESSION['paypal'])
    && isset($_SESSION['paypal']['OrderID'])
    && isset($_SESSION['paypal']['contact']['shipping'])
    )
{
    $paypal = new PayPalPaymentV2($payment_method);
    $PayPalOrder = $paypal->GetOrder($_SESSION['paypal']['OrderID']);

    if (!in_array($PayPalOrder->status, array('COMPLETED', 'APPROVED'))) {
      $paypal->LoggingManager->log('WARNING', 'Wallet callback aborted', array(
        'reason' => 'order status',
        'status' => $PayPalOrder->status,
        'order_id' => $_SESSION['paypal']['OrderID'],
      ));
      unset($_SESSION['paypal']);
      xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'payment_error=' . $paypal->code, 'NONSSL'));
    }

    $shipping_address = $paypal->parse_contact($_SESSION['paypal']['contact']['shipping']);
    $billing_address = (isset($_SESSION['paypal']['contact']['billing'])) ? $paypal->parse_contact($_SESSION['paypal']['contact']['billing']) : $shipping_address;

    $customers_data = array();
    foreach ($shipping_address as $key => $value) {
      $customers_data['delivery']['delivery_' . $key] = $value;
      $customers_data['plain'][$key] = $value;
    }
    foreach ($billing_address as $key => $value) {
      $customers_data['customers']['customers_' . $key] = $value;
      $customers_data['payment']['payment_' . $key] = $value;
    }
    $customers_data['info']['gender'] = '';
    $customers_data['info']['dob'] = '';
    $customers_data['info']['email_address'] = (isset($_SESSION['paypal']['contact']['shipping']['emailAddress'])) ? $_SESSION['paypal']['contact']['shipping']['emailAddress'] : '';
    $customers_data['info']['telephone'] = (isset($_SESSION['paypal']['contact']['shipping']['phoneNumber'])) ? $_SESSION['paypal']['contact']['shipping']['phoneNumber'] : '';
    $customers_data = $paypal->decode_utf8($customers_data);

    if (!isset($_SESSION['customer_id'])
        && $customers_data['info']['email_address'] != ''
        )
    {
      $paypal->login_customer($customers_data);
    }

    if (!isset($_SESSION['customer_id'])
        || !isset($_SESSION['paypal']['cartID'])
        || $_SESSION['paypal']['cartID'] != $_SESSION['cart']->cartID
        )
    {
      $paypal->LoggingManager->log('WARNING', 'Wallet callback aborted', array(
        'reason' => 'customer/cart mismatch',
        'has_customer_id' => isset($_SESSION['customer_id']),
        'cart_id_match' => (isset($_SESSION['paypal']['cartID']) ? ($_SESSION['paypal']['cartID'] == $_SESSION['cart']->cartID) : null),
      ));
      unset($_SESSION['paypal']);
      xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'payment_error=' . $paypal->code, 'NONSSL'));
    }

    // sendto (shipping was already chosen interactively in the wallet sheet,
    // $_SESSION['shipping'] is already set by ajax.php?ext=get_shipping_methods)
    $_SESSION['sendto'] = $paypal->get_shipping_address($_SESSION['customer_id'], $customers_data['delivery']);
    $_SESSION['delivery_zone'] = $shipping_address['country_iso_code_2'];

    $order = new order();

    if ($order->content_type == 'virtual'
        || ($order->content_type == 'virtual_weight')
        || ($_SESSION['cart']->count_contents_virtual() == 0)
        )
    {
      $_SESSION['shipping'] = false;
      $_SESSION['sendto'] = false;
    } elseif ($order->delivery['country']['iso_code_2'] != '') {
      $_SESSION['delivery_zone'] = $order->delivery['country']['iso_code_2'];
      if (isset($order->delivery['delivery_zone']) && $order->delivery['delivery_zone'] != '') {
        $_SESSION['delivery_zone'] = $order->delivery['delivery_zone'];
      }
    }

    // register a random ID in the session to check throughout the checkout procedure
    // against alterations in the shopping cart contents
    $_SESSION['cartID'] = $_SESSION['cart']->cartID;

    // payment
    $_SESSION['payment'] = $paypal->code;

    // billto
    $_SESSION['billto'] = $_SESSION['customer_default_address_id'];

    if ($order->billing['country']['iso_code_2'] != '') {
      $_SESSION['billing_zone'] = $order->billing['country']['iso_code_2'];
    }

    // paypal
    $_SESSION['paypal']['payment_modules'] = $paypal->code . '.php';
    if (isset($PayPalOrder->payer->payer_id)) {
      $_SESSION['paypal']['PayerID'] = $PayPalOrder->payer->payer_id;
    }

    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'));
} else {
    $paypal = new PayPalPaymentV2(($payment_method != '') ? $payment_method : 'paypalgooglepay');
    $paypal->LoggingManager->log('WARNING', 'Wallet callback aborted', array(
      'reason' => 'missing prerequisites',
      'payment_method' => $payment_method,
      'has_session_paypal' => isset($_SESSION['paypal']),
      'has_order_id' => isset($_SESSION['paypal']['OrderID']),
      'has_shipping_contact' => isset($_SESSION['paypal']['contact']['shipping']),
    ));
    xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL'));
}
