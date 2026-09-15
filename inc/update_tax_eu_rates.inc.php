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
      $countries_array[$countries['countries_iso_code_2']] = (int)$countries['countries_id'];
    }

    $tax_classes_array = array();
    $tax_classes_query = xtc_db_query("SELECT tax_class_id
                                         FROM ".TABLE_TAX_CLASS);
    if ($tax_classes_query === false) {
      return false;
    }

    while ($tax_classes = xtc_db_fetch_array($tax_classes_query)) {
      $tax_classes_array[] = (int)$tax_classes['tax_class_id'];
    }

    // an empty or truncated configuration value must not produce a bogus entry
    $geo_zones_array = array();
    if (defined('MODULE_TAX_EU_GEO_ZONES') && MODULE_TAX_EU_GEO_ZONES != '') {
      $geozones = preg_split("/[:,]/", MODULE_TAX_EU_GEO_ZONES);
      for ($i=0, $n=count($geozones); $i+1<$n; $i+=2) {
        $geo_zones_array[$geozones[$i]] = (int)$geozones[$i+1];
      }
    }

    // the endpoint is not authenticated, so the whole response is checked
    // and normalized before the first geo zone or tax rate is written
    $success = true;
    $normalized = array();

    foreach ($tax_rates_array as $tax_rates_country) {
      if (!is_array($tax_rates_country)) {
        $success = false;
        continue;
      }

      foreach ($tax_rates_country as $iso_code_2 => $tax_rates_info) {
        if (!is_string($iso_code_2)
            || preg_match('/^[A-Z]{2}$/D', $iso_code_2) !== 1
            || isset($normalized[$iso_code_2])
            || !is_array($tax_rates_info)
            || count($tax_rates_info) == 0
            || !isset($countries_array[$iso_code_2])
            )
        {
          $success = false;
          continue;
        }

        // one unusable rate discards the whole country, a half updated country is worse
        $rates_array = array();
        foreach ($tax_rates_info as $tax_class_id => $tax_rate) {
          if (!ctype_digit((string)$tax_class_id)
              || !in_array((int)$tax_class_id, $tax_classes_array)
              || (!is_string($tax_rate) && !is_numeric($tax_rate))
              || ((string)$tax_rate != ''
                  && preg_match('/^\d{1,3}(\.\d{1,4})?$/D', (string)$tax_rate) !== 1
                 )
              )
          {
            $rates_array = array();
            break;
          }

          // an empty rate deletes the entry
          $rates_array[(int)$tax_class_id] = (string)$tax_rate;
        }

        if (count($rates_array) == 0) {
          $success = false;
          continue;
        }

        $countries_to_update = array($countries_array[$iso_code_2]);
        if (isset($additional_countries[$iso_code_2])) {
          foreach ($additional_countries[$iso_code_2] as $iso_code_2_additional) {
            if (!isset($countries_array[$iso_code_2_additional])) {
              $success = false;
              continue;
            }

            $countries_to_update[] = $countries_array[$iso_code_2_additional];
          }
        }

        $normalized[$iso_code_2] = array(
          'countries' => $countries_to_update,
          'rates' => $rates_array,
        );
      }
    }

    // nothing usable came back, so nothing is written at all
    if (count($normalized) == 0) {
      return false;
    }

    foreach ($normalized as $iso_code_2 => $country_data) {
      if (!isset($geo_zones_array[$iso_code_2])) {
        $check_query = xtc_db_query("SELECT geo_zone_id
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

          $geo_zones_array[$iso_code_2] = (int)xtc_db_insert_id();
        } else {
          $check = xtc_db_fetch_array($check_query);
          $geo_zones_array[$iso_code_2] = (int)$check['geo_zone_id'];
        }
      }

      foreach ($country_data['countries'] as $zone_country_id) {
        if (xtc_db_query("UPDATE ".TABLE_ZONES_TO_GEO_ZONES."
                             SET geo_zone_id = ".(int)$geo_zones_array[$iso_code_2].",
                                 last_modified = now()
                           WHERE zone_country_id = ".(int)$zone_country_id) === false) {
          $success = false;
        }
      }

      foreach ($country_data['rates'] as $tax_class_id => $tax_rate) {
        $check_query = xtc_db_query("SELECT tax_rates_id
                                       FROM ".TABLE_TAX_RATES."
                                      WHERE tax_class_id = ".(int)$tax_class_id."
                                        AND tax_zone_id = ".(int)$geo_zones_array[$iso_code_2]);
        if ($check_query === false) {
          $success = false;
          continue;
        }

        if (xtc_db_num_rows($check_query) == 0 && $tax_rate != '') {
          $sql_data_array = array(
            'tax_zone_id' => (int)$geo_zones_array[$iso_code_2],
            'tax_class_id' => (int)$tax_class_id,
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
                                   SET tax_rate = '".$tax_rate."',
                                       tax_description = '".xtc_db_input(sprintf('DE::MwSt. %s%%||EN::VAT %s%%', $tax_rate, $tax_rate))."',
                                       last_modified = now()
                                 WHERE tax_rates_id = ".(int)$check['tax_rates_id']) === false) {
                $success = false;
              }
            } else {
              if (xtc_db_query("DELETE FROM ".TABLE_TAX_RATES." WHERE tax_rates_id = ".(int)$check['tax_rates_id']) === false) {
                $success = false;
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
