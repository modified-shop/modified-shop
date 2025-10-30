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

class shopvote
{
    var $code;
    var $title;
    var $description;
    var $sort_order;
    var $enabled;
    var $version;
    var $_check;

    function __construct() 
    {
        $this->version = '1.22';
        $this->code = 'shopvote';
        $this->title = MODULE_SHOPVOTE_TEXT_TITLE;
        $this->description = MODULE_SHOPVOTE_TEXT_DESCRIPTION;
        $this->sort_order = ((defined('MODULE_SHOPVOTE_SORT_ORDER')) ? MODULE_SHOPVOTE_SORT_ORDER : '');
        $this->enabled = ((defined('MODULE_SHOPVOTE_STATUS') && MODULE_SHOPVOTE_STATUS == 'true') ? true : false);
    }

    function process($file) 
    {
        if (isset($_POST['configuration'])
            && isset($_POST['configuration']['MODULE_SHOPVOTE_SCHEDULED_TASKS'])
            )
        {
          xtc_db_query("UPDATE ".TABLE_SCHEDULED_TASKS."
                           SET status = '".(($_POST['configuration']['MODULE_SHOPVOTE_SCHEDULED_TASKS'] == 'true') ? 1 : 0)."'
                         WHERE tasks = 'shopvote_import'");
        }

        if (is_array($_POST['configuration'])
            && count($_POST['configuration']) > 0
            )
        {
          foreach ($_POST['configuration'] as $key => $value) {
            $value = is_array($_POST['configuration'][$key]) ? implode(',', $_POST['configuration'][$key]) : $value;
            $value = str_replace("'", '"', $value);
            
            xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '" . xtc_db_input(encode_htmlentities($value)) . "' WHERE configuration_key = '" . $key . "'");
          }
        }
    }

    function display() 
    {
        return array('text' => '<br>' . xtc_button(BUTTON_SAVE) . '&nbsp;' .
                               xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module='.$this->code))
                     );
    }

    function check() 
    {
        if (!isset($this->_check)) {
          if (defined('MODULE_SHOPVOTE_STATUS')) {
            $this->_check = true;
          } else {
            $check_query = xtc_db_query("SELECT configuration_value 
                                           FROM " . TABLE_CONFIGURATION . " 
                                          WHERE configuration_key = 'MODULE_SHOPVOTE_STATUS'");
            $this->_check = xtc_db_num_rows($check_query);
          }
        }
        return $this->_check;
    }

    function install() 
    {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHOPVOTE_STATUS', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHOPVOTE_SHOPID', '', '6', '0', '', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHOPVOTE_API_KEY', '', '6', '0', '', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHOPVOTE_API_SECRET', '', '6', '0', '', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHOPVOTE_BADGE', '1',  '6', '1', 'xtc_cfg_select_option(array(\'1\', \'2\', \'3\', \'4\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHOPVOTE_DEFAULT_LANG', '".$_SESSION['language_code']."',  '6', '1', 'xtc_cfg_pull_down_language_code(', now())");

        // check scheduled tasks
        if (defined('TABLE_SCHEDULED_TASKS')) {
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHOPVOTE_SCHEDULED_TASKS', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
          $check_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_SCHEDULED_TASKS."
                                        WHERE tasks = 'shopvote_import'");
          if (xtc_db_num_rows($check_query) < 1) {                      
            xtc_db_query("INSERT INTO " . TABLE_SCHEDULED_TASKS . " (time_regularity, time_unit, status, tasks) VALUES ('1', 'h',  '0', 'shopvote_import')");
          }
        }

        $table_array = array(
          array('table' => TABLE_PRODUCTS, 'column' => 'shopvote_last_imported', 'default' => 'DATETIME'),
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

    function remove()
    {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_SHOPVOTE_%'");

        // scheduled task
        if (defined('TABLE_SCHEDULED_TASKS')) {
          xtc_db_query("DELETE FROM " . TABLE_SCHEDULED_TASKS . " WHERE tasks = 'shopvote_import'");
        }
    }

    function keys() 
    {
        $keys = array(
          'MODULE_SHOPVOTE_STATUS',
          'MODULE_SHOPVOTE_SHOPID',
          'MODULE_SHOPVOTE_API_KEY',
          'MODULE_SHOPVOTE_API_SECRET',
          'MODULE_SHOPVOTE_BADGE',
          'MODULE_SHOPVOTE_DEFAULT_LANG',
        );
        
        if (defined('TABLE_SCHEDULED_TASKS')) {
          $keys[] = 'MODULE_SHOPVOTE_SCHEDULED_TASKS';
        }
        
        return $keys;
    }    
}
