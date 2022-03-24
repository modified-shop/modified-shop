<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
  // include needed classes
  require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');
  
  $paypal = new PayPalPayment('paypalinstallment'); 
  if ($paypal->check_install() === true
      && $paypal->get_config('PAYPAL_MODE') == 'live'
      && $paypal->get_config('PAYPAL_INSTALLMENT_BANNER_DISPLAY') == 1
      && $paypal->get_config('PAYPAL_CLIENT_ID_'.strtoupper($paypal->get_config('PAYPAL_MODE'))) != ''
      )
  {
    $module_smarty->assign('PAYPAL_INSTALLMENT', '<div class="pp-message"></div>');
  }
