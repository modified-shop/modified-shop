<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// use always session_id from URL for payment providers
define('SESSION_FORCE_COOKIE_USE', 'False');

chdir('../../');
include('includes/application_top.php');

// include needed classes
require_once(DIR_WS_CLASSES.'order.php');
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPaymentV2.php');

$paypal = new PayPalPaymentV2('paypal');
if (isset($_SESSION['tmp_oID'])) {
  if (isset($_SESSION['paypal']['OrderID'])) {
    $PayPalOrder = $paypal->GetOrder($_SESSION['paypal']['OrderID']);
    if ($PayPalOrder->status == 'PAYER_ACTION_REQUIRED') {
      $paypal->remove_order($_SESSION['tmp_oID']);
    }
  }
}

xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, xtc_get_all_get_params(), 'NONSSL'));
