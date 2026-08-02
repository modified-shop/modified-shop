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


  function create_paypal_order() {
    global $order;

    $allowed_payment_methods = array('paypal', 'paypalexpress', 'paypalapplepay', 'paypalgooglepay', 'paypalacdc', 'paypalcard', 'paypalsepa');
    $payment_method = 'paypal';
    if (isset($_GET['payment_method']) && is_string($_GET['payment_method'])) {
      $payment_method = $_GET['payment_method'];
    } elseif (isset($_POST['payment_method']) && is_string($_POST['payment_method'])) {
      $payment_method = $_POST['payment_method'];
    }

    if (!in_array($payment_method, $allowed_payment_methods, true)) {
      return false;
    }

    $paypal = new PayPalPaymentV2($payment_method);
    if (!$paypal->is_valid_ajax_token()) {
      return false;
    }
    
    if (!isset($_SESSION['cart'])
        || $_SESSION['cart']->count_contents() <= 0
        )
    {
      return;
    }
    
    $express_payments = array('paypalexpress', 'paypalapplepay', 'paypalgooglepay');

    $order = $paypal->set_order_object();

    // rotate the nonce so every express attempt creates a fresh PayPal order
    if (in_array($paypal->code, $express_payments)) {
      $_SESSION['payment_nonce'] = md5(uniqid((string)rand(), true));
    }
        
    $payment_source = array();
    if ($paypal->code == 'paypalacdc') {
      // card fields: 3D Secure is requested via the order's payment_source
      $payment_source = array(
        'payment_source' => array(
          'card' => array(
            'attributes' => array(
              'verification' => array(
                'method' => 'SCA_WHEN_REQUIRED',
              )
            )
          )
        )
      );
    }

    if (isset($_POST['save_payment'])
        && $_POST['save_payment'] == 'save_payment'
        )
    {
      if ($paypal->code == 'paypalacdc') {
        $source_key = 'card';
        $payment_source['payment_source']['card']['attributes']['vault'] = array(
          'store_in_vault' => 'ON_SUCCESS',
        );
      } else {
        $source_key = 'paypal';
        $payment_source = array(
          'payment_source' => array(
            'paypal' => array(
              'attributes' => array(
                'vault' => array(
                  'store_in_vault' => 'ON_SUCCESS',
                  'usage_type' => 'MERCHANT',
                  'customer_type' => 'CONSUMER',
                  'permit_multiple_payment_tokens' => true,
                )
              )
            )
          )
        );
      }

      if (isset($_SESSION['customer_id'])) {
        $customer_id = $paypal->getCustomerId($_SESSION['customer_id']);

        if (!is_null($customer_id)) {
          $payment_source['payment_source'][$source_key]['attributes']['customer'] = array(
            'id' => $customer_id
          );
        }
      }
    }
    
    $_SESSION['paypal'] = array(
      'cartID' => $_SESSION['cart']->cartID,
      'OrderID' => $paypal->CreateOrder($payment_source)
    );

    if (empty($_SESSION['paypal']['OrderID']) || !is_string($_SESSION['paypal']['OrderID'])) {
      $paypal->LoggingManager->log('WARNING', 'Wallet CreateOrder failed', array('code' => $paypal->code));
    }

    if (!in_array($paypal->code, $express_payments)) {
      $paypal->PatchOrder($_SESSION['paypal']['OrderID']);
    }
    
    return $_SESSION['paypal']['OrderID'];
  }
