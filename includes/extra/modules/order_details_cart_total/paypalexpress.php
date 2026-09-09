<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
  if (defined('MODULE_PAYMENT_PAYPAL_SECRET')
      && MODULE_PAYMENT_PAYPAL_SECRET != ''
      )
  {
    // include needed classes
    require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPaymentV2.php');
  
    $paypal_applepay = new PayPalPaymentV2('paypalapplepay');
    $paypalapplepay = ($paypal_applepay->is_enabled()
                       && $paypal_applepay->get_config('MODULE_PAYMENT_'.strtoupper($paypal_applepay->code).'_SHOW_CART') == '1'
                       && (!isset($_SESSION['paypal_instruments'])
                         || (is_array($_SESSION['paypal_instruments']) && in_array('applepay', $_SESSION['paypal_instruments']))
                         )
                      );

    $paypal_googlepay = new PayPalPaymentV2('paypalgooglepay');
    $paypalgooglepay = ($paypal_googlepay->is_enabled()
                       && $paypal_googlepay->get_config('MODULE_PAYMENT_' . strtoupper($paypal_googlepay->code) . '_SHOW_CART') == '1'
                      );

    $paypal = new PayPalPaymentV2('paypalexpress');

    if ($paypal->is_enabled() || $paypalapplepay || $paypalgooglepay) {
      $paypal_smarty = new Smarty();
      $paypal_smarty->assign('language', $_SESSION['language']);

      if ($paypal->is_enabled()) {
        $paypal_smarty->assign('paypalexpress', true);
        if ($paypal->get_config('MODULE_PAYMENT_'.strtoupper($paypal->code).'_SHOW_CART_BNPL') == '1') {
          $paypal_smarty->assign('paypalbnpl', true);
        }
      }

      if ($paypalapplepay) {
        $paypal_smarty->assign('paypalapplepay', true);
      }

      if ($paypalgooglepay) {
        $paypal_smarty->assign('paypalgooglepay', true);
      }

      $paypal_smarty->caching = 0;

      $tpl_file = DIR_FS_EXTERNAL.'paypal/templates/apms.html';
      if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/apms.html')) {
        $tpl_file = DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/apms.html';
      }
      $smarty->assign('BUTTON_PAYPAL', $paypal_smarty->fetch($tpl_file));

      if (isset($_GET['payment_error'])) {
        if ($_GET['payment_error'] == 'paypalapplepay') {
          include_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/paypalapplepay.php');
          $error = $paypal_applepay->get_error();
        } elseif ($_GET['payment_error'] == 'paypalgooglepay') {
          include_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/paypalgooglepay.php');
          $error = $paypal_googlepay->get_error();
        } else {
          include_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/paypalexpress.php');
          $error = $paypal->get_error();
        }
        if (is_array($error)) {
          $smarty->assign('error_message',  $error['error']);
        }
      }
    }
  }
