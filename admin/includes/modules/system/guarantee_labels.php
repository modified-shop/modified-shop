<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  class guarantee_labels {

    var $code;
    var $title;
    var $description;
    var $sort_order;
    var $enabled;
    var $_check;
    var $version;
    var $properties;

    function __construct() {
      $this->version = '1.00';
      $this->code = 'guarantee_labels';
      $this->title = MODULE_GUARANTEE_LABELS_TEXT_TITLE;
      $this->description = MODULE_GUARANTEE_LABELS_TEXT_DESCRIPTION;
      $this->sort_order = '';
      $this->enabled = ((defined('MODULE_GUARANTEE_LABELS_STATUS') && MODULE_GUARANTEE_LABELS_STATUS == 'true') ? true : false);
      $this->properties['button_update'] = '<a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code . '&action=update') . '">' . BUTTON_UPDATE . '</a>';
    }

    function process($file) {
      // runs after the configuration was saved, so any status or group change drops the stale output
      $this->clear_shop_cache();
    }

    function display() {
      return array('text' => '<br /><div align="center">' . xtc_button(BUTTON_SAVE) .
                             xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . ((isset($_GET['set'])) ? $_GET['set'] : 'system') . '&module=' . $this->code)) . '</div>');
    }

    function check() {
      if (!isset($this->_check)) {
        if (defined('MODULE_GUARANTEE_LABELS_STATUS')) {
          $this->_check = true;
        } else {
          $check_query = xtc_db_query("SELECT configuration_value
                                         FROM ".TABLE_CONFIGURATION."
                                        WHERE configuration_key = 'MODULE_GUARANTEE_LABELS_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
      }
      return $this->_check;
    }

    function install() {
      global $messageStack;

      // the label may neither be scaled down nor cut off, so a real text measurement is required
      if ($this->renderer_available() === false) {
        $messageStack->add_session(MODULE_GUARANTEE_LABELS_TEXT_GD_ERROR, 'error');
        return;
      }

      $errors = $this->apply_schema();

      // the caller redirects right after install(), so the status is only written on a verified schema
      if (count($errors) > 0) {
        $messageStack->add_session(MODULE_GUARANTEE_LABELS_TEXT_SCHEMA_ERROR.'<br />'.implode('<br />', $errors), 'error');
        return;
      }

      $this->add_configuration('MODULE_GUARANTEE_LABELS_STATUS', 'true', 'xtc_cfg_select_option(array(\'true\', \'false\'), ');
      $this->add_configuration('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS', '', 'xtc_cfg_multi_checkbox(\'xtc_get_customers_statuses\', \'chr(44)\',');

      $messageStack->add_session(MODULE_GUARANTEE_LABELS_TEXT_INSTALL_SUCCESS, 'success');
    }

    function update() {
      global $messageStack;

      $errors = $this->apply_schema();

      if (count($errors) > 0) {
        $messageStack->add_session(MODULE_GUARANTEE_LABELS_TEXT_SCHEMA_ERROR.'<br />'.implode('<br />', $errors), 'error');
        return false;
      }

      // only add keys introduced by a later version, never touch existing values
      $this->add_configuration('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS', '', 'xtc_cfg_multi_checkbox(\'xtc_get_customers_statuses\', \'chr(44)\',');

      return MODULE_GUARANTEE_LABELS_TEXT_UPDATE_SUCCESS;
    }

    function remove() {
      xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key LIKE 'MODULE_GUARANTEE_LABELS_%'");
    }

    function keys() {
      return array(
        'MODULE_GUARANTEE_LABELS_STATUS',
        'MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS',
      );
    }

    function renderer_available() {
      require_once(DIR_FS_CATALOG.DIR_WS_CLASSES.'guarantee_labels_renderer.php');

      $renderer = new guarantee_labels_renderer();

      return $renderer->is_available();
    }

    /**
     * Creates and verifies the complete module schema.
     * Used by install() and update() alike, so a later schema change only has to be added here.
     *
     * @return array error messages, empty when the schema is complete
     */
    function apply_schema() {
      xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_ORDERS_GUARANTEE." (
                     `orders_guarantee_id` int(11) NOT NULL AUTO_INCREMENT,
                     `orders_id` int(11) NOT NULL,
                     `notice_hash` varchar(64) NOT NULL,
                     `date_added` datetime NOT NULL,
                     PRIMARY KEY (`orders_guarantee_id`),
                     UNIQUE KEY `idx_orders_id` (`orders_id`)
                     )");

      xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_ORDERS_PRODUCTS_GUARANTEE." (
                     `orders_products_guarantee_id` int(11) NOT NULL AUTO_INCREMENT,
                     `orders_id` int(11) NOT NULL,
                     `orders_products_id` int(11) NOT NULL,
                     `manufacturers_name` varchar(255) NOT NULL,
                     `manufacturers_model` varchar(64) NOT NULL,
                     `garan_duration` decimal(4,1) NOT NULL,
                     `garan_hash` varchar(64) NOT NULL,
                     `terms_hash` varchar(64) DEFAULT NULL,
                     `terms_filename` varchar(255) DEFAULT NULL,
                     `date_added` datetime NOT NULL,
                     PRIMARY KEY (`orders_products_guarantee_id`),
                     UNIQUE KEY `idx_orders_products_id` (`orders_products_id`),
                     KEY `idx_orders_id` (`orders_id`)
                     )");

      // CREATE TABLE IF NOT EXISTS does not add a new index to an existing table
      foreach ($this->schema_indexes() as $index) {
        if ($this->index_exists($index['table'], $index['name']) === false) {
          xtc_db_query("ALTER TABLE ".$index['table']." ADD ".$index['definition']);
        }
      }

      foreach ($this->schema_columns() as $column) {
        // an existing column is never changed silently, the shop data behind it is unknown
        if ($this->column_type($column['table'], $column['column']) === false) {
          xtc_db_query("ALTER TABLE ".$column['table']." ADD ".$column['column']." ".$column['definition']." AFTER ".$column['after']);
        }
      }

      return $this->verify_schema();
    }

    /**
     * Checks that every schema element is in place after apply_schema() ran.
     *
     * @return array error messages, empty when the schema is complete
     */
    function verify_schema() {
      $errors = array();

      foreach (array(TABLE_ORDERS_GUARANTEE, TABLE_ORDERS_PRODUCTS_GUARANTEE) as $table) {
        $table_query = xtc_db_query("SHOW TABLES LIKE '".str_replace('_', '\\_', xtc_db_input($table))."'");
        if (xtc_db_num_rows($table_query) < 1) {
          $errors[] = sprintf(MODULE_GUARANTEE_LABELS_TEXT_ERROR_TABLE, $table);
        }
      }

      // a missing table would make every following index check fail as well
      if (count($errors) > 0) {
        return $errors;
      }

      foreach ($this->schema_indexes() as $index) {
        if ($this->index_exists($index['table'], $index['name']) === false) {
          $errors[] = sprintf(MODULE_GUARANTEE_LABELS_TEXT_ERROR_INDEX, $index['name'], $index['table']);
        }
      }

      foreach ($this->schema_columns() as $column) {
        $type = $this->column_type($column['table'], $column['column']);

        if ($type === false) {
          $errors[] = sprintf(MODULE_GUARANTEE_LABELS_TEXT_ERROR_COLUMN, $column['column'], $column['table']);
        } elseif ($type != $column['type']) {
          $errors[] = sprintf(MODULE_GUARANTEE_LABELS_TEXT_ERROR_COLUMN_TYPE, $column['column'], $column['table'], $type, $column['type']);
        }
      }

      return $errors;
    }

    function schema_indexes() {
      return array(
        array('table' => TABLE_ORDERS_GUARANTEE, 'name' => 'idx_orders_id', 'definition' => 'UNIQUE KEY `idx_orders_id` (`orders_id`)'),
        array('table' => TABLE_ORDERS_PRODUCTS_GUARANTEE, 'name' => 'idx_orders_products_id', 'definition' => 'UNIQUE KEY `idx_orders_products_id` (`orders_products_id`)'),
        array('table' => TABLE_ORDERS_PRODUCTS_GUARANTEE, 'name' => 'idx_orders_id', 'definition' => 'KEY `idx_orders_id` (`orders_id`)'),
      );
    }

    /**
     * The two core columns. They also ship with the install schema and the database update,
     * so all three ways have to create the identical definition.
     */
    function schema_columns() {
      return array(
        array('table' => TABLE_PRODUCTS, 'column' => 'products_garan_duration', 'definition' => 'DECIMAL(4,1) NULL', 'type' => 'decimal(4,1)', 'after' => 'products_manufacturers_model'),
        array('table' => TABLE_PRODUCTS_CONTENT, 'column' => 'content_type', 'definition' => "VARCHAR(32) NOT NULL DEFAULT ''", 'type' => 'varchar(32)', 'after' => 'content_link'),
      );
    }

    function index_exists($table, $name) {
      $index_query = xtc_db_query("SHOW KEYS FROM ".$table." WHERE Key_name = '".xtc_db_input($name)."'");
      return (xtc_db_num_rows($index_query) > 0);
    }

    /**
     * @return mixed the column type in lower case, false when the column does not exist
     */
    function column_type($table, $column) {
      $column_query = xtc_db_query("SHOW COLUMNS FROM ".$table." LIKE '".str_replace('_', '\\_', xtc_db_input($column))."'");
      if (xtc_db_num_rows($column_query) < 1) {
        return false;
      }
      $column_data = xtc_db_fetch_array($column_query);
      return strtolower($column_data['Type']);
    }

    function add_configuration($key, $value, $set_function = '') {
      $check_query = xtc_db_query("SELECT configuration_id
                                     FROM ".TABLE_CONFIGURATION."
                                    WHERE configuration_key = '".xtc_db_input($key)."'");
      if (xtc_db_num_rows($check_query) > 0) {
        return;
      }

      $sql_data_array = array(
        'configuration_key' => $key,
        'configuration_value' => $value,
        'configuration_group_id' => 6,
        'sort_order' => 1,
        'set_function' => $set_function,
        'date_added' => 'now()',
      );
      xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
    }

    /**
     * Same effect as the delcache action in admin/configuration.php. Smarty block caches do not
     * know the state of the GARAN data, so a changed label has to drop them.
     */
    function clear_shop_cache() {
      global $modified_cache;

      clear_dir(DIR_FS_CATALOG.'cache/');

      require_once(DIR_FS_CATALOG.'includes/modified_cache.php');
      if (is_object($modified_cache)) {
        $modified_cache->clear();
      }
    }

  }
