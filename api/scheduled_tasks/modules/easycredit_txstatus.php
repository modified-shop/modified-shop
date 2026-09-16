<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function cron_easycredit_txstatus() {
    $table_query = xtc_db_query("SHOW TABLES LIKE 'easycredit'");
    if ($table_query === false || xtc_db_num_rows($table_query) !== 1) {
      return false;
    }

    require_once(DIR_FS_EXTERNAL.'Teambank/classes/TeambankPayment.php');

    $TeambankPayment = new TeambankPayment();

    return $TeambankPayment->process_pending_transactions();
  }
