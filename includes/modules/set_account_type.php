<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if (isset ($_SESSION['customer_id'])) {
  $account_type_query = xtc_db_query("-- /includes/application_top.php
                                      SELECT account_type,
                                             customers_default_address_id
                                        FROM ".TABLE_CUSTOMERS."
                                       WHERE customers_id = '".(int) $_SESSION['customer_id']."'");
  $account_type = xtc_db_fetch_array($account_type_query);

  // check if zone id is unset bug
  if (!isset ($_SESSION['customer_country_id'])) {
    $zone_query = xtc_db_query("-- /includes/application_top.php
                            SELECT entry_country_id
                              FROM ".TABLE_ADDRESS_BOOK."
                             WHERE customers_id='".(int) $_SESSION['customer_id']."'
                               AND address_book_id='".$account_type['customers_default_address_id']."'");

    $zone = xtc_db_fetch_array($zone_query);
    $_SESSION['customer_country_id'] = $zone['entry_country_id'];
  }
  $_SESSION['account_type'] = $account_type['account_type'];
} else {
  $_SESSION['account_type'] = '0';
}