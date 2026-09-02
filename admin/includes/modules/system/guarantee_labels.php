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
          sprintf(MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_EXTENSION, encode_htmlspecialchars($data['file'])),
          defined($installed) && in_array($data['file'], explode(';', constant($installed)), true),
        );
      }

      foreach (array('ADD_SELECT_DEFAULT' => 'products_garan_duration',
                     'ADD_SELECT_SEARCH' => 'products_garan_duration',
                     'ADD_SELECT_CART' => 'products_garan_duration',
                     'ADD_SELECT_PRODUCT' => 'products_garan_duration') as $constant => $column) {
        $rows[] = array(
          sprintf(MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_SELECT, encode_htmlspecialchars($constant)),
          defined($constant) && strpos(constant($constant), $column) !== false,
        );
      }

      $missing = $renderer->missing_requirements();
      $rows[] = array(MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_RENDERER, count($missing) < 1, implode(', ', $missing));

      // only languages the shop actually offers, a switched off one needs no texts
      $languages_query = xtc_db_query("SELECT directory, name
                                         FROM ".TABLE_LANGUAGES."
                                        WHERE status = '1'
                                     ORDER BY sort_order");

      while ($language = xtc_db_fetch_array($languages_query)) {
        $missing_parts = $this->missing_language_parts($language['directory']);
        $rows[] = array(
          sprintf(MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_NOTICE, encode_htmlspecialchars($language['name'])),
          count($missing_parts) < 1,
          implode(', ', $missing_parts),
        );
      }

      $schema_errors = $this->verify_schema();
      $rows[] = array(MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_SCHEMA, count($schema_errors) < 1,
                      implode(' ', $schema_errors), MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_INCOMPLETE);

      foreach ($this->writable_dirs() as $label => $dir) {
        $rows[] = array(sprintf(MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_WRITABLE, encode_htmlspecialchars($label)),
                        $this->dir_writable($dir), $dir, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_LOCKED);
      }

      // a query against a missing table or column would only produce a database error
      if (count($schema_errors) > 0) {
        return $this->diagnosis_table($rows);
      }

      // only a duration above two years produces a label and therefore needs the core data
      $incomplete_query = xtc_db_query("SELECT COUNT(*) AS total
                                        FROM ".TABLE_PRODUCTS." p
                                        LEFT JOIN ".TABLE_MANUFACTURERS." m ON m.manufacturers_id = p.manufacturers_id
                                        WHERE p.products_garan_duration > 2.0
                                        AND (m.manufacturers_id IS NULL
                                             OR m.manufacturers_status != '1'
                                             OR TRIM(COALESCE(p.products_manufacturers_model, '')) = '')");
      $incomplete = xtc_db_fetch_array($incomplete_query);
      $rows[] = array(MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_PRODUCTS, $incomplete['total'] < 1,
                      $incomplete['total'], MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_AFFECTED);

      // a value written past the article administration, for example by a foreign system
      $duration_query = xtc_db_query("SELECT COUNT(*) AS total
                                      FROM ".TABLE_PRODUCTS."
                                      WHERE products_garan_duration IS NOT NULL
                                      AND (products_garan_duration < 0.5
                                           OR products_garan_duration > 99.5
                                           OR MOD(ROUND(products_garan_duration * 10), 5) != 0)");
      $duration = xtc_db_fetch_array($duration_query);
      $rows[] = array(MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_DURATION, $duration['total'] < 1,
                      $duration['total'], MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_AFFECTED);

      // a second attachment per language is ambiguous, the snapshot then refuses to archive one
      $ambiguous_query = xtc_db_query("SELECT COUNT(*) AS total FROM (
                                         SELECT products_id, languages_id
                                         FROM ".TABLE_PRODUCTS_CONTENT."
                                         WHERE content_type = 'garan_terms'
                                         GROUP BY products_id, languages_id
                                         HAVING COUNT(*) > 1
                                       ) AS ambiguous");
      $ambiguous = xtc_db_fetch_array($ambiguous_query);
      $rows[] = array(MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_AMBIGUOUS, $ambiguous['total'] < 1,
                      $ambiguous['total'], MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_AFFECTED);

      // one file for several languages can be correct, the shop owner has to decide
      $shared_query = xtc_db_query("SELECT COUNT(*) AS total FROM (
                                      SELECT content_file
                                      FROM ".TABLE_PRODUCTS_CONTENT."
                                      WHERE content_type = 'garan_terms' AND content_file != ''
                                      GROUP BY content_file
                                      HAVING COUNT(DISTINCT languages_id) > 1
                                    ) AS shared");
      $shared = xtc_db_fetch_array($shared_query);
      $rows[] = array(MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_SHARED, $shared['total'] < 1,
                      $shared['total'], MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_AFFECTED);

      // A marking survives a module that was switched off, and a group leaving the b2b list turns
      // b2c without anyone asking again. Both leave an attachment the label points at but the
      // customer cannot reach.
      $groups_query = xtc_db_query("SELECT pc.content_id, pc.group_ids
                                      FROM ".TABLE_PRODUCTS_CONTENT." pc
                                     WHERE pc.content_type = 'garan_terms'");
      $unreachable = 0;

      require_once(DIR_FS_CATALOG.'inc/guarantee_labels_terms.inc.php');

      while ($content = xtc_db_fetch_array($groups_query)) {
        if (count(guarantee_labels_terms_missing_groups($content['group_ids'])) > 0) {
          $unreachable++;
        }
      }

      $rows[] = array(MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_GROUPS, $unreachable < 1,
                      $unreachable, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_AFFECTED);

      $damaged = $this->damaged_archives();
      $rows[] = array(MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_ARCHIVE, $damaged < 1,
                      $damaged, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_AFFECTED);

      return $this->diagnosis_table($rows);
    }

    /**
     * Renders the collected diagnosis rows. Labels arrive ready to print: their language
     * constants carry entities of their own, so escaping happens where a dynamic value enters
     * the sprintf and not once more here. Notes are plain values and are escaped below.
     *
     * @param array $rows label, state, note, own failure label
     * @return string
     */
    function diagnosis_table($rows) {
      $content = '<div class="clear div_box mrg5"><table class="tableInput border0">';

      foreach ($rows as $row) {
        $note = (isset($row[2]) && $row[2] !== '') ? ' '.encode_htmlspecialchars($row[2]) : '';
        $failed = (isset($row[3]) && $row[3] !== '') ? $row[3] : MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_FAILED;

        $content .= '<tr><td style="width:420px;"><span class="main">'.$row[0].'</span></td>'.
                    '<td><span class="main'.(($row[1] === true) ? '' : ' error').'">'.
                    (($row[1] === true) ? MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_OK : $failed.$note).
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
    /**
     * The directories the module writes into. The cache subdirectory is not listed on purpose:
     * "delcache" removes it and the renderer creates it again, so only its parent has to be writable.
     *
     * @return array label => absolute path
     */
    function writable_dirs() {
      return array(
        'cache/' => DIR_FS_CATALOG.'cache/',
        'media/guarantee_labels/archive/garan/' => DIR_FS_CATALOG.'media/guarantee_labels/archive/garan/',
        'media/guarantee_labels/archive/notice/' => DIR_FS_CATALOG.'media/guarantee_labels/archive/notice/',
        'media/products/garan_archive/' => DIR_FS_CATALOG.'media/products/garan_archive/',
      );
    }

    /**
     * A directory the module creates on demand counts as writable when its nearest existing
     * parent is writable.
     *
     * @param string $dir
     * @return bool
     */
    function dir_writable($dir) {
      $path = rtrim($dir, '/');

      while ($path !== '' && !is_dir($path)) {
        $parent = dirname($path);

        if ($parent === $path) {
          return false;
        }

        $path = $parent;
      }

      return ($path !== '' && is_writable($path));
    }

    /**
     * Counts archived order rows whose files are gone. A mail sent later would silently drop the
     * label or the guarantee conditions, so the shop owner has to see the number here.
     *
     * @return int
     */
    function damaged_archives() {
      require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_archive.php');

      $archive = new guarantee_labels_archive();
      $damaged = 0;

      // read_files() checks every file of the directory against its checksum, so a replaced
      // graphic counts here just like a missing one
      $notice_query = xtc_db_query("SELECT DISTINCT notice_hash FROM ".TABLE_ORDERS_GUARANTEE);
      while ($notice = xtc_db_fetch_array($notice_query)) {
        if ($archive->notice_read($notice['notice_hash']) === false) {
          $damaged++;
        }
      }

      $garan_query = xtc_db_query("SELECT DISTINCT garan_hash FROM ".TABLE_ORDERS_PRODUCTS_GUARANTEE);
      while ($garan = xtc_db_fetch_array($garan_query)) {
        if ($archive->garan_read($garan['garan_hash']) === false) {
          $damaged++;
        }
      }

      $terms_query = xtc_db_query("SELECT DISTINCT terms_hash, terms_filename
                                   FROM ".TABLE_ORDERS_PRODUCTS_GUARANTEE."
                                   WHERE terms_hash IS NOT NULL AND terms_hash != ''");
      while ($terms = xtc_db_fetch_array($terms_query)) {
        $file = $archive->terms_path($terms['terms_hash'], $terms['terms_filename']);

        if (!is_file($file) || hash_file('sha256', $file) !== $terms['terms_hash']) {
          $damaged++;
        }
      }

      return $damaged;
    }

    function process($file) {
      global $messageStack;

      // The status is read from the table because the constant of this request is the old one.
      // Activating without the renderer would leave every article without its label.
      $status_query = xtc_db_query("SELECT configuration_value
                                      FROM ".TABLE_CONFIGURATION."
                                     WHERE configuration_key = 'MODULE_GUARANTEE_LABELS_STATUS'");

      if (xtc_db_num_rows($status_query) > 0) {
        $status = xtc_db_fetch_array($status_query);

        if ($status['configuration_value'] == 'true') {
          // file and function names are plain values and are escaped, the schema errors are
          // finished messages built from language constants and must not be escaped again
          $missing = ($this->renderer_available() === false) ? array('imagettfbbox') : $this->missing_requirements();
          $schema = $this->verify_schema();

          if (count($missing) > 0 || count($schema) > 0) {
            xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                             SET configuration_value = 'false'
                           WHERE configuration_key = 'MODULE_GUARANTEE_LABELS_STATUS'");

            $messageStack->add_session($this->incomplete_message($missing, $schema), 'error');
          }
        }
      }

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

      // templates, fonts and the extension files, otherwise the module runs without ever drawing
      $missing = $this->missing_requirements();

      if (count($missing) > 0) {
        $messageStack->add_session($this->incomplete_message($missing), 'error');
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

    /**
     * The four parts a language needs for the notice: the graphic, the mail text, the link text
     * and the country specific Your Europe address. Missing one of them means the notice cannot
     * be shown in that language; the language neutral GARAN label is not affected.
     *
     * @param string $directory the language directory
     * @return array names of the missing parts
     */
    function missing_language_parts($directory) {
      $missing = array();
      $file = DIR_FS_CATALOG.'lang/'.$directory.'/extra/guarantee_labels.php';

      if (!is_file(DIR_FS_CATALOG.'lang/'.$directory.'/notice.svg')) {
        $missing[] = 'notice.svg';
      }

      if (!is_file($file)) {
        $missing[] = 'extra/guarantee_labels.php';
        return $missing;
      }

      // reading the file is the only way to see the texts of a language that is not loaded
      $content = (string)@file_get_contents($file);

      require_once(DIR_FS_CATALOG.'inc/guarantee_labels_output.inc.php');

      // the same list the checkout and the snapshot use, otherwise the diagnosis would call a
      // language complete that the storefront then refuses
      foreach (guarantee_labels_notice_constants() as $constant) {
        if (!preg_match("/define\\s*\\(\\s*'".$constant."'\\s*,\\s*'[^']+'/", $content)) {
          $missing[] = $constant;
        }
      }

      return $missing;
    }

    function renderer_available() {
      require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_renderer.php');

      $renderer = new guarantee_labels_renderer();

      return $renderer->is_available();
    }

    /**
     * Everything the module needs before it may run: the renderer with its templates and fonts,
     * and the files of the three class extensions. GD alone is not enough, a module without its
     * templates reports success and then quietly never draws a label.
     *
     * @return array the missing parts, empty when the module can be switched on
     */
    /**
     * The message that names what keeps the module from running.
     *
     * File and function names are plain values and are escaped here. The schema errors arrive as
     * finished messages built from language constants that carry entities of their own; escaping
     * those again would print the entity instead of the character.
     *
     * @param array $missing plain names
     * @param array $schema ready messages
     * @return string
     */
    function incomplete_message($missing, $schema = array()) {
      $parts = array();

      if (count($missing) > 0) {
        $parts[] = encode_htmlspecialchars(implode(', ', $missing));
      }

      foreach ($schema as $error) {
        $parts[] = $error;
      }

      return sprintf(MODULE_GUARANTEE_LABELS_TEXT_INCOMPLETE, implode(' ', $parts));
    }

    function missing_requirements() {
      require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_renderer.php');

      $renderer = new guarantee_labels_renderer();
      $missing = $renderer->missing_requirements();

      foreach (array_keys(self::EXTENSIONS) as $type) {
        if ($this->class_extension($type) === false) {
          $missing[] = self::EXTENSIONS[$type]['file'];
        }
      }

      return $missing;
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

      // Columns before indexes: an index over a column that is not there yet cannot be created.
      foreach ($this->schema_columns() as $column) {
        // A module table comes from CREATE TABLE above and is only verified. A column missing
        // there means the table is not the one this module built, and adding a primary or auto
        // increment column to it would fail anyway. Only the two core columns are added.
        if ($column['after'] === '') {
          continue;
        }

        // an existing column is never changed silently, the shop data behind it is unknown
        if ($this->column_type($column['table'], $column['column']) === false) {
          xtc_db_query("ALTER TABLE ".$column['table']." ADD ".$column['column']." ".$column['definition']." AFTER ".$column['after']);
        }
      }

      // CREATE TABLE IF NOT EXISTS does not add a new index to an existing table
      foreach ($this->schema_indexes() as $index) {
        // Only a name that is not there at all is created. An index of that name over other
        // columns or without its uniqueness is left alone: adding it again fails on the
        // duplicate name, and dropping it would touch an index the shop may use for its own
        // reasons. verify_schema() reports it instead.
        // an index over a column that is not there would end in an sql error, and a broken
        // module table is reported by verify_schema() instead
        foreach ($index['columns'] as $index_column) {
          if ($this->column_type($index['table'], $index_column) === false) {
            continue 2;
          }
        }

        if ($this->index_named($index) === false) {
          xtc_db_query("ALTER TABLE ".$index['table']." ADD ".$index['definition']);
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
        if ($this->index_exists($index) === false) {
          $errors[] = sprintf(MODULE_GUARANTEE_LABELS_TEXT_ERROR_INDEX, $index['name'], $index['table']);
        }
      }

      // The primary key of a module table is one column wide. A composite one would let a second
      // row of the same order through, which the whole snapshot logic rules out.
      foreach ($this->schema_primary_keys() as $table => $column) {
        $primary = array('table' => $table, 'name' => 'PRIMARY', 'columns' => array($column), 'unique' => true);

        if ($this->index_exists($primary) === false) {
          $errors[] = sprintf(MODULE_GUARANTEE_LABELS_TEXT_ERROR_INDEX, 'PRIMARY', $table);
        }
      }

      foreach ($this->schema_columns() as $column) {
        $definition = $this->column_definition($column['table'], $column['column']);

        if ($definition === false) {
          $errors[] = sprintf(MODULE_GUARANTEE_LABELS_TEXT_ERROR_COLUMN, $column['column'], $column['table']);
          continue;
        }

        if ($definition['type'] != $column['type']) {
          $errors[] = sprintf(MODULE_GUARANTEE_LABELS_TEXT_ERROR_COLUMN_TYPE, $column['column'], $column['table'], $definition['type'], $column['type']);
          continue;
        }

        // the type alone says nothing about whether the table can be written to
        foreach ($this->column_expectations($column) as $property => $expected) {
          if ($definition[$property] !== $expected) {
            $errors[] = sprintf(MODULE_GUARANTEE_LABELS_TEXT_ERROR_COLUMN_PROPERTY, $column['column'], $column['table'], $property);
          }
        }
      }

      return $errors;
    }

    /**
     * The properties of a column that decide whether the module can write to it, read out of the
     * definition it is created with. Only what is stated there is checked, so a shop that added
     * a comment or a collation of its own is left alone.
     *
     * @param array $column one entry of schema_columns()
     * @return array property => expected value
     */
    function column_expectations($column) {
      $keywords = strtoupper($column['definition']);
      $expected = array('null' => (strpos($keywords, 'NOT NULL') === false));

      if (strpos($keywords, 'AUTO_INCREMENT') !== false) {
        $expected['auto_increment'] = true;
        $expected['key'] = 'PRI';
      }

      // The default is always compared, not only where the definition names one. A column
      // declared without a DEFAULT clause has none, and the database reports null for it; a
      // column that carries one anyway would prefill new rows with a value the module never
      // meant, for example a duration of 0.0 that its own diagnosis then reports.
      $expected['default'] = null;

      if (preg_match("/DEFAULT\s+'([^']*)'/i", $column['definition'], $match)) {
        $expected['default'] = $match[1];
      }

      return $expected;
    }

    /**
     * The one column each module table is keyed by.
     *
     * @return array table => column
     */
    function schema_primary_keys() {
      return array(
        TABLE_ORDERS_GUARANTEE => 'orders_guarantee_id',
        TABLE_ORDERS_PRODUCTS_GUARANTEE => 'orders_products_guarantee_id',
      );
    }

    function schema_indexes() {
      return array(
        array('table' => TABLE_ORDERS_GUARANTEE, 'name' => 'idx_orders_id', 'columns' => array('orders_id'), 'unique' => true,
              'definition' => 'UNIQUE KEY `idx_orders_id` (`orders_id`)'),
        array('table' => TABLE_ORDERS_PRODUCTS_GUARANTEE, 'name' => 'idx_orders_products_id', 'columns' => array('orders_products_id'), 'unique' => true,
              'definition' => 'UNIQUE KEY `idx_orders_products_id` (`orders_products_id`)'),
        array('table' => TABLE_ORDERS_PRODUCTS_GUARANTEE, 'name' => 'idx_orders_id', 'columns' => array('orders_id'), 'unique' => false,
              'definition' => 'KEY `idx_orders_id` (`orders_id`)'),
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

        // CREATE TABLE IF NOT EXISTS leaves an existing table alone, so a partly built or older
        // module table would pass unnoticed and fail on the first snapshot instead
        array('table' => TABLE_ORDERS_GUARANTEE, 'column' => 'orders_guarantee_id', 'definition' => 'INT(11) NOT NULL AUTO_INCREMENT', 'type' => 'int(11)', 'after' => ''),
        array('table' => TABLE_ORDERS_GUARANTEE, 'column' => 'orders_id', 'definition' => 'INT(11) NOT NULL', 'type' => 'int(11)', 'after' => ''),
        array('table' => TABLE_ORDERS_GUARANTEE, 'column' => 'notice_hash', 'definition' => 'VARCHAR(64) NOT NULL', 'type' => 'varchar(64)', 'after' => ''),
        array('table' => TABLE_ORDERS_GUARANTEE, 'column' => 'date_added', 'definition' => 'DATETIME NOT NULL', 'type' => 'datetime', 'after' => ''),

        array('table' => TABLE_ORDERS_PRODUCTS_GUARANTEE, 'column' => 'orders_products_guarantee_id', 'definition' => 'INT(11) NOT NULL AUTO_INCREMENT', 'type' => 'int(11)', 'after' => ''),
        array('table' => TABLE_ORDERS_PRODUCTS_GUARANTEE, 'column' => 'orders_id', 'definition' => 'INT(11) NOT NULL', 'type' => 'int(11)', 'after' => ''),
        array('table' => TABLE_ORDERS_PRODUCTS_GUARANTEE, 'column' => 'orders_products_id', 'definition' => 'INT(11) NOT NULL', 'type' => 'int(11)', 'after' => ''),
        array('table' => TABLE_ORDERS_PRODUCTS_GUARANTEE, 'column' => 'manufacturers_name', 'definition' => 'VARCHAR(255) NOT NULL', 'type' => 'varchar(255)', 'after' => ''),
        array('table' => TABLE_ORDERS_PRODUCTS_GUARANTEE, 'column' => 'manufacturers_model', 'definition' => 'VARCHAR(64) NOT NULL', 'type' => 'varchar(64)', 'after' => ''),
        array('table' => TABLE_ORDERS_PRODUCTS_GUARANTEE, 'column' => 'garan_duration', 'definition' => 'DECIMAL(4,1) NOT NULL', 'type' => 'decimal(4,1)', 'after' => ''),
        array('table' => TABLE_ORDERS_PRODUCTS_GUARANTEE, 'column' => 'garan_hash', 'definition' => 'VARCHAR(64) NOT NULL', 'type' => 'varchar(64)', 'after' => ''),
        array('table' => TABLE_ORDERS_PRODUCTS_GUARANTEE, 'column' => 'terms_hash', 'definition' => 'VARCHAR(64) DEFAULT NULL', 'type' => 'varchar(64)', 'after' => ''),
        array('table' => TABLE_ORDERS_PRODUCTS_GUARANTEE, 'column' => 'terms_filename', 'definition' => 'VARCHAR(255) DEFAULT NULL', 'type' => 'varchar(255)', 'after' => ''),
        array('table' => TABLE_ORDERS_PRODUCTS_GUARANTEE, 'column' => 'date_added', 'definition' => 'DATETIME NOT NULL', 'type' => 'datetime', 'after' => ''),
      );
    }

    /**
     * Whether an index really is the one the module needs. A name alone says nothing: an older
     * table may carry the same name over other columns or without its uniqueness, and the first
     * snapshot would then run into a duplicate instead of an update.
     *
     * @param array $index one entry of schema_indexes()
     * @return bool
     */
    /**
     * Whether an index of that name exists at all, whatever it is over.
     *
     * @param array $index one entry of schema_indexes()
     * @return bool
     */
    function index_named($index) {
      $index_query = xtc_db_query("SHOW KEYS FROM ".$index['table']."
                                    WHERE Key_name = '".xtc_db_input($index['name'])."'");

      return (xtc_db_num_rows($index_query) > 0);
    }

    function index_exists($index) {
      $index_query = xtc_db_query("SHOW KEYS FROM ".$index['table']."
                                    WHERE Key_name = '".xtc_db_input($index['name'])."'
                                 ORDER BY Seq_in_index");

      if (xtc_db_num_rows($index_query) < 1) {
        return false;
      }

      $columns = array();
      $unique = true;

      while ($key = xtc_db_fetch_array($index_query)) {
        $columns[] = $key['Column_name'];
        $unique = ($unique && $key['Non_unique'] == '0');
      }

      return ($columns === $index['columns'] && $unique === $index['unique']);
    }

    /**
     * @return mixed the column type in lower case, false when the column does not exist
     */
    function column_type($table, $column) {
      $column_data = $this->column_definition($table, $column);

      return ($column_data === false) ? false : $column_data['type'];
    }

    /**
     * The full definition of one column, not only its type.
     *
     * A table can carry a column of the right type and still be unusable: without its primary
     * key, without the auto increment behind it, or nullable where the module never expects a
     * null. Those show up as an sql error on the first snapshot, which is far away from the
     * installation that should have reported them.
     *
     * @return mixed array of type, null, key, default and extra, false when the column is gone
     */
    function column_definition($table, $column) {
      $column_query = xtc_db_query("SHOW COLUMNS FROM ".$table." LIKE '".str_replace('_', '\\_', xtc_db_input($column))."'");

      if (xtc_db_num_rows($column_query) < 1) {
        return false;
      }

      $column_data = xtc_db_fetch_array($column_query);

      return array(
        'type' => strtolower($column_data['Type']),
        'null' => (isset($column_data['Null']) && strtoupper($column_data['Null']) === 'YES'),
        'key' => isset($column_data['Key']) ? strtoupper($column_data['Key']) : '',
        'default' => isset($column_data['Default']) ? $column_data['Default'] : null,
        'auto_increment' => (isset($column_data['Extra']) && strpos(strtolower($column_data['Extra']), 'auto_increment') !== false),
      );
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
