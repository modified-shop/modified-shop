<?php
/* -----------------------------------------------------------------------------------------
   $Id: header.php 3808 2012-10-28 20:39:04Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once(DIR_FS_INC.'xtc_restock_order.inc.php');

  function xtc_remove_order($order_id, $restock = false, $activate = true) {
    if ($restock == 'on') {
      xtc_restock_order($order_id, $activate);
    }
    xtc_db_query("DELETE FROM ".TABLE_ORDERS." WHERE orders_id = '".(int)$order_id."'");
    xtc_db_query("DELETE FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_id = '".(int)$order_id."'");
    xtc_db_query("DELETE FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES." WHERE orders_id = '".(int)$order_id."'");
    xtc_db_query("DELETE FROM ".TABLE_ORDERS_STATUS_HISTORY." WHERE orders_id = '".(int)$order_id."'");
    xtc_db_query("DELETE FROM ".TABLE_ORDERS_TOTAL." WHERE orders_id = '".(int)$order_id."'");
    xtc_db_query("DELETE FROM ".TABLE_ORDERS_PRODUCTS_DOWNLOAD." WHERE orders_id = '".(int)$order_id."'");

    /******** SHOPGATE **********/
    if(defined('MODULE_PAYMENT_SHOPGATE_STATUS') && MODULE_PAYMENT_SHOPGATE_STATUS=='True') {
      $sql_select = "SHOW TABLES LIKE '" . TABLE_SHOPGATE_ORDERS . "'";
      $query = xtc_db_query($sql_select);
      if (xtc_db_num_rows($query) > 0) {
        xtc_db_query("DELETE FROM " . TABLE_SHOPGATE_ORDERS . " WHERE orders_id = '" . (int)$order_id . "'");
      }
    }
    /******** SHOPGATE **********/
  }
?>