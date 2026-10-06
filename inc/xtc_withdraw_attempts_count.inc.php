<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // counts the failed order lookups of the last window per ip and per e-mail address
  function xtc_withdraw_attempts_count($ip, $email_address) {
    $attempts = array('ip' => 0, 'email' => 0);

    // an unknown ip must not lock out everybody behind the same proxy
    if ($ip != '') {
      $count_query = xtc_db_query("SELECT COUNT(*) AS total
                                     FROM ".TABLE_ORDERS_WITHDRAW_ATTEMPTS."
                                    WHERE customers_ip = '".xtc_db_input($ip)."'
                                      AND date_added >= now() - interval ".(int)MODULE_WITHDRAW_ATTEMPT_MINUTES." minute");
      $count = xtc_db_fetch_array($count_query);
      $attempts['ip'] = (int)$count['total'];
    }

    if ($email_address != '') {
      $count_query = xtc_db_query("SELECT COUNT(*) AS total
                                     FROM ".TABLE_ORDERS_WITHDRAW_ATTEMPTS."
                                    WHERE customers_email_address = '".xtc_db_input($email_address)."'
                                      AND date_added >= now() - interval ".(int)MODULE_WITHDRAW_ATTEMPT_MINUTES." minute");
      $count = xtc_db_fetch_array($count_query);
      $attempts['email'] = (int)$count['total'];
    }

    return $attempts;
  }
 ?>
