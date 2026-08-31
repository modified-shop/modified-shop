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
   * @param int $orders_id
   * @param int $orders_products_id
   * @return mixed array of an html and a text variant, false without a snapshot
   */
  function guarantee_labels_order_text($orders_id, $orders_products_id) {
    $products = guarantee_labels_order_products($orders_id);
    $orders_products_id = (int)$orders_products_id;

    if (!isset($products[$orders_products_id])) {
      return false;
    }

    if (!guarantee_labels_language(guarantee_labels_order_language($orders_id))
        || !defined('TEXT_GUARANTEE_ORDER_LABEL')
        )
    {
      return false;
    }

    $product = $products[$orders_products_id];

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_renderer.php');

    $renderer = new guarantee_labels_renderer();

    $lines = array(sprintf(TEXT_GUARANTEE_ORDER_LABEL,
                           $renderer->duration_text($product['garan_duration']),
                           $product['manufacturers_name'],
                           $product['manufacturers_model']));

    // named only when the file really travels with the mail
    if ($product['terms_filename'] !== null
        && $product['terms_hash'] !== null
        && defined('TEXT_GUARANTEE_ORDER_TERMS')
        )
    {
      require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_archive.php');

      $archive = new guarantee_labels_archive();

      if (is_file($archive->terms_path($product['terms_hash'], $product['terms_filename']))) {
        $lines[] = sprintf(TEXT_GUARANTEE_ORDER_TERMS, $product['terms_filename']);
      }
    }

    return array(
      'html' => implode('<br />', $lines),
      'txt' => implode("\n", array_map('decode_htmlentities', $lines)),
    );
  }
