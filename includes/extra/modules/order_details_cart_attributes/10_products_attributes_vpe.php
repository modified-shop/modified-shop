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
    switch ($attributes['weight_prefix']) {
      case '-':
        $vpe_value -= $attributes['attributes_vpe_value'];
        break;
      case '=':
        $vpe_value = $attributes['attributes_vpe_value'];
        break;
      default:
        $vpe_value += $attributes['attributes_vpe_value'];
    }
            
    $vpe_array = array(
      'products_vpe_status' => $products[$i]['vpe_status'],
      'products_vpe_value' => $vpe_value,
      'products_vpe' => (($attributes['attributes_vpe_id'] > 0) ? $attributes['attributes_vpe_id'] : $products[$i]['vpe_id']),
    );
    $module_content[$i]['PRODUCTS_VPE'] = $main->getVPEtext($vpe_array, $products[$i]['price']);
    $module_content[$i]['PRODUCTS_VPE_NAME'] = $main->vpe_name;
    $module_content[$i]['PRODUCTS_VPE_VALUE'] = $vpe_value;
  }
