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
    
    // the model number goes into an attribute of the sticker
    require_once(DIR_FS_INC.'encode_model_number.inc.php');
    if (substr_count($product_sticker, '%s') == 1) {
      $info_smarty->assign('MODULE_products_reviews', sprintf($product_sticker, encode_model_number($product->data['products_model'])));
    } elseif (substr_count($product_sticker, '%s') == 2) {
      // two placeholders are the script form of the sticker: the number is written for a script string, which is safe in an attribute too
      $info_smarty->assign('MODULE_products_reviews', sprintf($product_sticker, MODULE_TS_TRUSTEDSHOPS_ID, strtr((string)$product->data['products_model'], array('\\' => '\\\\', "'" => '\\x27', '"' => '\\x22', '<' => '\\x3C', '>' => '\\x3E', '&' => '\\x26', "\r" => ' ', "\n" => ' '))));
    } else {
      $info_smarty->assign('MODULE_products_reviews', $product_sticker);
    }
  }