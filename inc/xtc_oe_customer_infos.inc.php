<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_oe_customer_infos.inc.php

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003	 nextcommerce (xtc_get_products_price.inc.php,v 1.13 2003/08/20); www.nextcommerce.org
   (c) 2003 XT-Commerce
   
   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   XTC-Bestellbearbeitung:
   http://www.xtc-webservice.de / Matthias Hinsche
   info@xtc-webservice.de

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function  xtc_oe_customer_infos($customers_id) {

    $customer_query = xtc_db_query("SELECT a.entry_country_id,
                                           a.entry_zone_id 
                                      FROM " . TABLE_CUSTOMERS . " c
                                      JOIN " . TABLE_ADDRESS_BOOK . " a 
                                           ON c.customers_id = a.customers_id
                                              AND c.customers_default_address_id = a.address_book_id
                                     WHERE c.customers_id  = '" . (int)$customers_id . "'");
    $customer = xtc_db_fetch_array($customer_query);
	  $customer_info_array = array('country_id' => $customer['entry_country_id'],
                                 'zone_id' => $customer['entry_zone_id']);

    return $customer_info_array;
  }
?>