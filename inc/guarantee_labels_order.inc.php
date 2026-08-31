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
   * Reads the historical GARAN data of an order.
   *
   * Everything here comes from the module tables and the archive, never from the catalogue.
   * A first confirmation, a repeated mail and the order view in the administration therefore
   * show the same values, no matter what happened to the article in the meantime.
   */

  require_once(DIR_FS_INC.'guarantee_labels_snapshot.inc.php');

  /**
   * The snapshots of all positions of one order, keyed by orders_products_id.
   *
   * One query per order instead of one per position, and the result is kept for the request.
   *
   * @param int $orders_id
   * @return array empty when the order carries no snapshot
   */
  function guarantee_labels_order_products($orders_id) {
    static $orders = array();

    $orders_id = (int)$orders_id;

    if (isset($orders[$orders_id])) {
      return $orders[$orders_id];
    }

    $orders[$orders_id] = array();

    if ($orders_id < 1 || !guarantee_labels_snapshot_table(TABLE_ORDERS_PRODUCTS_GUARANTEE)) {
      return $orders[$orders_id];
    }

    $guarantee_query = xtc_db_query("SELECT orders_products_id,
                                            manufacturers_name,
                                            manufacturers_model,
                                            garan_duration,
                                            garan_hash,
                                            terms_hash,
                                            terms_filename
                                       FROM ".TABLE_ORDERS_PRODUCTS_GUARANTEE."
                                      WHERE orders_id = '".$orders_id."'");

    while ($guarantee = xtc_db_fetch_array($guarantee_query)) {
      $orders[$orders_id][(int)$guarantee['orders_products_id']] = $guarantee;
    }

    return $orders[$orders_id];
  }

  /**
   * The historical notice of one order, taken from the archived language state.
   *
   * @param int $orders_id
   * @return mixed array of text, link and url, false without a snapshot
   */
  function guarantee_labels_order_notice($orders_id) {
    static $orders = array();

    $orders_id = (int)$orders_id;

    if (array_key_exists($orders_id, $orders)) {
      return $orders[$orders_id];
    }

    $orders[$orders_id] = false;

    if ($orders_id < 1 || !guarantee_labels_snapshot_table(TABLE_ORDERS_GUARANTEE)) {
      return false;
    }

    $notice_query = xtc_db_query("SELECT notice_hash
                                    FROM ".TABLE_ORDERS_GUARANTEE."
                                   WHERE orders_id = '".$orders_id."'");

    if (xtc_db_num_rows($notice_query) < 1) {
      return false;
    }

    $notice = xtc_db_fetch_array($notice_query);

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_archive.php');

    $archive = new guarantee_labels_archive();
    $files = $archive->notice_read($notice['notice_hash']);

    // a missing archive means the historical wording is gone, a current one must not stand in
    if ($files === false || !isset($files['notice.json'])) {
      guarantee_labels_snapshot_log('notice read', $orders_id, array('archived language state is missing'));
      return false;
    }

    $data = json_decode($files['notice.json'], true);

    if (!is_array($data) || !isset($data['text'], $data['link'], $data['url'])) {
      guarantee_labels_snapshot_log('notice read', $orders_id, array('archived language state cannot be read'));
      return false;
    }

    $orders[$orders_id] = array(
      'hash' => $notice['notice_hash'],
      'text' => $data['text'],
      'link' => $data['link'],
      'url' => $data['url'],
    );

    return $orders[$orders_id];
  }

  /**
   * The archived guarantee conditions of an order as absolute file paths.
   *
   * check_attachments() prefixes DIR_FS_DOCUMENT_ROOT only when the path does not carry it
   * already, so absolute paths pass through untouched.
   *
   * @param int $orders_id
   * @return array
   */
  function guarantee_labels_order_terms($orders_id) {
    $attachments = array();
    $products = guarantee_labels_order_products($orders_id);

    if (count($products) < 1) {
      return $attachments;
    }

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_archive.php');

    $archive = new guarantee_labels_archive();

    foreach ($products as $product) {
      if ($product['terms_hash'] === null || $product['terms_filename'] === null) {
        continue;
      }

      $file = $archive->terms_path($product['terms_hash'], $product['terms_filename']);

      // the same conditions may belong to more than one position of the order
      if (is_file($file) && !in_array($file, $attachments, true)) {
        $attachments[] = $file;
      }
    }

    return $attachments;
  }

  /**
   * The language directory an order was placed in.
   *
   * getOrderData() only passes the language id, the storefront texts are kept per directory.
   *
   * @param int $orders_id
   * @return string empty when the order is unknown
   */
  function guarantee_labels_order_language($orders_id) {
    static $orders = array();

    $orders_id = (int)$orders_id;

    if (isset($orders[$orders_id])) {
      return $orders[$orders_id];
    }

    $orders[$orders_id] = '';

    if ($orders_id > 0) {
      $language_query = xtc_db_query("SELECT language
                                        FROM ".TABLE_ORDERS."
                                       WHERE orders_id = '".$orders_id."'");

      if (xtc_db_num_rows($language_query) > 0) {
        $language = xtc_db_fetch_array($language_query);
        $orders[$orders_id] = (string)$language['language'];
      }
    }

    return $orders[$orders_id];
  }

  /**
   * The guarantee of one order position in words.
   *
   * The label itself is a picture mark with strict rules and is never redrawn, neither as a
   * graphic nor as a copy of its layout. What is written out is the promise behind it, so the
   * customer can tell which article the attached conditions belong to.
   *
   * The guarantee and the attached conditions are kept apart: the order confirmation names
   * the file because it travels with it, the order view in the administration does not.
   *
   * @param int $orders_id
   * @param int $orders_products_id
   * @return mixed array of label and terms, each with an html and a text variant, false
   *         without a snapshot
   */
  function guarantee_labels_order_text($orders_id, $orders_products_id) {
    $products = guarantee_labels_order_products($orders_id);
    $orders_products_id = (int)$orders_products_id;

    if (!isset($products[$orders_products_id])) {
      return false;
    }

    if (!guarantee_labels_language(guarantee_labels_order_language($orders_id))
        || !guarantee_labels_texts_ready(array('TEXT_GUARANTEE_ORDER_LABEL'))
        )
    {
      return false;
    }

    $product = $products[$orders_products_id];

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_renderer.php');

    $renderer = new guarantee_labels_renderer();

    $label = sprintf(TEXT_GUARANTEE_ORDER_LABEL,
                     $renderer->duration_text($product['garan_duration']),
                     $product['manufacturers_name'],
                     $product['manufacturers_model']);

    $terms = '';

    // named only where the file really travels along
    if ($product['terms_filename'] !== null
        && $product['terms_hash'] !== null
        && defined('TEXT_GUARANTEE_ORDER_TERMS')
        )
    {
      require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_archive.php');

      $archive = new guarantee_labels_archive();

      if (is_file($archive->terms_path($product['terms_hash'], $product['terms_filename']))) {
        $terms = sprintf(TEXT_GUARANTEE_ORDER_TERMS, $product['terms_filename']);
      }
    }

    return array(
      'label' => array('html' => $label, 'txt' => decode_htmlentities($label)),
      'terms' => array('html' => $terms, 'txt' => ($terms === '') ? '' : decode_htmlentities($terms)),
    );
  }

  /**
   * The GARAN label of one order position, built from its archived files.
   *
   * The catalogue is never asked: an order shows the graphic it was placed with, even after
   * the article changed or disappeared. The archive is the source, the cache the copy the
   * browser can reach, so a missing cache entry is rebuilt from the archive here.
   *
   * @param int $orders_id
   * @param int $orders_products_id
   * @return string empty without a snapshot or without archived files
   */
  function guarantee_labels_order_label($orders_id, $orders_products_id) {
    require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');

    if (!guarantee_labels_active()) {
      return '';
    }

    $products = guarantee_labels_order_products($orders_id);
    $orders_products_id = (int)$orders_products_id;

    if (!isset($products[$orders_products_id])) {
      return '';
    }

    // the markup needs the storefront texts, which the administration does not load by itself
    if (!guarantee_labels_language(guarantee_labels_order_language($orders_id))) {
      return '';
    }

    $product = $products[$orders_products_id];

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_archive.php');

    $archive = new guarantee_labels_archive();
    $files = $archive->garan_read($product['garan_hash']);

    // without the archived graphic nothing is drawn, a current one would show other values
    if ($files === false) {
      return '';
    }

    // the archive is closed to http, the lazy view fetches the full graphic from the cache
    if (guarantee_labels_cache_url($product['garan_hash']) === '') {
      $archive->cache_write($product['garan_hash'], $files);
    }

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_renderer.php');

    $renderer = new guarantee_labels_renderer();

    return guarantee_labels_markup(array_merge($files, array(
      'hash' => $product['garan_hash'],
      'manufacturer' => $product['manufacturers_name'],
      'model' => $product['manufacturers_model'],
      'duration' => $renderer->duration_text($product['garan_duration']),
    )));
  }

  /**
   * The historical guarantee notice of one order, ready for a template.
   *
   * Wording and graphic come from the archive of the order, so a customer looking at an old
   * order sees the notice that belonged to it, not the one the shop shows today. The archive
   * is closed to http, therefore the graphic is copied into the cache on demand, the same way
   * the label does it.
   *
   * @param int $orders_id
   * @return mixed array of title and body, false without a snapshot
   */
  function guarantee_labels_order_notice_parts($orders_id) {
    require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');

    if (!guarantee_labels_active()) {
      return false;
    }

    $notice = guarantee_labels_order_notice($orders_id);

    if ($notice === false) {
      return false;
    }

    // the labels of the block itself follow the language of the order
    if (!guarantee_labels_language(guarantee_labels_order_language($orders_id))
        || !guarantee_labels_texts_ready(array('TEXT_GUARANTEE_NOTICE_TITLE', 'TEXT_GUARANTEE_NOTICE_OPEN',
                                               'TEXT_GUARANTEE_NOTICE_ALT', 'TEXT_GUARANTEE_LABEL_CLOSE',
                                               'TEXT_GUARANTEE_LABEL_RELOAD'))
        )
    {
      return false;
    }

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_archive.php');

    $archive = new guarantee_labels_archive();
    $source = guarantee_labels_notice_cache_url($notice['hash']);

    if ($source === '') {
      $files = $archive->notice_read($notice['hash']);

      if ($files === false) {
        return false;
      }

      $archive->cache_write($notice['hash'], array('notice.svg' => $files['notice.svg']));
      $source = guarantee_labels_notice_cache_url($notice['hash']);

      if ($source === '') {
        return false;
      }
    }

    $link = '<a class="guarantee-notice__link" href="'.guarantee_labels_attribute($notice['url']).'" target="_blank" rel="noopener">'.$notice['link'].'</a>';

    return guarantee_labels_notice_block(TEXT_GUARANTEE_NOTICE_TITLE,
                                         $notice['text'],
                                         $link,
                                         '',
                                         encode_htmlspecialchars($source),
                                         guarantee_labels_attribute(TEXT_GUARANTEE_NOTICE_ALT));
  }

  /**
   * Whether an order contains goods at all.
   *
   * Asked of the order itself, not of the catalogue: a position counts as digital when the
   * order carries a download for it. An order without positions has no goods either, which is
   * the state a manually created order starts in.
   *
   * orders.content_type is not used. It is written once when the order is created and is not
   * recalculated when the administration adds or removes positions.
   *
   * @param int $orders_id
   * @return bool
   */
  function guarantee_labels_order_physical($orders_id) {
    static $orders = array();

    $orders_id = (int)$orders_id;

    if (isset($orders[$orders_id])) {
      return $orders[$orders_id];
    }

    $orders[$orders_id] = false;

    if ($orders_id < 1) {
      return false;
    }

    $products_query = xtc_db_query("SELECT op.orders_products_id,
                                           opd.orders_products_download_id
                                      FROM ".TABLE_ORDERS_PRODUCTS." op
                                 LEFT JOIN ".TABLE_ORDERS_PRODUCTS_DOWNLOAD." opd
                                           ON opd.orders_products_id = op.orders_products_id
                                     WHERE op.orders_id = '".$orders_id."'");

    while ($product = xtc_db_fetch_array($products_query)) {
      if ($product['orders_products_download_id'] === null) {
        $orders[$orders_id] = true;
        break;
      }
    }

    return $orders[$orders_id];
  }
