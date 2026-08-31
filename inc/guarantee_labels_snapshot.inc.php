<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  /**
   * Writes the historical GARAN data of an order.
   *
   * The checkout and the manual order in the administration use the same functions here, so a
   * manually created order carries the same snapshot as one from the storefront. A snapshot is
   * never derived again later: a changed article, a renamed manufacturer or a replaced set of
   * guarantee conditions must not alter an order that already exists.
   */

  // raised when the composition of the notice snapshot changes, it enters notice_hash
  define('GUARANTEE_LABELS_NOTICE_VERSION', '1.00');

  /**
   * Makes sure the storefront texts of one language are available.
   *
   * The administration loads only lang/<language>/extra/admin/, so the file is pulled in here
   * when it is missing. Constants live for the whole request, therefore a second language in
   * the same request is refused instead of answered with the wrong wording.
   *
   * @param string $language the language directory of the order
   * @return bool
   */
  function guarantee_labels_language($language) {
    static $loaded;

    $language = trim((string)$language);

    if (!defined('TEXT_GUARANTEE_NOTICE_MAIL')) {
      $file = DIR_FS_CATALOG.'lang/'.$language.'/extra/guarantee_labels.php';

      if ($language === '' || strpbrk($language, "/\\\0") !== false || !is_file($file)) {
        return false;
      }

      require_once($file);
      $loaded = $language;

      return true;
    }

    return (!isset($loaded) || $loaded === $language);
  }

  /**
   * The texts that belong to the notice of one language.
   *
   * The administration loads only lang/<language>/extra/admin/, so the storefront texts are
   * pulled in here when they are missing. Constants live for the whole request, therefore a
   * second language in the same request gets no snapshot instead of the wrong texts.
   *
   * @param string $language the language directory of the order
   * @return mixed array of text, link, url and title, false when the language is not fully kept
   */
  function guarantee_labels_notice_texts($language) {
    if (!guarantee_labels_language($language)) {
      return false;
    }

    $constants = array(
      'text' => 'TEXT_GUARANTEE_NOTICE_MAIL',
      'link' => 'TEXT_GUARANTEE_NOTICE_LINK',
      'url' => 'TEXT_GUARANTEE_NOTICE_URL',
      // only labels the block and stays out of notice_hash, see guarantee_labels_notice_hash()
      'title' => 'TEXT_GUARANTEE_NOTICE_TITLE',
    );

    $texts = array();

    foreach ($constants as $key => $constant) {
      if (!defined($constant) || trim(constant($constant)) === '') {
        return false;
      }

      $texts[$key] = trim(constant($constant));
    }

    return $texts;
  }

  /**
   * The graphic of the notice belonging to one language.
   *
   * @param string $language the language directory of the order
   * @return mixed absolute path, false when the language package brings no graphic
   */
  function guarantee_labels_notice_file($language) {
    $language = trim((string)$language);

    if ($language === '' || strpbrk($language, "/\\\0") !== false) {
      return false;
    }

    $file = DIR_FS_CATALOG.'lang/'.$language.'/notice.svg';

    return is_file($file) ? $file : false;
  }

  /**
   * Serialises values with their length in front, so different field boundaries can never
   * produce the same input stream.
   */
  function guarantee_labels_hash_fields($fields) {
    $stream = '';

    foreach ($fields as $field) {
      $stream .= strlen($field).':'.$field.'|';
    }

    return hash('sha256', $stream);
  }

  /**
   * The complete historical language state of the notice: graphic, mail text, link text and
   * the address of the Your Europe portal.
   *
   * @param string $language
   * @return mixed sha256 of the state, false when a part is missing
   */
  function guarantee_labels_notice_hash($language) {
    $file = guarantee_labels_notice_file($language);
    $texts = guarantee_labels_notice_texts($language);

    if ($file === false || $texts === false) {
      return false;
    }

    return guarantee_labels_hash_fields(array(
      hash_file('sha256', $file),
      $texts['text'],
      $texts['link'],
      $texts['url'],
      GUARANTEE_LABELS_NOTICE_VERSION,
    ));
  }

  /**
   * Writes the notice snapshot of one order and archives what it points at.
   *
   * The row means one thing only: this order has a historical notice available for its mails.
   * It documents neither how the order came to be nor whether the notice was displayed.
   *
   * @param int $orders_id
   * @param string $language the language directory of the order
   * @param mixed $customers_status the group of the customer, null uses the session
   * @return bool false when the module is off, the language is incomplete or the archive fails
   */
  function guarantee_labels_notice_snapshot($orders_id, $language, $customers_status = null) {
    require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');

    $orders_id = (int)$orders_id;

    if ($orders_id < 1 || !guarantee_labels_active($customers_status)) {
      return false;
    }

    $hash = guarantee_labels_notice_hash($language);

    if ($hash === false) {
      return false;
    }

    // one snapshot per order, a repeated call must not create a second row
    $existing_query = xtc_db_query("SELECT orders_guarantee_id
                                      FROM ".TABLE_ORDERS_GUARANTEE."
                                     WHERE orders_id = '".$orders_id."'");

    if (xtc_db_num_rows($existing_query) > 0) {
      return true;
    }

    $texts = guarantee_labels_notice_texts($language);
    $file = guarantee_labels_notice_file($language);

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_archive.php');

    $archive = new guarantee_labels_archive();

    $written = $archive->notice_write($hash, array(
      'notice.svg' => file_get_contents($file),
      'notice.json' => json_encode(array(
        'version' => GUARANTEE_LABELS_NOTICE_VERSION,
        'language' => (string)$language,
        'text' => $texts['text'],
        'link' => $texts['link'],
        'url' => $texts['url'],
      )),
    ));

    // without the archived language state a later mail would have to guess, so no row is written
    if ($written === false) {
      guarantee_labels_snapshot_log('notice', $orders_id, $archive->get_errors());
      return false;
    }

    xtc_db_perform(TABLE_ORDERS_GUARANTEE, array(
      'orders_id' => $orders_id,
      'notice_hash' => $hash,
      'date_added' => 'now()',
    ));

    return true;
  }

  /**
   * Loads the GARAN values of one article from the catalogue.
   *
   * Used where no cart data is at hand, above all by the manual order in the administration.
   *
   * @param int $products_id
   * @return mixed array in the shape the output functions expect, false without an article
   */
  function guarantee_labels_snapshot_product($products_id) {
    $products_id = (int)$products_id;

    if ($products_id < 1) {
      return false;
    }

    $products_query = xtc_db_query("SELECT products_id,
                                           products_garan_duration,
                                           products_manufacturers_model,
                                           manufacturers_id
                                      FROM ".TABLE_PRODUCTS."
                                     WHERE products_id = '".$products_id."'");

    if (xtc_db_num_rows($products_query) < 1) {
      return false;
    }

    return xtc_db_fetch_array($products_query);
  }

  /**
   * The guarantee conditions of one article in the language of the order.
   *
   * @return mixed array of hash and file name, false when there is no usable attachment
   */
  function guarantee_labels_terms_snapshot($products_id, $languages_id) {
    $terms_query = xtc_db_query("SELECT content_file
                                   FROM ".TABLE_PRODUCTS_CONTENT."
                                  WHERE products_id = '".(int)$products_id."'
                                    AND languages_id = '".(int)$languages_id."'
                                    AND content_type = 'garan_terms'");

    if (xtc_db_num_rows($terms_query) < 1) {
      return false;
    }

    $terms = xtc_db_fetch_array($terms_query);
    $filename = guarantee_labels_terms_filename($terms['content_file']);

    if ($filename === false) {
      guarantee_labels_snapshot_log('terms', $products_id, array('the name of the attached document cannot be used for a mail: '.$terms['content_file']));
      return false;
    }

    $source = DIR_FS_CATALOG.'media/products/'.$terms['content_file'];

    // the article names a document the shop no longer holds, that is worth knowing about
    if (!is_file($source)) {
      guarantee_labels_snapshot_log('terms', $products_id, array('attached document is missing: '.$terms['content_file']));
      return false;
    }

    $hash = hash_file('sha256', $source);

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_archive.php');

    $archive = new guarantee_labels_archive();

    if ($archive->terms_write($hash, $filename, $source) === false) {
      guarantee_labels_snapshot_log('terms', $products_id, $archive->get_errors());
      return false;
    }

    return array('hash' => $hash, 'filename' => $filename);
  }

  /**
   * The mail path hands PHPMailer a file path and no separate name, so the archived file has to
   * carry the name of the attachment itself. check_attachments() splits its list on commas,
   * which is why a comma is rejected instead of being replaced.
   *
   * @param string $file the stored attachment name
   * @return mixed the plain file name, false when it cannot be used as an attachment
   */
  function guarantee_labels_terms_filename($file) {
    $file = (string)$file;

    // a path part would leave the archive directory, the other characters break the mail path
    if ($file !== basename($file)
        || strpbrk($file, ",/\\\0") !== false
        || preg_match('/[\x00-\x1F\x7F]/', $file)
        || trim($file) === ''
        || $file === '.'
        || $file === '..'
        || strpos($file, '.') === false
        )
    {
      return false;
    }

    return $file;
  }

  /**
   * Writes the GARAN snapshot of one order position.
   *
   * @param int $orders_id
   * @param int $orders_products_id the row that was just written to orders_products
   * @param array $product the article data with duration, model identifier and manufacturer
   * @param int $languages_id the language of the order, it selects the guarantee conditions
   * @param mixed $customers_status the group of the customer, null uses the session
   * @return bool false when the article carries no complete GARAN data
   */
  function guarantee_labels_product_snapshot($orders_id, $orders_products_id, $product, $languages_id, $customers_status = null) {
    require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');

    $orders_id = (int)$orders_id;
    $orders_products_id = (int)$orders_products_id;

    if ($orders_id < 1 || $orders_products_id < 1 || !guarantee_labels_active($customers_status)) {
      return false;
    }

    if (!guarantee_labels_candidate($product)) {
      return false;
    }

    $names = guarantee_labels_manufacturer_names(array($product['manufacturers_id']));
    $label = guarantee_labels_product_label($product, $names);

    // an inactive manufacturer or a failing renderer leaves the position without a snapshot
    if ($label === false) {
      return false;
    }

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_archive.php');

    $archive = new guarantee_labels_archive();

    // the cache may be cleared at any time, the archive keeps the graphics of the order
    $written = $archive->garan_write($label['hash'], array(
      'colour.svg' => $label['colour.svg'],
      'nested.svg' => $label['nested.svg'],
    ));

    if ($written === false) {
      guarantee_labels_snapshot_log('garan', $orders_id, $archive->get_errors());
      return false;
    }

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_renderer.php');

    $renderer = new guarantee_labels_renderer();
    $terms = guarantee_labels_terms_snapshot(isset($product['products_id']) ? $product['products_id'] : 0, $languages_id);

    $sql_data_array = array(
      'orders_id' => $orders_id,
      'orders_products_id' => $orders_products_id,
      'manufacturers_name' => $label['manufacturer'],
      'manufacturers_model' => $product['products_manufacturers_model'],
      'garan_duration' => $renderer->normalize_duration($product['products_garan_duration']),
      'garan_hash' => $label['hash'],
      'terms_hash' => ($terms === false) ? 'null' : $terms['hash'],
      'terms_filename' => ($terms === false) ? 'null' : $terms['filename'],
      'date_added' => 'now()',
    );

    xtc_db_perform(TABLE_ORDERS_PRODUCTS_GUARANTEE, $sql_data_array);

    return true;
  }

  /**
   * A failing archive must not stop an order, but it may not stay silent either.
   */
  function guarantee_labels_snapshot_log($type, $reference, $errors) {
    if (count((array)$errors) < 1) {
      return;
    }

    require_once(DIR_FS_INC.'guarantee_labels_log.inc.php');

    guarantee_labels_log('error', 'snapshot {type} for {reference} failed: {errors}', array(
      'type' => $type,
      'reference' => $reference,
      'errors' => implode(' | ', $errors),
    ));
  }

  /**
   * Removes the snapshot of a deleted order position.
   *
   * The check for the table keeps the order editing working in a shop where the module was
   * never installed, the same way xtc_remove_order() handles the withdrawal tables.
   *
   * @param int $orders_products_id
   * @return void
   */
  function guarantee_labels_product_snapshot_delete($orders_products_id) {
    $orders_products_id = (int)$orders_products_id;

    if ($orders_products_id < 1 || !guarantee_labels_snapshot_table(TABLE_ORDERS_PRODUCTS_GUARANTEE)) {
      return;
    }

    xtc_db_query("DELETE FROM ".TABLE_ORDERS_PRODUCTS_GUARANTEE."
                        WHERE orders_products_id = '".$orders_products_id."'");
  }

  /**
   * The module tables survive an uninstall, so their presence is asked and not the status.
   *
   * @param string $table
   * @return bool
   */
  function guarantee_labels_snapshot_table($table) {
    static $known = array();

    if (!isset($known[$table])) {
      $table_query = xtc_db_query("SHOW TABLES LIKE '".str_replace('_', '\\_', $table)."'");
      $known[$table] = (xtc_db_num_rows($table_query) > 0);
    }

    return $known[$table];
  }

  /**
   * Checks the GARAN values of one order position.
   *
   * The manufacturer arrives as a name here, not as an id: an order keeps its own values and
   * does not follow the catalogue. Everything else is checked the way the article
   * administration checks it, so both places accept exactly the same data.
   *
   * @param array $values manufacturers_name, manufacturers_model and garan_duration
   * @return array the normalised values and the errors that were found
   */
  function guarantee_labels_validate_snapshot($values) {
    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_renderer.php');

    $renderer = new guarantee_labels_renderer();
    $errors = array();

    $name = trim((string)$values['manufacturers_name']);
    $model = trim((string)$values['manufacturers_model']);
    $duration = trim((string)$values['garan_duration']);

    // an emptied set of core data means the position carries no guarantee any more
    if ($name === '' && $model === '' && $duration === '') {
      return array('values' => false, 'errors' => $errors);
    }

    $normalized = $renderer->normalize_duration($duration);

    if ($normalized === false || !$renderer->qualifies($normalized)) {
      $errors[] = sprintf(ERROR_GUARANTEE_LABELS_SNAPSHOT_DURATION, encode_htmlspecialchars($duration));
    }

    if ($name === '') {
      $errors[] = ERROR_GUARANTEE_LABELS_SNAPSHOT_MANUFACTURER;
    }

    if ($model === '') {
      $errors[] = ERROR_GUARANTEE_LABELS_MODEL;
    }

    if ($name !== '' && $renderer->is_ready() && $renderer->fits('manufacturer', $name) === false) {
      $errors[] = sprintf(ERROR_GUARANTEE_LABELS_MANUFACTURER_WIDTH, encode_htmlspecialchars($name));
    }

    if ($model !== '' && $renderer->is_ready() && $renderer->fits('model', $model) === false) {
      $errors[] = sprintf(ERROR_GUARANTEE_LABELS_MODEL_WIDTH, encode_htmlspecialchars($model));
    }

    if (!$renderer->is_ready()) {
      $errors[] = sprintf(ERROR_GUARANTEE_LABELS_NOT_READY, implode(', ', $renderer->missing_requirements()));
    }

    if (count($errors) > 0) {
      return array('values' => false, 'errors' => $errors);
    }

    return array(
      'values' => array(
        'manufacturers_name' => $name,
        'manufacturers_model' => $model,
        'garan_duration' => $normalized,
      ),
      'errors' => $errors,
    );
  }

  /**
   * Writes a corrected GARAN snapshot of one order position.
   *
   * Nothing is written before the graphics of the new values are archived and verified. A
   * failing archive therefore leaves the previous snapshot untouched, including a guarantee
   * document that belongs to it. The catalogue is never changed.
   *
   * @param int $orders_id
   * @param int $orders_products_id
   * @param mixed $values the checked values, false deletes the row
   * @return array the errors that stopped the write
   */
  function guarantee_labels_write_snapshot($orders_id, $orders_products_id, $values) {
    $orders_id = (int)$orders_id;
    $orders_products_id = (int)$orders_products_id;
    $errors = array();

    if ($orders_id < 1 || $orders_products_id < 1) {
      return array(ERROR_GUARANTEE_LABELS_SNAPSHOT_UNKNOWN);
    }

    if ($values === false) {
      xtc_db_query("DELETE FROM ".TABLE_ORDERS_PRODUCTS_GUARANTEE."
                          WHERE orders_id = '".$orders_id."'
                            AND orders_products_id = '".$orders_products_id."'");
      return $errors;
    }

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_renderer.php');
    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_archive.php');

    $renderer = new guarantee_labels_renderer();
    $label = $renderer->label($values['manufacturers_name'], $values['manufacturers_model'], $values['garan_duration']);

    if ($label === false) {
      return array(sprintf(ERROR_GUARANTEE_LABELS_SNAPSHOT_RENDER, implode(', ', $renderer->get_errors())));
    }

    $archive = new guarantee_labels_archive();
    $written = $archive->garan_write($label['hash'], array(
      'colour.svg' => $label['colour.svg'],
      'nested.svg' => $label['nested.svg'],
    ));

    if ($written === false) {
      guarantee_labels_snapshot_log('garan edit', $orders_id, $archive->get_errors());
      return array(sprintf(ERROR_GUARANTEE_LABELS_SNAPSHOT_ARCHIVE, implode(', ', $archive->get_errors())));
    }

    $sql_data_array = array(
      'manufacturers_name' => $values['manufacturers_name'],
      'manufacturers_model' => $values['manufacturers_model'],
      'garan_duration' => $values['garan_duration'],
      'garan_hash' => $label['hash'],
    );

    if (array_key_exists('terms_hash', $values)) {
      $sql_data_array['terms_hash'] = ($values['terms_hash'] === null) ? 'null' : $values['terms_hash'];
      $sql_data_array['terms_filename'] = ($values['terms_filename'] === null) ? 'null' : $values['terms_filename'];
    }

    $existing_query = xtc_db_query("SELECT orders_products_guarantee_id
                                      FROM ".TABLE_ORDERS_PRODUCTS_GUARANTEE."
                                     WHERE orders_products_id = '".$orders_products_id."'");

    if (xtc_db_num_rows($existing_query) > 0) {
      xtc_db_perform(TABLE_ORDERS_PRODUCTS_GUARANTEE, $sql_data_array, 'update', "orders_products_id = '".$orders_products_id."'");
    } else {
      $sql_data_array['orders_id'] = $orders_id;
      $sql_data_array['orders_products_id'] = $orders_products_id;
      $sql_data_array['date_added'] = 'now()';

      if (!array_key_exists('terms_hash', $sql_data_array)) {
        $sql_data_array['terms_hash'] = 'null';
        $sql_data_array['terms_filename'] = 'null';
      }

      xtc_db_perform(TABLE_ORDERS_PRODUCTS_GUARANTEE, $sql_data_array);
    }

    return $errors;
  }

  /**
   * Archives an uploaded guarantee document.
   *
   * @param string $field the name of the file field
   * @return mixed array of hash and file name, false when it cannot be used
   */
  function guarantee_labels_archive_upload($field) {
    if (!isset($_FILES[$field])
        || !isset($_FILES[$field]['tmp_name'])
        || $_FILES[$field]['error'] !== UPLOAD_ERR_OK
        || !is_uploaded_file($_FILES[$field]['tmp_name'])
        )
    {
      return false;
    }

    $filename = guarantee_labels_terms_filename($_FILES[$field]['name']);

    if ($filename === false) {
      return false;
    }

    $hash = hash_file('sha256', $_FILES[$field]['tmp_name']);

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_archive.php');

    $archive = new guarantee_labels_archive();

    if ($archive->terms_write($hash, $filename, $_FILES[$field]['tmp_name']) === false) {
      guarantee_labels_snapshot_log('terms upload', $filename, $archive->get_errors());
      return false;
    }

    return array('hash' => $hash, 'filename' => $filename);
  }

  /**
   * Writes what changed into the history of the order.
   *
   * The entry names the old and the new value of every field, so a correction stays traceable
   * without a log of its own. It never notifies the customer.
   *
   * @param int $orders_id
   * @param int $orders_products_id
   * @param mixed $before the snapshot before the change, false when there was none
   * @param mixed $after the snapshot after it, false when it was removed
   * @return void
   */
  function guarantee_labels_snapshot_history($orders_id, $orders_products_id, $before, $after) {
    require_once(DIR_FS_INC.'html_encoding.php');

    $fields = array(
      'manufacturers_name' => TEXT_GUARANTEE_LABELS_SNAPSHOT_MANUFACTURER,
      'manufacturers_model' => TEXT_GUARANTEE_LABELS_SNAPSHOT_MODEL,
      'garan_duration' => TEXT_GUARANTEE_LABELS_SNAPSHOT_DURATION,
      'terms_filename' => TEXT_GUARANTEE_LABELS_SNAPSHOT_TERMS,
    );

    $changes = array();

    foreach ($fields as $field => $title) {
      $old = ($before === false || !isset($before[$field]) || $before[$field] === null) ? '' : (string)$before[$field];
      $new = ($after === false || !isset($after[$field]) || $after[$field] === null) ? '' : (string)$after[$field];

      // a kept document is not part of the posted values and therefore not a change
      if ($after !== false && $field === 'terms_filename' && !array_key_exists('terms_filename', $after)) {
        continue;
      }

      if ($old !== $new) {
        $changes[] = sprintf(decode_htmlentities(TEXT_GUARANTEE_LABELS_SNAPSHOT_HISTORY_FIELD), decode_htmlentities($title), $old, $new);
      }
    }

    if (count($changes) < 1) {
      return;
    }

    $status_query = xtc_db_query("SELECT orders_status
                                    FROM ".TABLE_ORDERS."
                                   WHERE orders_id = '".(int)$orders_id."'");
    $status = (xtc_db_num_rows($status_query) > 0) ? xtc_db_fetch_array($status_query) : array('orders_status' => 0);

    xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, array(
      'orders_id' => (int)$orders_id,
      'orders_status_id' => (int)$status['orders_status'],
      'date_added' => 'now()',
      'customer_notified' => '0',
      // the history is shown as plain text, entities would end up on the screen
      'comments' => sprintf(decode_htmlentities(TEXT_GUARANTEE_LABELS_SNAPSHOT_HISTORY), (int)$orders_products_id, implode(', ', $changes)),
    ));
  }
