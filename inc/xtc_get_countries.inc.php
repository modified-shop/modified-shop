<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_countries.inc.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $   


   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_get_countries.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_get_countriesList($countries_id = '', $with_iso_codes = false) {
    $countries_array = array();
    $no_france_fx = " AND countries_iso_code_2 != 'FX' ";
    if (xtc_not_null($countries_id)) {
      if ($with_iso_codes == true) {
        $countries = xtc_db_query("SELECT countries_name, countries_iso_code_2, countries_iso_code_3 
                                     FROM " . TABLE_COUNTRIES . " 
                                    WHERE countries_id = '" . (int)$countries_id . "' 
                                      AND status = '1'
                                          ".$no_france_fx."
                                 ORDER BY countries_name
                                  ");
        $countries_values = xtc_db_fetch_array($countries);
        $countries_array = array('countries_name' => $countries_values['countries_name'],
                                 'countries_iso_code_2' => $countries_values['countries_iso_code_2'],
                                 'countries_iso_code_3' => $countries_values['countries_iso_code_3']);
      } else {
        $countries = xtc_db_query("SELECT countries_name 
                                     FROM " . TABLE_COUNTRIES . " 
                                    WHERE countries_id = '" . (int)$countries_id . "'
                                          ".$no_france_fx."                                    
                                      AND status = '1'
                                  ");
        $countries_values = xtc_db_fetch_array($countries);
        $countries_array = array('countries_name' => $countries_values['countries_name']);
      }
    } else {
      $countries = xtc_db_query("SELECT countries_id, countries_name 
                                   FROM " . TABLE_COUNTRIES . " 
                                  WHERE status = '1'
                                        ".$no_france_fx."                                  
                               ORDER BY countries_name");
      while ($countries_values = xtc_db_fetch_array($countries)) {
        $countries_array[] = array('countries_id' => $countries_values['countries_id'],
                                   'countries_name' => $countries_values['countries_name']);
      }
    }

    return $countries_array;
  }
 ?>