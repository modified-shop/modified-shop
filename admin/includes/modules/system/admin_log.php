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

class admin_log {
  var $code, $title, $description, $enabled;

  function __construct() {
     $this->code = 'admin_log';
     $this->title = MODULE_ADMIN_LOG_TEXT_TITLE;
     $this->description = MODULE_ADMIN_LOG_TEXT_DESCRIPTION;
     $this->sort_order = defined('MODULE_ADMIN_LOG_SORT_ORDER') ? MODULE_ADMIN_LOG_SORT_ORDER : '';
     $this->enabled = ((defined('MODULE_ADMIN_LOG_STATUS') && MODULE_ADMIN_LOG_STATUS == 'true') ? true : false);
  }

  function process($file) {
    if (isset($_POST['configuration'])
        && isset($_POST['configuration']['MODULE_ADMIN_LOG_SCHEDULED_TASKS'])
        )
    {
      xtc_db_query("UPDATE ".TABLE_SCHEDULED_TASKS."
                       SET status = '".(($_POST['configuration']['MODULE_ADMIN_LOG_SCHEDULED_TASKS'] == 'true' && (int)$_POST['configuration']['MODULE_ADMIN_LOG_TRESHOLD_DAYS'] > 0) ? 1 : 0)."'
                     WHERE tasks = 'adminlog_maintenance'");
    }
  }

  function display() {
    return array('text' => '<br /><div align="center">' . xtc_button(BUTTON_SAVE) .
                           xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=admin_log')) . "</div>");
  }

  function check() {
    if (!isset($this->_check)) {
      if (defined('MODULE_ADMIN_LOG_STATUS')) {
        $this->_check = true;
      } else {
        $check_query = xtc_db_query("SELECT configuration_value 
                                       FROM " . TABLE_CONFIGURATION . " 
                                      WHERE configuration_key = 'MODULE_ADMIN_LOG_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
    }
    return $this->_check;
  }
    
  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_ADMIN_LOG_STATUS', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");  
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_ADMIN_LOG_DISPLAY', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");  
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_ADMIN_LOG_SHOW_DETAILS', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");  
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_ADMIN_LOG_SHOW_DETAILS_FULL', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");  
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_ADMIN_LOG_SCHEDULED_TASKS', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");  
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, date_added) VALUES ('MODULE_ADMIN_LOG_TRESHOLD_DAYS', '365',  '6', '1', '', now())");

    xtc_db_query("CREATE TABLE IF NOT EXISTS `admin_log` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `customers_id` int(11) NOT NULL,
                    `categories_id` int(11) NOT NULL,
                    `products_id` int(11) NOT NULL,
                    `manufacturers_id` int(11) NOT NULL,
                    `content_group` int(11) NOT NULL,
                    `orders_id` int(11) NOT NULL,
                    `module` varchar(128) NOT NULL,
                    `type` varchar(128) NOT NULL,
                    `configuration_id` int(11) NOT NULL,
                    `date_modified` datetime NOT NULL,
                    `text` text NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_customers_id` (`customers_id`),
                    KEY `idx_categories_id` (`categories_id`),
                    KEY `idx_products_id` (`products_id`),
                    KEY `idx_manufacturers_id` (`manufacturers_id`),
                    KEY `idx_content_group` (`content_group`),
                    KEY `idx_orders_id` (`orders_id`),
                    KEY `idx_configuration_id` (`configuration_id`),
                    KEY `idx_date_modified` (`date_modified`)
                  )");

    $table_array = array(
      array('table' => 'admin_log', 'column' => 'date_modified', 'name' => 'idx_date_modified'),
    );
    foreach ($table_array as $table) {
      $check_query = xtc_db_query("SHOW INDEX FROM ".$table['table']." WHERE Column_name = '".xtc_db_input($table['column'])."'");
      if (xtc_db_num_rows($check_query) < 1) {
        xtc_db_query("ALTER TABLE ".$table['table']." ADD INDEX ".$table['name']." (".$table['column'].")");            
      }
    }

    $check_query = xtc_db_query("SELECT *
                                   FROM ".TABLE_SCHEDULED_TASKS."
                                  WHERE tasks = 'adminlog_maintenance'");
    if (xtc_db_num_rows($check_query) < 1) {                      
      xtc_db_query("INSERT INTO " . TABLE_SCHEDULED_TASKS . " (time_regularity, time_unit, status, tasks) VALUES ('1', 'd',  '0', 'adminlog_maintenance')");
    }
  }

  function remove() {
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key in ('" . implode("', '", $this->keys()) . "')");
    xtc_db_query("DELETE FROM " . TABLE_SCHEDULED_TASKS . " WHERE tasks = 'adminlog_maintenance'");
    if ($_SESSION['customer_id'] == '1') {
      xtc_db_query("DROP TABLE `admin_log`");
    }
  }

  function keys() {
    $key = array(
      'MODULE_ADMIN_LOG_STATUS',
      'MODULE_ADMIN_LOG_DISPLAY',
      'MODULE_ADMIN_LOG_SHOW_DETAILS',
      'MODULE_ADMIN_LOG_SHOW_DETAILS_FULL',
      'MODULE_ADMIN_LOG_SCHEDULED_TASKS',
      'MODULE_ADMIN_LOG_TRESHOLD_DAYS',
    );

    return $key;
  }
}
