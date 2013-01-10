<?php
/* -----------------------------------------------------------------------------------------
   $Id: configuration_get_conf.inc.php

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2008 Gambio OHG

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  //  -> function to get shop_configuration values
  function xtc_get_shop_conf($configuration_key, $result_type = 'ASSOC') {

    $configuration_values = false;

    if($result_type == 'ASSOC' || $result_type == 'NUMERIC'){

      if(is_array($configuration_key)){
        foreach($configuration_key as $key){
          $configuration_query = xtc_db_query("
                      SELECT
                        configuration_value
                      FROM
                        ".TABLE_SHOP_CONFIGURATION."
                      WHERE
                        configuration_key = '" . $key . "'
                        LIMIT 1
                      ");
          if(xtc_db_num_rows($configuration_query) == 1){
            if($configuration_values == false) $configuration_values = array();
            $configuration_row = xtc_db_fetch_array($configuration_query);
            if($result_type == 'ASSOC') {
              $configuration_values[$key] = $configuration_row['configuration_value'];
            } else {
              $configuration_values[] = $configuration_row['configuration_value'];
            }
          }
        }
      }
      else{
        $configuration_query = xtc_db_query("
                    SELECT
                      configuration_value
                    FROM
                      ".TABLE_SHOP_CONFIGURATION."
                    WHERE
                      configuration_key = '" . $configuration_key . "'
                      LIMIT 1
                    ");

        if(xtc_db_num_rows($configuration_query) == 1){
          if($configuration_values == false) $configuration_values = '';
          $configuration_row = xtc_db_fetch_array($configuration_query);
          $configuration_values = $configuration_row['configuration_value'];
        }
      }
    }
    return $configuration_values;
  }
?>