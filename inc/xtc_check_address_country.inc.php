<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // false only if the address exists and its country is disabled
  function xtc_check_address_country($address_book_id, $customers_id) {
    require_once (DIR_FS_INC.'xtc_get_countries.inc.php');

    $address_query = xtc_db_query("SELECT entry_country_id
                                     FROM ".TABLE_ADDRESS_BOOK."
                                    WHERE customers_id = '".(int)$customers_id."'
                                      AND address_book_id = '".(int)$address_book_id."'");
    if (xtc_db_num_rows($address_query) < 1) {
      return true;
    }
    $address = xtc_db_fetch_array($address_query);

    return (xtc_get_countriesList($address['entry_country_id']) !== false);
  }
?>
