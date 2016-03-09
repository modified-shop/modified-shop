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
  $paypal_cart = new PayPalPayment('paypalcart');
  if ($paypal_cart->enabled === true
      && $paypal_cart->get_config('MODULE_PAYMENT_'.strtoupper($paypal_cart->code).'_SHOW_PRODUCT') == '1'
      ) 
  {
    $info_smarty->assign('ADD_CART_BUTTON_PAYPAL', $paypal_cart->product_checkout_button());
  }
?>