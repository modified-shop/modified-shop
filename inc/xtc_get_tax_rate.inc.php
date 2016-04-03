<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_tax_rate.inc.php 862 2005-04-16 10:26:29Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_get_tax_rate.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_get_tax_rate($class_id, $country_id = -1, $zone_id = -1) {

    if ($country_id == -1 && $zone_id == -1) {
      if (!isset($_SESSION['customer_id'])) {
        $country_id = STORE_COUNTRY;
        $zone_id = STORE_ZONE;
      } else {
        $country_id = $_SESSION['customer_country_id'];
        $zone_id = $_SESSION['customer_zone_id'];
      }
    } else {
      $country_id = $country_id;
      $zone_id = $zone_id;
    }

    $tax_query = xtDBquery("SELECT sum(tax_rate) as tax_rate 
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
                           GROUP BY tr.tax_priority");
    if (xtc_db_num_rows($tax_query,true)) {
      $tax_multiplier = 1.0;
      while ($tax = xtc_db_fetch_array($tax_query,true)) {
        $tax_multiplier *= 1.0 + ($tax['tax_rate'] / 100);
      }
      return ($tax_multiplier - 1.0) * 100;
    } else {
      return 0;
    }
  }


  function xtc_get_tax_class($tax_class_id, $country_id = -1, $zone_id = -1) {
        
    if ($country_id == -1 && $zone_id == -1) {
      if (!isset($_SESSION['customer_id'])) {
        if (isset($_SESSION['country'])) {
          $country_id = $_SESSION['country'];
        } else {
          $country_id = STORE_COUNTRY;
          $zone_id = STORE_ZONE;
        }
      } else {
        $country_id = $_SESSION['customer_country_id'];
        $zone_id = $_SESSION['customer_zone_id'];
      }
    } else {
      $country_id = $country_id;
      $zone_id = $zone_id;
    }
                                                              
    $tax_query = xtDBquery("SELECT tax_class_id
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
                                AND tr.tax_priority = '99'
                              LIMIT 1");
    if (xtc_db_num_rows($tax_query, true) == 1) {
      $tax = xtc_db_fetch_array($tax_query, true);
      return $tax['tax_class_id'];
    } else {
      return $tax_class_id;
    }
  }
?>