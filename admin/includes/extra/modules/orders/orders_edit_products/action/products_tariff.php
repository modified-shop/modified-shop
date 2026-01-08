<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  if (defined('MODULE_PRODUCTS_TARIFF_STATUS')
      && MODULE_PRODUCTS_TARIFF_STATUS == 'true'
      && isset($_GET['subaction'])
      && $_GET['subaction'] == 'tariff'
      )
  {
    $products_tariff_query = xtc_db_query("SELECT * 
                                             FROM ".TABLE_ORDERS_PRODUCTS." 
                                            WHERE orders_id = '".(int)$_POST['oID']."' 
                                              AND orders_products_id = '".(int)$_POST['opID']."'");
    if (xtc_db_num_rows($products_tariff_query) > 0) {
      $sql_data_array = array(
        'products_tariff' => $_POST['products_tariff'],
        'products_origin' => $_POST['products_origin'],
        'products_tariff_title' => $_POST['products_tariff_title'],
      );
      xtc_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array, 'update', "orders_id = '".(int)$_POST['oID']."' AND orders_products_id = '".(int)$_POST['opID']."'");
    }
    
    xtc_redirect(xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=products&oID='.(int)$_POST['oID']));
  }