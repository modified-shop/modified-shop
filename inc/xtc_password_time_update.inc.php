<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // updates the account with the columns of $sql_data_array (values as in xtc_db_perform()) and a new password version,
  // which ends the other sessions; $parameters are further conditions of the update
  // the version is one higher than the stored one and at least the current time, so two changes in the same second
  // never share a value; LAST_INSERT_ID() hands it to this connection only
  // returns the new version, or false when no row matched
  function xtc_password_time_update($customers_id, $sql_data_array = array(), $parameters = '') {
    $set = array("customers_password_time = LAST_INSERT_ID(GREATEST(customers_password_time + 1, ".time()."))");
    foreach ($sql_data_array as $column => $value) {
      switch ((string)$value) {
        case 'now()':
        case 'null':
          $set[] = $column." = ".$value;
          break;
        default:
          $set[] = $column." = '".xtc_db_input($value)."'";
          break;
      }
    }

    xtc_db_query("UPDATE ".TABLE_CUSTOMERS."
                     SET ".implode(",
                         ", $set)."
                   WHERE customers_id = '".(int)$customers_id."'".
                         (($parameters != '') ? " AND ".$parameters : ''));
    if (xtc_db_affected_rows() != 1) {
      return false;
    }

    $password_time_query = xtc_db_query("SELECT LAST_INSERT_ID() AS password_time");
    $password_time = xtc_db_fetch_array($password_time_query);
    return (int)$password_time['password_time'];
  }
