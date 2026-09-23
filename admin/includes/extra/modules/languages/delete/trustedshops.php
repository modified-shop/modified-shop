<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  // the table only exists while the module is installed
  $table_query = xtc_db_query("SHOW TABLES LIKE '" . str_replace('_', '\\_', TABLE_TRUSTEDSHOPS) . "'");
  if (!$table_query) {
    $delete_failed = true;
  } elseif (xtc_db_num_rows($table_query) > 0) {
    if (!xtc_db_query("DELETE FROM " . TABLE_TRUSTEDSHOPS . " WHERE languages_id = '" . (int)$lID . "'")) {
      $delete_failed = true;
    }
  }
