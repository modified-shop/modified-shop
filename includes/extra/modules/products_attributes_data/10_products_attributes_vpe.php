<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  if ($_SESSION['customers_status']['customers_status_show_price'] == '1') {
    $vpe_value = $product->data['products_vpe_value'] + $products_options['attributes_vpe_value'];
    switch ($products_options['weight_prefix']) {
      case '-':
        $vpe_value = $product->data['products_vpe_value'] - $products_options['attributes_vpe_value'];
        break;
      case '=':
        $vpe_value = $products_options['attributes_vpe_value'];
        break;
    }
    
    $vpe_array = array(
      'products_vpe_status' => $product->data['products_vpe_status'],
      'products_vpe_value' => $vpe_value,
      'products_vpe' => (($products_options['attributes_vpe_id'] > 0) ? $products_options['attributes_vpe_id'] : $product->data['products_vpe']),
    );
    $products_options_data[$row]['DATA'][$col]['VPE'] = $main->getVPEtext($vpe_array, $full);
    $products_options_data[$row]['DATA'][$col]['VPE_NAME'] = $main->vpe_name;
    $products_options_data[$row]['DATA'][$col]['VPE_VALUE'] = $vpe_value;
  }