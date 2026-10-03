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
include_once(DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/payment/klarna_express.php');
require_once(DIR_FS_CATALOG.'includes/modules/payment/klarna_express.php');

$klarna_error = 'disabled';
$klarna_data = false;
if (klarna_express::express_enabled() === true) {
  $klarna_express = new klarna_express();

  $klarna_data = $klarna_express->check_express_request($_POST);
  if (is_array($klarna_data)) {
    $klarna_error = (($klarna_express->start_express($klarna_data) === true) ? '' : 'account');
  } else {
    $klarna_error = $klarna_data;
  }

  if ($klarna_error != '') {
    $klarna_express->logger->log('klarna', 'express callback aborted: '.$klarna_error);
  }
}

if ($klarna_error != '') {
  unset($_SESSION['klarna_express']);

  $messageStack->add_session('shopping_cart', ((in_array($klarna_error, array('address', 'country'))) ? MODULE_PAYMENT_KLARNA_EXPRESS_TEXT_ERROR_ADDRESS : MODULE_PAYMENT_KLARNA_EXPRESS_TEXT_ERROR_CALLBACK));
  xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL'));
}

xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
