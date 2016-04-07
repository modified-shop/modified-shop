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

class shipcloud {
  var $code, $title, $description, $enabled;

  function __construct() {
    global $order;

     $this->code = 'shipcloud';
     $this->title = MODULE_SHIPCLOUD_TEXT_TITLE;
     $this->description = MODULE_SHIPCLOUD_TEXT_DESCRIPTION;
     $this->enabled = ((MODULE_SHIPCLOUD_STATUS == 'True') ? true : false);
   }

  function process($file) {
  }

  function display() {
    return array('text' => '<div align="center">' . xtc_button('OK') .
                            xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=shipcloud')) . "</div>");
  }

  function check() {
    if (!isset($this->_check)) {
      $check_query = xtc_db_query("SELECT configuration_value 
                                     FROM " . TABLE_CONFIGURATION . " 
                                    WHERE configuration_key = 'MODULE_SHIPCLOUD_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }

  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_STATUS', 'False',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_API', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_EMAIL', 'False',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");    
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_EMAIL_TYPE', 'Shop',  '6', '1', 'xtc_cfg_select_option(array(\'Shop\', \'shipcloud\'), ', now())");    
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_LOG', 'False',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");    
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_COMPANY', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_FIRSTNAME', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_LASTNAME', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_ADDRESS', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_POSTCODE', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_CITY', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_TELEPHONE', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_BANK_HOLDER', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_BANK_NAME', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_ACCOUNT_IBAN', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_ACCOUNT_BIC', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPCLOUD_PARCEL', '20,40,30;15,20,20;', '6', '1', 'xtc_cfg_textarea(', now())");

    $table_array = array(
      array('column' => 'external', 'default' => 'INT(1) NOT NULL'),
      array('column' => 'sc_label_url', 'default' => 'VARCHAR(512) NOT NULL'),
      array('column' => 'sc_id', 'default' => 'VARCHAR(256) NOT NULL'),
      array('column' => 'sc_date_added', 'default' => 'DATETIME NOT NULL'),
      array('column' => 'sc_date_pickup', 'default' => 'DATETIME NOT NULL'),
    );
    foreach ($table_array as $table) {
      $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_ORDERS_TRACKING." LIKE '".xtc_db_input($table['column'])."'");
      if (xtc_db_num_rows($check_query) < 1) {
        xtc_db_query("ALTER TABLE ".TABLE_ORDERS_TRACKING." ADD ".$table['column']." ".$table['default']."");
      }
    }

    $admin_query = xtc_db_query("SELECT * 
                                   FROM ".TABLE_ADMIN_ACCESS."
                                  LIMIT 1");
    $admin = xtc_db_fetch_array($admin_query);
    if (!isset($admin['shipcloud_pickup'])) {
      xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." ADD `shipcloud_pickup` INT(1) DEFAULT '0' NOT NULL");
      xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET shipcloud_pickup = '9' WHERE customers_id = 'groups' LIMIT 1");
      xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET shipcloud_pickup = '1' WHERE customers_id = '1' LIMIT 1");        
      if ($_SESSION['customer_id'] > 1) {
        xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET shipcloud_pickup = '1' WHERE customers_id = '".$_SESSION['customer_id']."' LIMIT 1") ;
      }
    }
  }

  function remove() {
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
  }

  function keys() {
    return array('MODULE_SHIPCLOUD_STATUS',
                 'MODULE_SHIPCLOUD_API',
                 'MODULE_SHIPCLOUD_EMAIL',
                 'MODULE_SHIPCLOUD_EMAIL_TYPE',
                 'MODULE_SHIPCLOUD_PARCEL',
                 'MODULE_SHIPCLOUD_COMPANY',
                 'MODULE_SHIPCLOUD_FIRSTNAME',
                 'MODULE_SHIPCLOUD_LASTNAME',
                 'MODULE_SHIPCLOUD_ADDRESS',
                 'MODULE_SHIPCLOUD_POSTCODE',
                 'MODULE_SHIPCLOUD_CITY',
                 'MODULE_SHIPCLOUD_TELEPHONE',
                 'MODULE_SHIPCLOUD_BANK_NAME',
                 'MODULE_SHIPCLOUD_BANK_HOLDER',
                 'MODULE_SHIPCLOUD_ACCOUNT_IBAN',
                 'MODULE_SHIPCLOUD_ACCOUNT_BIC',
                 'MODULE_SHIPCLOUD_LOG',
                 );
  }
}
?>