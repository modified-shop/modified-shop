<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // only the e-mail counter is cleared, the rows keep counting for the ip until they expire
  function xtc_withdraw_attempts_reset($email_address) {
    xtc_db_query("UPDATE ".TABLE_ORDERS_WITHDRAW_ATTEMPTS."
                     SET customers_email_address = ''
                   WHERE customers_email_address = '".xtc_db_input($email_address)."'");
  }
 ?>
