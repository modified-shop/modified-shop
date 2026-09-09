<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // receives client-side wallet errors (e.g. Google Pay SDK rejections) that
  // would otherwise only ever reach the buyer's browser console
  require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPaymentV2.php');

  function log_paypal_client_error() {
    $allowed_payment_methods = array('paypalapplepay', 'paypalgooglepay');
    $payment_method = ((isset($_GET['payment_method']) && is_string($_GET['payment_method'])) ? $_GET['payment_method'] : '');

    if (!isset($_SERVER['REQUEST_METHOD'])
        || $_SERVER['REQUEST_METHOD'] != 'POST'
        || !in_array($payment_method, $allowed_payment_methods, true)
        )
    {
      return array('success' => false);
    }

    $paypal = new PayPalPaymentV2($payment_method);
    if (!isset($_POST['token'])
        || !is_string($_POST['token'])
        || !hash_equals($paypal->get_client_error_token(), $_POST['token'])
        )
    {
      return array('success' => false);
    }

    $now = time();
    $log_requests = ((isset($_SESSION['paypal_client_error_log']) && is_array($_SESSION['paypal_client_error_log'])) ? $_SESSION['paypal_client_error_log'] : array());
    $log_requests = array_values(array_filter($log_requests, function ($timestamp) use ($now) {
      return (is_int($timestamp) && $timestamp >= ($now - 60));
    }));
    if (count($log_requests) >= 5) {
      $_SESSION['paypal_client_error_log'] = $log_requests;
      return array('success' => false);
    }
    $log_requests[] = $now;
    $_SESSION['paypal_client_error_log'] = $log_requests;

    $sanitize = function ($value, $length) {
      if (!is_scalar($value)) {
        return '';
      }
      return substr(preg_replace('/[\x00-\x1F\x7F]/', ' ', (string)$value), 0, $length);
    };

    $paypal->LoggingManager->log('WARNING', 'Wallet client-side error', array(
      'step'    => $sanitize((isset($_POST['step']) ? $_POST['step'] : ''), 100),
      'name'    => $sanitize((isset($_POST['name']) ? $_POST['name'] : ''), 100),
      'message' => $sanitize((isset($_POST['message']) ? $_POST['message'] : ''), 1000),
      'url'     => $sanitize((isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''), 1000),
    ));

    return array('success' => true);
  }
