<?php
/**
 * $Id$
 *
 * modified eCommerce Shopsoftware
 * http://www.modified-shop.org
 *
 * Copyright (c) 2009 - 2013 [www.modified-shop.org]
 *
 * Released under the GNU General Public License
 */
 
 
function check_country_required_zones($country_id) 
{
    $query = xtc_db_query(
      "SELECT count(*) AS total  
         FROM ".TABLE_ZONES." z 
         JOIN ".TABLE_COUNTRIES." c 
           ON c.countries_id = z.zone_country_id 
        WHERE z.zone_country_id = '".(int)$country_id."'
      "); 
    
    $check = xtc_db_fetch_array($query); 
    if ($check['total'] > 0) {
        $query = xtc_db_query(
          "SELECT required_zones
            FROM ".TABLE_COUNTRIES."
           WHERE countries_id  = '".(int)$country_id."'
          ");
        if (xtc_db_num_rows($query)) {
            $dbData = xtc_db_fetch_array($query);
            return $dbData['required_zones'];
        } 
    }
    return true;
}