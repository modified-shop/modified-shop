<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2003 XT-Commerce

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function get_customers_address($address_book_id, $customer_details = false, $address_details = false) {
    $customer_select = ",
       c.payment_unallowed,
       c.shipping_unallowed,
       c.customers_firstname as firstname,
       c.customers_cid as csID,
       c.customers_gender as gender,
       c.customers_lastname as lastname,
       c.customers_telephone as telephone,
       c.customers_email_address as email_address
      ";

    $address_select = ",
       ab.entry_company as company,
       ab.entry_street_address as street_address,
       ab.entry_suburb as suburb,
       ab.entry_gender as gender,
       ab.entry_postcode as postcode,
       ab.entry_city as city,
       ab.entry_zone_id as zone_id,
       ab.entry_country_id as country_id,
       ab.entry_state as state,
       co.countries_name as title,
       co.countries_id as id,
       co.countries_iso_code_2 as iso_code_2,
       co.countries_iso_code_3 as iso_code_3,
       co.address_format_id as format_id,
       z.zone_name
      ";

    $default_select = '';
    if ($customer_details === true) $default_select .= $customer_select;
    if ($address_details === true) $default_select .= $address_select;      
    
    $customer_address_query = xtc_db_query("SELECT ab.entry_country_id as country_id,
                                                   ab.entry_zone_id as zone_id
                                                   " . $default_select . "
                                              FROM " . TABLE_CUSTOMERS . " c
                                         LEFT JOIN " . TABLE_ADDRESS_BOOK . " ab
                                                   ON ab.customers_id = '" . (int)$_SESSION['customer_id'] . "'
                                                      AND ab.address_book_id = '".(int)$address_book_id."'
                                                   " . $default_join . "
                                         LEFT JOIN " . TABLE_ZONES . " z 
                                                   ON ab.entry_zone_id = z.zone_id
                                         LEFT JOIN " . TABLE_COUNTRIES . " co 
                                                   ON ab.entry_country_id = co.countries_id
                                             WHERE c.customers_id = '" . (int)$_SESSION['customer_id'] . "'");
    $customer_address = xtc_db_fetch_array($customer_address_query);
    
    return $customer_address;
  }
