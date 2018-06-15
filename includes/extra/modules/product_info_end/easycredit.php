<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
  
  require_once(DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/payment/easycredit.php');
  require_once(DIR_WS_MODULES.'payment/easycredit.php');
  $easycredit = new easycredit();

  require_once (DIR_FS_INC.'xtc_get_countries.inc.php');
  $country = xtc_get_countriesList(((isset($_SESSION['country'])) ? $_SESSION['country'] : ((isset($_SESSION['customer_country_id'])) ? $_SESSION['customer_country_id'] : STORE_COUNTRY)), true);
  
  if ($easycredit->enabled === true
      && $country['countries_iso_code_2'] == 'DE'
      ) 
  {  
    $amount = $xtPrice->xtcGetPrice($product->data['products_id'], false, 1, $product->data['products_tax_class_id'], $product->data['products_price']); 
    $presentment = $easycredit->get_presentment_product($amount);
    $info_smarty->assign('EASYCREDIT', $presentment);
  }
?>