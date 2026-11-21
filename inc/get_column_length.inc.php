<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  /**
   * get_column_length()
   *
   * the length of a char or varchar column; without strict mode the database
   * cuts longer values, so a check before saving needs the real limit
   *
   * @param string $table table name
   * @param string $column column name
   * @return int 0 when the column has no character length
   */
  function get_column_length($table, $column) {
    $column_query = xtc_db_query("SHOW COLUMNS FROM " . $table . " LIKE '" . xtc_db_input($column) . "'");
    $column_data = xtc_db_fetch_array($column_query);
    if (is_array($column_data) && preg_match('/^(?:var)?char\((\d+)\)/i', $column_data['Type'], $match)) {
      return (int)$match[1];
    }

    return 0;
  }
?>
