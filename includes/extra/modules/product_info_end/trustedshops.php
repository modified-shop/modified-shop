<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  if (defined('MODULE_TS_TRUSTEDSHOPS_ID') 
      && MODULE_TS_PRODUCT_STICKER_STATUS == '1'
      && MODULE_TS_PRODUCT_STICKER != ''
      )
  {    
    $product_sticker = MODULE_TS_PRODUCT_STICKER;
    $product_sticker = preg_replace('/data-sku="([\w\-\_]+)"/', 'data-sku="%s"', $product_sticker);
    $product_sticker = preg_replace('/data-gtin="([\w\-\_]+)"/', 'data-sku="%s"', $product_sticker);
    $product_sticker = preg_replace('/data-mpn="([\w\-\_]+)"/', 'data-sku="%s"', $product_sticker);
    
    $info_smarty->assign('MODULE_products_reviews', sprintf($product_sticker, MODULE_TS_TRUSTEDSHOPS_ID, $product->data['products_model']));
  }