<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // the cart ends an express checkout, the normal checkout offers all payment methods again
  if (isset($_SESSION['klarna']['express_flow'])) {
    unset($_SESSION['klarna']);
    if (isset($_SESSION['payment']) && $_SESSION['payment'] === 'klarna_express') {
      unset($_SESSION['payment']);
    }
  }

  if (defined('MODULE_PAYMENT_KLARNA_EXPRESS_STATUS')
      && MODULE_PAYMENT_KLARNA_EXPRESS_STATUS == 'True'
      && defined('MODULE_PAYMENT_KLARNA_EXPRESS_BUTTON_CART')
      && MODULE_PAYMENT_KLARNA_EXPRESS_BUTTON_CART == 'True'
      && defined('MODULE_PAYMENT_KLARNA_EXPRESS_CLIENT_ID')
      && MODULE_PAYMENT_KLARNA_EXPRESS_CLIENT_ID != ''
      && $_SESSION['cart']->show_total() > 0
      )
  {
    // include needed classes
    include_once(DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/payment/klarna_express.php');
    require_once(DIR_FS_CATALOG.'includes/modules/payment/klarna_express.php');

    $klarna_express = new klarna_express();

    // no button when the shop restricts the module for this customer group or cart
    if ($klarna_express->express_available() === true) {
      $klarna_express_config = $klarna_express->get_express_config(
        $klarna_express->prepare_express(),
        str_replace('&amp;', '&', xtc_href_link(FILENAME_SHOPPING_CART, 'payment_error=klarna_express', 'NONSSL'))
      );

      $smarty->assign('BUTTON_KLARNA', $klarna_express->get_express_button($klarna_express_config, 'authorize(options, cfg.payload, done);'));
    }

    if (isset($_GET['payment_error']) && $_GET['payment_error'] == 'klarna_express') {
      $smarty->assign('error_message', MODULE_PAYMENT_KLARNA_EXPRESS_TEXT_ERROR_MESSAGE);
    }
  }
