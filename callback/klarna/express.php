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
require_once(DIR_FS_EXTERNAL.'klarna/functions/klarna_express_language.php');
klarna_express_include_language();
require_once(DIR_FS_CATALOG.'includes/modules/payment/klarna_express.php');

$klarna_error = 'disabled';
$klarna_data = false;
$klarna_short = false;
if (klarna_express::express_enabled() === true) {
  $klarna_express = new klarna_express();

  $klarna_data = $klarna_express->check_express_request($_POST);
  if (is_array($klarna_data)) {
    $klarna_error = (($klarna_express->start_express($klarna_data) === true) ? '' : 'account');
    if ($klarna_error == '') {
      // without a shipping method to start with the customer chooses on the shipping page
      $klarna_short = $klarna_express->start_short_checkout();
    }
  } else {
    $klarna_error = $klarna_data;

    // browser Back and resubmit: the token is used up, the running flow is still valid
    if ($klarna_error == 'token' && klarna_express::express_session_valid() === true) {
      if (isset($_SESSION['klarna']['short_checkout']) && $_SESSION['klarna']['short_checkout'] === true) {
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, 'conditions=true', 'SSL'));
      }
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
  }

  if ($klarna_error != '') {
    $klarna_express->logger->log('klarna', 'express callback aborted: '.$klarna_error);
  }
}

if ($klarna_error != '') {
  unset($_SESSION['klarna_express']);

  if (in_array($klarna_error, array('address', 'country'))) {
    $klarna_message = MODULE_PAYMENT_KLARNA_EXPRESS_TEXT_ERROR_ADDRESS;
  } elseif ($klarna_error == 'unavailable') {
    $klarna_message = MODULE_PAYMENT_KLARNA_EXPRESS_TEXT_ERROR_UNAVAILABLE;
  } elseif ($klarna_error == 'stock') {
    $klarna_message = MODULE_PAYMENT_KLARNA_EXPRESS_TEXT_ERROR_STOCK;
  } elseif ($klarna_error == 'order_value') {
    $klarna_message = MODULE_PAYMENT_KLARNA_EXPRESS_TEXT_ERROR_ORDER_VALUE;
  } elseif ($klarna_error == 'gift') {
    $klarna_message = GUEST_VOUCHER_NOT_ALLOWED;
  } else {
    $klarna_message = MODULE_PAYMENT_KLARNA_EXPRESS_TEXT_ERROR_CALLBACK;
  }
  $messageStack->add_session('shopping_cart', $klarna_message);
  xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL'));
}

if ($klarna_short === true) {
  xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, 'conditions=true', 'SSL'));
}

xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
