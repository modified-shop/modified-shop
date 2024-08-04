<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function get_admin_access($customers_id, $cache = true) {
    static $admin_access_array;
    
    if (!isset($admin_access_array)) $admin_access_array = array();
    
    if (!isset($admin_access_array[$customers_id]) || $cache === false) {
      $admin_access_array[$customers_id] = array();
      $admin_access_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_ADMIN_ACCESS . "
                                           WHERE customers_id = '" . xtc_db_input($customers_id) . "'");
      if (xtc_db_num_rows($admin_access_query) > 0) {
        $admin_access_array[$customers_id] = xtc_db_fetch_array($admin_access_query);
      }
    }
    
    return $admin_access_array[$customers_id];
  }
