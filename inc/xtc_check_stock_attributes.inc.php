<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003 nextcommerce (xtc_check_stock_attributes.inc.php); www.nextcommerce.org 
   (c) 2006 XT-Commerce (xtc_check_stock_attributes.inc.php 899 2005-04-29)

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_check_stock_attributes($attribute_id, $products_quantity) {
    $out_of_stock = '';

    //BOF - DokuMan - 2010-02-26 - security fix for attributes selection
    /*   
    $stock_query=xtc_db_query("SELECT
                             attributes_stock
                             FROM ".TABLE_PRODUCTS_ATTRIBUTES."
                             WHERE products_attributes_id='".$attribute_id."'");
    */
    $stock_query=xtc_db_query("SELECT
                             attributes_stock
                             FROM ".TABLE_PRODUCTS_ATTRIBUTES."
                             WHERE products_attributes_id=".(int)$attribute_id);
    //EOF - DokuMan - 2010-02-26 - security fix for attributes selection

    $stock_data=xtc_db_fetch_array($stock_query);
    $stock_left = $stock_data['attributes_stock'] - $products_quantity;
    $out_of_stock = '';

    if ($stock_left < 0) {
      $out_of_stock = '<span class="markProductOutOfStock">' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</span>';
    }

    return $out_of_stock;
  }
?>