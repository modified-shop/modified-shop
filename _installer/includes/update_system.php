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

  foreach ($tax_class_id_array as $tax_class_id => $tax_class_title) {                                        
    $check_query = xtc_db_query("SELECT *
                                   FROM ".TABLE_TAX_CLASS."
                                  WHERE tax_class_id = ".$tax_class_id);
    if (xtc_db_num_rows($check_query) == 0) {
      $sql_data_array = array(
        'tax_class_id' => $tax_class_id,
        'tax_class_title' => decode_utf8($tax_class_title),
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

  //install new configurations
  if (file_exists(DIR_FS_CATALOG.DIR_ADMIN.'includes/configuration_installer.php')) {
    define('_VALID_XTC', true);
    include(DIR_FS_CATALOG.DIR_ADMIN.'includes/configuration_installer.php');
  }
  
  // check phpfastcache
  if (is_dir(DIR_FS_EXTERNAL.'phpfastcache') && !is_dir(DIR_FS_EXTERNAL.'Phpfastcache')) {
    rename(DIR_FS_EXTERNAL.'phpfastcache', DIR_FS_EXTERNAL.'Phpfastcache');
  }
  
  // rename config key
  $config_array = array(
    'MAX_DISPLAY_CONTENT_MANAGER' => 'MAX_DISPLAY_CONTENT_MANAGER_RESULTS',
    'MAX_DISPLAY_STATS_STATS_PRODUCTS_PURCHASED_RESULTS' => 'MAX_DISPLAY_STATS_PRODUCTS_PURCHASED_RESULTS',
    'MAX_DISPLAY_LIST_CUSTOMERS' => 'MAX_DISPLAY_CUSTOMERS_RESULTS',
    'MAX_DISPLAY_CONTENT_MANAGER' => 'MAX_DISPLAY_CONTENT_MANAGER_RESULTS',
    'MAX_DISPLAY_NEWSLETTER_RECIPIENTS' => 'MAX_DISPLAY_NEWSLETTER_RECIPIENTS_RESULTS',
    'MAX_DISPLAY_ORDERS_STATUS' => 'MAX_DISPLAY_ORDERS_STATUS_RESULTS',
  );
  foreach ($config_array as $old_config => $new_config) {
    if (!defined($new_config)) {
      xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                       SET configuration_key = '".$new_config."'
                     WHERE configuration_key = '".$old_config."'");
    }
    xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key = '".$old_config."'");
  }