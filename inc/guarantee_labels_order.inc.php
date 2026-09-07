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
  require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');

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
    $snapshots = guarantee_labels_order_snapshots($orders_id);

    // no snapshot, nothing to classify
    if (count($snapshots) < 1) {
      return $orders[$orders_id];
    }

    foreach ($snapshots as $orders_products_id => $snapshot) {
      if (guarantee_labels_order_position_goods($orders_id, $orders_products_id)) {
        $orders[$orders_id][$orders_products_id] = $snapshot;
      }
    }

    return $orders[$orders_id];
  }

  /**
   * Whether one position of an order counts as goods and may carry a guarantee.
   *
   * The one place that answers this, for the order views as well as for the administration.
   * Reading and writing must not disagree: a position the views hide would otherwise stay
   * editable, and a position they show could become unsavable.
   *
   * @param int $orders_id
   * @param int $orders_products_id
   * @return bool
   */
  function guarantee_labels_order_position_goods($orders_id, $orders_products_id) {
    $orders_products_id = (int)$orders_products_id;
    $types = guarantee_labels_order_position_types($orders_id);

    // There are no foreign keys, so an outside delete can leave a snapshot whose position is
    // gone. It is not shown and its document never reaches a mail.
    if (!isset($types[$orders_products_id])) {
      return false;
    }

    // A manually created order is classified by its positions, and it keeps being classified
    // while it is edited: adding a download attribute makes a position digital and takes its
    // guarantee away.
    if (guarantee_labels_order_by_position($orders_id)) {
      return ($types[$orders_products_id] === true);
    }

    $content_type = guarantee_labels_order_content_type($orders_id);
    $snapshots = guarantee_labels_order_snapshots($orders_id);

    // An existing snapshot of a checkout order vouches for itself: it was written when the
    // order was placed, with the attributes and the settings of that day. Only the order wide
    // classification may still take it away, otherwise switching
    // DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED would rewrite old orders.
    if (isset($snapshots[$orders_products_id])) {
      return guarantee_labels_physical($content_type);
    }

    // A snapshot that does not exist yet has no history to protect. Nothing vouches for the
    // position, so it has to answer itself: orders.content_type says mixed for the whole order
    // and would otherwise let a purely digital position be given a guarantee by hand.
    return guarantee_labels_physical($content_type) && ($types[$orders_products_id] === true);
  }

  /**
   * Whether the positions of an order decide its content, instead of orders.content_type.
   *
   * A checkout order carries its classification in orders.content_type. It was made when the
   * order was placed, with the attributes the customer chose and the settings of that day, and
   * it must not be recomputed later: DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED can be switched at
   * any time and would otherwise rewrite the history of old orders.
   *
   * A manually created order has the column empty, because admin/customers.php does not fill
   * it. Only there the positions answer, and they keep answering while the order is edited.
   *
   * @param int $orders_id
   * @return bool
   */
  function guarantee_labels_order_by_position($orders_id) {
    return (guarantee_labels_order_content_type($orders_id) === '');
  }

  /**
   * The stored content type of an order, empty when it was created manually.
   *
   * @param int $orders_id
   * @return string
   */
  function guarantee_labels_order_content_type($orders_id) {
    static $orders = array();

    $orders_id = (int)$orders_id;

    if (isset($orders[$orders_id])) {
      return $orders[$orders_id];
    }

    $orders[$orders_id] = '';

    if ($orders_id < 1) {
      return '';
    }

    $order_query = xtc_db_query("SELECT content_type
                                   FROM ".TABLE_ORDERS."
                                  WHERE orders_id = '".$orders_id."'");

    if (xtc_db_num_rows($order_query) > 0) {
      $order = xtc_db_fetch_array($order_query);
      $orders[$orders_id] = isset($order['content_type']) ? trim((string)$order['content_type']) : '';
    }

    return $orders[$orders_id];
  }

  /**
   * Which positions of an order count as goods, keyed by orders_products_id.
   *
   * Only order data is asked. The catalogue would answer for today and let a deleted article
   * or a changed attribute reclassify an old order, which is exactly what a snapshot rules out.
   * The rule follows shopping_cart::get_content_type().
   *
   * @param int $orders_id
   * @return array orders_products_id => bool
   */
  function guarantee_labels_order_position_types($orders_id) {
    static $orders = array();

    $orders_id = (int)$orders_id;

    if (isset($orders[$orders_id])) {
      return $orders[$orders_id];
    }

    $orders[$orders_id] = array();

    if ($orders_id < 1) {
      return $orders[$orders_id];
    }

    // shopping_cart::get_content_type() asks nothing when downloads are off, every position is
    // goods then. A leftover download row of a shop that switched them off must not take the
    // guarantee away.
    $downloads_on = (defined('DOWNLOAD_ENABLED') && DOWNLOAD_ENABLED == 'true');
    $multiple = (defined('DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED') && DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED == 'true');

    // Counted per position, not joined: a position with a download attribute next to a physical
    // one would look purely digital to a join, although the shop counts it as mixed.
    $products_query = xtc_db_query("SELECT op.orders_products_id,
                                           (SELECT COUNT(*)
                                              FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES." opa
                                             WHERE opa.orders_products_id = op.orders_products_id) AS attributes,
                                           (SELECT COUNT(*)
                                              FROM ".TABLE_ORDERS_PRODUCTS_DOWNLOAD." opd
                                             WHERE opd.orders_products_id = op.orders_products_id) AS downloads
                                      FROM ".TABLE_ORDERS_PRODUCTS." op
                                     WHERE op.orders_id = '".$orders_id."'");

    while ($product = xtc_db_fetch_array($products_query)) {
      $downloads = (int)$product['downloads'];

      // no download at all makes the position goods in either setting, otherwise a chosen
      // download makes it digital unless a physical attribute stands next to it
      $orders[$orders_id][(int)$product['orders_products_id']] = !$downloads_on
                                                              || ($downloads < 1)
                                                              || (!$multiple && (int)$product['attributes'] > $downloads);
    }

    return $orders[$orders_id];
  }

  /**
   * The stored GARAN rows of an order, keyed by orders_products_id, unfiltered.
   *
   * The history baseline of the administration reads here, everything that is shown to a
   * customer reads through guarantee_labels_order_products().
   *
   * @param int $orders_id
   * @return array empty when the order carries no snapshot
   */
  function guarantee_labels_order_snapshots($orders_id) {
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
   * The notice concerns goods, so an order without any is left without it. That is decided
   * here and not when the snapshot was written, and it holds for the mail and for the order
   * view of the customer alike.
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

    if ($orders_id < 1
        || !guarantee_labels_snapshot_table(TABLE_ORDERS_GUARANTEE)
        || !guarantee_labels_order_physical($orders_id)
        )
    {
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
      guarantee_labels_snapshot_log('notice read', $orders_id,
                                    $archive->has_errors() ? $archive->get_errors() : array('archived language state is missing'));
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
      // archives written before the heading was stored fall back to the language file
      'title' => isset($data['title']) ? $data['title'] : '',
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
  /**
   * The archived guarantee conditions of one position, verified against their hash.
   *
   * Mail text and attachment list both ask here. Asked separately, the text could name a
   * document the attachment step then drops as damaged, and the mail would claim to carry
   * something it does not.
   *
   * @param array $product one row of guarantee_labels_order_products()
   * @param int $orders_id only for the log entry
   * @return string absolute path, empty when there is none or it is damaged
   */
  function guarantee_labels_terms_file($product, $orders_id = 0) {
    static $checked = array();

    if (!isset($product['terms_hash'], $product['terms_filename'])
        || $product['terms_hash'] === null
        || $product['terms_filename'] === null
        )
    {
      return '';
    }

    $key = $product['terms_hash'].'/'.$product['terms_filename'];

    if (isset($checked[$key])) {
      return $checked[$key];
    }

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_archive.php');

    $archive = new guarantee_labels_archive();
    $file = $archive->terms_path($product['terms_hash'], $product['terms_filename']);

    // a replaced or truncated archive file would attach the wrong conditions to the mail
    if (!is_file($file) || hash_file('sha256', $file) !== $product['terms_hash']) {
      guarantee_labels_snapshot_log('terms read', $orders_id, array('archived guarantee conditions do not match '.$product['terms_hash']));
      $checked[$key] = '';
      return '';
    }

    $checked[$key] = $file;

    return $file;
  }

  function guarantee_labels_order_terms($orders_id) {
    $attachments = array();
    $products = guarantee_labels_order_products($orders_id);

    if (count($products) < 1) {
      return $attachments;
    }

    foreach ($products as $product) {
      $file = guarantee_labels_terms_file($product, $orders_id);

      // the same conditions may belong to more than one position of the order
      if ($file !== '' && !in_array($file, $attachments, true)) {
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

    // The values are historical either way. Only the sentence around them comes from the
    // language file, so a customer browsing in another language gets the wording of that
    // language instead of no guarantee line at all.
    guarantee_labels_language(guarantee_labels_order_language($orders_id));

    if (!guarantee_labels_texts_ready(array('TEXT_GUARANTEE_ORDER_LABEL'))) {
      return false;
    }

    $product = $products[$orders_products_id];

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_renderer.php');

    $renderer = new guarantee_labels_renderer();

    $duration = $renderer->duration_text($product['garan_duration']);

    // Both variants are built from the plain values instead of converting one into the other.
    // A manufacturer name or a file name may carry angle brackets, and the html variant is
    // printed unescaped by the order views and the mail template.
    $label = array(
      'html' => sprintf(TEXT_GUARANTEE_ORDER_LABEL,
                        encode_htmlspecialchars($duration),
                        encode_htmlspecialchars($product['manufacturers_name']),
                        encode_htmlspecialchars($product['manufacturers_model'])),
      'txt' => sprintf(decode_htmlentities(TEXT_GUARANTEE_ORDER_LABEL),
                       $duration,
                       $product['manufacturers_name'],
                       $product['manufacturers_model']),
    );

    $terms = array('html' => '', 'txt' => '');

    // named only where the file really travels along
    if (defined('TEXT_GUARANTEE_ORDER_TERMS')
        && guarantee_labels_terms_file($product, $orders_id) !== ''
        )
    {
      $terms = array(
        'html' => sprintf(TEXT_GUARANTEE_ORDER_TERMS, encode_htmlspecialchars($product['terms_filename'])),
        'txt' => sprintf(decode_htmlentities(TEXT_GUARANTEE_ORDER_TERMS), $product['terms_filename']),
      );
    }

    return array('label' => $label, 'terms' => $terms);
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

    if (!guarantee_labels_order_active()) {
      return '';
    }

    $products = guarantee_labels_order_products($orders_id);
    $orders_products_id = (int)$orders_products_id;

    if (!isset($products[$orders_products_id])) {
      return '';
    }

    // The administration does not load the storefront texts by itself, so loading is attempted
    // here. It may fail because the customer browses in another language than the order was
    // placed in; the label is language neutral and falls back to its own wording then.
    guarantee_labels_language(guarantee_labels_order_language($orders_id));

    $product = $products[$orders_products_id];

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_archive.php');

    $archive = new guarantee_labels_archive();
    $files = $archive->garan_read($product['garan_hash']);

    // without the archived graphic nothing is drawn, a current one would show other values
    if ($files === false) {
      guarantee_labels_snapshot_log('garan read', $orders_id,
                                    $archive->has_errors() ? $archive->get_errors() : array('archived label is missing: '.$product['garan_hash']));
      return '';
    }

    // The archive is closed to http, the lazy view fetches the full graphic from the cache. A
    // failing copy leaves the overlay without its graphic, so it does not stay silent either.
    if (guarantee_labels_cache_url($product['garan_hash']) === ''
        && $archive->cache_write($product['garan_hash'], $files) === false
        )
    {
      guarantee_labels_snapshot_log('garan cache', $orders_id, $archive->get_errors());
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

    if (!guarantee_labels_order_active()) {
      return false;
    }

    $notice = guarantee_labels_order_notice($orders_id);

    if ($notice === false) {
      return false;
    }

    // Text, link, address and heading are historical and come from the archive. Only the
    // controls of the block are read from the language file, so a mismatch costs the wording
    // around the notice and never the notice itself.
    guarantee_labels_language(guarantee_labels_order_language($orders_id));

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_archive.php');

    $archive = new guarantee_labels_archive();
    $source = guarantee_labels_notice_cache_url($notice['hash']);

    if ($source === '') {
      $files = $archive->notice_read($notice['hash']);

      if ($files === false) {
        guarantee_labels_snapshot_log('notice read', $orders_id,
                                      $archive->has_errors() ? $archive->get_errors() : array('archived notice is missing: '.$notice['hash']));
        return false;
      }

      if ($archive->cache_write($notice['hash'], array('notice.svg' => $files['notice.svg'])) === false) {
        guarantee_labels_snapshot_log('notice cache', $orders_id, $archive->get_errors());
      }

      // The archived text and link are read, only the copy into the cache failed. They go out
      // either way; guarantee_labels_notice_block() leaves out the graphic and its button.
      $source = guarantee_labels_notice_cache_url($notice['hash']);
    }

    $link = '<a class="guarantee-notice__link" href="'.guarantee_labels_attribute($notice['url']).'" target="_blank" rel="noopener">'.$notice['link'].'</a>';

    $title = ($notice['title'] !== '')
           ? $notice['title']
           : guarantee_labels_text('TEXT_GUARANTEE_NOTICE_TITLE', 'GARAN');

    return guarantee_labels_notice_block($title,
                                         $notice['text'],
                                         $link,
                                         '',
                                         encode_htmlspecialchars($source),
                                         guarantee_labels_attribute(guarantee_labels_text('TEXT_GUARANTEE_NOTICE_ALT', $title)));
  }

  /**
   * Whether an order contains goods at all.
   *
   * Asked of the order itself, not of the catalogue: a position counts as digital when the
   * order carries a download for it. An order without positions has no goods either, which is
   * the state a manually created order starts in.
   *
   * orders.content_type decides whenever the shop filled it, because the checkout classifies a
   * position by its selected attributes and can call a single position mixed. The module never
   * writes that column; a manually created order leaves it empty and is asked position by
   * position instead.
   *
   * @param int $orders_id
   * @return bool
   */
  /**
   * The article behind one position of one order. The order editing must not trust a products id
   * from the request: it decides which article the values are taken from and which guarantee
   * conditions are archived into the order.
   *
   * @param int $orders_id
   * @param int $orders_products_id
   * @return int zero when the position does not belong to this order
   */
  function guarantee_labels_order_position_product($orders_id, $orders_products_id) {
    $products_query = xtc_db_query("SELECT products_id
                                      FROM ".TABLE_ORDERS_PRODUCTS."
                                     WHERE orders_id = '".(int)$orders_id."'
                                       AND orders_products_id = '".(int)$orders_products_id."'");

    if (xtc_db_num_rows($products_query) < 1) {
      return 0;
    }

    $product = xtc_db_fetch_array($products_query);

    return (int)$product['products_id'];
  }

  /**
   * The language of the order as an id. It selects the guarantee conditions and may differ from
   * the language of the backend session.
   *
   * @param int $orders_id
   * @return int
   */
  /**
   * The language code of an order, for values that carry one variant per language.
   *
   * Derived from orders.language the same way xtc_php_mail() does it, so a value resolved here
   * matches the mail that is about to be sent.
   *
   * @param int $orders_id
   * @return string empty when the language is unknown
   */
  function guarantee_labels_order_language_code($orders_id) {
    static $orders = array();

    $orders_id = (int)$orders_id;

    if (isset($orders[$orders_id])) {
      return $orders[$orders_id];
    }

    $orders[$orders_id] = '';
    $language = guarantee_labels_order_language($orders_id);

    if ($language === '') {
      return '';
    }

    $code_query = xtc_db_query("SELECT code
                                  FROM ".TABLE_LANGUAGES."
                                 WHERE directory = '".xtc_db_input($language)."'");

    if (xtc_db_num_rows($code_query) > 0) {
      $row = xtc_db_fetch_array($code_query);
      $orders[$orders_id] = (string)$row['code'];
    }

    return $orders[$orders_id];
  }

  function guarantee_labels_order_language_id($orders_id) {
    // Derived from orders.language, which is the language of the order. orders_address_edit()
    // changes only that column when the administration switches the language, so the stored
    // languages_id can point at the language the order was created in and would then pick the
    // guarantee conditions of the wrong one.
    $language = guarantee_labels_order_language($orders_id);

    if ($language === '') {
      return 0;
    }

    $language_query = xtc_db_query("SELECT languages_id
                                      FROM ".TABLE_LANGUAGES."
                                     WHERE directory = '".xtc_db_input($language)."'");

    if (xtc_db_num_rows($language_query) < 1) {
      return 0;
    }

    $row = xtc_db_fetch_array($language_query);

    return (int)$row['languages_id'];
  }

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

    // the checkout already decided, and it knew about attributes this query cannot see
    if (!guarantee_labels_order_by_position($orders_id)) {
      $orders[$orders_id] = guarantee_labels_physical(guarantee_labels_order_content_type($orders_id));

      return $orders[$orders_id];
    }

    $orders[$orders_id] = in_array(true, guarantee_labels_order_position_types($orders_id), true);

    return $orders[$orders_id];
  }
