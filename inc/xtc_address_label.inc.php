<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_address_label.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_address_label.inc.php,v 1.5 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // include needed functions
  require_once(DIR_FS_INC . 'xtc_get_address_format_id.inc.php');
  require_once(DIR_FS_INC . 'xtc_address_format.inc.php');

  function xtc_address_label($customers_id, $address_id = 1, $html = false, $boln = '', $eoln = "\n") {
    $address_query = xtc_db_query("SELECT entry_firstname as firstname, 
                                          entry_lastname as lastname, 
                                          entry_company as company, 
                                          entry_street_address as street_address, 
                                          entry_suburb as suburb, 
                                          entry_city as city, 
                                          entry_postcode as postcode, 
                                          entry_state as state, 
                                          entry_zone_id as zone_id, 
                                          entry_country_id as country_id 
                                     FROM " . TABLE_ADDRESS_BOOK . " 
                                    WHERE customers_id = '" . (int)$customers_id . "' 
                                      AND address_book_id = '" . (int)$address_id . "'");
    $address = xtc_db_fetch_array($address_query);
    $format_id = xtc_get_address_format_id($address['country_id']);

    return xtc_address_format($format_id, $address, $html, $boln, $eoln);
  }
?>