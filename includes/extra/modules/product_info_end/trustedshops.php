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
    
    $placeholders = substr_count($product_sticker, '%s');
    if ($placeholders == 1 || $placeholders == 2) {
      // the model number is written for the place of its placeholder: a script, or markup
      require_once(DIR_FS_INC.'encode_model_number.inc.php');
      require_once(DIR_FS_INC.'markup_ends_in_script.inc.php');
      $products_model = (string)$product->data['products_model'];
      if (markup_ends_in_script(substr($product_sticker, 0, strrpos($product_sticker, '%s')))) {
        $products_model = strtr($products_model, array('\\' => '\\\\', "'" => '\\u0027', '"' => '\\u0022', '`' => '\\u0060', '$' => '\\u0024', '<' => '\\u003C', '>' => '\\u003E', '&' => '\\u0026', "\r" => '\\r', "\n" => '\\n'));
      } else {
        $products_model = encode_model_number($products_model);
      }
      if ($placeholders == 1) {
        $info_smarty->assign('MODULE_products_reviews', sprintf($product_sticker, $products_model));
      } else {
        $info_smarty->assign('MODULE_products_reviews', sprintf($product_sticker, MODULE_TS_TRUSTEDSHOPS_ID, $products_model));
      }
    } else {
      $info_smarty->assign('MODULE_products_reviews', $product_sticker);
    }
  }