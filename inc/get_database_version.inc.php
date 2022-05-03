<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified eCommerce Shopsoftware
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
    
  function get_database_version() {
    static $database_version;
    
    if (!isset($database_version)) {
      $check_query = xtDBquery("SELECT version 
                                  FROM ".TABLE_DATABASE_VERSION."
                              ORDER BY id DESC");
      $check = xtc_db_fetch_array($check_query, true);
    
      $database_version = array(
        'plain' => preg_replace('/[^0-9\.]/', '', $check['version']),
        'full' => $check['version'],
      );
    }
    
    return $database_version;
  }
