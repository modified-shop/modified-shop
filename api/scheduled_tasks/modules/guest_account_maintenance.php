<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function cron_guest_account_maintenance() {
    if (!defined('DELETE_GUEST_ACCOUNT') || DELETE_GUEST_ACCOUNT != 'true') {
      return true;
    }

    $days = (defined('DELETE_GUEST_ACCOUNT_DAYS') && (int)DELETE_GUEST_ACCOUNT_DAYS > 0) ? (int)DELETE_GUEST_ACCOUNT_DAYS : 1;
    $date_threshold = date('Y-m-d H:i:s', strtotime('-'.$days.' days'));

    $customers_query = xtc_db_query("SELECT c.customers_id
                                        FROM ".TABLE_CUSTOMERS." c
                                   LEFT JOIN ".TABLE_ORDERS." o ON o.customers_id = c.customers_id
                                       WHERE c.account_type = 1
                                         AND c.customers_date_added < '".xtc_db_input($date_threshold)."'
                                         AND o.orders_id IS NULL");
    while ($customers = xtc_db_fetch_array($customers_query)) {
      xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS." WHERE customers_id = '".(int)$customers['customers_id']."'");
      xtc_db_query("DELETE FROM ".TABLE_ADDRESS_BOOK." WHERE customers_id = '".(int)$customers['customers_id']."'");
      xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_INFO." WHERE customers_info_id = '".(int)$customers['customers_id']."'");
      xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_IP." WHERE customers_id = '".(int)$customers['customers_id']."'");
      xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_BASKET." WHERE customers_id = '".(int)$customers['customers_id']."'");
      xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_BASKET_ATTRIBUTES." WHERE customers_id = '".(int)$customers['customers_id']."'");
    }

    return true;
  }
