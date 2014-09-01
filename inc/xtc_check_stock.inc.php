<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_check_stock.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_check_stock.inc.php); www.nextcommerce.org 

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
 // include needed functions
 require_once(DIR_FS_INC . 'xtc_get_products_stock.inc.php');
  function xtc_check_stock($products_id, $products_quantity) {
    $stock_left = xtc_get_products_stock($products_id) - $products_quantity;
    $out_of_stock = '';

    if ($stock_left < 0) {
      $out_of_stock = '<span class="markProductOutOfStock">' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</span>';
    }

    return $out_of_stock;
  }
 ?>
