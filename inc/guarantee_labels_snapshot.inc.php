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
   * The texts that belong to the notice of one language.
   *
   * The administration loads only lang/<language>/extra/admin/, so the storefront texts are
   * pulled in here when they are missing. Constants live for the whole request, therefore a
   * second language in the same request gets no snapshot instead of the wrong texts.
   *
   * @param string $language the language directory of the order
   * @return mixed array of text, link and url, false when the language is not fully kept
   */
  function guarantee_labels_notice_texts($language) {
    static $loaded;

    $language = trim((string)$language);

    if (!defined('TEXT_GUARANTEE_NOTICE_MAIL')) {
      $file = DIR_FS_CATALOG.'lang/'.$language.'/extra/guarantee_labels.php';

      if (strpbrk($language, "/\\\0") !== false || !is_file($file)) {
        return false;
      }

      require_once($file);
      $loaded = $language;
    } elseif (isset($loaded) && $loaded !== $language) {
      return false;
    }

    $constants = array(
      'text' => 'TEXT_GUARANTEE_NOTICE_MAIL',
      'link' => 'TEXT_GUARANTEE_NOTICE_LINK',
      'url' => 'TEXT_GUARANTEE_NOTICE_URL',
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

    $products_query = xtc_db_query("SELECT products_garan_duration,
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
      return false;
    }

    $source = DIR_FS_CATALOG.'media/products/'.$terms['content_file'];

    if (!is_file($source)) {
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
