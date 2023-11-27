<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2008 Gambio OHG

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  
  function xtc_get_shop_conf($configuration_key) {
    $configuration = get_shop_configuration();
     
    $configuration_values = false;
    if (is_array($configuration_key)) {
      $configuration_values = array();
      foreach($configuration_key as $key) {
        if (isset($configuration[$key])) {
          $configuration_values[$key] = $configuration[$key];
        }
      }
    } elseif (isset($configuration[$configuration_key])) {
      $configuration_values = $configuration[$configuration_key];
    }
    
    return $configuration_values;
  }
  
  
  function get_shop_offline_status() {
    global $PHP_SELF;
    
    $configuration = get_shop_configuration();
    
    if (isset($configuration['SHOP_OFFLINE']) && $configuration['SHOP_OFFLINE'] == 'checked') {
      $customers_status = ((basename($PHP_SELF) != FILENAME_LOGOFF && isset($_SESSION['customers_status'])) ? $_SESSION['customers_status']['customers_status'] : DEFAULT_CUSTOMERS_STATUS_ID_GUEST);
      //check for admins
      if ($customers_status == '0') {
        return false;
      }
      //check for allowed customers groups
      if (isset($configuration['SHOP_OFFLINE_ALLOWED_CUSTOMERS_GROUPS']) 
          && trim($configuration['SHOP_OFFLINE_ALLOWED_CUSTOMERS_GROUPS']) != ''
          )
      {
        $customers_group ='c_'.$customers_status.'_group';
        if (strpos($configuration['SHOP_OFFLINE_ALLOWED_CUSTOMERS_GROUPS'], $customers_group) !== false) {
          return false;
        }
      }
      //check for allowed customers emails
      if (isset($configuration['SHOP_OFFLINE_ALLOWED_CUSTOMERS_EMAILS']) 
          && trim($configuration['SHOP_OFFLINE_ALLOWED_CUSTOMERS_EMAILS']) != ''
          )
      {
        $configuration['SHOP_OFFLINE_ALLOWED_CUSTOMERS_EMAILS'] = preg_replace("'[\r\n\s]+'", '', $configuration['SHOP_OFFLINE_ALLOWED_CUSTOMERS_EMAILS']);
        $emails_array = explode(',', $configuration['SHOP_OFFLINE_ALLOWED_CUSTOMERS_EMAILS']);
        if (count($emails_array) > 0 
            && isset($_SESSION['customer_email_address'])
            && in_array($_SESSION['customer_email_address'], $emails_array)
            )
        {
          return false;
        }
      }
      return true;
    }
    
    return false;
  }
  
  
  function get_shop_configuration() {
    static $configuration_array;
    
    if (!isset($configuration_array)) {
      $configuration_array = array();
      $configuration_query = xtc_db_query("SELECT configuration_key,
                                                  configuration_value
                                             FROM shop_configuration");
      while ($configuration = xtc_db_fetch_array($configuration_query)) {
        $configuration_array[$configuration['configuration_key']] = stripslashes($configuration['configuration_value']);
      }
    }
    
    return $configuration_array;
  }
