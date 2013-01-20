<?php
/* --------------------------------------------------------------
   $Id: configuration_installer.php 3582 2012-08-31 09:46:45Z web28 $
   (c) 2012 by www.rpa-com.de
   modified 1.06
   --------------------------------------------------------------*/

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

$cfg_install = false;
$cfg_group_install = false;
$values = array();
$values_group = array();

//##############################//

//configuration_group_id 1 --- "Mein Shop"
  $values[] = "(NULL, 'CHECKOUT_USE_PRODUCTS_SHORT_DESCRIPTION', 'false', '1', '40', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'CHECKOUT_SHOW_PRODUCTS_IMAGES', 'true', '1', '41', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  //$values[] = "(NULL, 'CHECKOUT_SHOW_PRODUCTS_IMAGES_STYLE', 'max-width:90px;', '1', '42', NULL, NOW(), NULL, NULL);";
  //$values[] = "(NULL, 'IBN_BILLNR', '1', '1', '99', NULL, NOW(), NULL, NULL);"; //modified 1.07
  //$values[] = "(NULL, 'IBN_BILLNR_FORMAT', '{n}-{d}-{m}-{y}', '1', '99', NULL, NOW(), NULL, NULL);"; //modified 1.07

//configuration_group_id 2 --- "Minimum Werte"

//configuration_group_id 3 --- "Maximalwerte"

//configuration_group_id 4 --- "Bild Optionen"
  $values[] = "(NULL, 'PRODUCT_IMAGE_NO_ENLARGE_UNDER_DEFAULT', 'false', '4', '6', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'), ');";

//configuration_group_id 5 --- "Kundendetails"

//configuration_group_id 6 --- "Modul Optionen"

//configuration_group_id 7 --- "Versandoptionen"
  //$values[] = "(NULL, 'SHIPPING_DEFAULT_TAX_CLASS_METHOD', '1', 7, 7, NULL, NOW(), 'xtc_get_default_tax_class_method_name', 'xtc_cfg_pull_down_default_tax_class_methods(');"; //modified 1.07

//configuration_group_id 8 --- "Artikel Listen Optionen"
  $values[] = "(NULL, 'SHOW_BUTTON_BUY_NOW', 'false', '8', '20', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";

//configuration_group_id 9 --- "Lagerverwaltungs Optionen"
  $values[] = "(NULL, 'STOCK_CHECKOUT_UPDATE_PRODUCTS_STATUS', 'true', '9', '20', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
//configuration_group_id 10 --- "Logging Optionen"

//configuration_group_id 11 --- "Cache Optionen"

//configuration_group_id 12 --- "Email Optionen"
  $values[] = "(NULL, 'EMAIL_SQL_ERRORS', 'false', '12', '14', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'EMAIL_BILLING_ATTACHMENTS', '', '12', '39', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'SHOW_IMAGES_IN_EMAIL', 'false', '12', '50', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'SHOW_IMAGES_IN_EMAIL_DIR', 'thumbnail', '12', '51', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'thumbnail\', \'info\'),');";
  $values[] = "(NULL, 'SHOW_IMAGES_IN_EMAIL_STYLE', 'max-width:90px;max-height:120px;', '12', '52', NULL, NOW(), NULL, NULL);";

//configuration_group_id 13 --- "Download Optionen"

//configuration_group_id 14 --- "GZIP Kompression"

//configuration_group_id 15 --- "Sessions"
  $values[] = "(NULL, 'SESSION_LIFE_CUSTOMERS', '1440', '15', '20', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'SESSION_LIFE_ADMIN', '7200', '15', '21', NULL, NOW(), NULL, NULL);";

//configuration_group_id 16 --- "Metatags Suchmaschinen"

//configuration_group_id 17 --- "Zusatzmodule"
  $values_group[] = "(17,'Additional Modules','Additional Modules',17,1);";
  $values[] = "(NULL, 'GOOGLE_RSS_FEED_REFID', '', 17, 15, NULL, NOW(), NULL, NULL);";

//configuration_group_id 18 --- "UST-ID"

//configuration_group_id 19 --- "Google Conversionr"

//configuration_group_id 20 --- "Import/export"

//configuration_group_id 21 --- "Afterbuy"
  $values[] = "(NULL, 'AFTERBUY_DEALERS', '3', '21', '7', NULL , NOW(), NULL , NULL);";
  $values[] = "(NULL, 'AFTERBUY_IGNORE_GROUPE', '', '21', '8', NULL , NOW(), NULL , NULL);";

