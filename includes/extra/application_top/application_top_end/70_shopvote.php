<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
  
  
  if (defined('MODULE_SHOPVOTE_STATUS')
      && MODULE_SHOPVOTE_STATUS == 'true'
      && MODULE_SHOPVOTE_API_KEY != ''
      && MODULE_SHOPVOTE_API_SECRET != ''
      && (!defined('MODULE_SHOPVOTE_SCHEDULED_TASKS') || MODULE_SHOPVOTE_SCHEDULED_TASKS == 'false')
      && is_object($product) 
      && $product->isProduct() === true
      && (time() - strtotime($product->data['shopvote_last_imported']) >= 86400)
      )
  {
    // include needed classes
    require_once (DIR_FS_EXTERNAL.'shopvote/shopvote_import.php');

    $days = 365;
    $timestamp = strtotime($product->data['shopvote_last_imported']);
    if ($timestamp !== false) {
      $days = ceil((time() - $timestamp) / 86400);
      if ($days > 365) {
        $days = 365;
      }
    }

    $shopvote = new shopvote_import();
    $response = $shopvote->import($days, $product->data['products_id'], (int)strtotime($product->data['shopvote_last_imported']) < 1);
  
    if ($response === true) {
      xtc_db_query("UPDATE ".TABLE_PRODUCTS."
                       SET shopvote_last_imported = now()
                     WHERE products_id = '".(int)$product->data['products_id']."'");
    }
  }
