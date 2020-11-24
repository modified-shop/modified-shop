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

  require_once (DIR_FS_INC.'xtc_get_countries.inc.php');
  $country = xtc_get_countriesList(((isset($_SESSION['country'])) ? $_SESSION['country'] : ((isset($_SESSION['customer_country_id'])) ? $_SESSION['customer_country_id'] : STORE_COUNTRY)), true);
  
  if ($paypal_installment->check_install() === true
      && $paypal_installment->get_config('PAYPAL_MODE') == 'live'
      && $paypal_installment->get_config('PAYPAL_INSTALLMENT_BANNER_DISPLAY') == 1
      )
  {
    $client_id = $paypal_installment->get_config('PAYPAL_CLIENT_ID_'.strtoupper($paypal_installment->get_config('PAYPAL_MODE')));
    
    if ($client_id != '') {
      $amount = $_SESSION['cart']->show_total();
      require (DIR_FS_EXTERNAL.'paypal/modules/installment.php');
      
      $presentment = sprintf($installment_js, $client_id, $_SESSION['currency'], $amount, $country['countries_iso_code_2'], 'flex', $paypal_installment->get_config('PAYPAL_INSTALLMENT_BANNER_COLOR'));
      $module_smarty->assign('PAYPAL_INSTALLMENT', $installment_container.$presentment);
    }
  }
?>