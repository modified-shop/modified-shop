<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_tax_description.inc.php 1166 2005-08-21 00:52:02Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_get_tax_description.inc.php); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_get_tax_description($class_id, $country_id= -1, $zone_id= -1) {
    
    // VERSANDKOSTEN IM WARENKORB
    if (isset($_SESSION['country'])) {
      $country_id = $_SESSION['country'];
    }
  	
  	if ( ($country_id == -1) && ($zone_id == -1) ) {
      if (!isset($_SESSION['customer_id'])) {
        $country_id = STORE_COUNTRY;
        $zone_id = STORE_ZONE;
      } else {
        $country_id = $_SESSION['customer_country_id'];
        $zone_id = $_SESSION['customer_zone_id'];
      }
    }else{
      $country_id = $country_id;
      $zone_id = $zone_id;
    }
  	
    $tax_query = xtDBquery("SELECT tax_description 
                              FROM " . TABLE_TAX_RATES . " tr 
                         LEFT JOIN " . TABLE_ZONES_TO_GEO_ZONES . " za 
                                   ON (tr.tax_zone_id = za.geo_zone_id) 
                         LEFT JOIN " . TABLE_GEO_ZONES . " tz 
                                   ON (tz.geo_zone_id = tr.tax_zone_id) 
                             WHERE (za.zone_country_id is null 
                                    OR za.zone_country_id = '0' 
                                    OR za.zone_country_id = '" . (int)$country_id . "') 
                               AND (za.zone_id is null 
                                    OR za.zone_id = '0' 
                                    OR za.zone_id = '" . (int)$zone_id . "') 
                               AND tr.tax_class_id = '" . (int)$class_id . "' 
                          ORDER BY tr.tax_priority");
    if (xtc_db_num_rows($tax_query,true)) {
      $tax_description = '';
      while ($tax = xtc_db_fetch_array($tax_query,true)) {
        $tax_description .= $tax['tax_description'] . ' + ';
      }
      $tax_description = substr($tax_description, 0, -3);

      return $tax_description;
    } else {
      return TEXT_UNKNOWN_TAX_RATE;
    }
  }
?>