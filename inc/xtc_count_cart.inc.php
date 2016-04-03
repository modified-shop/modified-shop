<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_count_cart.inc.php 1205 2010-08-24 10:08:53Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 XT-Commerce (xtc_count_cart.inc.php 975 2005-06-07)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // counts total ammount of a product ID in cart.
  function xtc_count_cart() {
    $id_list = $_SESSION['cart']->get_product_id_list();    
    $id_list_array = explode(',', $id_list);
    $id_list_array = array_map('trim', $id_list_array);
    $actual_content = array ();
    for ($i = 0, $n = sizeof($id_list_array); $i < $n; $i ++) {
      $actual_content[] = array ('id' => $id_list_array[$i], 'qty' => $_SESSION['cart']->get_quantity($id_list_array[$i]));
    }
    
    // merge product IDs
    for ($i = 0, $n = sizeof($actual_content); $i < $n; $i ++) {
      $act_id = xtc_get_prid($actual_content[$i]['id']);
      if (!isset($_SESSION['actual_content'][$act_id]['qty'])) {
        $_SESSION['actual_content'][$act_id]['qty'] = 0;
      }
      $_SESSION['actual_content'][$act_id]['qty'] += $actual_content[$i]['qty'];
    }    
  }
?>