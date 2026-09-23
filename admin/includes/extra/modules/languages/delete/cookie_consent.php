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

  // the tables survive an uninstall of the module, so they are checked instead of its status
  $cookie_consent_tables = array(TABLE_COOKIE_CONSENT_CATEGORIES, TABLE_COOKIE_CONSENT_COOKIES);

  foreach ($cookie_consent_tables as $cookie_consent_table) {
    $table_query = xtc_db_query("SHOW TABLES LIKE '" . str_replace('_', '\\_', $cookie_consent_table) . "'");
    if (!$table_query) {
      $delete_failed = true;
    } elseif (xtc_db_num_rows($table_query) > 0) {
      if (!xtc_db_query("DELETE FROM " . $cookie_consent_table . " WHERE languages_id = '" . (int)$lID . "'")) {
        $delete_failed = true;
      }
    }
  }
