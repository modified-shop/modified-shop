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
  
  // install new configurations
  if (file_exists(DIR_FS_CATALOG.DIR_ADMIN.'includes/configuration_installer.php')) {
    define('_VALID_XTC', true);
    include(DIR_FS_CATALOG.DIR_ADMIN.'includes/configuration_installer.php');
  }
  
  // check phpfastcache
  if (is_dir(DIR_FS_EXTERNAL.'phpfastcache') && !is_dir(DIR_FS_EXTERNAL.'Phpfastcache')) {
    rename(DIR_FS_EXTERNAL.'phpfastcache', DIR_FS_EXTERNAL.'Phpfastcache');
  }
  
  // update htaccess
  if (is_file(DIR_FS_CATALOG.'.htaccess')) {
    $rewrite = false;
  
    $htaccess = file(DIR_FS_CATALOG.'.htaccess');
    if (is_array($htaccess)) {
      $file = '';
      foreach ($htaccess as $line) {
        if (preg_match('#^ErrorDocument[\s]+([0-9]+)[\s]+\/sitemap\.html\?error\=([0-9]+)$#', $line, $matches)) {
          $line = sprintf("ErrorDocument %s /error.php?error=%s\n", $matches[1], $matches[2]);
          $rewrite = true;
        }
        $file .= $line;
      }
    
      if ($rewrite === true) {
        file_put_contents(DIR_FS_CATALOG.'.htaccess', $file);
      }
    }
  }
  
  // set shipping title
  xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                   SET configuration_value = 'shipping_default'
                 WHERE configuration_key = 'SHOW_SHIPPING_MODULE_TITLE'
                   AND configuration_value = 'standard'");

  // scheduled task
  $scheduled_tasks_array = array();
  $scheduled_query = xtc_db_query("SELECT *
                                     FROM ".TABLE_SCHEDULED_TASKS);
  while ($scheduled = xtc_db_fetch_array($scheduled_query)) {
    $scheduled_tasks_array[] = $scheduled['tasks'];
  }
  
  if (defined('MODULE_SITEMAPORG_STATUS')
      && !in_array('export_sitemap', $scheduled_tasks_array)
      )
  {
    xtc_db_query("INSERT INTO " . TABLE_SCHEDULED_TASKS . " (time_regularity, time_unit, status, tasks) VALUES ('1', 'd',  '".((MODULE_SITEMAPORG_STATUS == 'True') ? 1 : 0)."', 'export_sitemap')");
  }

  if (defined('MODULE_SHOPVOTE_STATUS')
      && !in_array('shopvote_import', $scheduled_tasks_array)
      )
  {
    xtc_db_query("INSERT INTO " . TABLE_SCHEDULED_TASKS . " (time_regularity, time_unit, status, tasks) VALUES ('1', 'h',  '".((MODULE_SHOPVOTE_STATUS == 'true') ? 1 : 0)."', 'shopvote_import')");
  }

  if (defined('MODULE_TRUSTEDSHOPS_STATUS')
      && !in_array('trustedshops_import', $scheduled_tasks_array)
      )
  {
    $check_query = xtc_db_query("SELECT *
                                   FROM ".TABLE_TRUSTEDSHOPS."
                                  WHERE product_sticker_api = 1
                                    AND status = 1");
    xtc_db_query("INSERT INTO " . TABLE_SCHEDULED_TASKS . " (time_regularity, time_unit, status, tasks) VALUES ('1', 'h',  '".((xtc_db_num_rows($check_query) > 0) ? 1 : 0)."', 'trustedshops_import')");
  }

  if (defined('MODULE_AVALEX_STATUS')
      && !in_array('avalex_update', $scheduled_tasks_array)
      )
  {
    xtc_db_query("INSERT INTO " . TABLE_SCHEDULED_TASKS . " (time_regularity, time_unit, status, tasks) VALUES ('1', 'h',  '".((MODULE_AVALEX_STATUS == 'True') ? 1 : 0)."', 'avalex_update')");
  }

  if (defined('MODULE_JANOLAW_STATUS')
      && !in_array('janolaw_update', $scheduled_tasks_array)
      )
  {
    xtc_db_query("INSERT INTO " . TABLE_SCHEDULED_TASKS . " (time_regularity, time_unit, status, tasks) VALUES ('1', 'h',  '".((MODULE_JANOLAW_STATUS == 'True') ? 1 : 0)."', 'janolaw_update')");
  }

  if (defined('MODULE_PROTECTEDSHOPS_STATUS')
      && !in_array('protectedshops_update', $scheduled_tasks_array)
      )
  {
    xtc_db_query("INSERT INTO " . TABLE_SCHEDULED_TASKS . " (time_regularity, time_unit, status, tasks) VALUES ('1', 'h',  '".((MODULE_PROTECTEDSHOPS_STATUS == 'true') ? 1 : 0)."', 'protectedshops_update')");
  }

  if (defined('MODULE_MAGNALISTER_STATUS')
      && !in_array('magnalister', $scheduled_tasks_array)
      )
  {
    xtc_db_query("INSERT INTO " . TABLE_SCHEDULED_TASKS . " (time_regularity, time_unit, status, tasks) VALUES ('15', 'm',  '".((MODULE_MAGNALISTER_STATUS == 'True') ? 1 : 0)."', 'magnalister')");
  }
  
  // reviews
  if (defined('MODULE_TRUSTEDSHOPS_STATUS') || defined('MODULE_SHOPVOTE_STATUS')) {
    $table_array = array(
      array('table' => TABLE_REVIEWS, 'column' => 'external_id', 'default' => 'VARCHAR(256)'),
      array('table' => TABLE_REVIEWS, 'column' => 'external_source', 'default' => 'VARCHAR(32)'),
    );
    foreach ($table_array as $table) {
      $check_query = xtc_db_query("SHOW COLUMNS FROM ".$table['table']." LIKE '".xtc_db_input($table['column'])."'");
      if (xtc_db_num_rows($check_query) < 1) {
        xtc_db_query("ALTER TABLE ".$table['table']." ADD ".$table['column']." ".$table['default']."");
      }
    }

    $table_array = array(
      array('table' => TABLE_REVIEWS, 'column' => 'external_id', 'name' => 'idx_external_id'),
      array('table' => TABLE_REVIEWS, 'column' => 'external_source', 'name' => 'idx_external_source'),
    );
    foreach ($table_array as $table) {
      $check_query = xtc_db_query("SHOW INDEX FROM ".$table['table']." WHERE Column_name = '".xtc_db_input($table['column'])."'");
      if (xtc_db_num_rows($check_query) < 1) {
        xtc_db_query("ALTER TABLE ".$table['table']." ADD INDEX ".$table['name']." (".$table['column'].")");            
      }
    }
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
    'MAX_DISPLAY_CROSS_SELL_GROUPS' => 'MAX_DISPLAY_CROSS_SELL_GROUPS_RESULTS',
    'MAX_DISPLAY_CURRENCIES' => 'MAX_DISPLAY_CURRENCIES_RESULTS',
    'MAX_DISPLAY_CUSTOMERS_STATUS' => 'MAX_DISPLAY_CUSTOMERS_STATUS_RESULTS',
    'MAX_DISPLAY_LIST_CUSTOMERS' => 'MAX_DISPLAY_CUSTOMERS_RESULTS',
    'MAX_DISPLAY_LANGUAGES' => 'MAX_DISPLAY_LANGUAGES_RESULTS',
    'MAX_DISPLAY_NEWSLETTER_RECIPIENTS' => 'MAX_DISPLAY_NEWSLETTER_RECIPIENTS_RESULTS',
    'MAX_DISPLAY_ORDERS_STATUS' => 'MAX_DISPLAY_ORDERS_STATUS_RESULTS',
    'MAX_DISPLAY_PARCEL_CARRIER' => 'MAX_DISPLAY_PARCEL_CARRIER_RESULTS',
    'MAX_DISPLAY_PRODUCTS_VPE' => 'MAX_DISPLAY_PRODUCTS_VPE_RESULTS',
    'MAX_DISPLAY_SHIPPING_STATUS' => 'MAX_DISPLAY_SHIPPING_STATUS_RESULTS',
    'MAX_DISPLAY_STATS_STATS_PRODUCTS_PURCHASED_RESULTS' => 'MAX_DISPLAY_STATS_PRODUCTS_PURCHASED_RESULTS',
    'MAX_DISPLAY_TAX_CLASSES' => 'MAX_DISPLAY_TAX_CLASSES_RESULTS',

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
  
  // rename config key
  foreach ($config_array as $old_config => $new_config) {
    if (!defined($new_config)) {
      xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                       SET configuration_key = '".$new_config."'
                     WHERE configuration_key = '".$old_config."'");
    }
    xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key = '".$old_config."'");
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

  xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNAM'");
  xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNUM'");
  
  // add columns
  $table_array = array(
    array('table' => TABLE_ADMIN_ACCESS, 'group' => 9, 'column' => 'paypal_info', 'default' => 'INT(1) NOT NULL DEFAULT 0', 'after' => 'blacklist_logs'),
    array('table' => TABLE_ADMIN_ACCESS, 'group' => 9, 'column' => 'paypal_module', 'default' => 'INT(1) NOT NULL DEFAULT 0', 'after' => 'paypal_info'),
    array('table' => TABLE_ADMIN_ACCESS, 'group' => 5, 'column' => 'newsletter_recipients', 'default' => 'INT(1) NOT NULL DEFAULT 0', 'after' => 'paypal_module'),
    array('table' => TABLE_ADMIN_ACCESS, 'group' => 9, 'column' => 'semknox', 'default' => 'INT(1) NOT NULL DEFAULT 0', 'after' => 'newsletter_recipients'),
    array('table' => TABLE_ADMIN_ACCESS, 'group' => 9, 'column' => 'dhl', 'default' => 'INT(1) NOT NULL DEFAULT 0', 'after' => 'semknox'),
  );
  foreach ($table_array as $table) {
    $columns_array = array();
    $check_query = xtc_db_query("SHOW COLUMNS FROM ".$table['table']);
    while ($check = xtc_db_fetch_array($check_query)) {
      $columns_array[] = $check['Field'];
    }
    
    if (!in_array($table['column'], $columns_array)) {
      if (!isset($table['after']) || in_array($table['after'], $columns_array)) {
        xtc_db_query("ALTER TABLE ".$table['table']." ADD ".$table['column']." ".$table['default'].((isset($table['after'])) ? " AFTER ".$table['after'] : ''));
      
        if (isset($table['group'])) {
          xtc_db_query("UPDATE ".$table['table']." SET ".$table['column']." = 1 WHERE customers_id = 1");
          xtc_db_query("UPDATE ".$table['table']." SET ".$table['column']." = ".$table['group']." WHERE customers_id = 'groups'");
        }
      }
    }
  }
