<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
  if ((defined('MODULE_PAYMENT_EASYCREDIT_STATUS') && MODULE_PAYMENT_EASYCREDIT_STATUS == 'True')
      || (defined('MODULE_PAYMENT_EASYINVOICE_STATUS') && MODULE_PAYMENT_EASYINVOICE_STATUS == 'True')
      )
  {
    $amount = $xtPrice->xtcGetPrice($product->data['products_id'], false, 1, $product->data['products_tax_class_id'], $product->data['products_price']); 
    
    $payment_array = array();
    if (defined('MODULE_PAYMENT_EASYCREDIT_STATUS') && MODULE_PAYMENT_EASYCREDIT_STATUS == 'True') $payment_array[] = 'INSTALLMENT';
    if (defined('MODULE_PAYMENT_EASYINVOICE_STATUS') && MODULE_PAYMENT_EASYINVOICE_STATUS == 'True') $payment_array[] = 'BILL';

    $presentment  = '<style>easycredit-widget{width:100%;}</style>';
    $presentment .= '<script type="module" src="https://ratenkauf.easycredit.de/api/resource/webcomponents/v3/easycredit-components/easycredit-components.esm.js"></script>';
    $presentment .= '<easycredit-widget webshop-id="'.MODULE_PAYMENT_EASYCREDIT_SHOP_ID.'" amount="'.$amount.'" payment-types="'.implode(',', $payment_array).'" display-type="minimal" class="hydrated"></easycredit-widget>';
    
    $info_smarty->assign('EASYCREDIT', $presentment);
  }
