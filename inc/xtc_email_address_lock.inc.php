<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // serializes the check and the assignment of an email address to a regular account;
  // a plain connection of its own, because a second lock on the session's connection
  // would release the session lock on MySQL before 5.7.5, and named locks are server wide
  function xtc_email_address_lock($email_address, $release = false) {
    static $lock_name = '';

    if ($release === true) {
      if ($lock_name != '') {
        xtc_db_query("SELECT RELEASE_LOCK('".xtc_db_input($lock_name)."')", 'email_lock_link');
        xtc_db_close('email_lock_link');
        $lock_name = '';
      }
      return true;
    }

    $name = 'MODeml_'.md5(DB_DATABASE.'|'.mb_strtolower(trim($email_address)));
    if (!is_object(xtc_db_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, 'email_lock_link', false))) {
      return false;
    }

    $lock_query = xtc_db_query("SELECT GET_LOCK('".xtc_db_input($name)."', 10) AS email_lock", 'email_lock_link');
    $lock = xtc_db_fetch_array($lock_query);
    if (is_array($lock) && isset($lock['email_lock']) && $lock['email_lock'] == '1') {
      $lock_name = $name;
      return true;
    }

    xtc_db_close('email_lock_link');
    return false;
  }
