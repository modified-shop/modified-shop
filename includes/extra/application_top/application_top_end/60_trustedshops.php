<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
  
  
  if (defined('MODULE_TRUSTEDSHOPS_STATUS') && MODULE_TRUSTEDSHOPS_STATUS == 'true') {
    // load configuration
    $trustedshops_query = xtc_db_query("SELECT *
                                          FROM ".TABLE_TRUSTEDSHOPS."
                                         WHERE status = '1'
                                           AND languages_id = '".(int)$_SESSION['languages_id']."'");
    if (xtc_db_num_rows($trustedshops_query) > 0) {
      $trustedshops = xtc_db_fetch_array($trustedshops_query);
      foreach ($trustedshops as $key => $value) {
        defined('MODULE_TS_'.strtoupper($key)) OR define('MODULE_TS_'.strtoupper($key), $value);
      }
    }
  }
?>