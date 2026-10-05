<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2025 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once(DIR_FS_INC.'split_multi_language_value.inc.php');

  function merge_multi_language_value($posted_values, $stored_value) {

    // languages switched off in the admin get no input field, their stored value must survive a save
    // the entries are detected like in parse_multi_language_value(), so the merge sees what the form renders
    $value_array = array();
    foreach (split_multi_language_value($stored_value) as $language_code => $value) {
      $value_array[strtoupper($language_code)] = $value;
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
