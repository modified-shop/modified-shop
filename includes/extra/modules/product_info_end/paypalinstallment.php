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
    require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');

    $paypal = new PayPalPayment('paypalinstallment');
    if ($paypal->get_config('PAYPAL_INSTALLMENT_BANNER_PRODUCT_DISPLAY') == 1
        && $paypal->get_config('PAYPAL_CLIENT_ID_'.strtoupper($paypal->get_config('PAYPAL_MODE'))) != ''
        )
    {
      $info_smarty->assign('PAYPAL_INSTALLMENT', '<div class="pp-message"></div>');
    }
  }