<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2025 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function merge_multi_language_value($posted_values, $stored_value) {

    // languages switched off in the admin get no input field, their stored value must survive a save
    $value_array = array();
    foreach (explode('||', (string)$stored_value) as $val) {
      $val_array = explode('::', $val, 2);
      if (count($val_array) == 2 && trim($val_array[0]) != '' && trim($val_array[1]) != '') {
        $value_array[strtoupper(trim($val_array[0]))] = trim($val_array[1]);
      }
    }

    if (count($value_array) == 0 && trim((string)$stored_value) != '') {
      // a value without language prefix is edited in the default language field
      $value_array[strtoupper(DEFAULT_LANGUAGE)] = trim((string)$stored_value);
    }

    foreach ((array)$posted_values as $language_code => $value) {
      $language_code = strtoupper(trim($language_code));
      if (xtc_not_null($value)) {
        $value_array[$language_code] = $value;
      } else {
        unset($value_array[$language_code]);
      }
    }

    $merged_value = array();
    foreach ($value_array as $language_code => $value) {
      $merged_value[] = $language_code . '::' . $value;
    }

    return implode('||', $merged_value);
  }
?>
