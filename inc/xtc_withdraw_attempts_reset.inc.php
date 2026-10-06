<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // the ip rows only expire, otherwise one known valid order would lift the ip lock again and again
  function xtc_withdraw_attempts_reset($email_address) {
    xtc_db_query("DELETE FROM ".TABLE_ORDERS_WITHDRAW_ATTEMPTS."
                        WHERE customers_email_address = '".xtc_db_input($email_address)."'");
  }
 ?>
