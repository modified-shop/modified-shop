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
   * The lock is on the connection the run works with, a connection of its own stays idle during
   * a long run and the server closes it. Before MySQL 5.7.5 the session lock of this request ends here
   *
   * @param string $configuration_key  row that keeps the time of the last start
   * @param int $interval  seconds between two starts
   * @return boolean
   */
  function xtc_batch_start($configuration_key, $interval) {
    // a cron job arrives some seconds earlier or later
    $interval = max(0, (int)$interval - 10);
    $key = xtc_db_input($configuration_key);

    // most calls end here without the lock
    if (xtc_batch_start_time($key, $interval) === false) {
      return false;
    }

    // named locks are server wide, the name carries the database
    $lock_name = 'MODbat_'.md5(DB_DATABASE.'|'.$configuration_key);
    $lock_query = xtc_db_query("SELECT GET_LOCK('".xtc_db_input($lock_name)."', 0) AS batch_lock");
    $lock = (($lock_query !== false) ? xtc_db_fetch_array($lock_query) : false);
    if (!is_array($lock) || !isset($lock['batch_lock']) || $lock['batch_lock'] != '1') {
      return false;
    }
    // a persistent connection outlives the request
    register_shutdown_function('xtc_batch_end', $lock_name);

    // read again under the lock, another run can have started and ended in between
    $time_start = xtc_batch_start_time($key, $interval);
    if ($time_start === false) {
      xtc_batch_end($lock_name);
      return false;
    }

    if ($time_start === null) {
      $stored = xtc_db_query("INSERT IGNORE INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('".$key."', UNIX_TIMESTAMP(), '6', '0', NOW())");
    } else {
      $stored = xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                                 SET configuration_value = UNIX_TIMESTAMP()
                               WHERE configuration_key = '".$key."'");
    }
    if ($stored === false) {
      xtc_batch_end($lock_name);
      return false;
    }

    return true;
  }


  /**
   * xtc_batch_start_time()
   *
   * the stored time of the last start, null without one, false when the interval has not passed.
   * Both times come from the database in one statement, a time in the future blocks nothing
   *
   * @param string $key  escaped configuration key
   * @param int $interval
   * @return mixed
   */
  function xtc_batch_start_time($key, $interval) {
    $check_query = xtc_db_query("SELECT UNIX_TIMESTAMP() AS time_now,
                                        (SELECT configuration_value
                                           FROM ".TABLE_CONFIGURATION."
                                          WHERE configuration_key = '".$key."') AS time_start");
    $check = (($check_query !== false) ? xtc_db_fetch_array($check_query) : false);
    if (!is_array($check)) {
      return false;
    }
    if ($check['time_start'] === null) {
      return null;
    }
    if ((int)$check['time_start'] > ((int)$check['time_now'] - $interval)
        && (int)$check['time_start'] <= (int)$check['time_now']
        )
    {
      return false;
    }

    return (int)$check['time_start'];
  }


  /**
   * xtc_batch_end()
   *
   * @param string $lock_name
   */
  function xtc_batch_end($lock_name) {
    global $db_link;

    // a connection that is closed has let go of the lock already
    if (is_object($db_link)) {
      try {
        xtc_db_query("SELECT RELEASE_LOCK('".xtc_db_input($lock_name)."')");
      } catch (Throwable $ex) {}
    }
  }
