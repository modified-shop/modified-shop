<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_countries.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_get_countries.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_get_countriesList($countries_id = '', $with_iso_codes = false) {
    $countries_array = array();
    if (xtc_not_null($countries_id)) {
      if ($with_iso_codes == true) {
        $countries = xtc_db_query("select countries_name, countries_iso_code_2, countries_iso_code_3 from " . TABLE_COUNTRIES . " where countries_id = '" . $countries_id . "' and status = '1' order by countries_name");
        $countries_values = xtc_db_fetch_array($countries);
        $countries_array = array('countries_name' => $countries_values['countries_name'],
                                 'countries_iso_code_2' => $countries_values['countries_iso_code_2'],
                                 'countries_iso_code_3' => $countries_values['countries_iso_code_3']);
      } else {
        $countries = xtc_db_query("select countries_name from " . TABLE_COUNTRIES . " where countries_id = '" . $countries_id . "' and status = '1'");
        $countries_values = xtc_db_fetch_array($countries);
        $countries_array = array('countries_name' => $countries_values['countries_name']);
      }
    } else {
      $countries = xtc_db_query("select countries_id, countries_name from " . TABLE_COUNTRIES . " where status = '1' order by countries_name");
      while ($countries_values = xtc_db_fetch_array($countries)) {
        $countries_array[] = array('countries_id' => $countries_values['countries_id'],
                                   'countries_name' => $countries_values['countries_name']);
      }
    }

    return $countries_array;
  }
 ?>