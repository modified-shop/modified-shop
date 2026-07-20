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
    $paypal = new PayPalPaymentV2(isset($_REQUEST['payment_method']) ? $_REQUEST['payment_method'] : 'paypalgooglepay');

    $paypal->LoggingManager->log('WARNING', 'Wallet client-side error', array(
      'step'    => (isset($_POST['step']) ? substr($_POST['step'], 0, 100) : ''),
      'name'    => (isset($_POST['name']) ? substr($_POST['name'], 0, 100) : ''),
      'message' => (isset($_POST['message']) ? substr($_POST['message'], 0, 1000) : ''),
      'url'     => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''),
    ));

    return array('success' => true);
  }
