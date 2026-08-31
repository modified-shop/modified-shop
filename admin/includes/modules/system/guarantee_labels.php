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

    // the article check and the label in article lists and in the checkout run as class
    // extensions of the article administration, the product class and the order class, all
    // of them registered by this module
    const EXTENSIONS = array(
      'categories' => array('file' => 'guarantee_labels_product.php', 'class' => 'guarantee_labels_product'),
      'product' => array('file' => 'guarantee_labels_listing.php', 'class' => 'guarantee_labels_listing'),
      'order' => array('file' => 'guarantee_labels_order.php', 'class' => 'guarantee_labels_order'),
    );

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

      // only for the module being looked at, the list instantiates every module
      if (isset($_GET['module']) && $_GET['module'] === $this->code && $this->check() > 0) {
        $this->properties['add_content'] = $this->diagnosis();
      }
    }

    /**
     * Shows whether everything the module needs is in place.
     *
     * The parts sit in different places: two class extensions of the shop, one of the
     * administration, columns added to the select lists, the official templates and fonts, and
     * one graphic per language. A missing piece stays silent in the storefront, which is why it
     * is listed here.
     *
     * @return string
     */
    function diagnosis() {
      require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_renderer.php');

      $renderer = new guarantee_labels_renderer();
      $rows = array();

      foreach (self::EXTENSIONS as $type => $data) {
        $installed = 'MODULE_'.strtoupper($type).'_INSTALLED';
        $rows[] = array(
          sprintf(MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_EXTENSION, $data['file']),
          defined($installed) && in_array($data['file'], explode(';', constant($installed)), true),
        );
      }

      foreach (array('ADD_SELECT_DEFAULT' => 'products_garan_duration',
                     'ADD_SELECT_SEARCH' => 'products_garan_duration',
                     'ADD_SELECT_CART' => 'products_garan_duration',
                     'ADD_SELECT_PRODUCT' => 'products_garan_duration') as $constant => $column) {
        $rows[] = array(
          sprintf(MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_SELECT, $constant),
          defined($constant) && strpos(constant($constant), $column) !== false,
        );
      }

      $missing = $renderer->missing_requirements();
      $rows[] = array(MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_RENDERER, count($missing) < 1, implode(', ', $missing));

      $languages_query = xtc_db_query("SELECT directory, name FROM ".TABLE_LANGUAGES." ORDER BY sort_order");
      while ($language = xtc_db_fetch_array($languages_query)) {
        $rows[] = array(
          sprintf(MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_NOTICE, $language['name']),
          is_file(DIR_FS_CATALOG.'lang/'.$language['directory'].'/notice.svg')
          && is_file(DIR_FS_CATALOG.'lang/'.$language['directory'].'/extra/guarantee_labels.php'),
        );
      }

      $content = '<div class="clear div_box mrg5"><table class="tableInput border0">';

      foreach ($rows as $row) {
        $note = (isset($row[2]) && $row[2] !== '') ? ' '.encode_htmlspecialchars($row[2]) : '';
        $content .= '<tr><td style="width:420px;"><span class="main">'.$row[0].'</span></td>'.
                    '<td><span class="main'.(($row[1] === true) ? '' : ' error').'">'.
                    (($row[1] === true) ? MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_OK : MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_FAILED.$note).
                    '</span></td></tr>';
      }

      // the module never empties a cache, that belongs to the shop owner
      $content .= '</table><div class="main mrg5">'.MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_CACHE.'</div>';

      return '<br /><div class="main div_header"><b>'.MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS.'</b></div>'.$content.'</div>';
    }

    /**
     * The module administration calls this after saving. It is the run of an export module,
     * not a save hook, so nothing beyond the own configuration belongs here. After a status
     * or group change the shop owner empties the cache through the action the shop brings for
     * it, delcache in admin/configuration.php.
     */
    function process($file) {
      $this->save_b2b_customers_status();
    }

    /**
     * An empty multi checkbox selection never reaches $_POST, so the generic save of the module
     * administration keeps the previous value and the shop owner cannot clear the list again.
     * The module therefore writes this key itself, the way cao_faktura writes its own fields.
     */
    function save_b2b_customers_status() {
      $groups = array();

      if (isset($_POST['configuration']['MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS'])
          && is_array($_POST['configuration']['MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS'])
          )
      {
        foreach ($_POST['configuration']['MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS'] as $group) {
          if ((int)$group > 0) {
            $groups[] = (int)$group;
          }
        }

        $groups = array_unique($groups);
        sort($groups);
      }

      xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                       SET configuration_value = '".xtc_db_input(implode(',', $groups))."',
                           last_modified = now()
                     WHERE configuration_key = 'MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS'");
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
      $this->register_class_extension();

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
      $this->register_class_extension();

      return MODULE_GUARANTEE_LABELS_TEXT_UPDATE_SUCCESS;
    }

    function remove() {
      $this->unregister_class_extension();
      xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key LIKE 'MODULE_GUARANTEE_LABELS_%'");
    }

    /**
     * The class extensions are installed from here instead of separately, so the shop owner
     * keeps one module and one switch. update() reinstalls them when they were removed by
     * hand.
     */
    function register_class_extension() {
      foreach (self::EXTENSIONS as $type => $data) {
        $extension = $this->class_extension($type);

        if ($extension !== false && $extension->check() < 1) {
          $extension->install();
          $this->update_class_extensions($type);
        }
      }
    }

    function unregister_class_extension() {
      foreach (self::EXTENSIONS as $type => $data) {
        $extension = $this->class_extension($type);

        if ($extension !== false && $extension->check() > 0) {
          $extension->remove();
          $this->update_class_extensions($type);
        }
      }
    }

    /**
     * Rebuilds MODULE_<TYPE>_INSTALLED from the modules that are really installed, so other
     * class extensions and their sort order stay untouched.
     */
    function update_class_extensions($type) {
      require_once(DIR_FS_INC.'update_module_configuration.inc.php');

      update_module_configuration($type);
    }

    /**
     * @param string $type the module type the extension belongs to
     * @return mixed the class extension, false when its file is missing
     */
    function class_extension($type) {
      if (!isset(self::EXTENSIONS[$type])) {
        return false;
      }

      // only the article administration lives below the admin directory
      $directory = ($type === 'categories')
                 ? DIR_FS_ADMIN.'includes/modules/categories/'
                 : DIR_FS_CATALOG.'includes/modules/'.$type.'/';

      $file = $directory.self::EXTENSIONS[$type]['file'];

      if (!is_file($file)) {
        return false;
      }

      require_once($file);

      if (!class_exists(self::EXTENSIONS[$type]['class'])) {
        return false;
      }

      $extension_class = self::EXTENSIONS[$type]['class'];

      return new $extension_class();
    }

    function keys() {
      return array(
        'MODULE_GUARANTEE_LABELS_STATUS',
        'MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS',
      );
    }

    function renderer_available() {
      require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_renderer.php');

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

  }
