<?php
/* -----------------------------------------------------------------------------------------
   $Id$   


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
    static $countries_array;
    
    if (!isset($countries_array)) {
      $countries_query = xtDBquery("SELECT *
                                      FROM " . TABLE_COUNTRIES . " 
                                     WHERE status = '1'
                                       AND countries_iso_code_2 != 'FX'
                                  ORDER BY countries_name");
      while ($countries = xtc_db_fetch_array($countries_query, true)) {
        $countries_array[$countries['countries_id']] = $countries;
      }
    }
    
    if (xtc_not_null($countries_id)) {
      return $countries_array[$countries_id];
    } else {
      return array_values($countries_array);
    }
  }
 ?>