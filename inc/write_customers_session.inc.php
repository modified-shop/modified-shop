<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified eCommerce Shopsoftware
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  function write_customers_session($customer_id) {
    $customers_query = xtc_db_query("SELECT *
                                       FROM ".TABLE_CUSTOMERS." c 
                                       JOIN ".TABLE_ADDRESS_BOOK." ab
                                            ON c.customers_id = ab.customers_id
                                               AND c.customers_default_address_id = ab.address_book_id
                                      WHERE c.customers_id = '".(int)$customer_id."'");
    $customers = xtc_db_fetch_array($customers_query);

    $_SESSION['customer_cid'] = $customers['customers_cid'];
    $_SESSION['customer_gender'] = $customers['customers_gender'];
    $_SESSION['customer_first_name'] = $customers['customers_firstname'];
    $_SESSION['customer_last_name'] = $customers['customers_lastname'];
    $_SESSION['customer_email_address'] = $customers['customers_email_address'];
    $_SESSION['customer_vat_id'] = $customers['customers_vat_id'];
    $_SESSION['customer_default_address_id'] = $customers['customers_default_address_id'];
    $_SESSION['customer_country_id'] = $customers['entry_country_id'];
    $_SESSION['customer_zone_id'] = $customers['entry_zone_id'];
    $_SESSION['account_type'] = $customers['account_type'];
  }
?>