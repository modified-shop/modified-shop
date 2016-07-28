<?php

//VPE Attribut
$vpe_price = $full;
$attr_vpe_name = $attr_vpe_price = '';
$attr_vpe_value = $attr_vpe_price_plain = 0;
$vpe_value = $product->data['products_vpe_value'] + $products_options['attributes_vpe_value'];
switch ($products_options['weight_prefix']) {
  case '-':
    $vpe_value = $product->data['products_vpe_value'] - $products_options['attributes_vpe_value'];
    break;
  case '=':
    $vpe_value = $products_options['attributes_vpe_value'];
    break;
}

if ($products_options['attributes_vpe_value'] != 0.0 && $vpe_price > 0) {
  $attr_vpe_name = xtc_get_vpe_name($products_options['attributes_vpe_id']);
  $attr_vpe_value = $vpe_value;
  $attr_vpe_price_plain = $xtPrice->xtcFormat($vpe_price * (1 / $vpe_value), false);
  $attr_vpe_price = $xtPrice->xtcFormatCurrency($attr_vpe_price_plain, 0, false);
}

$products_options_data[$row]['DATA'][$col]['VPE_NAME'] = $attr_vpe_name;
$products_options_data[$row]['DATA'][$col]['VPE_VALUE'] = $attr_vpe_value;
$products_options_data[$row]['DATA'][$col]['VPE_PRICE'] = $attr_vpe_price;
$products_options_data[$row]['DATA'][$col]['VPE_PRICE_PLAIN'] = $attr_vpe_price_plain;
