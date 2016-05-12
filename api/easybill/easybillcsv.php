<?php
/* --------------------------------------------------------------
  $Id$

  modified eCommerce Shopsoftware
  http://www.modified-shop.org

  Copyright (c) 2009 - 2013 [www.modified-shop.org]
  --------------------------------------------------------------
  based on:
  (c) 2013 Falk Wolsky
  
  Released under the GNU General Public License
  --------------------------------------------------------------*/
  
  chdir('../../');
  require('includes/application_top_callback.php');
    
  // include needed functions
  if (!function_exists('xtc_set_time_limit')) {
    require_once(DIR_FS_INC.'xtc_set_time_limit.inc.php');
  }
  
  if (defined('MODULE_EASYBILL_CSV_STATUS') 
      && MODULE_EASYBILL_CSV_STATUS == 'True'
      && trim(MODULE_EASYBILL_CSV_CRON_TOKEN) != ''
      && isset($_GET['token'])
      && $_GET['token'] == MODULE_EASYBILL_CSV_CRON_TOKEN
      ) 
  {
    define('_VALID_XTC', true);
    
    include(DIR_FS_CATALOG.(defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/').'includes/modules/system/easybillcsv.php');
    $module = new easybillcsv();

    $module->export = 'no';

    // orders status
    $module->from_orders_status = DEFAULT_SHIPPING_STATUS_ID;
    if (count($_GET['orders_status']) > 0) {
      $_GET['orders_status'] = preg_replace('/[^0-9,]/', '', $_GET['orders_status']);
      $orders_status = explode(',', $_GET['orders_status']);
      $module->from_orders_status = implode("', '", $orders_status);
    }

    // customers status
    $module->from_customers_status = DEFAULT_CUSTOMERS_STATUS_ID;
    if (isset($_GET['customers_status'])) {
      $_GET['customers_status'] = preg_replace('/[^0-9,]/', '', $_GET['customers_status']);
      $customers_status = explode(',', $_GET['customers_status']);
      $module->from_customers_status = implode("', '", $customers_status);
    } 
    
    // last export
    if (isset($_GET['date'])) {
      $date = date("Y-m-d", time() - (60*60*24*intval($_GET['date'])));
      xtc_db_query("UPDATE easybill_last_export SET last_exported = '".$date."'");
    } else {
      $last_export_query = xtc_db_query("SELECT last_exported FROM easybill_last_export");
      $last_export = xtc_db_fetch_array($last_export_query);
      $date = $last_export['last_exported'];
    }
    $module->from_order_date = $date;

    $module->process('easybill/easybill_' . time() . '.csv');

  } else {
    die('Direct Access to this location is not allowed.');
  }
?>