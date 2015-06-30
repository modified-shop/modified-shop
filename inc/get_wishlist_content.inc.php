<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2014 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  // include needed functions
  require_once(DIR_FS_INC.'xtc_get_products_image.inc.php');

  function get_wishlist_content() {
    global $main, $xtPrice, $product, $PHP_SELF;
    
    $module_data = array();
    
    // build array with wishlist content and count quantity  
    $products = $_SESSION['wishlist']->get_products();

    for ($i = 0, $n = sizeof($products); $i < $n; $i++) {
      $del_button = '<a href="' . xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action', 'box', 'prd_id')).'action=remove_product&wishlist=true&prd_id=' . $products[$i]['id'], 'NONSSL') . '">' . xtc_image_button('wishlist_del.gif', IMAGE_BUTTON_DELETE) . '</a>';
      $cart_del_button = '<a href="' . xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action', 'box', 'prd_id')).'action=remove_product&whishlist=true&box=cart&prd_id=' . $products[$i]['id'], 'NONSSL') . '">' . xtc_image_button('cart_del.gif', IMAGE_BUTTON_DELETE) . '</a>';

       //get $shipping_status_name, $shipping_status_image
      $shipping_status_name = $shipping_status_image = $shipping_status_link = '';
      if (isset($products[$i]['shippingtime']) && ACTIVATE_SHIPPING_STATUS == 'true') {
        $shipping_status_name = $main->getShippingStatusName($products[$i]['shippingtime']);
        $shipping_status_image = $main->getShippingStatusImage($products[$i]['shippingtime']);
        $shipping_status_link = $main->getShippingStatusName($products[$i]['shippingtime'], true);      
      }
  
      $module_data[] = array ('PRODUCTS_LINK' => xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($products[$i]['id'], $products[$i]['name'])),
                              'PRODUCTS_NAME' => $products[$i]['name'],
                              'PRODUCTS_IMAGE' => $product->productImage(xtc_get_products_image(xtc_get_prid($products[$i]['id'])), 'thumbnail'),
                              'PRODUCTS_BUTTON_DELETE' => $del_button,
                              'PRODUCTS_BUTTON_DELETE_CART' => $cart_del_button,
                              'PRODUCTS_VPE' => $products[$i]['vpe'],
                              'PRODUCTS_SHIPPING_LINK' => $main->getShippingLink(),
                              'PRODUCTS_SHIPPING_NAME' => $shipping_status_name,
                              'PRODUCTS_SHIPPING_IMAGE' => $shipping_status_image,
                              'PRODUCTS_SHIPPING_NAME_LINK' => $shipping_status_link,
                              'PRODUCTS_TAX_INFO' => $main->getTaxInfo($products[$i]['tax']),
                              'PRODUCTS_PRICE' => $xtPrice->xtcFormat($products[$i]['price'], true, 0, false, 0, 0, 0),
                              'PRODUCTS_BUTTON_BUY_NOW' => $product->getWishlistToCartButton($products[$i]['id'], $products[$i]['name']),
                              'PRODUCTS_QTY' => $products[$i]['quantity'],
                              );
    }
    
    return $module_data;
  }
?>