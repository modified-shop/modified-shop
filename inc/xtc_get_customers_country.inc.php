<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_customers_country.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_get_zone_code.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_get_customers_country($customers_id) {
    $customers_query = xtc_db_query("SELECT customers_default_address_id 
                                       FROM " . TABLE_CUSTOMERS . " 
                                      WHERE customers_id = '" . (int)$customers_id . "'");
    $customers = xtc_db_fetch_array($customers_query);

    $address_book_query = xtc_db_query("SELECT entry_country_id 
                                          FROM " . TABLE_ADDRESS_BOOK . " 
                                         WHERE address_book_id = '" . $customers['customers_default_address_id'] . "'");
    $address_book = xtc_db_fetch_array($address_book_query);
    return $address_book['entry_country_id'];
  }
 ?>