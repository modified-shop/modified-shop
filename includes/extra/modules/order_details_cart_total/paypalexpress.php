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
  
    $paypal = new PayPalPayment('paypalexpress');
    if ($paypal->enabled === true) {  
      $paypal_smarty = new Smarty();
      $paypal_smarty->assign('language', $_SESSION['language']);
      $paypal_smarty->assign('paypalexpress', true);
      if ($paypal->get_config('MODULE_PAYMENT_'.strtoupper($paypal->code).'_SHOW_CART_BNPL') == '1') {
        $paypal_smarty->assign('paypalbnpl', true);
      }
      $paypal_smarty->caching = 0;

      $tpl_file = DIR_FS_EXTERNAL.'paypal/templates/apms.html';
      if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/apms.html')) {
        $tpl_file = DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/apms.html';
      }
      $smarty->assign('BUTTON_PAYPAL', $paypal_smarty->fetch($tpl_file));
  
      if (isset($_GET['payment_error'])) {
        include_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/paypalexpress.php');
        $error = $paypal->get_error();
        $smarty->assign('error_message',  $error['error']);
      }
    }  
  }