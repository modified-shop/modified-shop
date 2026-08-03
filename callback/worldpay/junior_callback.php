<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
   (c) 2011 osCommerce(advanced_search_result.php,v 1.68 2003/05/14)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('RUN_MODE_NOSESSION', true);

  chdir('../../');
  require('includes/application_top.php');
  require_once(DIR_WS_MODULES.'payment/worldpay_junior.php');

  header('Content-Type: text/plain; charset=UTF-8');

  if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    worldpay_junior_callback_response(405, 'Method not allowed');
  }

  if (!isset($_POST['transStatus']) || !is_scalar($_POST['transStatus'])) {
    worldpay_junior_callback_response(400, 'Invalid transaction status');
  }

  $transaction_status = (string)$_POST['transStatus'];
  if ($transaction_status !== 'Y' && $transaction_status !== 'C') {
    worldpay_junior_callback_response(422, 'Unsupported transaction status');
  }

  $callback = worldpay_junior::validate_callback($_POST);
  if ($callback === false) {
    worldpay_junior_callback_response(403, 'Invalid callback');
  }

  $language_file = DIR_WS_LANGUAGES.$callback['language'].'/modules/payment/worldpay_junior.php';
  if (is_file($language_file)) {
    require_once($language_file);
  } else {
    require_once(DIR_WS_LANGUAGES.'english/modules/payment/worldpay_junior.php');
  }

  $provider_transaction_id = isset($_POST['transId']) && is_scalar($_POST['transId'])
    ? (string)$_POST['transId']
    : '';
  $result = worldpay_junior::process_callback(
    $callback,
    $transaction_status === 'Y',
    $provider_transaction_id
  );
  if (!is_array($result)) {
    worldpay_junior_callback_response(409, 'Callback could not be applied');
  }

  // A verified payment also wins over any later cancellation notification.
  if ($result['success'] === true && empty($callback['legacy'])) {
    require_once(DIR_WS_CLASSES.'checkout.php');
    if (!checkout::mark_payment_ready(
          $callback['checkout_key'],
          $callback['customers_id'],
          $callback['orders_id']
        ))
    {
      worldpay_junior_callback_response(409, 'Payment could not be published');
    }
  }

  worldpay_junior_callback_response(200, 'OK');


  function worldpay_junior_callback_response($status_code, $message) {
    http_response_code((int)$status_code);
    echo (string)$message;
    exit;
  }
