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

    $tax_query = xtDBquery("select sum(tax_rate) as tax_rate from " . TABLE_TAX_RATES . " tr left join " . TABLE_ZONES_TO_GEO_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id) left join " . TABLE_GEO_ZONES . " tz on (tz.geo_zone_id = tr.tax_zone_id) where (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . $country_id . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . $zone_id . "') and tr.tax_class_id = '" . $class_id . "' group by tr.tax_priority");
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
 ?>