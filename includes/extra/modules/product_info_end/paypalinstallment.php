<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
  require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');
  $paypal_installment = new PayPalPayment('paypalinstallment');
  
  if ($paypal_installment->check_install() === true
      && $paypal_installment->get_config('PAYPAL_MODE') == 'live'
      && $paypal_installment->get_config('PAYPAL_INSTALLMENT_BANNER_DISPLAY') == 1
      )
  {
    $client_id = $paypal_installment->get_config('PAYPAL_CLIENT_ID_'.strtoupper($paypal_installment->get_config('PAYPAL_MODE')));
    
    if ($client_id != '') {
      $amount = $xtPrice->xtcGetPrice($product->data['products_id'], false, 1, $product->data['products_tax_class_id'], $product->data['products_price']); 
      require (DIR_FS_EXTERNAL.'paypal/modules/installment.php');
      
      $presentment = sprintf($installment_html, $client_id, $_SESSION['currency'], $amount);
      $info_smarty->assign('PAYPAL_INSTALLMENT', $presentment);
    }
  }
?>