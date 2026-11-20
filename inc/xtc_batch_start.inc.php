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
   * xtc_batch_start()
   *
   * start of a job behind a public URL: one run at a time and one start per interval.
   * The lock lives on a plain connection of its own, because a second lock on the session's
   * connection would release the session lock on MySQL before 5.7.5, and it ends with the request
   *
   * @param string $configuration_key  row that keeps the time of the last start
   * @param int $interval  seconds between two starts
   * @return boolean
   */
  function xtc_batch_start($configuration_key, $interval) {
    $now = time();
    // a cron job arrives some seconds earlier or later
    $interval = max(0, (int)$interval - 10);

    $check_query = xtc_db_query("SELECT configuration_value
                                   FROM ".TABLE_CONFIGURATION."
                                  WHERE configuration_key = '".xtc_db_input($configuration_key)."'");
    $check = xtc_db_fetch_array($check_query);
    // a time in the future blocks nothing
    if (is_array($check)
        && (int)$check['configuration_value'] > ($now - $interval)
        && (int)$check['configuration_value'] <= $now
        )
    {
      return false;
    }

    if (!is_object(xtc_db_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, 'batch_lock_link', false))) {
      return false;
    }

    // named locks are server wide, the name carries the database
    $lock_query = xtc_db_query("SELECT GET_LOCK('".xtc_db_input('MODbat_'.md5(DB_DATABASE.'|'.$configuration_key))."', 0) AS batch_lock", 'batch_lock_link');
    $lock = xtc_db_fetch_array($lock_query);
    if (!is_array($lock) || !isset($lock['batch_lock']) || $lock['batch_lock'] != '1') {
      xtc_db_close('batch_lock_link');
      return false;
    }

    // of several calls only one changes the value, also when a short run ended in between
    if (is_array($check)) {
      xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                       SET configuration_value = '".$now."'
                     WHERE configuration_key = '".xtc_db_input($configuration_key)."'
                       AND configuration_value = '".xtc_db_input($check['configuration_value'])."'");
    } else {
      xtc_db_query("INSERT IGNORE INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('".xtc_db_input($configuration_key)."', '".$now."', '6', '0', NOW())");
    }
    if (xtc_db_affected_rows() < 1) {
      xtc_db_close('batch_lock_link');
      return false;
    }

    return true;
  }
