<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  class dhl_business {
    var $code, $title, $description, $enabled;

    function __construct() {
      global $order;
      
      $this->version = '1.17';
      $this->code = 'dhl_business';
      $this->title = MODULE_DHL_BUSINESS_TEXT_TITLE;
      $this->description = MODULE_DHL_BUSINESS_TEXT_DESCRIPTION.'<br><br><br><b>Version</b><br>'.$this->version;
      $this->enabled = ((defined('MODULE_DHL_BUSINESS_STATUS') && MODULE_DHL_BUSINESS_STATUS == 'True') ? true : false);
      $this->sort_order = '';
    }

    function process($file) {
    }

    function display() {
      return array('text' => '<div align="center">' . xtc_button('OK') .
                              xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=dhl_business')) . "</div>");
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_DHL_BUSINESS_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_USER', '',  '6', '1', '', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_DHL_BUSINESS_SIGNATURE', '',  '6', '1', 'xtc_cfg_password_field_module(', 'xtc_cfg_display_password', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_EKP', '',  '6', '1', '', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_ACCOUNT', 'WORLD:01',  '6', '1', '', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_PREFIX', '',  '6', '1', '', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_WEIGHT_CN23', '0.1',  '6', '1', '', now())");

      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_NOTIFICATION', 'False',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_DHL_BUSINESS_STATUS_UPDATE', '-1',  '6', '1', 'xtc_cfg_get_DHL_BUSINESS_orders_status(', 'xtc_get_order_status_name', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_CODING', 'False',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_PRODUCT', 'Paket',  '6', '1', 'xtc_cfg_select_option(array(\'Paket\', \'Warenpost\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_DISPLAY_LABEL', 'False',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_RETOURE', 'False',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_BULKY', 'False',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_PERSONAL', 'False',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_NO_NEIGHBOUR', 'False',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_PARCEL_OUTLET', 'False',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_PREMIUM', 'False',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_AVS', '0',  '6', '1', 'xtc_cfg_select_option(array(\'0\', \'16\', \'18\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_IDENT', '0',  '6', '1', 'xtc_cfg_select_option(array(\'0\', \'16\', \'18\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_ENDORSEMENT', 'IMMEDIATE',  '6', '1', 'xtc_cfg_select_option(array(\'IMMEDIATE\', \'ABANDONMENT\'), ', now())");

      // customer data
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_COMPANY', '',  '6', '1', '', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_FIRSTNAME', '',  '6', '1', '', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_LASTNAME', '',  '6', '1', '', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_ADDRESS', '',  '6', '1', '', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_POSTCODE', '',  '6', '1', '', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_CITY', '',  '6', '1', '', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_TELEPHONE', '',  '6', '1', '', now())");
    
      // bank data
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_ACCOUNT_OWNER', '',  '6', '1', '', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_ACCOUNT_NUMBER', '',  '6', '1', '', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_BANK_CODE', '',  '6', '1', '', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_BANK_NAME', '',  '6', '1', '', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_IBAN', '',  '6', '1', '', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DHL_BUSINESS_BIC', '',  '6', '1', '', now())");

      $table_array = array(
        array('table' => TABLE_ORDERS_TRACKING, 'column' => 'external', 'default' => 'INT(1) NOT NULL'),
        array('table' => TABLE_ORDERS_TRACKING, 'column' => 'dhl_label_url', 'default' => 'VARCHAR(512) NOT NULL'),
        array('table' => TABLE_ORDERS_TRACKING, 'column' => 'dhl_export_url', 'default' => 'VARCHAR(512) NOT NULL'),
      );
      foreach ($table_array as $table) {
        $check_query = xtc_db_query("SHOW COLUMNS FROM ".$table['table']." LIKE '".xtc_db_input($table['column'])."'");
        if (xtc_db_num_rows($check_query) < 1) {
          xtc_db_query("ALTER TABLE ".$table['table']." ADD ".$table['column']." ".$table['default']."");
        }
      }
    }

    function remove() {
      xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array(
        'MODULE_DHL_BUSINESS_STATUS',
        'MODULE_DHL_BUSINESS_USER',
        'MODULE_DHL_BUSINESS_SIGNATURE',
        'MODULE_DHL_BUSINESS_EKP',
        'MODULE_DHL_BUSINESS_ACCOUNT',
        'MODULE_DHL_BUSINESS_PREFIX',
        'MODULE_DHL_BUSINESS_WEIGHT_CN23',
        
        'MODULE_DHL_BUSINESS_NOTIFICATION',
        'MODULE_DHL_BUSINESS_STATUS_UPDATE',
        'MODULE_DHL_BUSINESS_CODING',
        'MODULE_DHL_BUSINESS_PRODUCT',
        'MODULE_DHL_BUSINESS_RETOURE',
        'MODULE_DHL_BUSINESS_PERSONAL',
        'MODULE_DHL_BUSINESS_NO_NEIGHBOUR',
        'MODULE_DHL_BUSINESS_AVS',
        'MODULE_DHL_BUSINESS_IDENT',
        'MODULE_DHL_BUSINESS_PARCEL_OUTLET',
        'MODULE_DHL_BUSINESS_BULKY',
        'MODULE_DHL_BUSINESS_DISPLAY_LABEL',
        'MODULE_DHL_BUSINESS_PREMIUM',
        'MODULE_DHL_BUSINESS_ENDORSEMENT',
        
        'MODULE_DHL_BUSINESS_COMPANY',
        'MODULE_DHL_BUSINESS_FIRSTNAME',
        'MODULE_DHL_BUSINESS_LASTNAME',
        'MODULE_DHL_BUSINESS_ADDRESS',
        'MODULE_DHL_BUSINESS_POSTCODE',
        'MODULE_DHL_BUSINESS_CITY',
        'MODULE_DHL_BUSINESS_TELEPHONE',

        'MODULE_DHL_BUSINESS_ACCOUNT_OWNER',
        'MODULE_DHL_BUSINESS_ACCOUNT_NUMBER',
        'MODULE_DHL_BUSINESS_BANK_CODE',
        'MODULE_DHL_BUSINESS_BANK_NAME',
        'MODULE_DHL_BUSINESS_IBAN',
        'MODULE_DHL_BUSINESS_BIC',
      );
    }
  }


  if (!function_exists('xtc_cfg_get_DHL_BUSINESS_orders_status')) {
    function xtc_cfg_get_DHL_BUSINESS_orders_status($cfg_value, $cfg_key) {    
      return xtc_draw_pull_down_menu('configuration['.$cfg_key.']', array_merge(array(array('id' => '-1', 'text' => TEXT_DHL_BUSINESS_NO),array('id' => '0', 'text' => TEXT_DHL_BUSINESS_NO_STATUS_CHANGE)), xtc_get_orders_status()), $cfg_value);
    }
  }
