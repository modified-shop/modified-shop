<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // one row per failed attempt, so every write stands on its own
  function xtc_withdraw_attempts_add($ip, $email_address) {
    // entries outside the window never count again, so they can go
    xtc_db_query("DELETE FROM ".TABLE_ORDERS_WITHDRAW_ATTEMPTS."
                        WHERE date_added < now() - interval ".(int)MODULE_WITHDRAW_ATTEMPT_MINUTES." minute");

    $sql_data_array = array(
      'customers_ip' => $ip,
      'customers_email_address' => $email_address,
      'date_added' => 'now()',
    );
    xtc_db_perform(TABLE_ORDERS_WITHDRAW_ATTEMPTS, $sql_data_array);
  }
 ?>
