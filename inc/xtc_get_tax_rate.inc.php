<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_get_tax_rate.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_get_tax_rate.inc.php 862 2005-04-16)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_get_tax_rate($class_id, $country_id = -1, $zone_id = -1) {
    static $tax_rates = array(); //DokuMan - 2011-09-30 - reduce database queries for tax calculations

    if ( ($country_id == -1) && ($zone_id == -1) ) {
      if (!isset($_SESSION['customer_id'])) {
        $country_id = STORE_COUNTRY;
        $zone_id = STORE_ZONE;
      } else {
        $country_id = $_SESSION['customer_country_id'];
        $zone_id = $_SESSION['customer_zone_id'];
      }
    }
    //BOF - DokuMan - 2011-09-30 - removed - unnecessary?
    //else {
    //   $country_id = $country_id;
    //   $zone_id = $zone_id;
    //}
    //EOF - DokuMan - 2011-09-30 - removed - unnecessary?

    //BOF - DokuMan - 2011-09-30 - reduce database queries for tax calculations
    /*
    $tax_query = xtDBquery("SELECT SUM(tax_rate) AS tax_rate
                            FROM " . TABLE_TAX_RATES . " tr
                       LEFT JOIN " . TABLE_ZONES_TO_GEO_ZONES . " za ON (tr.tax_zone_id = za.geo_zone_id)
                       LEFT JOIN " . TABLE_GEO_ZONES . " tz ON (tz.geo_zone_id = tr.tax_zone_id)
                           WHERE (za.zone_country_id is null
                              OR za.zone_country_id = 0
                              OR za.zone_country_id = '" . (int)$country_id . "')
                             AND (za.zone_id is null
                              OR za.zone_id = 0
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
    */
    if (!isset($tax_rates[$class_id][$country_id][$zone_id]['rate'])) {
      $tax_query = xtDBquery("SELECT SUM(tax_rate) AS tax_rate
                              FROM " . TABLE_TAX_RATES . " tr
                         LEFT JOIN " . TABLE_ZONES_TO_GEO_ZONES . " za ON (tr.tax_zone_id = za.geo_zone_id)
                         LEFT JOIN " . TABLE_GEO_ZONES . " tz ON (tz.geo_zone_id = tr.tax_zone_id)
                             WHERE (za.zone_country_id is null
                                OR za.zone_country_id = 0
                                OR za.zone_country_id = '" . (int)$country_id . "')
                               AND (za.zone_id is null
                                OR za.zone_id = 0
                                OR za.zone_id = '" . (int)$zone_id . "')
                               AND tr.tax_class_id = '" . (int)$class_id . "'
                          GROUP BY tr.tax_priority");
      if (xtc_db_num_rows($tax_query)) {
        $tax_multiplier = 1.0;
        while ($tax = xtc_db_fetch_array($tax_query,true)) {
          $tax_multiplier *= 1.0 + ($tax['tax_rate'] / 100);
        }

        $tax_rates[$class_id][$country_id][$zone_id]['rate'] = ($tax_multiplier - 1.0) * 100;
      } else {
        $tax_rates[$class_id][$country_id][$zone_id]['rate'] = 0;
      }
    }

    return $tax_rates[$class_id][$country_id][$zone_id]['rate'];
    //EOF - DokuMan - 2011-09-30 - reduce database queries for tax calculations
  }
?>