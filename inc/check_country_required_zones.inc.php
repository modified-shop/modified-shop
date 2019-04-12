<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
 
 
  function check_country_required_zones($country_id) {
    $query = xtc_db_query("SELECT SUM(c.required_zones) as total
                             FROM ".TABLE_ZONES." z 
                             JOIN ".TABLE_COUNTRIES." c 
                               ON c.countries_id = z.zone_country_id 
                            WHERE z.countries_id = '".(int)$country_id."'"); 
    $check = xtc_db_fetch_array($query); 
    return (($dbData['total'] > 0) ? true : false);
  }
?>