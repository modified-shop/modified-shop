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
  
  if ($paypal_installment->enabled === true
      && $country['countries_iso_code_2'] == 'DE'
      ) 
  {
    $total_amount = $_SESSION['cart']->show_total(); 
    
    $min_amount = $paypal_installment->get_min_installment_amount();
    $max_amount = $paypal_installment->get_max_installment_amount();
    
    if ((string)$total_amount >= (string)$min_amount['amount']
        && (string)$total_amount <= (string)$max_amount['amount']
        )
    {
      $pp_smarty = new Smarty();

      if ($paypal_installment->get_config('MODULE_PAYMENT_'.strtoupper($paypal_installment->code).'_UPSTREAM_CART') == '1') {
        $presentment_array = $paypal_installment->get_presentment($total_amount, $_SESSION['currency'], $country['countries_iso_code_2'], true);
        $pp_smarty->assign('presentment', array($presentment_array));
        $pp_smarty->assign('details', (((int)$presentment_array['apr'] == 0) ? '0' : '1'));
        if ((int)$presentment_array['apr'] == 0) {
          $pp_smarty->assign('logo_image', xtc_image(DIR_WS_IMAGES.'icons/pp_credit-german_h_rgb.png'));
        }
      } else {
        $pp_smarty->assign('logo_image', xtc_image(DIR_WS_IMAGES.'icons/pp_credit-german_h_rgb.png'));
      }

      $total = $total_amount;
      include(DIR_FS_EXTERNAL.'paypal/modules/presentment.php');
    
      $pp_smarty->assign('link_class', $link_class);
      $pp_smarty->assign('link', $link);
      $pp_smarty->assign('creditor', $store_owner);
      $pp_smarty->assign('notice', TEXT_PAYPALINSTALLMENT_NOTICE_CART);
      $pp_smarty->assign('cart', true);

      $pp_smarty->assign('total_amount', $paypal_installment->format_price_currency($total_amount));
      
      $pp_smarty->assign('language', $_SESSION['language']);
      $presentment = $pp_smarty->fetch(DIR_FS_EXTERNAL.'paypal/templates/presentment_info.html');
      $module_smarty->assign('PAYPAL_INSTALLMENT', $presentment);
    } else {
      $pp_smarty = new Smarty();
      $pp_smarty->assign('cart', true);
      $pp_smarty->assign('nopresentment', true);
      $pp_smarty->assign('min_amount', $paypal_installment->format_price_currency($min_amount['amount']));
      $pp_smarty->assign('max_amount', $paypal_installment->format_price_currency($max_amount['amount']));
      $pp_smarty->assign('logo_image', xtc_image(DIR_WS_IMAGES.'icons/pp_credit-german_h_rgb.png'));

      $pp_smarty->assign('language', $_SESSION['language']);
      $presentment = $pp_smarty->fetch(DIR_FS_EXTERNAL.'paypal/templates/presentment_info.html');
      $module_smarty->assign('PAYPAL_INSTALLMENT', $presentment);
    }
  }
?>