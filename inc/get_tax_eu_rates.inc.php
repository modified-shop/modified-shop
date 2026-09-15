<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // $complete tells the caller whether anything had to be discarded
  function get_tax_eu_rates(&$complete = null) {
    $complete = true;

    // DIR_WS_CLASSES is absolute in the catalog context and relative in the admin context
    require_once(DIR_FS_CATALOG.'includes/classes/modified_api.php');

    modified_api::reset();
    $response = modified_api::request('modified/tax/');

    if (!is_array($response) || count($response) == 0) {
      $complete = false;

      return false;
    }

    // the endpoint is not authenticated, so nothing is passed on unchecked
    $tax_rates_array = array();

    foreach ($response as $tax_rates_country) {
      if (!is_array($tax_rates_country)) {
        $complete = false;
        continue;
      }

      foreach ($tax_rates_country as $iso_code_2 => $tax_rates_info) {
        if (!is_string($iso_code_2)
            || preg_match('/^[A-Z]{2}$/D', $iso_code_2) !== 1
            || isset($tax_rates_array[$iso_code_2])
            || !is_array($tax_rates_info)
            || count($tax_rates_info) == 0
            )
        {
          $complete = false;
          continue;
        }

        // one unusable rate discards the whole country, a half updated country is worse
        $rates_array = array();
        foreach ($tax_rates_info as $tax_class_id => $tax_rate) {
          if (!ctype_digit((string)$tax_class_id)
              || (int)$tax_class_id < 1
              || (!is_string($tax_rate) && !is_numeric($tax_rate))
              || ((string)$tax_rate != ''
                  && preg_match('/^\d{1,3}(\.\d{1,4})?$/D', (string)$tax_rate) !== 1
                 )
              )
          {
            $rates_array = array();
            break;
          }

          // an empty rate means the entry has to go
          $rates_array[(int)$tax_class_id] = (string)$tax_rate;
        }

        if (count($rates_array) == 0) {
          $complete = false;
          continue;
        }

        $tax_rates_array[$iso_code_2] = $rates_array;
      }
    }

    if (count($tax_rates_array) == 0) {
      $complete = false;

      return false;
    }

    return $tax_rates_array;
  }
