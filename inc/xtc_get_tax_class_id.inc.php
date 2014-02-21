<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_tax_class_id.inc.php 899 2005-04-29 02:40:57Z hhgag $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_get_tax_class_id($products_id) {

    $tax_query = xtc_db_query("SELECT products_tax_class_id
                                 FROM ".TABLE_PRODUCTS."
                                WHERE products_id='".(int)$products_id."'");
    $tax_data = xtc_db_fetch_array($tax_query);

    return $tax_data['products_tax_class_id'];
  }
 ?>