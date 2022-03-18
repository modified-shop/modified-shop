<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

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
   
  function xtc_get_zone_code($country_id, $zone_id, $default_zone) {
    $zone_code_query = xtDBquery("SELECT zone_code 
                                    FROM ".TABLE_ZONES." 
                                   WHERE zone_country_id = '".(int)$country_id."' 
                                     AND zone_id = '".(int)$zone_id."'");
    if (xtc_db_num_rows($zone_code_query, true) > 0) {
      $zone_code = xtc_db_fetch_array($zone_code_query, true);
      return $zone_code['zone_code'];
    }
    
    return $default_zone;
  }
