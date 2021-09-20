<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  function get_customers_status_by_id($customers_status_id) {
    static $customers_status_array;
    
    if (!isset($customers_status_array)) {
      $customers_status_array = array();
    }
    
    if (!isset($customers_status_array[$customers_status_id])) {
      $customers_status_query = xtDBquery("SELECT *
                                             FROM " . TABLE_CUSTOMERS_STATUS . "
                                            WHERE customers_status_id = '" . (int)$customers_status_id . "'
                                              AND language_id = '" . (int)$_SESSION['languages_id'] . "'");
      $customers_status_array[$customers_status_id] = xtc_db_fetch_array($customers_status_query, true);
    }
    
    return $customers_status_array[$customers_status_id];
  }
?>