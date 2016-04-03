<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_zone_code.inc.php 899 2005-04-29 02:40:57Z hhgag $   

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
    $state_prov_query = xtc_db_query("SELECT zone_code 
                                        FROM ".TABLE_ZONES." 
                                       WHERE zone_country_id = '".(int)$country_id."' 
                                         AND zone_id = '".(int)$zone_id."'");
    if (!xtc_db_num_rows($state_prov_query)) {
      $state_prov_code = $default_zone;
    } else {
      $state_prov_values = xtc_db_fetch_array($state_prov_query);
      $state_prov_code = $state_prov_values['zone_code'];
    }
    return $state_prov_code;
  }
 ?>