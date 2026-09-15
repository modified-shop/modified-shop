<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function update_tax_eu_rates($additional_countries = null) {
    if (!is_array($additional_countries)) {
      $additional_countries = array(
        'FR' => array(
          'MC', // Monaco
        ),
      );
    }

    // DIR_WS_CLASSES is absolute in the catalog context and relative in the admin context
    require_once(DIR_FS_CATALOG.'includes/classes/modified_api.php');

    modified_api::reset();
    $tax_rates_array = modified_api::request('modified/tax/');

    if (!is_array($tax_rates_array) || count($tax_rates_array) == 0) {
      return false;
    }

    $countries_array = array();
    $countries_query = xtc_db_query("SELECT countries_id,
                                            countries_iso_code_2
                                       FROM ".TABLE_COUNTRIES."
                                   ORDER BY countries_name");
    if ($countries_query === false) {
      return false;
    }

    while ($countries = xtc_db_fetch_array($countries_query)) {
      $countries_array[$countries['countries_iso_code_2']] = $countries['countries_id'];
    }

    // an empty or truncated configuration value must not produce a bogus entry
    $geo_zones_array = array();
    if (defined('MODULE_TAX_EU_GEO_ZONES') && MODULE_TAX_EU_GEO_ZONES != '') {
      $geozones = preg_split("/[:,]/", MODULE_TAX_EU_GEO_ZONES);
      for ($i=0, $n=count($geozones); $i+1<$n; $i+=2) {
        $geo_zones_array[$geozones[$i]] = $geozones[$i+1];
      }
    }

    // every country is updated on its own, a single failure must not hide the others
    $success = true;

    foreach ($tax_rates_array as $tax_rates_country) {
      foreach ($tax_rates_country as $iso_code_2 => $tax_rates_info) {

        if (!isset($countries_array[$iso_code_2])) {
          $success = false;
          continue;
        }

        if (!isset($geo_zones_array[$iso_code_2])) {
          $check_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_GEO_ZONES."
                                        WHERE geo_zone_name LIKE ('%Steuerzone ".xtc_db_input($iso_code_2)."%')");
          if ($check_query === false) {
            $success = false;
            continue;
          }

          if (xtc_db_num_rows($check_query) == 0) {
            $sql_data_array = array(
              'geo_zone_name' => sprintf('DE::Steuerzone %s||EN::Tax zone %s', $iso_code_2, $iso_code_2),
              'geo_zone_description' => sprintf('DE::Steuerzone %s||EN::Tax zone %s', $iso_code_2, $iso_code_2),
              'date_added' => 'now()'
            );
            if (xtc_db_perform(TABLE_GEO_ZONES, $sql_data_array) === false) {
              $success = false;
              continue;
            }

            $geo_zones_array[$iso_code_2] = xtc_db_insert_id();
          } else {
            $check = xtc_db_fetch_array($check_query);
            $geo_zones_array[$iso_code_2] = $check['geo_zone_id'];
          }
        }

        if (xtc_db_query("UPDATE ".TABLE_ZONES_TO_GEO_ZONES."
                             SET geo_zone_id = ".$geo_zones_array[$iso_code_2].",
                                 last_modified = now()
                           WHERE zone_country_id = ".$countries_array[$iso_code_2]) === false) {
          $success = false;
        }

        if (isset($additional_countries[$iso_code_2])) {
          foreach ($additional_countries[$iso_code_2] as $iso_code_2_additional) {
            if (!isset($countries_array[$iso_code_2_additional])) {
              $success = false;
              continue;
            }

            if (xtc_db_query("UPDATE ".TABLE_ZONES_TO_GEO_ZONES."
                                 SET geo_zone_id = ".$geo_zones_array[$iso_code_2].",
                                     last_modified = now()
                               WHERE zone_country_id = ".$countries_array[$iso_code_2_additional]) === false) {
              $success = false;
            }
          }
        }

        foreach ($tax_rates_info as $tax_class_id => $tax_rate) {
          $check_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_TAX_RATES."
                                        WHERE tax_class_id = ".(int)$tax_class_id."
                                          AND tax_zone_id = ".$geo_zones_array[$iso_code_2]);
          if ($check_query === false) {
            $success = false;
            continue;
          }

          if (xtc_db_num_rows($check_query) == 0 && $tax_rate != '') {
            $sql_data_array = array(
              'tax_zone_id' => $geo_zones_array[$iso_code_2],
              'tax_class_id' => $tax_class_id,
              'tax_priority' => '1',
              'tax_rate' => $tax_rate,
              'tax_description' => sprintf('DE::MwSt. %s%%||EN::VAT %s%%', $tax_rate, $tax_rate),
              'date_added' => 'now()'
            );
            if (xtc_db_perform(TABLE_TAX_RATES, $sql_data_array) === false) {
              $success = false;
            }
          } else {
            $check = xtc_db_fetch_array($check_query);

            if (isset($check['tax_rates_id'])) {
              if ($tax_rate != '') {
                if (xtc_db_query("UPDATE ".TABLE_TAX_RATES."
                                     SET tax_rate = ".$tax_rate.",
                                         tax_description = '".xtc_db_input(sprintf('DE::MwSt. %s%%||EN::VAT %s%%', $tax_rate, $tax_rate))."',
                                         last_modified = now()
                                   WHERE tax_rates_id = ".$check['tax_rates_id']) === false) {
                  $success = false;
                }
              } else {
                if (xtc_db_query("DELETE FROM ".TABLE_TAX_RATES." WHERE tax_rates_id = ".$check['tax_rates_id']) === false) {
                  $success = false;
                }
              }
            }
          }
        }
      }
    }

    $configuration = array();
    foreach ($geo_zones_array as $key => $val) {
      $configuration[] = $key.':'.$val;
    }
    if (xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                         SET configuration_value = '".xtc_db_input(implode(',', $configuration))."'
                       WHERE configuration_key = 'MODULE_TAX_EU_GEO_ZONES'") === false) {
      $success = false;
    }

    return $success;
  }
