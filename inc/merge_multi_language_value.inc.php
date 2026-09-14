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
    // the entries are detected exactly like in parse_multi_language_value(), otherwise the merge
    // would see a different value than the form renders
    $value_array = array();
    foreach (explode('||', (string)$stored_value) as $val) {
      $val_array = explode('::', $val);
      if (count($val_array) == 2) {
        if (!empty($val_array[1])) {
          $value_array[strtoupper(trim($val_array[0]))] = trim($val_array[1]);
        }
      }
      unset($val_array);
    }

    if (count($value_array) == 0 && xtc_not_null($stored_value)) {
      // a value without language prefix is edited in the default language field
      $value_array[strtoupper(DEFAULT_LANGUAGE)] = $stored_value;
    }

    foreach ((array)$posted_values as $language_code => $value) {
      $value_array[strtoupper(trim($language_code))] = $value;
    }

    $merged_value = array();
    foreach ($value_array as $language_code => $value) {
      if (xtc_not_null($value)) {
        $merged_value[] = $language_code . '::' . $value;
      }
    }

    return implode('||', $merged_value);
  }
?>
