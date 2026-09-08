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
  define('GUARANTEE_LABELS_NOTICE_VERSION', '1.01');

  /**
   * Makes sure the storefront texts of one language are available.
   *
   * The administration loads only lang/<language>/extra/admin/, so the file is pulled in here
   * when it is missing. Constants live for the whole request, therefore a second language in
   * the same request is refused instead of answered with the wrong wording.
   *
   * Already defined constants are not simply accepted: in the storefront they belong to the
   * language of the session, which is the language the customer browses in and not necessarily
   * the one the order was placed in. The order view of the customer account would otherwise
   * label an English order with German wording.
   *
   * @param string $language the language directory of the order
   * @return bool
   */
  function guarantee_labels_language($language) {
    static $loaded;

    $language = trim((string)$language);

    if ($language === '' || strpbrk($language, "/\\\0") !== false) {
      return false;
    }

    if (defined('TEXT_GUARANTEE_NOTICE_MAIL')) {
      if (!isset($loaded)) {
        // whoever loaded them did it for the language of this request
        $session = isset($_SESSION['language']) ? trim((string)$_SESSION['language']) : '';

        if ($session !== '') {
          $loaded = $session;
        }
      }

      if (isset($loaded)) {
        return ($loaded === $language);
      }

      // Without a session language the file itself says whether it is the same wording. A miss is
      // answered but not remembered: the next call may ask for the language that really is loaded,
      // and an empty $loaded would still be isset() and lock the answer to false for the request.
      $answered = guarantee_labels_language_of($language);

      if ($answered) {
        $loaded = $language;
      }

      return $answered;
    }

    $file = DIR_FS_CATALOG.'lang/'.$language.'/extra/guarantee_labels.php';

    if (!is_file($file)) {
      return false;
    }

    require_once($file);
    $loaded = $language;

    return true;
  }

  /**
   * Whether the constants already in memory are the ones of this language. Only asked when the
   * request carries no session language, so the answer cannot be taken from there.
   *
   * @param string $language the language directory
   * @return bool
   */
  function guarantee_labels_language_of($language) {
    $file = DIR_FS_CATALOG.'lang/'.$language.'/extra/guarantee_labels.php';

    if (!is_file($file)) {
      return false;
    }

    $content = (string)@file_get_contents($file);

    if (!preg_match("/define\\s*\\(\\s*'TEXT_GUARANTEE_NOTICE_MAIL'\\s*,\\s*'((?:[^'\\\\]|\\\\.)*)'/", $content, $match)) {
      return false;
    }

    return (stripslashes($match[1]) === TEXT_GUARANTEE_NOTICE_MAIL);
  }

  /**
   * The texts that belong to the notice of one language.
   *
   * The administration loads only lang/<language>/extra/admin/, so the storefront texts are
   * pulled in here when they are missing. Constants live for the whole request, therefore a
   * second language in the same request gets no snapshot instead of the wrong texts.
   *
   * Completeness is decided by guarantee_labels_notice_constants(), the same list the checkout
   * and the module diagnosis use. All four returned values are archived and hashed.
   *
   * @param string $language the language directory of the order
   * @return mixed array of text, link, url and title, false when the language is not fully kept
   */
  function guarantee_labels_notice_texts($language) {
    if (!guarantee_labels_language($language)) {
      return false;
    }

    require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');

    // the same completeness the checkout demands, so a snapshot cannot outlive a silent checkout
    if (!guarantee_labels_texts_ready(guarantee_labels_notice_constants())) {
      return false;
    }

    return array(
      'text' => trim(TEXT_GUARANTEE_NOTICE_MAIL),
      'link' => trim(TEXT_GUARANTEE_NOTICE_LINK),
      'url' => trim(TEXT_GUARANTEE_NOTICE_URL),
      'title' => trim(TEXT_GUARANTEE_NOTICE_TITLE),
    );
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

    // Every field that is archived is hashed. Two languages can share graphic and texts and
    // still differ in their directory name, and a changed heading produces a new archive
    // instead of quietly reusing the old one.
    return guarantee_labels_hash_fields(array(
      hash_file('sha256', $file),
      (string)$language,
      $texts['text'],
      $texts['link'],
      $texts['url'],
      $texts['title'],
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

    // The only failure in this file that used to be silent. A language without notice.svg still
    // shows the notice in the checkout, so the order would go out without one and nothing said so.
    if ($hash === false) {
      guarantee_labels_snapshot_log('notice', $orders_id, array('no archivable notice for '.$language));
      return false;
    }

    // one snapshot per order, a repeated call must not create a second row
    // Every read path asks first. Without the table xtc_db_query() returns false, xtc_db_num_rows()
    // reads that as "no row yet", and the archive would be rewritten on every order while the
    // insert fails silently.
    if (!guarantee_labels_snapshot_table(TABLE_ORDERS_GUARANTEE)) {
      guarantee_labels_snapshot_log('notice', $orders_id, array('table '.TABLE_ORDERS_GUARANTEE.' is missing'));
      return false;
    }

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
        // archived so a later view does not need the language file of the order
        'title' => $texts['title'],
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
   * Whether one attachment is visible to the customer group of the order.
   *
   * The same rule the storefront applies, see includes/define_conditions.php: with GROUP_CHECK
   * switched on the group has to be named in group_ids, so an empty selection reaches nobody.
   * With it switched off group_ids is not read at all and everything is visible.
   *
   * The order decides, not the session: the administration creates an order for a customer of
   * another group than its own.
   *
   * @param string $group_ids the selection of the attachment administration
   * @param mixed $customers_status the group of the order, null uses the session
   * @return bool
   */
  function guarantee_labels_terms_visible($group_ids, $customers_status = null) {
    if (!defined('GROUP_CHECK') || GROUP_CHECK != 'true') {
      return true;
    }

    $status = ($customers_status === null && isset($_SESSION['customers_status']['customers_status_id']))
            ? $_SESSION['customers_status']['customers_status_id']
            : $customers_status;

    // Without a group there is nothing to check against. (int)null would ask for c_0_group, the
    // administration, which a correctly maintained attachment never carries: the document would
    // be judged invisible and never archived.
    if ($status === null || $status === '') {
      return false;
    }

    return in_array((int)$status, guarantee_labels_terms_groups($group_ids), true);
  }

  /**
   * The customer groups a selection of the attachment administration names.
   *
   * One place knows the stored shape "c_<id>_group,": the visibility check of the archive and
   * the module diagnosis both read it through here.
   *
   * @param mixed $group_ids the stored selection
   * @return array group ids
   */
  function guarantee_labels_terms_groups($group_ids) {
    if (!preg_match_all('/c_([0-9]+)_group/', (string)$group_ids, $matches)) {
      return array();
    }

    return array_map('intval', $matches[1]);
  }

  /**
   * The guarantee conditions of one article in the language of the order.
   *
   * @return mixed array of hash and file name, false when there is no usable attachment
   */
  function guarantee_labels_terms_snapshot($products_id, $languages_id, $customers_status = null) {
    $terms_query = xtc_db_query("SELECT content_file, group_ids
                                   FROM ".TABLE_PRODUCTS_CONTENT."
                                  WHERE products_id = '".(int)$products_id."'
                                    AND languages_id = '".(int)$languages_id."'
                                    AND content_type = 'garan_terms'
                               ORDER BY content_id");

    $terms_rows = xtc_db_num_rows($terms_query);

    if ($terms_rows < 1) {
      return false;
    }

    // the attachment administration keeps this unique, an older stand may not be. Guessing one
    // of several documents would put an arbitrary file into the order, so none is taken.
    if ($terms_rows > 1) {
      guarantee_labels_snapshot_log('terms', $products_id, array('more than one attachment of type garan_terms for language '.(int)$languages_id));
      return false;
    }

    $terms = xtc_db_fetch_array($terms_query);

    // The attachment administration restricts a document to customer groups. Archiving one the
    // buyer may not see would attach it to the confirmation of a group that is excluded from it.
    if (!guarantee_labels_terms_visible($terms['group_ids'], $customers_status)) {
      guarantee_labels_snapshot_log('terms', $products_id, array('the attached document is not visible to the customer group of the order'));
      return false;
    }

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
    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_archive.php');

    // one rule for writing and reading, it lives with the archive that has to honour it
    $archive = new guarantee_labels_archive();

    return ($archive->usable_filename($file) === true) ? (string)$file : false;
  }

  /**
   * Writes the GARAN snapshot of one order position.
   *
   * @param int $orders_id
   * @param int $orders_products_id the row that was just written to orders_products
   * @param array $product the article data with duration, model identifier and manufacturer
   * @param int $languages_id the language of the order, it selects the guarantee conditions
   * @param mixed $customers_status the group of the customer, null uses the session
   * @param string $uprid the cart id of the position, it decides for a chosen combination
   * @return bool false when the article carries no complete GARAN data
   */
  function guarantee_labels_product_snapshot($orders_id, $orders_products_id, $product, $languages_id, $customers_status = null, $uprid = '') {
    require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');

    $orders_id = (int)$orders_id;
    $orders_products_id = (int)$orders_products_id;

    if ($orders_id < 1 || $orders_products_id < 1 || !guarantee_labels_active($customers_status)) {
      return false;
    }

    // the same question every read path asks, so a missing table does not end in archived files
    // that no row will ever point at
    if (!guarantee_labels_snapshot_table(TABLE_ORDERS_PRODUCTS_GUARANTEE)) {
      guarantee_labels_snapshot_log('garan', $orders_id, array('table '.TABLE_ORDERS_PRODUCTS_GUARANTEE.' is missing'));
      return false;
    }

    if (!guarantee_labels_candidate($product, $uprid)) {
      return false;
    }

    $names = guarantee_labels_manufacturer_names(array($product['manufacturers_id']));
    $label = guarantee_labels_product_label($product, $names, $uprid);


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
    $terms = guarantee_labels_terms_snapshot(isset($product['products_id']) ? $product['products_id'] : 0, $languages_id, $customers_status);

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

    // kept for the administration as well: an admin action must not fail into the log alone
    guarantee_labels_snapshot_failures($errors);
  }

  /**
   * Collects the failures of the current request so an admin action can report them.
   *
   * A snapshot that does not apply is no error: an article without GARAN data, a b2b group or a
   * language the shop does not keep simply produce no row. Only a broken language, renderer or
   * archive gets here, and only those belong in front of the merchant.
   *
   * @param mixed $errors messages to remember, null returns and clears what was collected
   * @return array
   */
  function guarantee_labels_snapshot_failures($errors = null) {
    static $failures = array();

    if ($errors === null) {
      $collected = $failures;
      $failures = array();

      return $collected;
    }

    foreach ((array)$errors as $error) {
      $failures[] = $error;
    }

    return $failures;
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
  function guarantee_labels_product_snapshot_delete($orders_id, $orders_products_id) {
    $orders_id = (int)$orders_id;
    $orders_products_id = (int)$orders_products_id;

    if ($orders_id < 1 || $orders_products_id < 1 || !guarantee_labels_snapshot_table(TABLE_ORDERS_PRODUCTS_GUARANTEE)) {
      return;
    }

    // Both come from the request. orders_product_delete() removes the position itself with both
    // columns, this hook runs before it and has to be just as narrow: a mismatched pair would
    // otherwise take the snapshot of a different order while its position stays.
    xtc_db_query("DELETE FROM ".TABLE_ORDERS_PRODUCTS_GUARANTEE."
                        WHERE orders_id = '".$orders_id."'
                          AND orders_products_id = '".$orders_products_id."'");
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
    require_once(DIR_FS_INC.'guarantee_labels_validate_product.inc.php');

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

    $errors = array_merge($errors, guarantee_labels_validate_texts($renderer, $name, $model, ERROR_GUARANTEE_LABELS_SNAPSHOT_MANUFACTURER));

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
   * The label the entered values would produce. The mask shows what is about to be saved, not
   * what is stored, otherwise the preview would confirm a change that was never made.
   *
   * Rendering fills the cache under the resulting hash. That is the same directory a later save
   * archives from, so nothing is written twice and nothing wrong can survive: the directory name
   * follows from the values.
   *
   * @param array $values manufacturers_name, manufacturers_model, garan_duration
   * @param string $language the language of the order, for the texts around the graphic
   * @return string markup, empty when the values do not produce a label
   */
  function guarantee_labels_preview_label($values, $language) {
    require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');
    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_renderer.php');

    $checked = guarantee_labels_validate_snapshot($values);

    if ($checked['values'] === false) {
      return '';
    }

    if (!guarantee_labels_language($language)) {
      return '';
    }

    $renderer = new guarantee_labels_renderer();
    $label = $renderer->label($checked['values']['manufacturers_name'],
                              $checked['values']['manufacturers_model'],
                              $checked['values']['garan_duration']);

    if ($label === false) {
      return '';
    }

    $label['duration'] = $renderer->duration_text($checked['values']['garan_duration']);
    $label['manufacturer'] = $checked['values']['manufacturers_name'];
    $label['model'] = $checked['values']['manufacturers_model'];

    return guarantee_labels_markup($label);
  }

  /**
   * Whether a position really belongs to the order it is edited under.
   *
   * Order and position both come from the request. Without this check a prepared call could
   * change the snapshot of a different order and log the change in the wrong history.
   *
   * @param int $orders_id
   * @param int $orders_products_id
   * @return bool
   */
  function guarantee_labels_order_position($orders_id, $orders_products_id) {
    $position_query = xtc_db_query("SELECT orders_products_id
                                      FROM ".TABLE_ORDERS_PRODUCTS."
                                     WHERE orders_id = '".(int)$orders_id."'
                                       AND orders_products_id = '".(int)$orders_products_id."'");

    return (xtc_db_num_rows($position_query) > 0);
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

    if ($orders_id < 1 || $orders_products_id < 1 || !guarantee_labels_order_position($orders_id, $orders_products_id)) {
      return array(ERROR_GUARANTEE_LABELS_SNAPSHOT_UNKNOWN);
    }

    // a removal stays possible, it is how a snapshot of a position that turned digital is cleared
    if ($values === false) {
      xtc_db_query("DELETE FROM ".TABLE_ORDERS_PRODUCTS_GUARANTEE."
                          WHERE orders_id = '".$orders_id."'
                            AND orders_products_id = '".$orders_products_id."'");
      return $errors;
    }

    require_once(DIR_FS_INC.'guarantee_labels_order.inc.php');

    // the same answer the order views use, so nothing stays visible that cannot be saved
    if (!guarantee_labels_order_position_goods($orders_id, $orders_products_id)) {
      return array(ERROR_GUARANTEE_LABELS_SNAPSHOT_VIRTUAL);
    }

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_renderer.php');
    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_archive.php');

    $renderer = new guarantee_labels_renderer();
    $label = $renderer->label($values['manufacturers_name'], $values['manufacturers_model'], $values['garan_duration']);

    if ($label === false) {
      return array(sprintf(ERROR_GUARANTEE_LABELS_SNAPSHOT_RENDER, implode(', ', $renderer->get_errors())));
    }

    // a failing cache write does not stop the label, but it must not stay in the log alone
    if ($renderer->has_errors()) {
      guarantee_labels_snapshot_log('render edit', $orders_id, $renderer->get_errors());
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
                                     WHERE orders_id = '".$orders_id."'
                                       AND orders_products_id = '".$orders_products_id."'");

    if (xtc_db_num_rows($existing_query) > 0) {
      xtc_db_perform(TABLE_ORDERS_PRODUCTS_GUARANTEE, $sql_data_array, 'update', "orders_id = '".$orders_id."' AND orders_products_id = '".$orders_products_id."'");
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
   * Whether an uploaded file may become archived guarantee conditions.
   *
   * The shop keeps its accepted types in one list, so an admin who extends it is respected here
   * as well. The same six lists the content manager merges are used, otherwise a file that can
   * be attached to an article could not be uploaded as its replacement in the order. Name and
   * content have to agree, the same rule the upload class applies.
   *
   * @param string $filename the checked name of the upload
   * @param string $file the temporary file
   * @return bool
   */
  function guarantee_labels_terms_accepted($filename, $file) {
    // without the list nothing is accepted, a missing check would be worse than a refused upload
    if (!defined('DIR_FS_ADMIN') || !is_file(DIR_FS_ADMIN.'includes/upload_types.php')) {
      guarantee_labels_snapshot_log('terms upload', $filename, array('upload_types.php not readable'));
      return false;
    }

    require(DIR_FS_ADMIN.'includes/upload_types.php');

    // exactly the six lists the content manager merges for an article file, so nothing that can
    // be attached to an article is refused as a replacement in the order
    $extensions = array_merge($accepted_image_extensions, $accepted_file_extensions,
                              $accepted_extfile_extensions, $accepted_audio_extensions,
                              $accepted_movie_extensions, $accepted_compressed_extensions);
    $mime_types = array_merge($accepted_image_mime_types, $accepted_file_mime_types,
                              $accepted_extfile_mime_types, $accepted_audio_mime_types,
                              $accepted_movie_mime_types, $accepted_compressed_mime_types);

    if (!is_file($file)) {
      guarantee_labels_snapshot_log('terms upload', $filename, array('file not readable'));
      return false;
    }

    $extension = strtolower(substr((string)$filename, strrpos((string)$filename, '.') + 1));
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = strtolower((string)$finfo->file($file));

    if (!in_array($extension, $extensions, true) || !in_array($mime_type, $mime_types, true)) {
      guarantee_labels_snapshot_log('terms upload', $filename, array('type not accepted: '.$extension.', '.$mime_type));
      return false;
    }

    return true;
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

    if (guarantee_labels_terms_accepted($filename, $_FILES[$field]['tmp_name']) === false) {
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
