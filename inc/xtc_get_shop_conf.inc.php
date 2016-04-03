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

  /*
    -> function to get shop_configuration values
  */
  
  function xtc_get_shop_conf($configuration_key, $result_type = 'ASSOC') {

    $configuration_values = false;
  
    if($result_type == 'ASSOC' || $result_type == 'NUMERIC'){

      if(is_array($configuration_key)){
        foreach($configuration_key as $key){
          $configuration_query = xtc_db_query("SELECT configuration_value
                                                 FROM shop_configuration
                                                WHERE configuration_key = '" . xtc_db_input($key) . "'
                                                LIMIT 1");
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
        $configuration_query = xtc_db_query("SELECT configuration_value
                                               FROM shop_configuration
                                              WHERE configuration_key = '" . xtc_db_input($configuration_key) . "'
                                              LIMIT 1");
        if(xtc_db_num_rows($configuration_query) == 1){
          if($configuration_values == false) $configuration_values = '';
          $configuration_row = xtc_db_fetch_array($configuration_query);
          $configuration_values = $configuration_row['configuration_value'];
        }
      }
    }
    return $configuration_values;
  }
  
  function get_shop_offline_status() {
    $configuration_query = xtc_db_query("SELECT configuration_key,
                                                configuration_value
                                           FROM shop_configuration
                                          WHERE configuration_key LIKE 'SHOP_OFFLINE%'");
    while ($config = xtc_db_fetch_array($configuration_query)) {
      $configuration[$config['configuration_key']] = stripslashes($config['configuration_value']);
    }
    //echo '<pre>'.print_r($configuration,1).'</pre>';EXIT;
    
    if (isset($configuration['SHOP_OFFLINE']) && $configuration['SHOP_OFFLINE'] == 'checked') {
      $customers_status = $_SESSION['customers_status']['customers_status'];
      //check for admins
      if ($customers_status == '0') {
        return false;
      }
      //check for allowed customers groups
      if (isset($configuration['SHOP_OFFLINE_ALLOWED_CUSTOMERS_GROUPS']) && trim($configuration['SHOP_OFFLINE_ALLOWED_CUSTOMERS_GROUPS']) != '') {
        $customers_group ='c_'.$customers_status.'_group';
        if (strpos($configuration['SHOP_OFFLINE_ALLOWED_CUSTOMERS_GROUPS'], $customers_group) !== false) {
          return false;
        }
      }
      //check for allowed customers emails
      if (isset($configuration['SHOP_OFFLINE_ALLOWED_CUSTOMERS_EMAILS']) && trim($configuration['SHOP_OFFLINE_ALLOWED_CUSTOMERS_EMAILS']) != '') {
        $customers_email = get_customer_email_by_id($_SESSION['customer_id']);
        $configuration['SHOP_OFFLINE_ALLOWED_CUSTOMERS_EMAILS'] = preg_replace("'[\r\n\s]+'",'',$configuration['SHOP_OFFLINE_ALLOWED_CUSTOMERS_EMAILS']);
        $emails_array = explode(',',$configuration['SHOP_OFFLINE_ALLOWED_CUSTOMERS_EMAILS']);
        if ($customers_email && in_array($customers_email,$emails_array)) {
          return false;
        }
      }
      return true;
    }
    return false;
  }
  
  function get_customer_email_by_id($cID) {
    $customers_status_query = xtc_db_query("SELECT customers_email_address 
                                              FROM " . TABLE_CUSTOMERS . " 
                                             WHERE customers_id = '" . (int)$cID . "'");
    $customers_status_value = xtc_db_fetch_array($customers_status_query);
    return $customers_status_value['customers_email_address'];
  }
?>