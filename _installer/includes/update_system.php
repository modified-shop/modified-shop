<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // downloads
  $downloads_query = xtc_db_query("SELECT opd.orders_id,
                                          opd.orders_products_id, 
                                          opd.orders_products_filename,
                                          opd.orders_products_download_id,
                                          o.customers_id, 
                                          o.customers_email_address
                                     FROM ".TABLE_ORDERS_PRODUCTS_DOWNLOAD." opd 
                                     JOIN ".TABLE_ORDERS." o 
                                          ON o.orders_id = opd.orders_id
                                    WHERE download_key = ''");
  if (xtc_db_num_rows($downloads_query) > 0) {
    while ($downloads = xtc_db_fetch_array($downloads_query)) {
      $download_key = md5($downloads['orders_id'].$downloads['orders_products_id'].$downloads['customers_id'].$downloads['customers_email_address'].$downloads['orders_products_filename']);
      xtc_db_query("UPDATE ".TABLE_ORDERS_PRODUCTS_DOWNLOAD."
                       SET download_key = '".xtc_db_input($download_key)."'
                     WHERE orders_products_download_id = '".(int)$downloads['orders_products_download_id']."'");
    }
  }

  // whos online
  $primary = false;
  $whosonline_query = xtc_db_query("SHOW INDEX FROM ".TABLE_WHOS_ONLINE);
  while ($whosonline = xtc_db_fetch_array($whosonline_query)) {
    if ($whosonline['Key_name'] == 'PRIMARY' && $whosonline['Column_name'] == 'session_id') {
      $primary = true;
    }
  }

  if ($primary === false) {
    xtc_db_query("TRUNCATE ".TABLE_WHOS_ONLINE);
    xtc_db_query("ALTER TABLE ".TABLE_WHOS_ONLINE." ADD PRIMARY KEY (session_id)");
  }

  // exclude payments
  if (defined('MODULE_EXCLUDE_PAYMENT_NUMBER')) {
    for ($i = 1; $i <= MODULE_EXCLUDE_PAYMENT_NUMBER; $i ++) {
      xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                       SET set_function = 'xtc_cfg_checkbox_unallowed_module(\'shipping\', \'configuration[MODULE_EXCLUDE_PAYMENT_SHIPPING_".$i."]\','
                     WHERE configuration_key = 'MODULE_EXCLUDE_PAYMENT_SHIPPING_".$i."'");

      xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                       SET set_function = 'xtc_cfg_checkbox_unallowed_module(\'payment\', \'configuration[MODULE_EXCLUDE_PAYMENT_PAYMENT_".$i."]\','
                     WHERE configuration_key = 'MODULE_EXCLUDE_PAYMENT_PAYMENT_".$i."'");
    }
  }

  // personal offer
  $customers_status_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CUSTOMERS_STATUS."
                                        GROUP BY customers_status_id");
  while ($customers_status = xtc_db_fetch_array($customers_status_query)) {
    $check_query = xtc_db_query("SHOW KEYS 
                                      FROM ".TABLE_PERSONAL_OFFERS_BY.$customers_status['customers_status_id']." 
                                     WHERE Key_name = 'idx_quantity'");
    if (xtc_db_num_rows($check_query) < 1) {
      xtc_db_query("ALTER TABLE ".TABLE_PERSONAL_OFFERS_BY.$customers_status['customers_status_id']."  ADD KEY `idx_quantity` (`quantity`)");
    }
  }

  // update tax rates
  $tax_class_id_array = array(
    '1' => 'DE::Standardsatz||EN::Standard rate',
    '2' => 'DE::ermäßigter Satz 1||EN::reduced rate 1',
    '3' => 'DE::ermäßigter Satz 2||EN::reduced rate 2',
    '4' => 'DE::stark ermäßigter Satz||EN::highly reduced rate',
    '5' => 'DE::Zwischensatz||EN::Intermediate rate',
  );

  xtc_db_query("ALTER TABLE ".TABLE_TAX_CLASS." MODIFY tax_class_title VARCHAR(255) NOT NULL");

  foreach ($tax_class_id_array as $tax_class_id => $tax_class_title) {                                        
    $tax_class_title = decode_utf8($tax_class_title);

    $check_query = xtc_db_query("SELECT *
                                   FROM ".TABLE_TAX_CLASS."
                                  WHERE tax_class_id = ".$tax_class_id);
    if (xtc_db_num_rows($check_query) == 0) {
      $sql_data_array = array(
        'tax_class_id' => $tax_class_id,
        'tax_class_title' => $tax_class_title,
        'date_added' => 'now()'
      );
      xtc_db_perform(TABLE_TAX_CLASS, $sql_data_array);
    } else {
      $check = xtc_db_fetch_array($check_query);
      if ($check['tax_class_title'] != $tax_class_title) {
        xtc_db_query("UPDATE ".TABLE_TAX_CLASS."
                         SET tax_class_title = '".xtc_db_input($tax_class_title)."'
                       WHERE tax_class_id = ".$tax_class_id);
      }
    }
  }
  
  // delete duplicate content
  if (defined('REVIEWS_PURCHASED_INFOS')
      && REVIEWS_PURCHASED_INFOS != ''
      )
  {
    $content_query = xtc_db_query("SELECT *
                                     FROM ".TABLE_CONTENT_MANAGER."
                                    WHERE content_title = '".xtc_db_input('Information on the authenticity of customer reviews')."'");
    if (xtc_db_num_rows($content_query) > 1) {
      while ($content = xtc_db_fetch_array($content_query)) {
        if ($content['content_group'] != REVIEWS_PURCHASED_INFOS) {
          xtc_db_query("DELETE FROM ".TABLE_CONTENT_MANAGER."
                              WHERE content_group = '".(int)$content['content_group']."'");
        }
      }
    }
  }
  
  //install new configurations
  if (file_exists(DIR_FS_CATALOG.DIR_ADMIN.'includes/configuration_installer.php')) {
    define('_VALID_XTC', true);
    include(DIR_FS_CATALOG.DIR_ADMIN.'includes/configuration_installer.php');
  }
  
  // check phpfastcache
  if (is_dir(DIR_FS_EXTERNAL.'phpfastcache') && !is_dir(DIR_FS_EXTERNAL.'Phpfastcache')) {
    rename(DIR_FS_EXTERNAL.'phpfastcache', DIR_FS_EXTERNAL.'Phpfastcache');
  }
  
  // set shipping title
  xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                   SET configuration_value = 'shipping_default'
                 WHERE configuration_key = 'SHOW_SHIPPING_MODULE_TITLE'
                   AND configuration_value = 'standard'");

  // scheduled task
  if (defined('MODULE_SITEMAPORG_STATUS') 
      && MODULE_SITEMAPORG_STATUS == 'True'
      )
  {
    xtc_db_query("INSERT INTO " . TABLE_SCHEDULED_TASKS . " (time_regularity, time_unit, status, tasks) VALUES ('1', 'd',  '0', 'export_sitemap')");
  }

  // delete invalid geo_zones
  xtc_db_query("DELETE z2gz 
                  FROM ".TABLE_ZONES_TO_GEO_ZONES." z2gz 
             LEFT JOIN ".TABLE_GEO_ZONES." gz 
                       ON gz.geo_zone_id = z2gz.geo_zone_id 
                 WHERE gz.geo_zone_id IS NULL");
  
  // reset paypal allowed zones
  $paypal_module_array = array(
    'MODULE_PAYMENT_PAYPALACDC_ALLOWED',
    'MODULE_PAYMENT_PAYPALBANCONTACT_ALLOWED',
    'MODULE_PAYMENT_PAYPALBLIK_ALLOWED',
    'MODULE_PAYMENT_PAYPALEPS_ALLOWED',
    'MODULE_PAYMENT_PAYPALGIROPAY_ALLOWED',
    'MODULE_PAYMENT_PAYPALIDEAL_ALLOWED',
    'MODULE_PAYMENT_PAYPALMYBANK_ALLOWED',
    'MODULE_PAYMENT_PAYPALPRZELEWY_ALLOWED',
    'MODULE_PAYMENT_PAYPALPUI_ALLOWED',
    'MODULE_PAYMENT_PAYPALSOFORT_ALLOWED',
    'MODULE_PAYMENT_PAYPALTRUSTLY_ALLOWED',
  );
  
  foreach ($paypal_module_array as $paypal_module) {
    if (defined($paypal_module)) {
      xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                       SET configuration_value = ''
                     WHERE configuration_key = '".xtc_db_input($paypal_module)."'");
    }
  }
  
  // rename config key
  $config_array = array(
    'MAX_DISPLAY_CONTENT_MANAGER' => 'MAX_DISPLAY_CONTENT_MANAGER_RESULTS',
    'MAX_DISPLAY_STATS_STATS_PRODUCTS_PURCHASED_RESULTS' => 'MAX_DISPLAY_STATS_PRODUCTS_PURCHASED_RESULTS',
    'MAX_DISPLAY_LIST_CUSTOMERS' => 'MAX_DISPLAY_CUSTOMERS_RESULTS',
    'MAX_DISPLAY_CONTENT_MANAGER' => 'MAX_DISPLAY_CONTENT_MANAGER_RESULTS',
    'MAX_DISPLAY_NEWSLETTER_RECIPIENTS' => 'MAX_DISPLAY_NEWSLETTER_RECIPIENTS_RESULTS',
    'MAX_DISPLAY_ORDERS_STATUS' => 'MAX_DISPLAY_ORDERS_STATUS_RESULTS',

    'MODULE_DHL_STATUS' => 'MODULE_DHL_BUSINESS_STATUS',
    'MODULE_DHL_USER' => 'MODULE_DHL_BUSINESS_USER',
    'MODULE_DHL_SIGNATURE' => 'MODULE_DHL_BUSINESS_SIGNATURE',
    'MODULE_DHL_EKP' => 'MODULE_DHL_BUSINESS_EKP',
    'MODULE_DHL_ACCOUNT' => 'MODULE_DHL_BUSINESS_ACCOUNT',
    'MODULE_DHL_PREFIX' => 'MODULE_DHL_BUSINESS_PREFIX',
    'MODULE_DHL_WEIGHT_CN23' => 'MODULE_DHL_BUSINESS_WEIGHT_CN23',
    'MODULE_DHL_NOTIFICATION' => 'MODULE_DHL_BUSINESS_NOTIFICATION',
    'MODULE_DHL_STATUS_UPDATE' => 'MODULE_DHL_BUSINESS_STATUS_UPDATE',
    'MODULE_DHL_CODING' => 'MODULE_DHL_BUSINESS_CODING',
    'MODULE_DHL_PRODUCT' => 'MODULE_DHL_BUSINESS_PRODUCT',
    'MODULE_DHL_RETOURE' => 'MODULE_DHL_BUSINESS_RETOURE',
    'MODULE_DHL_PERSONAL' => 'MODULE_DHL_BUSINESS_PERSONAL',
    'MODULE_DHL_NO_NEIGHBOUR' => 'MODULE_DHL_BUSINESS_NO_NEIGHBOUR',
    'MODULE_DHL_AVS' => 'MODULE_DHL_BUSINESS_AVS',
    'MODULE_DHL_IDENT' => 'MODULE_DHL_BUSINESS_IDENT',
    'MODULE_DHL_PARCEL_OUTLET' => 'MODULE_DHL_BUSINESS_PARCEL_OUTLET',
    'MODULE_DHL_BULKY' => 'MODULE_DHL_BUSINESS_BULKY',
    'MODULE_DHL_DISPLAY_LABEL' => 'MODULE_DHL_BUSINESS_DISPLAY_LABEL',
    'MODULE_DHL_PREMIUM' => 'MODULE_DHL_BUSINESS_PREMIUM',
    'MODULE_DHL_ENDORSEMENT' => 'MODULE_DHL_BUSINESS_ENDORSEMENT',
    'MODULE_DHL_COMPANY' => 'MODULE_DHL_BUSINESS_COMPANY',
    'MODULE_DHL_FIRSTNAME' => 'MODULE_DHL_BUSINESS_FIRSTNAME',
    'MODULE_DHL_LASTNAME' => 'MODULE_DHL_BUSINESS_LASTNAME',
    'MODULE_DHL_ADDRESS' => 'MODULE_DHL_BUSINESS_ADDRESS',
    'MODULE_DHL_POSTCODE' => 'MODULE_DHL_BUSINESS_POSTCODE',
    'MODULE_DHL_CITY' => 'MODULE_DHL_BUSINESS_CITY',
    'MODULE_DHL_TELEPHONE' => 'MODULE_DHL_BUSINESS_TELEPHONE',
    'MODULE_DHL_ACCOUNT_OWNER' => 'MODULE_DHL_BUSINESS_ACCOUNT_OWNER',
    'MODULE_DHL_ACCOUNT_NUMBER' => 'MODULE_DHL_BUSINESS_ACCOUNT_NUMBER',
    'MODULE_DHL_BANK_CODE' => 'MODULE_DHL_BUSINESS_BANK_CODE',
    'MODULE_DHL_BANK_NAME' => 'MODULE_DHL_BUSINESS_BANK_NAME',
    'MODULE_DHL_IBAN' => 'MODULE_DHL_BUSINESS_IBAN',
    'MODULE_DHL_BIC' => 'MODULE_DHL_BUSINESS_BIC',
  );

  // google analytics
  if (defined('TRACKING_GOOGLEANALYTICS_ID')
      && TRACKING_GOOGLEANALYTICS_ID != ''
      )
  {
    $config_array['TRACKING_GOOGLEANALYTICS_ACTIVE'] = 'MODULE_GOOGLE_ANALYTICS_STATUS';
    $config_array['TRACKING_GOOGLEANALYTICS_ID'] = 'MODULE_GOOGLE_ANALYTICS_TAG_ID';
    $config_array['TRACKING_GOOGLE_LINKID'] = 'MODULE_GOOGLE_ANALYTICS_LINKID';
    $config_array['TRACKING_GOOGLE_DISPLAY'] = 'MODULE_GOOGLE_ANALYTICS_DISPLAY';
    $config_array['TRACKING_GOOGLE_ECOMMERCE'] = 'MODULE_GOOGLE_ANALYTICS_ECOMMERCE';
    
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_GOOGLE_ANALYTICS_ADS_ID', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_GOOGLE_ANALYTICS_ADS_CONVERSION_ID', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_GOOGLE_ANALYTICS_COUNT_ADMIN', '".TRACKING_COUNT_ADMIN_ACTIVE."',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
  }
  
  // matomo
  if (defined('TRACKING_PIWIK_ID')
      && TRACKING_PIWIK_ID != ''
      )
  {
    $config_array['TRACKING_PIWIK_ACTIVE'] = 'MODULE_MATOMO_ANALYTICS_STATUS';
    $config_array['TRACKING_PIWIK_ID'] = 'MODULE_MATOMO_ANALYTICS_ID';
    $config_array['TRACKING_PIWIK_LOCAL_PATH'] = 'MODULE_MATOMO_ANALYTICS_LOCAL_PATH';
    $config_array['TRACKING_PIWIK_GOAL'] = 'MODULE_MATOMO_ANALYTICS_GOAL';
    
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_MATOMO_ANALYTICS_COUNT_ADMIN', '".TRACKING_COUNT_ADMIN_ACTIVE."',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
  }

  // facebook
  if (defined('TRACKING_FACEBOOK_ID')
      && TRACKING_FACEBOOK_ID != ''
      )
  {
    $config_array['TRACKING_FACEBOOK_ACTIVE'] = 'MODULE_FACEBOOK_PIXEL_STATUS';
    $config_array['TRACKING_FACEBOOK_ID'] = 'MODULE_FACEBOOK_PIXEL_ID';
    
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_FACEBOOK_PIXEL_COUNT_ADMIN', '".TRACKING_COUNT_ADMIN_ACTIVE."',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
  }
  
  // delete old configuration
  xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'TRACKING_COUNT_ADMIN_ACTIVE'");
  xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'TRACKING_GOOGLEANALYTICS_ACTIVE'");
  xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'TRACKING_GOOGLEANALYTICS_ID'");
  xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'TRACKING_GOOGLE_LINKID'");
  xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'TRACKING_GOOGLE_DISPLAY'");
  xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'TRACKING_GOOGLE_ECOMMERCE'");
  xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'TRACKING_PIWIK_ACTIVE'");
  xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'TRACKING_PIWIK_ID'");
  xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'TRACKING_PIWIK_LOCAL_PATH'");
  xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'TRACKING_PIWIK_GOAL'");
  xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'TRACKING_FACEBOOK_ACTIVE'");
  xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'TRACKING_FACEBOOK_ID'");
  
  // rename config key
  foreach ($config_array as $old_config => $new_config) {
    if (!defined($new_config)) {
      xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                       SET configuration_key = '".$new_config."'
                     WHERE configuration_key = '".$old_config."'");
    }
    xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key = '".$old_config."'");
  }