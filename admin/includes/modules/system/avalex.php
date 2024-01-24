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
  
  // include needed classes
  require_once(DIR_FS_EXTERNAL.'avalex/avalex_update.php');
  
  class avalex {
    var $code, $title, $description, $enabled;

    function __construct() {
      global $order;
      
      $this->version = '1.01';
      $this->code = 'avalex';
      $this->title = MODULE_AVALEX_TEXT_TITLE;
      $this->description = MODULE_AVALEX_TEXT_DESCRIPTION.'<br><br><br><b>Version</b><br>'.$this->version;
      $this->enabled = ((defined('MODULE_AVALEX_STATUS') && MODULE_AVALEX_STATUS == 'True') ? true : false);
      $this->sort_order = '';
    }

    function process($file) {
      if (defined('TABLE_SCHEDULED_TASKS')
          && isset($_POST['configuration'])
          && isset($_POST['configuration']['MODULE_AVALEX_STATUS'])
          )
      {
        xtc_db_query("UPDATE ".TABLE_SCHEDULED_TASKS."
                         SET status = '".(($_POST['configuration']['MODULE_AVALEX_STATUS'] == 'True') ? 1 : 0)."'
                       WHERE tasks = 'avalex_update'");
      }

      if ($this->enabled === true 
          && isset($_POST['import']) 
          && $_POST['import'] == 'yes'
          )
      {
        $avalex = new avalex_update();
        $avalex->check_update();
      }
    }

    function display() {    
      return array('text' =>  '<br/><b>'.MODULE_AVALEX_ACTION_TITLE.'</b><br/>'.
                              MODULE_AVALEX_ACTION_DESC.'<br>'.
                              xtc_draw_radio_field('import', 'no', true).NO.'<br>'.
                              xtc_draw_radio_field('import', 'yes', false).YES.'<br>'.

                             '<br /><div align="center">' . xtc_button('OK') .
                              xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=avalex')) . "</div>");
    }

    function check() {
      if (!isset($this->_check)) {
        if (defined('MODULE_AVALEX_STATUS')) {
          $this->_check = true;
        } else {
          $check_query = xtc_db_query("SELECT configuration_value 
                                         FROM " . TABLE_CONFIGURATION . " 
                                        WHERE configuration_key = 'MODULE_AVALEX_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
      }
      return $this->_check;
    }

    function install() {
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_AVALEX_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_AVALEX_API', '',  '6', '1', '', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_AVALEX_DOMAIN', '',  '6', '1', '', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_AVALEX_TYPE', 'Database',  '6', '4', 'xtc_cfg_select_option(array(\'File\', \'Database\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_AVALEX_LAST_UPDATED', '',  '6', '6', '', now())");

      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_AVALEX_TYPE_AGB', '3',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_AVALEX_TYPE_DSE', '2',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_AVALEX_TYPE_WRB', '9',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_AVALEX_TYPE_IMP', '4',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");

      $query_result = xtc_db_query("SHOW COLUMNS FROM `" . TABLE_ADMIN_ACCESS . "`");
      $db_table_rows = array();
      while ($row = xtc_db_fetch_array($query_result)) {
        $db_table_rows[] = $row['Field'];
      }
      
      if (!in_array('avalex', $db_table_rows)) {
        xtc_db_query("ALTER TABLE `" . TABLE_ADMIN_ACCESS . "` ADD `avalex` INT(1) NOT NULL DEFAULT 0");
        xtc_db_query("UPDATE `" . TABLE_ADMIN_ACCESS . "` SET `avalex` = 1 WHERE `customers_id` = 1");
        xtc_db_query("UPDATE `" . TABLE_ADMIN_ACCESS . "` SET `avalex` = 1 WHERE `customers_id` = ".$_SESSION['customer_id']);
        xtc_db_query("UPDATE `" . TABLE_ADMIN_ACCESS . "` SET `avalex` = 9 WHERE `customers_id`='groups'");
      }

      // check scheduled tasks
      if (defined('TABLE_SCHEDULED_TASKS')) {
        $check_query = xtc_db_query("SELECT *
                                       FROM ".TABLE_SCHEDULED_TASKS."
                                      WHERE tasks = 'avalex_update'");
        if (xtc_db_num_rows($check_query) < 1) {                      
          xtc_db_query("INSERT INTO " . TABLE_SCHEDULED_TASKS . " (time_regularity, time_unit, status, tasks) VALUES ('1', 'h',  '0', 'avalex_update')");
        }
      }
    }

    function remove() {
      xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");

      // scheduled task
      if (defined('TABLE_SCHEDULED_TASKS')) {
        xtc_db_query("DELETE FROM " . TABLE_SCHEDULED_TASKS . " WHERE tasks = 'avalex_update'");
      }
    }

    function keys() {
      return array(
        'MODULE_AVALEX_STATUS',
        'MODULE_AVALEX_API',
        'MODULE_AVALEX_DOMAIN',
        'MODULE_AVALEX_TYPE',
        'MODULE_AVALEX_TYPE_AGB',
        'MODULE_AVALEX_TYPE_DSE',
        'MODULE_AVALEX_TYPE_WRB',
        'MODULE_AVALEX_TYPE_IMP',
      );
    }
  }
