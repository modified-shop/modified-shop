<?php
/* -----------------------------------------------------------------------------------------
   $Id: order_details_cart.php 3717 2012-09-29 10:09:21Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(order_details.php,v 1.8 2003/05/03); www.oscommerce.com
   (c) 2003   nextcommerce (order_details.php,v 1.16 2003/08/17); www.nextcommerce.org
   (c) 2006 xt:Commerce (order_details_cart.php 1281 2005-10-03); www.xt-commerce.de

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c  Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$module_smarty = new Smarty;

$module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
if(defined('MODULE_PAYMENT_KLARNA_PARTPAYMENT_STATUS') && MODULE_PAYMENT_KLARNA_PARTPAYMENT_STATUS=='True' && strpos($_SESSION['customers_status']['customers_status_payment_unallowed'], 'klarna_partPayment') === false){
    include_once(DIR_WS_INCLUDES.'modules/payment/klarna/display_klarna_cart.php');
}

// include needed functions
require_once (DIR_FS_INC.'xtc_get_products_stock.inc.php');
require_once (DIR_FS_INC.'xtc_remove_non_numeric.inc.php');
require_once (DIR_FS_INC.'xtc_get_short_description.inc.php');
require_once (DIR_FS_INC.'xtc_format_price.inc.php');
require_once (DIR_FS_INC.'check_stock_specials.inc.php');

$module_content = array ();
$any_out_of_stock = '';
$mark_stock = '';
$hidden_options = '';

$products = $_SESSION['cart']->get_products();
for ($i = 0, $n = sizeof($products); $i < $n; $i ++) {

  if (STOCK_CHECK == 'true') {
    $mark_stock = xtc_check_stock($products[$i]['id'], $products[$i]['quantity']);
    if ($mark_stock) {
      $_SESSION['any_out_of_stock'] = 1;
    }
  }
  
  if (STOCK_CHECK_SPECIALS == 'true' && $xtPrice->xtcCheckSpecial($products[$i]['id'])) {
    $mark_stock = check_stock_specials($products[$i]['id'], $products[$i]['quantity']);
    if ($mark_stock) {
      $_SESSION['any_out_of_stock'] = 1;
    }  
  }
  
  $image = '';
  if ($products[$i]['image'] != '') {
    $image = $product->productImage($products[$i]['image'],'thumbnail');
  }

  $del_button = '<a href="' . xtc_href_link(FILENAME_SHOPPING_CART, 'action=remove_product&prd_id=' . $products[$i]['id'], 'NONSSL') . '">' . xtc_image_button('cart_del.gif', IMAGE_BUTTON_DELETE) . '</a>';
  $del_link = '<a href="' . xtc_href_link(FILENAME_SHOPPING_CART, 'action=remove_product&prd_id=' . $products[$i]['id'], 'NONSSL') . '">' . IMAGE_BUTTON_DELETE . '</a>';

  $module_content[$i] = array(
    'PRODUCTS_NAME' => $products[$i]['name'].$mark_stock,
    'PRODUCTS_QTY' => xtc_draw_input_field('cart_quantity[]', $products[$i]['quantity'], 'size="2"').
                      xtc_draw_hidden_field('products_id[]', $products[$i]['id']).
                      xtc_draw_hidden_field('old_qty[]', $products[$i]['quantity']),
    'PRODUCTS_MODEL' => $products[$i]['model'],
    'PRODUCTS_SHIPPING_TIME' => $products[$i]['shipping_time'],
    'PRODUCTS_TAX' => number_format($products[$i]['tax'], TAX_DECIMAL_PLACES), 
    'PRODUCTS_IMAGE' => $image, 
    'IMAGE_ALT' => $products[$i]['name'],
    'BOX_DELETE' => xtc_draw_checkbox_field('cart_delete[]', $products[$i]['id']), 
    'PRODUCTS_LINK' => xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($products[$i]['id'], $products[$i]['name'])), 
    'BUTTON_DELETE' => $del_button,
    'LINK_DELETE' => $del_link,                  
    'PRODUCTS_PRICE' => $xtPrice->xtcFormat($products[$i]['final_price'], true), // $products[$i]['final_price'] is quantity * plain price including attributes_price
    'PRODUCTS_SINGLE_PRICE' => $xtPrice->xtcFormat($products[$i]['price'], true), // $products[$i]['price'] is single plain price including attributes_price
    'PRODUCTS_WEIGHT' => $products[$i]['final_weight'], //$products[$i]['final_weight']  is quantity * products_weight including attributes_weight
    'PRODUCTS_SINGLE_WEIGHT' => $products[$i]['weight'], //$products[$i]['final_weight']  single products_weight including attributes_weight
    'PRODUCTS_SHORT_DESCRIPTION' => xtc_get_short_description($products[$i]['id']), 
    'BUTTON_WISHLIST' => $product->getCartToWishlistLink($products[$i]['id'], $products[$i]['name']), 
    'ATTRIBUTES' => '',
  );

  foreach(auto_include(DIR_FS_CATALOG.'includes/extra/modules/order_details_cart_content/','php') as $file) require ($file);
  //products attributes
  if (isset ($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
    $subindex = 0;
    reset($products[$i]['attributes']);
    while (list ($option, $value) = each($products[$i]['attributes'])) {
      $hidden_options .= xtc_draw_hidden_field('id['.$products[$i]['id'].']['.$option.']', $value);

      $attributes = $main->getAttributes($products[$i]['id'],$option,$value);

      $attribute_stock_check = '';
      if (ATTRIBUTE_STOCK_CHECK == 'true' && STOCK_CHECK == 'true') {
        if ($attributes['attributes_stock'] - $products[$i]['quantity'] < 0) {
          $attribute_stock_check  = '<span class="markProductOutOfStock">' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</span>';
          $_SESSION['any_out_of_stock'] = 1;
        }
      }

      $module_content[$i]['ATTRIBUTES'][$subindex] = array(
        'ID' => $attributes['products_attributes_id'],
        'MODEL' => $attributes['attributes_model'],
        'EAN' => $attributes['attributes_ean'],
        'NAME' => $attributes['products_options_name'],
        'VALUE_NAME' => $attributes['products_options_values_name'].$attribute_stock_check
      );
      
      foreach(auto_include(DIR_FS_CATALOG.'includes/extra/modules/order_details_cart_attributes/','php') as $file) require ($file);
    
      $subindex++;
    }
  }
}
$smarty->assign('HIDDEN_OPTIONS', $hidden_options);

$discount = 0;
$total_content = '';
$total =$_SESSION['cart']->show_total();
if ($_SESSION['customers_status']['customers_status_ot_discount_flag'] == '1' && $_SESSION['customers_status']['customers_status_ot_discount'] != '0.00') {
  if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
    $price = $total-$_SESSION['cart']->show_tax(false);
  } else {
    $price = $total;
  }
  $discount = $xtPrice->xtcGetDC($price, $_SESSION['customers_status']['customers_status_ot_discount']);
  $total_content = $_SESSION['customers_status']['customers_status_ot_discount'].' % '.SUB_TITLE_OT_DISCOUNT.' -'.xtc_format_price($discount, $price_special = 1, $calculate_currencies = false).'<br />';
}

if ($_SESSION['customers_status']['customers_status_show_price'] == '1') {
  if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0) $total-=$discount;
  if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) $total-=$discount;
  if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 1) $total-=$discount;
  $total_content .= SUB_TITLE_SUB_TOTAL.$xtPrice->xtcFormat($total, true).'<br />';
} else {
  $total_content .= NOT_ALLOWED_TO_SEE_PRICES.'<br />';
}

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/modules/order_details_cart_total/','php') as $file) require ($file);
if (SHOW_SHIPPING == 'true') {
  $module_smarty->assign('SHIPPING_INFO', $main->getShippingLink()); //web28 -2012-09-29 - use main function
}
if ($_SESSION['customers_status']['customers_status_show_price'] == '1' && MODULE_SMALL_BUSINESS != 'true') {
  $module_smarty->assign('UST_CONTENT', $_SESSION['cart']->show_tax());
}

include DIR_FS_CATALOG.'/includes/shipping_estimate.php';

$module_smarty->assign('TOTAL_CONTENT', $total_content);
$module_smarty->assign('language', $_SESSION['language']);
$module_smarty->assign('module_content', $module_content);
$module_smarty->assign('TOTAL_WEIGHT', $_SESSION['cart']->weight + SHIPPING_BOX_WEIGHT);

$module_smarty->caching = 0;
$module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/order_details.html');

$smarty->assign('MODULE_order_details', $module);
?>