//configuration_group_id 22 --- "Such-Optionen"
  //$values[] = "(NULL, 'SEARCH_HIGHLIGHT', 'true', 22, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";   //modified 1.07
  //$values[] = "(NULL, 'SEARCH_HIGHLIGHT_STYLE', 'color:#000;background-color:#eee;border:dotted #000 1px;', 22, 5, NULL, NOW(), NULL, NULL);"; //modified 1.07

//configuration_group_id 23 --- "Econda Tracking"
  $values_group[] = "(23,'Econda Tracking','Econda Tracking System',23,1);";

//configuration_group_id 24 --- "google analytics & piwik tracking"
  $values_group[] = "(24,'PIWIK &amp; Google Analytics Tracking','Settings for PIWIK &amp; Google Analytics Tracking',24,1);";

  $values[] = "('', 'TRACKING_COUNT_ADMIN_ACTIVE', 'false', 24, 1, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "('', 'TRACKING_GOOGLEANALYTICS_ACTIVE', 'false', 24, 2, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "('', 'TRACKING_GOOGLEANALYTICS_ID','UA-XXXXXXX-X', 24, 3, NULL, NOW(), NULL, NULL);";
  $values[] = "('', 'TRACKING_PIWIK_ACTIVE', 'false', 24, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "('', 'TRACKING_PIWIK_LOCAL_PATH','www.domain.de/piwik', 24, 5, NULL, NOW(), NULL, NULL);";
  $values[] = "('', 'TRACKING_PIWIK_ID','1', 24, 6, NULL, NOW(), NULL, NULL);";
  $values[] = "('', 'TRACKING_PIWIK_GOAL','1', 24, 7, NULL, NOW(), NULL, NULL);";

//configuration_group_id 31 --- "Moneybookers"
  $values_group[] = "(31,'Moneybookers','Moneybookers System',31,1);";

//configuration_group_id 40 --- "Popup window configuration"
  $values_group[] = "(40,'Popup Window Configuration','Popup Window Parameters',40,1);";

  $values[] = "(NULL, 'POPUP_SHIPPING_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=400&width=600', '40', '10', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POPUP_SHIPPING_LINK_CLASS', 'thickbox', '40', '11', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POPUP_CONTENT_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=400&width=600', '40', '20', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POPUP_CONTENT_LINK_CLASS', 'thickbox', '40', '21', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POPUP_PRODUCT_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=450&width=750', '40', '30', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POPUP_PRODUCT_LINK_CLASS', 'thickbox', '40', '31', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POPUP_COUPON_HELP_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=400&width=600', '40', '40', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POPUP_COUPON_HELP_LINK_CLASS', 'thickbox', '40', '41', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POPUP_PRODUCT_PRINT_SIZE', 'width=640, height=600', '40', '60', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'POPUP_PRINT_ORDER_SIZE', 'width=640, height=600', '40', '70', NULL, NOW(), NULL, NULL);";

//configuration_group_id 1000 --- "Adminbereich"
  $values_group[] = "(1000,'Adminarea Options','Adminarea Configuration', 1000,1);";

  $values[] = "(NULL, 'USE_ADMIN_THUMBS_IN_LIST', 'true', '1000', '32', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";
  $values[] = "(NULL, 'USE_ADMIN_THUMBS_IN_LIST_STYLE', 'max-width:40px;max-height:40px;', '1000', '33', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'MAX_DISPLAY_ORDER_RESULTS', '30', '1000', '50', NULL , NOW(), NULL , NULL);";
  $values[] = "(NULL, 'MAX_DISPLAY_LIST_PRODUCTS', '50', '1000', '51', NULL , NOW(), NULL , NULL);";
  $values[] = "(NULL, 'MAX_DISPLAY_LIST_CUSTOMERS', '100', '1000', '52', NULL , NOW(), NULL , NULL);";
  $values[] = "(NULL, 'MAX_ROW_LISTS_ATTR_OPTIONS', '10', '1000', '53', NULL , NOW(), NULL , NULL);";
  $values[] = "(NULL, 'MAX_ROW_LISTS_ATTR_VALUES', '50', '1000', '54', NULL , NOW(), NULL , NULL);";
  $values[] = "(NULL, 'WHOS_ONLINE_TIME_LAST_CLICK', '900', '1000', '60', NULL, NOW(), NULL, NULL);";
  $values[] = "(NULL, 'WHOS_ONLINE_IP_WHOIS_SERVICE', 'http://www.utrace.de/?query=', '1000', '62', NULL, NOW(), NULL, NULL);"; 
  $values[] = "(NULL, 'CONFIRM_SAVE_ENTRY', 'true', '1000', '70', NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');";

  //configuration_group_id 111125 --- "Paypal"

//##############################//

//install configuration group
$cfg_group_install = insert_into_config_group_table($values_group);

//update configuration group
$cfg_group_install = update_config_group_table($values_group_update);

//install configuration
$cfg_install = insert_into_config_table($values);

//update configuration
$cfg_install = update_config_table($values_update);

//redirect
if ($cfg_install || $cfg_group_install) {
  xtc_redirect(xtc_href_link(FILENAME_CONFIGURATION, 'gID=' . (int)$_GET['gID']));
}

//---------- FUNCTIONS ----------//
function insert_into_config_table($values)
{
  global $messageStack;
  //print_r($values);
  $install = false;
  foreach($values as $value) {
    $cfg_arr = explode(',', $value);
    $cfg_key = str_replace("'", '',$cfg_arr[1]); // Hochkommata entfernen
    $result_cfg = xtc_db_query("SELECT * FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = '" . trim($cfg_key) . "' LIMIT 1");
    if (xtc_db_num_rows($result_cfg) == 0) {
      $insert_into = "INSERT INTO ".TABLE_CONFIGURATION." (configuration_id ,configuration_key ,configuration_value ,configuration_group_id ,sort_order ,last_modified ,date_added ,use_function ,set_function) VALUES ";
      if( xtc_db_query($insert_into.$value)){
        $messageStack->add_session('OK: INSERT INTO '.TABLE_CONFIGURATION.' '.$value, 'success');
        $install = true;
      } else {
        $messageStack->add_session('ERROR: INSERT INTO '.TABLE_CONFIGURATION.' '.$value, 'error');
      }
    }
  }
  return $install;
}

function update_config_table($values)
{
  global $messageStack;
  //print_r($values);
  $install = false;
  foreach($values as $value) {
    $cfg_arr = explode(',', $value);
    $cfg_key = str_replace("'", '',$cfg_arr[1]); // Hochkommata entfernen
    $result_cfg = xtc_db_query("SELECT * FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = '" . trim($cfg_key) . "' LIMIT 1");
    if (xtc_db_num_rows($result_cfg) == 0) {
      $insert_into = "INSERT INTO ".TABLE_CONFIGURATION." (configuration_id ,configuration_key ,configuration_value ,configuration_group_id ,sort_order ,last_modified ,date_added ,use_function ,set_function) VALUES ";
      if( xtc_db_query($insert_into.$value)){
        $messageStack->add_session('OK: INSERT INTO '.TABLE_CONFIGURATION.' '.$value, 'success');
        $install = true;
      } else {
        $messageStack->add_session('ERROR: INSERT INTO '.TABLE_CONFIGURATION.' '.$value, 'error');
      }
    }
  }
  return $install;
}

function insert_into_config_group_table($values_group)
{
  global $messageStack;
  $install = false;
  foreach($values_group as $value) {
    $cfg_arr = explode(',', $value);
    $cfg_id = str_replace(array("(","'"), '',$cfg_arr[0]);
    $query = "SELECT * FROM ".TABLE_CONFIGURATION_GROUP." WHERE configuration_group_id = '".$cfg_id ."' LIMIT 1";
    $result_cfg_query = xtc_db_query($query);
    if (xtc_db_num_rows($result_cfg_query) == 0) {
      $insert_into = "INSERT INTO ".TABLE_CONFIGURATION_GROUP ." VALUES ";
      if (xtc_db_query($insert_into.$value)) {
        $messageStack->add_session('OK: INSERT INTO '.TABLE_CONFIGURATION_GROUP.' '.$value, 'success');
        return true;
      } else {
        $messageStack->add_session('ERROR: INSERT INTO '.TABLE_CONFIGURATION_GROUP.' '.$value, 'error');
      }
    }
  }
  return $install;
}

function update_config_group_table($values_group)
{
  global $messageStack;
  $install = false;
  foreach($values_group as $value) {
    $cfg_arr = explode(',', $value);
    $cfg_id = str_replace(array("(","'"), '',$cfg_arr[0]);
    $query = "SELECT * FROM ".TABLE_CONFIGURATION_GROUP." WHERE configuration_group_id = '".$cfg_id ."' LIMIT 1";
    $result_cfg_query = xtc_db_query($query);
    if (xtc_db_num_rows($result_cfg_query) == 0) {
      $insert_into = "INSERT INTO ".TABLE_CONFIGURATION_GROUP ." VALUES ";
      if (xtc_db_query($insert_into.$value)) {
        $messageStack->add_session('OK: INSERT INTO '.TABLE_CONFIGURATION_GROUP.' '.$value, 'success');
        return true;
      } else {
        $messageStack->add_session('ERROR: INSERT INTO '.TABLE_CONFIGURATION_GROUP.' '.$value, 'error');
      }
    }
  }
  return $install;
}