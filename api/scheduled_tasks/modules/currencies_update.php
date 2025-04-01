<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  function cron_currencies_update() {
    // include needed functions
    require_once(DIR_FS_CATALOG.'inc/quote_currency.inc.php');

    $currency_query = xtc_db_query("SELECT * FROM " . TABLE_CURRENCIES);
    while ($currency = xtc_db_fetch_array($currency_query)) {
      $rate = quote_currency($currency['code']);
      if ($rate !== false && $rate > 0) {
        $sql_data_array = array(
          'value' => $rate,
          'last_updated' => 'now()',
        );
        xtc_db_perform(TABLE_CURRENCIES, $sql_data_array, 'update', "currencies_id = '" . (int)$currency['currencies_id'] . "'");
      }
    }
    
    return true;
  }