<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_address_format_id.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com
   (c) 2003	 nextcommerce (xtc_get_address_format_id.inc.php,v 1.3 2003/08/13); www.nextcommerce.org 

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_get_address_format_id($country_id) {
    $address_format_query = xtDBquery("SELECT address_format_id as format_id 
                                         FROM " . TABLE_COUNTRIES . " 
                                        WHERE countries_id = '" . (int)$country_id . "'");
    if (xtc_db_num_rows($address_format_query, true)) {
      $address_format = xtc_db_fetch_array($address_format_query, true);
      return $address_format['format_id'];
    } else {
      return '1';
    }
  }
?>