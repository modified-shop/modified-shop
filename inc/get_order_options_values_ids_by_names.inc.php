<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2014 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  function get_order_options_values_ids_by_names($pID, $options_name, $values_name, $language = '')
  {
    $ids_array = array(
      'options_id' => 0,
      'value_id' => 0
    );
    
    if (empty($options_name) || preg_replace('/\s/', '', $values_name) == '') {
      return $ids_array;
    }
    
    if (empty($language)) {
      $language = $_SESSION['language'];
    }
    
    $order_lang_query = xtDBquery("SELECT languages_id
                                  FROM ".TABLE_LANGUAGES."
                                 WHERE directory = '".xtc_db_input($language)."'");
    if (xtc_db_num_rows($order_lang_query, true) > 0) {
      $tmp = xtc_db_fetch_array($order_lang_query, true);
      $language_id = $tmp['languages_id'];
    } else {
      $language_id = (int)$_SESSION['languages_id'];
    }
  
    $posible_query = xtc_db_query("SELECT products_options_id
                                     FROM ".TABLE_PRODUCTS_OPTIONS."
                                    WHERE products_options_name = '".xtc_db_input($options_name)."' 
                                      AND language_id = ".$language_id);
    if (xtc_db_num_rows($posible_query)) {
      $options = array();
      while ($result = xtc_db_fetch_array($posible_query)) {
        $options[] = $result['products_options_id'];
      }
      if (!empty($options)) {
        $products_options_query = xtc_db_query("SELECT options_id,options_values_id 
                                                  FROM ".TABLE_PRODUCTS_ATTRIBUTES." 
                                                 WHERE options_id IN (".implode(',', $options).") 
                                                   AND products_id = ".(int)$pID);
        if (xtc_db_num_rows($products_options_query)) {
          $options_values = array();
          while ($result = xtc_db_fetch_array($products_options_query)) {
            $options_values[] = $result['options_values_id'];
          }
          $result = xtc_db_query("SELECT pov2po.products_options_id AS options_id, 
                                         pov.products_options_values_id AS value_id
                                    FROM ".TABLE_PRODUCTS_OPTIONS_VALUES." AS pov
                                    JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS." AS	pov2po
                                         ON pov2po.products_options_values_id = pov.products_options_values_id
                                   WHERE pov2po.products_options_values_id IN(".implode(',', $options_values).")
                                     AND pov.products_options_values_name = '".xtc_db_input($values_name)."' AND
                                     AND pov.language_id = ".$language_id);
          if (xtc_db_num_rows($result)) {
            return xtc_db_fetch_array($result);
          }
        }
      }
    }
    
    return $ids_array;
  }
